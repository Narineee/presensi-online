<?php

namespace App\Http\Controllers;

use App\Models\JadwalShiftCs;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PresensiController extends Controller
{
    /**
     * Menampilkan halaman utama presensi (form absen hari ini & riwayat presensi).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Ambil data presensi hari ini jika sudah pernah absen
        $todayPresensi = Presensi::where('pengguna_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        // Khusus CS: ambil informasi jadwal shift hari ini
        $shiftToday = null;
        if ($user->role === 'cs' && $user->cs) {
            $jadwal = JadwalShiftCs::where('cs_id', $user->cs->id)
                ->where('tanggal', $today)
                ->with('shift')
                ->first();

            if ($jadwal && $jadwal->shift) {
                $shiftToday = $jadwal->shift;
            }
        }

        // Ambil riwayat presensi pengguna dengan pagination
        $historyQuery = Presensi::where('pengguna_id', $user->id);

        // Filter bulan jika dipilih
        if ($request->filled('bulan')) {
            $historyQuery->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                ->whereYear('tanggal', date('Y', strtotime($request->bulan)));
        }

        $riwayat = $historyQuery->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();

        // Hitung statistik presensi bulan ini
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $stats = [
            'total_hadir' => Presensi::where('pengguna_id', $user->id)
                ->whereYear('tanggal', $currentYear)
                ->whereMonth('tanggal', $currentMonth)
                ->where('status', 'hadir')
                ->count(),
            'total_onsite' => Presensi::where('pengguna_id', $user->id)
                ->whereYear('tanggal', $currentYear)
                ->whereMonth('tanggal', $currentMonth)
                ->where('mode_kerja', 'onsite')
                ->count(),
            'total_wfh' => Presensi::where('pengguna_id', $user->id)
                ->whereYear('tanggal', $currentYear)
                ->whereMonth('tanggal', $currentMonth)
                ->where('mode_kerja', 'wfh')
                ->count(),
        ];

        return view('presensi.index', compact('user', 'todayPresensi', 'shiftToday', 'riwayat', 'stats'));
    }

    /**
     * Memproses dan menyimpan data presensi masuk.
     */
    public function storeMasuk(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // Cek apakah sudah absen masuk hari ini
        $existing = Presensi::where('pengguna_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if ($existing && $existing->jam_masuk) {
            return redirect()->back()->with('error', 'Anda sudah melakukan presensi masuk hari ini.');
        }

        // Validasi input data presensi
        $request->validate([
            'mode_kerja' => 'required|in:onsite,wfh',
            'lokasi_masuk' => 'required|string',
            'foto_masuk' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'mode_kerja.required' => 'Pilih mode kerja (Onsite atau WFH).',
            'lokasi_masuk.required' => 'Titik lokasi GPS wajib terdeteksi. Silakan izinkan akses lokasi di browser Anda.',
            'foto_masuk.required' => 'Foto selfie wajib diambil melalui kamera atau diunggah.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ]);

        // Simpan foto selfie masuk (dukungan base64 dari kamera atau file upload)
        $fotoPath = null;
        if (str_starts_with($request->foto_masuk, 'data:image')) {
            $imageParts = explode(';base64,', $request->foto_masuk);
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageExtension = $imageTypeAux[1] ?? 'jpg';
            $imageBinary = base64_decode($imageParts[1]);

            $fileName = 'masuk_'.$user->id.'_'.date('Ymd_His').'.'.$imageExtension;
            Storage::disk('public')->put('presensi/masuk/'.$fileName, $imageBinary);
            $fotoPath = 'presensi/masuk/'.$fileName;
        }

        // Khusus CS: cek keterlambatan terhadap jam shift
        $keteranganTambahan = $request->keterangan;
        if ($user->role === 'cs' && $user->cs) {
            $jadwal = JadwalShiftCs::where('cs_id', $user->cs->id)
                ->where('tanggal', $today)
                ->with('shift')
                ->first();

            if ($jadwal && $jadwal->shift) {
                $shift = $jadwal->shift;
                $toleransi = $shift->toleransi_masuk ?? 0;
                $batasMasuk = Carbon::parse($today.' '.$shift->jam_masuk)->addMinutes($toleransi);
                $jamSekarang = Carbon::now();

                if ($jamSekarang->greaterThan($batasMasuk)) {
                    $menitTerlambat = $jamSekarang->diffInMinutes(Carbon::parse($today.' '.$shift->jam_masuk));
                    $infoTerlambat = "[Terlambat {$menitTerlambat} menit dari shift {$shift->nama}]";
                    $keteranganTambahan = $keteranganTambahan ? $keteranganTambahan.' '.$infoTerlambat : $infoTerlambat;
                }
            }
        }

        // Simpan atau buat record presensi hari ini
        Presensi::updateOrCreate(
            [
                'pengguna_id' => $user->id,
                'tanggal' => $today,
            ],
            [
                'jam_masuk' => Carbon::now()->format('H:i:s'),
                'status' => 'hadir',
                'mode_kerja' => $request->mode_kerja,
                'foto_masuk' => $fotoPath,
                'lokasi_masuk' => $request->lokasi_masuk,
                'keterangan' => $keteranganTambahan,
            ]
        );

        return redirect()->route('presensi.index')
            ->with('success', 'Presensi masuk berhasil dicatat pada pukul '.Carbon::now()->format('H:i:s').' WIB!');
    }

    /**
     * Memproses dan menyimpan data presensi pulang/keluar.
     */
    public function storeKeluar(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $presensi = Presensi::where('pengguna_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if (! $presensi || ! $presensi->jam_masuk) {
            return redirect()->back()->with('error', 'Anda belum melakukan presensi masuk hari ini.');
        }

        if ($presensi->jam_keluar) {
            return redirect()->back()->with('error', 'Anda sudah melakukan presensi pulang hari ini.');
        }

        // Validasi input data presensi pulang
        $request->validate([
            'lokasi_keluar' => 'required|string',
            'foto_keluar' => 'required|string',
            'keterangan_keluar' => 'nullable|string|max:255',
        ], [
            'lokasi_keluar.required' => 'Titik lokasi GPS kepulangan wajib terdeteksi.',
            'foto_keluar.required' => 'Foto selfie pulang wajib diambil.',
        ]);

        // Simpan foto selfie pulang
        $fotoPath = null;
        if (str_starts_with($request->foto_keluar, 'data:image')) {
            $imageParts = explode(';base64,', $request->foto_keluar);
            $imageTypeAux = explode('image/', $imageParts[0]);
            $imageExtension = $imageTypeAux[1] ?? 'jpg';
            $imageBinary = base64_decode($imageParts[1]);

            $fileName = 'keluar_'.$user->id.'_'.date('Ymd_His').'.'.$imageExtension;
            Storage::disk('public')->put('presensi/keluar/'.$fileName, $imageBinary);
            $fotoPath = 'presensi/keluar/'.$fileName;
        }

        // Gabungkan keterangan jika ada
        $finalKeterangan = $presensi->keterangan;
        if ($request->filled('keterangan_keluar')) {
            $finalKeterangan = $finalKeterangan
                ? $finalKeterangan.' | Pulang: '.$request->keterangan_keluar
                : 'Pulang: '.$request->keterangan_keluar;
        }

        // Update record presensi dengan jam keluar
        $presensi->update([
            'jam_keluar' => Carbon::now()->format('H:i:s'),
            'foto_keluar' => $fotoPath,
            'lokasi_keluar' => $request->lokasi_keluar,
            'keterangan' => $finalKeterangan,
        ]);

        return redirect()->route('presensi.index')
            ->with('success', 'Presensi pulang berhasil dicatat pada pukul '.Carbon::now()->format('H:i:s').' WIB. Selamat beristirahat!');
    }
}
