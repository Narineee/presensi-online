<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Aktivitas;
use App\Models\Magang;
use App\Models\Pekerjaan;
use App\Models\PengajuanIzin;
use App\Models\PengajuanTugasLuar;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Beranda pembimbing: ringkasan binaan, presensi hari ini, dan hal yang perlu ditindaklanjuti.
     */
    public function index(Request $request): View
    {
        $pembimbing = Auth::user()->pembimbing;

        if (! $pembimbing) {
            return view('pembimbing.dashboard', ['pembimbing' => null]);
        }

        $today = Carbon::today();

        $binaan = Magang::where('pembimbing_id', $pembimbing->id)
            ->with(['pengguna', 'penilaian'])
            ->orderBy('nama_lengkap')
            ->get();

        // Binaan aktif: status aktif dan periode magang belum lewat
        $aktif = $binaan->filter(fn ($m) => $m->status === 'aktif'
            && (! $m->tanggal_selesai || $m->tanggal_selesai->gte($today)));

        $aktifIds = $aktif->pluck('pengguna_id')->filter()->values();
        $semuaIds = $binaan->pluck('pengguna_id')->filter()->values();

        // ---- Presensi hari ini (binaan aktif) ----
        $presensiHariIni = Presensi::whereIn('pengguna_id', $aktifIds)
            ->whereDate('tanggal', $today)
            ->with('pengajuanTugasLuar')
            ->get();

        $hari = [
            'hadir' => $presensiHariIni->where('status', 'hadir')->count(),
            'sakit' => $presensiHariIni->where('status', 'sakit')->count(),
            'izin' => $presensiHariIni->where('status', 'izin')->count(),
            'cuti' => $presensiHariIni->where('status', 'cuti')->count(),
            'tl' => $presensiHariIni->filter(fn ($p) => $p->status === 'hadir' && $p->is_tugas_luar)->count(),
        ];

        // ---- Perlu ditindaklanjuti ----
        $aktivitasPending = Aktivitas::whereIn('pengguna_id', $semuaIds)
            ->where('status', 'pending')
            ->with('pengguna.magang')
            ->get();

        $izinPending = PengajuanIzin::whereIn('pengguna_id', $semuaIds)
            ->where('status_approval', 'pending')
            ->with('pengguna.magang')
            ->get();

        $tlMenunggu = PengajuanTugasLuar::whereIn('pengguna_id', $semuaIds)
            ->where('status_verifikasi', 'menunggu')
            ->with('pengguna.magang')
            ->get();

        // Perlu dinilai: belum ada penilaian, dan magang sudah selesai / berakhir dalam 14 hari
        $perluNilai = $binaan->filter(fn ($m) => ! $m->penilaian
            && ($m->status === 'selesai'
                || ($m->tanggal_selesai && $m->tanggal_selesai->lte($today->copy()->addDays(14)))));

        $perluTindak = [
            [
                'jumlah' => $aktivitasPending->count(),
                'judul' => 'Validasi Aktivitas',
                'ringkas' => $this->ringkas($aktivitasPending->map(fn ($a) => $this->namaDepan($a->pengguna?->magang?->nama_lengkap))),
                'url' => route('pembimbing.aktivitas.index', ['status' => 'pending']),
            ],
            [
                'jumlah' => $izinPending->count(),
                'judul' => 'Verifikasi Ketidakhadiran',
                'ringkas' => $this->ringkas($izinPending->map(fn ($i) => ucfirst($i->jenis_izin).' '.$this->namaDepan($i->pengguna?->magang?->nama_lengkap))),
                'url' => route('pembimbing.izin.index', ['status_approval' => 'pending']),
            ],
            [
                'jumlah' => $tlMenunggu->count(),
                'judul' => 'Verifikasi Tugas Luar',
                'ringkas' => $this->ringkas($tlMenunggu->map(fn ($t) => Str::limit((string) $t->tujuan, 22).' - '.$this->namaDepan($t->pengguna?->magang?->nama_lengkap))),
                'url' => route('pembimbing.tugas-luar.index', ['status_verifikasi' => 'menunggu']),
            ],
            [
                'jumlah' => $perluNilai->count(),
                'judul' => 'Penilaian Akhir',
                'ringkas' => $this->ringkas($perluNilai->map(fn ($m) => $this->namaDepan($m->nama_lengkap)), ' dan '),
                'url' => route('pembimbing.penilaian.index', ['status_nilai' => 'belum']),
            ],
        ];

        // ---- Pekerjaan mendekati tenggat (<= 7 hari, termasuk yang sudah lewat) ----
        $pekerjaanDekat = Pekerjaan::where('pembimbing_id', $pembimbing->id)
            ->where('status', 'aktif')
            ->whereNotNull('target_selesai')
            ->whereDate('target_selesai', '<=', $today->copy()->addDays(7))
            ->with('magang')
            ->orderBy('target_selesai')
            ->get()
            ->map(function ($p) use ($today) {
                $tenggat = Carbon::parse($p->target_selesai);

                return [
                    'judul' => $p->judul,
                    'nama' => $this->namaDepan($p->magang?->nama_lengkap),
                    'tenggat' => $tenggat,
                    'progress' => $p->progress,
                    'terlambat' => $tenggat->lt($today),
                ];
            });

        // ---- Sapaan ----
        $gelar = match ($pembimbing->getAttribute('jenis_kelamin')) {
            'P' => 'Bu ',
            'L' => 'Pak ',
            default => '',
        };
        $sapaan = $gelar.$this->namaDepan($pembimbing->nama_lengkap);

        return view('pembimbing.dashboard', [
            'pembimbing' => $pembimbing,
            'sapaan' => $sapaan,
            'jumlahAktif' => $aktif->count(),
            'hari' => $hari,
            'perluTindak' => $perluTindak,
            'pekerjaanDekat' => $pekerjaanDekat,
        ]);
    }

    private function namaDepan(?string $nama): string
    {
        return Str::of((string) $nama)->trim()->before(' ')->toString();
    }

    /**
     * Ringkas daftar nama menjadi satu baris, contoh: "Alya, Hasnia, dll".
     */
    private function ringkas(Collection $daftar, string $pemisah = ', ', int $maks = 2): string
    {
        $unik = $daftar->filter()->unique()->values();

        if ($unik->isEmpty()) {
            return 'Tidak ada yang menunggu';
        }

        $teks = $unik->take($maks)->implode($pemisah);

        return $unik->count() > $maks ? $teks.', dll' : $teks;
    }
}