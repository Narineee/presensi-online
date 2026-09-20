<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\JadwalShiftCs;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Models\Pengguna;
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
            ->whereDate('tanggal', $today)
            ->first();

        // Khusus CS: ambil informasi jadwal shift hari ini
        $shiftToday = null;
        if ($user->role === 'cs' && $user->cs) {
            $jadwal = JadwalShiftCs::where('cs_id', $user->cs->id)
                ->whereDate('tanggal', $today)
                ->with('shift')
                ->first();

            if ($jadwal && $jadwal->shift) {
                $shiftToday = $jadwal->shift;
            }
        }

        // Ambil riwayat presensi pengguna dengan pagination
        $historyQuery = Presensi::where('pengguna_id', $user->id);

        // Filter bulan jika dipilih
        if ($request->filled('bulan') && preg_match('/^\d{4}-\d{2}$/', $request->bulan)) {
            $historyQuery->whereYear('tanggal', substr($request->bulan, 0, 4))
                ->whereMonth('tanggal', substr($request->bulan, 5, 2));
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

        // Cek apakah pengguna sudah mengisi aktivitas harian hari ini (wajib untuk Magang & CS)
        $hasAktivitasToday = Aktivitas::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        $countAktivitasToday = Aktivitas::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->count();

        // Ambil data lokasi kantor dan radius presensi
        $officeLocation = $this->getOfficeLocation($user);

        return view('presensi.index', compact('user', 'todayPresensi', 'shiftToday', 'riwayat', 'stats', 'hasAktivitasToday', 'countAktivitasToday', 'officeLocation'));
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
            ->whereDate('tanggal', $today)
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

        // Khusus Onsite: Validasi apakah pengguna berada di dalam radius kantor
        $officeLocation = $this->getOfficeLocation($user);
        if ($request->mode_kerja === 'onsite') {
            $coords = explode(',', $request->lokasi_masuk);
            if (count($coords) === 2) {
                $userLat = (float) trim($coords[0]);
                $userLng = (float) trim($coords[1]);
                $jarak = $this->calculateDistance($officeLocation['lat'], $officeLocation['lng'], $userLat, $userLng);

                if ($jarak > $officeLocation['radius']) {
                    return redirect()->back()->withInput()->with('error', "Titik lokasi Anda berada di luar radius kantor ({$jarak} meter, batas maksimal: {$officeLocation['radius']} meter). Silakan lakukan presensi di area kantor atau pilih mode WFH jika bekerja remote.");
                }
            }
        }

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

        // Waktu kerja: 08:00 WITA - 16:00 WITA
        // Apabila presensi masuk lebih dari jam 08:00 WITA, akan tercatat terlambat untuk seluruh kegiatan presensi (baik magang maupun cs)
        $jamSekarang = Carbon::now();
        $jamMasukStr = $jamSekarang->format('H:i:s');
        $batasMasuk = $jamSekarang->copy()->setTime(8, 0, 0);

        $keteranganTambahan = $request->keterangan;
        $menitTerlambat = 0;

        if ($jamSekarang->greaterThan($batasMasuk)) {
            $menitTerlambat = max(1, (int) ceil(abs($jamSekarang->diffInSeconds($batasMasuk)) / 60));
            $infoTerlambat = "[Terlambat {$menitTerlambat} menit]";
            $keteranganTambahan = $keteranganTambahan ? $keteranganTambahan.' '.$infoTerlambat : $infoTerlambat;
        }

        // Simpan atau buat record presensi hari ini
        Presensi::updateOrCreate(
            [
                'pengguna_id' => $user->id,
                'tanggal' => $today,
            ],
            [
                'jam_masuk' => $jamMasukStr,
                'status' => 'hadir',
                'mode_kerja' => $request->mode_kerja,
                'foto_masuk' => $fotoPath,
                'lokasi_masuk' => $request->lokasi_masuk,
                'keterangan' => $keteranganTambahan,
            ]
        );

        $pesanSukses = 'Presensi masuk berhasil dicatat pada pukul '.$jamMasukStr.' WITA!';
        if ($jamSekarang->greaterThan($batasMasuk)) {
            $pesanSukses .= ' (Tercatat Terlambat '.$menitTerlambat.' menit dari batas jam 08.00 WITA)';
        }

        return redirect()->route('presensi.index')
            ->with('success', $pesanSukses);
    }

    /**
     * Memproses dan menyimpan data presensi pulang/keluar.
     */
    public function storeKeluar(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $presensi = Presensi::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if (! $presensi || ! $presensi->jam_masuk) {
            return redirect()->back()->with('error', 'Anda belum melakukan presensi masuk hari ini.');
        }

        if ($presensi->jam_keluar) {
            return redirect()->back()->with('error', 'Anda sudah melakukan presensi pulang hari ini.');
        }

        // Validasi: Presensi pulang hanya dapat diinput apabila sudah mengisi aktivitas harian hari ini (berlaku untuk Magang & CS)
        $hasAktivitas = Aktivitas::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if (! $hasAktivitas) {
            return redirect()->back()->with('error', 'Anda belum mengisi aktivitas harian hari ini. Silakan isi aktivitas harian terlebih dahulu sebelum melakukan presensi pulang.');
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

        // Khusus Onsite: Validasi apakah pengguna berada di dalam radius kantor saat presensi pulang
        $officeLocation = $this->getOfficeLocation($user);
        if ($presensi->mode_kerja === 'onsite') {
            $coords = explode(',', $request->lokasi_keluar);
            if (count($coords) === 2) {
                $userLat = (float) trim($coords[0]);
                $userLng = (float) trim($coords[1]);
                $jarak = $this->calculateDistance($officeLocation['lat'], $officeLocation['lng'], $userLat, $userLng);

                if ($jarak > $officeLocation['radius']) {
                    return redirect()->back()->withInput()->with('error', "Titik lokasi kepulangan Anda berada di luar radius kantor ({$jarak} meter, batas maksimal: {$officeLocation['radius']} meter). Silakan lakukan presensi pulang di area kantor.");
                }
            }
        }

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
            ->with('success', 'Presensi pulang berhasil dicatat pada pukul '.Carbon::now()->format('H:i:s').' WITA. Selamat beristirahat!');
    }

    /**
     * Mencetak laporan rekapitulasi presensi pribadi (untuk Magang & CS).
     */
    public function cetak(Request $request)
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        // Eager load relasi magang/CS
        $user->load(['magang.divisi', 'magang.pembimbing', 'cs.pembimbing']);

        $query = Presensi::where('pengguna_id', $user->id);

        $bulan = $request->input('bulan', Carbon::today()->format('Y-m'));
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalAkhir = $request->input('tanggal_akhir');

        if ($tanggalMulai && $tanggalAkhir && strtotime($tanggalMulai) && strtotime($tanggalAkhir)) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);
            $periodeText = Carbon::parse($tanggalMulai)->isoFormat('D MMMM Y').' s/d '.Carbon::parse($tanggalAkhir)->isoFormat('D MMMM Y');
        } elseif ($bulan && preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            $query->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2));
            $periodeText = Carbon::createFromFormat('Y-m', $bulan)->isoFormat('MMMM Y');
        } else {
            $bulan = Carbon::today()->format('Y-m');
            $query->whereYear('tanggal', Carbon::today()->year)
                ->whereMonth('tanggal', Carbon::today()->month);
            $periodeText = Carbon::today()->isoFormat('MMMM Y');
        }

        $presensi = $query->orderBy('tanggal', 'asc')
            ->orderBy('jam_masuk', 'asc')
            ->get();

        $stats = [
            'total_hadir' => $presensi->where('status', 'hadir')->count(),
            'total_onsite' => $presensi->where('mode_kerja', 'onsite')->count(),
            'total_wfh' => $presensi->where('mode_kerja', 'wfh')->count(),
        ];

        $divisi = $user->magang?->divisi ?? Divisi::first();
        $pembimbing = $user->magang?->pembimbing ?? ($user->cs?->pembimbing ?? Pembimbing::first());
        $pengaturan = Pengaturan::getPengaturan();

        return view('presensi.cetak', compact('user', 'presensi', 'stats', 'periodeText', 'bulan', 'divisi', 'pembimbing', 'pengaturan'));
    }

    /**
     * Mendapatkan koordinat dan radius kantor yang berlaku untuk pengguna.
     *
     * @return array{lat: float, lng: float, radius: int, nama: string}
     */
    private function getOfficeLocation(?Pengguna $user = null): array
    {
        $default = [
            'lat' => -3.4893886444181983,
            'lng' => 114.8252583950533,
            'radius' => 100,
            'nama' => 'Kantor Utama',
        ];

        if ($user && $user->magang && $user->magang->divisi) {
            $divisi = $user->magang->divisi;
            if ($divisi->latitude && $divisi->longitude) {
                return [
                    'lat' => (float) $divisi->latitude,
                    'lng' => (float) $divisi->longitude,
                    'radius' => (int) ($divisi->radius_meter ?? 100),
                    'nama' => $divisi->nama_divisi,
                ];
            }
        }

        $divisi = Divisi::whereNotNull('latitude')->whereNotNull('longitude')->first();
        if ($divisi && $divisi->latitude && $divisi->longitude) {
            return [
                'lat' => (float) $divisi->latitude,
                'lng' => (float) $divisi->longitude,
                'radius' => (int) ($divisi->radius_meter ?? 100),
                'nama' => $divisi->nama_divisi,
            ];
        }

        return $default;
    }

    /**
     * Menghitung jarak antara dua koordinat (dalam meter) menggunakan formula Haversine.
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // Meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }
}
