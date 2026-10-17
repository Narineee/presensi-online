<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\AktivitasDetail;
use App\Models\Divisi;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Models\Pengguna;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AktivitasController extends Controller
{
    /**
     * Menampilkan daftar / rekap aktivitas harian magang.
     */
    public function index(Request $request)
    {
        return $this->riwayat($request);
    }

    /**
     * Menampilkan riwayat / rekap aktivitas harian magang.
     */
    public function riwayat(Request $request)
    {
        /** @var Pengguna $user */
        $user = Auth::user();
        $magang = $user->magang;

        $query = Aktivitas::where('pengguna_id', $user->id)
            ->with(['details.rencanaKerja', 'pekerjaan', 'validator.pembimbing']);

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $status = $request->input('status');
        $q = $request->input('q');

        if ($tanggalAwal && $tanggalSelesai) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalSelesai]);
        } elseif ($tanggalAwal) {
            $query->whereDate('tanggal', '>=', $tanggalAwal);
        } elseif ($tanggalSelesai) {
            $query->whereDate('tanggal', '<=', $tanggalSelesai);
        }

        if ($status && in_array($status, ['approve', 'pending', 'revisi'])) {
            $query->where('status', $status);
        }

        if ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('judul', 'like', "%{$q}%")
                    ->orWhere('isi', 'like', "%{$q}%")
                    ->orWhereHas('details', function ($dq) use ($q) {
                        $dq->where('progres', 'like', "%{$q}%")
                            ->orWhere('rencana_kerja_snapshot', 'like', "%{$q}%")
                            ->orWhereHas('rencanaKerja', function ($rq) use ($q) {
                                $rq->where('judul', 'like', "%{$q}%");
                            });
                    })
                    ->orWhereHas('pekerjaan', function ($pq) use ($q) {
                        $pq->where('judul', 'like', "%{$q}%");
                    });
            });
        }

        $allUserAktivitas = Aktivitas::where('pengguna_id', $user->id)->get();
        $stats = [
            'total' => $allUserAktivitas->count(),
            'approve' => $allUserAktivitas->where('status', 'approve')->count(),
            'pending' => $allUserAktivitas->where('status', 'pending')->count(),
            'revisi' => $allUserAktivitas->where('status', 'revisi')->count(),
        ];

        $aktivitasList = $query->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('aktivitas.riwayat', compact('user', 'magang', 'aktivitasList', 'stats', 'tanggalAwal', 'tanggalSelesai', 'status', 'q'));
    }

    /**
     * Formulir tambah aktivitas harian.
     */
    public function create()
    {
        $user = Auth::user();
        $magang = $user->magang;

        $rencanaKerjaList = $magang
            ? $magang->rencanaKerja()->where('status', 'aktif')->orderBy('judul', 'asc')->get()
            : collect();

        return view('aktivitas.create', compact('rencanaKerjaList'));
    }

    /**
     * Simpan aktivitas harian baru beserta rincian rencana kerja & progres masing-masing.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $magang = $user->magang;
        abort_if(! $magang, 403, 'Akses khusus peserta magang.');

        $allowedRencana = $magang->rencanaKerja()->where('status', 'aktif')->get();
        $allowedRencanaIds = $allowedRencana->pluck('id')->toArray();

        $request->validate([
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.rencana_kerja_id' => ['required', 'in:'.implode(',', $allowedRencanaIds)],
            'items.*.progres' => 'required|string|min:5',
        ], [
            'tanggal.required' => 'Tanggal aktivitas wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'items.required' => 'Minimal pilih 1 rencana kerja untuk dilaporkan.',
            'items.min' => 'Minimal pilih 1 rencana kerja untuk dilaporkan.',
            'items.*.rencana_kerja_id.required' => 'Rencana kerja wajib dipilih.',
            'items.*.rencana_kerja_id.in' => 'Rencana kerja yang dipilih tidak valid atau bukan milik Anda.',
            'items.*.progres.required' => 'Uraian progres pekerjaan wajib diisi.',
            'items.*.progres.min' => 'Uraian progres minimal 5 karakter.',
        ]);

        // Cek duplikasi pemilihan rencana kerja yang sama dalam satu pengisian aktivitas
        $selectedRencanaIds = collect($request->items)->pluck('rencana_kerja_id')->toArray();
        if (count($selectedRencanaIds) !== count(array_unique($selectedRencanaIds))) {
            return redirect()->back()->withInput()->with('error', 'Rencana kerja yang sama tidak boleh dipilih lebih dari satu kali dalam satu tanggal pengisian aktivitas.');
        }

        // Susun ringkasan isi untuk kompatibilitas data & pencarian
        $isiRingkasan = collect($request->items)->map(function ($item) use ($allowedRencana) {
            $rk = $allowedRencana->firstWhere('id', $item['rencana_kerja_id']);
            $judul = $rk ? $rk->judul : 'Rencana Kerja';

            return "• [{$judul}]: ".trim($item['progres']);
        })->implode("\n\n");

        DB::transaction(function () use ($user, $request, $allowedRencana, $isiRingkasan) {
            $aktivitas = Aktivitas::create([
                'pengguna_id' => $user->id,
                'tanggal' => $request->tanggal,
                'judul' => 'Aktivitas Harian ('.count($request->items).' Rencana Kerja)',
                'isi' => $isiRingkasan,
                'progress' => 0,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $rk = $allowedRencana->firstWhere('id', $item['rencana_kerja_id']);
                AktivitasDetail::create([
                    'aktivitas_id' => $aktivitas->id,
                    'rencana_kerja_id' => $item['rencana_kerja_id'],
                    'rencana_kerja_snapshot' => $rk?->judul,
                    'progres' => trim($item['progres']),
                ]);
            }
        });

        if ($request->redirect_to === 'presensi') {
            return redirect()->route('presensi.index')->with('success', 'Aktivitas harian berhasil dicatat! Sekarang Anda dapat melakukan presensi pulang.');
        }

        return redirect()->route('presensi.index')->with('success', 'Aktivitas harian berhasil dicatat dan menunggu validasi pembimbing.');
    }

    /**
     * Detail aktivitas dan catatan validasi pembimbing.
     */
    public function show($id)
    {
        $aktivitas = Aktivitas::where('pengguna_id', Auth::id())
            ->with(['details.rencanaKerja', 'pekerjaan', 'validator.pembimbing'])
            ->findOrFail($id);

        return view('aktivitas.show', compact('aktivitas'));
    }

    /**
     * Formulir edit aktivitas.
     */
    public function edit($id)
    {
        $user = Auth::user();
        $magang = $user->magang;

        $aktivitas = Aktivitas::where('pengguna_id', $user->id)
            ->with('details.rencanaKerja')
            ->findOrFail($id);

        if (! $aktivitas->canBeEdited()) {
            return redirect()->route('magang.rekap')
                ->with('error', 'Aktivitas yang telah disetujui oleh pembimbing tidak dapat diubah lagi.');
        }

        $rencanaKerjaList = $magang
            ? $magang->rencanaKerja()->orderBy('judul', 'asc')->get()
            : collect();

        return view('aktivitas.edit', compact('aktivitas', 'rencanaKerjaList'));
    }

    /**
     * Perbarui aktivitas.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $magang = $user->magang;
        abort_if(! $magang, 403, 'Akses khusus peserta magang.');

        $aktivitas = Aktivitas::where('pengguna_id', $user->id)
            ->with('details')
            ->findOrFail($id);

        if (! $aktivitas->canBeEdited()) {
            return redirect()->route('magang.rekap')
                ->with('error', 'Aktivitas yang telah disetujui oleh pembimbing tidak dapat diubah lagi.');
        }

        $allowedRencana = $magang->rencanaKerja()->get();
        $allowedRencanaIds = $allowedRencana->pluck('id')->toArray();

        $request->validate([
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.rencana_kerja_id' => ['required', 'in:'.implode(',', $allowedRencanaIds)],
            'items.*.progres' => 'required|string|min:5',
        ], [
            'tanggal.required' => 'Tanggal aktivitas wajib diisi.',
            'items.required' => 'Minimal pilih 1 rencana kerja untuk dilaporkan.',
            'items.min' => 'Minimal pilih 1 rencana kerja untuk dilaporkan.',
            'items.*.rencana_kerja_id.required' => 'Rencana kerja wajib dipilih.',
            'items.*.rencana_kerja_id.in' => 'Rencana kerja yang dipilih tidak valid atau bukan milik Anda.',
            'items.*.progres.required' => 'Uraian progres pekerjaan wajib diisi.',
            'items.*.progres.min' => 'Uraian progres minimal 5 karakter.',
        ]);

        $selectedRencanaIds = collect($request->items)->pluck('rencana_kerja_id')->toArray();
        if (count($selectedRencanaIds) !== count(array_unique($selectedRencanaIds))) {
            return redirect()->back()->withInput()->with('error', 'Rencana kerja yang sama tidak boleh dipilih lebih dari satu kali dalam satu tanggal pengisian aktivitas.');
        }

        $isiRingkasan = collect($request->items)->map(function ($item) use ($allowedRencana) {
            $rk = $allowedRencana->firstWhere('id', $item['rencana_kerja_id']);
            $judul = $rk ? $rk->judul : 'Rencana Kerja';

            return "• [{$judul}]: ".trim($item['progres']);
        })->implode("\n\n");

        $statusBaru = ($aktivitas->status === 'revisi') ? 'pending' : $aktivitas->status;

        DB::transaction(function () use ($aktivitas, $request, $allowedRencana, $isiRingkasan, $statusBaru) {
            $aktivitas->update([
                'tanggal' => $request->tanggal,
                'judul' => 'Aktivitas Harian ('.count($request->items).' Rencana Kerja)',
                'isi' => $isiRingkasan,
                'status' => $statusBaru,
            ]);

            // Hapus detail lama dan ganti dengan detail baru
            $aktivitas->details()->delete();

            foreach ($request->items as $item) {
                $rk = $allowedRencana->firstWhere('id', $item['rencana_kerja_id']);
                AktivitasDetail::create([
                    'aktivitas_id' => $aktivitas->id,
                    'rencana_kerja_id' => $item['rencana_kerja_id'],
                    'rencana_kerja_snapshot' => $rk?->judul,
                    'progres' => trim($item['progres']),
                ]);
            }
        });

        return redirect()->route('magang.rekap')->with('success', 'Aktivitas harian berhasil diperbarui!');
    }

    /**
     * Hapus catatan aktivitas.
     */
    public function destroy($id)
    {
        $aktivitas = Aktivitas::where('pengguna_id', Auth::id())->findOrFail($id);

        if (! $aktivitas->canBeEdited()) {
            return redirect()->route('magang.rekap')
                ->with('error', 'Aktivitas yang telah disetujui tidak dapat dihapus.');
        }

        $aktivitas->delete();

        return redirect()->route('magang.rekap')
            ->with('success', 'Catatan aktivitas harian berhasil dihapus.');
    }

    /**
     * Cetak rekapitulasi aktivitas harian pribadi.
     */
    public function cetak(Request $request)
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        $user->load(['magang.divisi', 'magang.pembimbing']);

        $query = Aktivitas::where('pengguna_id', $user->id)
            ->with(['details.rencanaKerja', 'pekerjaan', 'validator.pembimbing']);

        $bulan = $request->input('bulan', Carbon::today()->format('Y-m'));
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');

        if ($tanggalMulai && $tanggalAkhir && strtotime($tanggalMulai) && strtotime($tanggalAkhir)) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);
            $periodeText = Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y').' s/d '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $query->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2));
            $periodeText = Carbon::createFromFormat('Y-m-d', $bulan.'-01')->isoFormat('MMMM Y');
        } else {
            $bulan = Carbon::today()->format('Y-m');
            $query->whereYear('tanggal', Carbon::today()->year)
                ->whereMonth('tanggal', Carbon::today()->month);
            $periodeText = Carbon::today()->isoFormat('MMMM Y');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $aktivitas = $query->orderBy('tanggal', 'asc')->orderBy('id', 'asc')->get();

        $stats = [
            'total' => $aktivitas->count(),
            'approve' => $aktivitas->where('status', 'approve')->count(),
            'pending' => $aktivitas->where('status', 'pending')->count(),
            'revisi' => $aktivitas->where('status', 'revisi')->count(),
        ];

        $targetDate = $tanggalMulai ?? ($bulan ? Carbon::parse($bulan.'-01')->toDateString() : Carbon::today()->toDateString());
        $divisi = $user->magang?->getDivisiAt($targetDate) ?? $user->magang?->divisi ?? Divisi::first();
        $pembimbing = $user->magang?->getPembimbingAt($targetDate) ?? $user->magang?->pembimbing ?? Pembimbing::first();
        $pengaturan = Pengaturan::getPengaturan();

        // Mengelompokkan aktivitas berdasarkan Rencana Kerja (sesuai PRD alur-rekap.md)
        $groupedByRencana = Aktivitas::groupByRencana($aktivitas);

        return view('aktivitas.cetak', compact('user', 'aktivitas', 'groupedByRencana', 'stats', 'periodeText', 'bulan', 'divisi', 'pembimbing', 'pengaturan'));
    }
}
