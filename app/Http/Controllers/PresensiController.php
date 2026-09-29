<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Services\FaceVerificationService;
use App\Support\StoresBase64Image;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    use StoresBase64Image;

    public function __construct(private FaceVerificationService $face) {}

    /**
     * Menampilkan halaman utama presensi (form absen hari ini & riwayat presensi).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->role === 'magang' && ! $user->magang?->face_registered_at) {
            return redirect()->route('wajah.create')
                ->with('error', 'Daftarkan wajah Anda terlebih dahulu sebelum presensi.');
        }
        $today = Carbon::today()->toDateString();

        // Ambil data presensi hari ini jika sudah pernah absen
        $todayPresensi = Presensi::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

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

        // Cek apakah pengguna sudah mengisi aktivitas harian hari ini (wajib untuk Magang)
        $hasAktivitasToday = Aktivitas::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        $countAktivitasToday = Aktivitas::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->count();

        // Cek batasan jam presensi (WITA):
        // Masuk: mulai 07:30 WITA, lewat 08:00 WITA tercatat terlambat.
        // Pulang: 16:00 - 18:00 WITA. Lewat 18:00 WITA tidak dapat presensi.
        $now = Carbon::now();
        $bukaMasuk = $now->copy()->setTime(7, 30, 0);
        $batasTepatWaktu = $now->copy()->setTime(8, 0, 0);
        $bukaPulang = $now->copy()->setTime(16, 0, 0);
        $tutupPresensi = $now->copy()->setTime(18, 0, 0);

        $timeStatus = [
            'is_before_masuk' => $now->lessThan($bukaMasuk),
            'is_late_masuk' => $now->greaterThan($batasTepatWaktu),
            'is_after_tutup' => $now->greaterThan($tutupPresensi),
            'is_before_pulang' => $now->lessThan($bukaPulang),
            'is_waktu_pulang' => $now->greaterThanOrEqualTo($bukaPulang) && $now->lessThanOrEqualTo($tutupPresensi),
        ];

        // Ambil data lokasi kantor dan radius presensi
        $officeLocation = $this->getOfficeLocation($user);

        return view('presensi.index', compact('user', 'todayPresensi', 'riwayat', 'stats', 'hasAktivitasToday', 'countAktivitasToday', 'officeLocation', 'timeStatus'));
    }

    /**
     * Memproses dan menyimpan data presensi masuk.
     */
    public function storeMasuk(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $existing = Presensi::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existing && $existing->jam_masuk) {
            return redirect()->back()->with('error', 'Anda sudah melakukan presensi masuk hari ini.');
        }

        // Batas jam: buka 07.30, tutup 18.00 WITA
        $jamSekarang = Carbon::now();
        $bukaMasuk = $jamSekarang->copy()->setTime(7, 30, 0);
        $tutupHari = $jamSekarang->copy()->setTime(18, 0, 0);

        if ($jamSekarang->lessThan($bukaMasuk)) {
            return redirect()->back()->with('error', 'Presensi masuk belum dibuka. Presensi masuk dimulai pukul 07.30 WITA.');
        }

        if ($jamSekarang->greaterThan($tutupHari)) {
            return redirect()->back()->with('error', 'Waktu presensi untuk hari ini telah berakhir (pukul 18.00 WITA).');
        }

        $request->validate([
            'mode_kerja' => 'required|in:onsite,wfh',
            'lokasi_masuk' => 'required|string',
            'foto_masuk' => 'required|string',
            'face_descriptor' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'mode_kerja.required' => 'Pilih mode kerja (Onsite atau WFH).',
            'lokasi_masuk.required' => 'Titik lokasi GPS wajib terdeteksi. Silakan izinkan akses lokasi di browser Anda.',
            'foto_masuk.required' => 'Foto selfie wajib diambil.',
            'face_descriptor.required' => 'Verifikasi wajah belum selesai. Silakan ulangi.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ]);

        $inputAman = $request->except(['foto_masuk', 'face_descriptor']);

        // Onsite: cek radius kantor
        $officeLocation = $this->getOfficeLocation($user);
        if ($request->mode_kerja === 'onsite') {
            $coords = explode(',', $request->lokasi_masuk);
            if (count($coords) === 2) {
                $jarak = $this->calculateDistance(
                    $officeLocation['lat'], $officeLocation['lng'],
                    (float) trim($coords[0]), (float) trim($coords[1])
                );

                if ($jarak > $officeLocation['radius']) {
                    return redirect()->back()->withInput($inputAman)->with('error', "Titik lokasi Anda berada di luar radius kantor ({$jarak} meter, batas maksimal: {$officeLocation['radius']} meter). Silakan presensi di area kantor atau pilih mode WFH jika bekerja remote.");
                }
            }
        }

        // Verifikasi wajah
        $face = $this->checkFace($user, $request->face_descriptor);
        if ($face['error']) {
            Log::warning('Verifikasi wajah masuk gagal', ['user' => $user->id, 'distance' => $face['distance']]);

            return redirect()->back()->withInput($inputAman)->with('error', $face['error']);
        }

        // Simpan foto; batalkan presensi jika foto gagal tersimpan
        $fotoPath = str_starts_with($request->foto_masuk, 'data:image')
            ? $this->compressAndStoreImage($request->foto_masuk, 'presensi/masuk', 'masuk_'.$user->id.'_'.date('Ymd_His'))
            : null;

        if (! $fotoPath) {
            return redirect()->back()->withInput($inputAman)->with('error', 'Foto presensi gagal disimpan. Silakan ulangi verifikasi wajah.');
        }

        // Hitung keterlambatan (batas 08.00 WITA)
        $jamSekarang = Carbon::now();
        $jamMasukStr = $jamSekarang->format('H:i:s');
        $batasMasuk = $jamSekarang->copy()->setTime(8, 0, 0);

        $keteranganTambahan = $request->keterangan;
        $menitTerlambat = 0;
        $statusPresensi = 'hadir'; // enum status tidak punya 'terlambat'; keterlambatan dicatat di keterangan

        if ($jamSekarang->greaterThan($batasMasuk)) {
            $menitTerlambat = max(1, (int) ceil($batasMasuk->diffInSeconds($jamSekarang) / 60));
            $infoTerlambat = "[Terlambat {$menitTerlambat} menit]";
            $keteranganTambahan = $keteranganTambahan ? $keteranganTambahan.' '.$infoTerlambat : $infoTerlambat;
        }

        Presensi::updateOrCreate(
            ['pengguna_id' => $user->id, 'tanggal' => $today],
            [
                'jam_masuk' => $jamMasukStr,
                'status' => $statusPresensi,
                'mode_kerja' => $request->mode_kerja,
                'foto_masuk' => $fotoPath,
                'lokasi_masuk' => $request->lokasi_masuk,
                'face_distance_masuk' => $face['distance'],
                'keterangan' => $keteranganTambahan,
            ]
        );

        $pesanSukses = 'Presensi masuk berhasil dicatat pukul '.$jamMasukStr.' WITA.';
        if ($menitTerlambat > 0) {
            $pesanSukses .= " Tercatat terlambat {$menitTerlambat} menit dari batas 08.00 WITA.";
        }

        return redirect()->route('presensi.index')->with('success', $pesanSukses);
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

        // Batas jam pulang: 16.00 sampai 18.00 WITA
        $jamSekarang = Carbon::now();
        $bukaPulang = $jamSekarang->copy()->setTime(16, 0, 0);
        $tutupPulang = $jamSekarang->copy()->setTime(18, 0, 0);

        if ($jamSekarang->lessThan($bukaPulang)) {
            return redirect()->back()->with('error', 'Presensi pulang belum dibuka. Presensi pulang dapat dilakukan pukul 16.00 sampai 18.00 WITA.');
        }

        if ($jamSekarang->greaterThan($tutupPulang)) {
            return redirect()->back()->with('error', 'Batas waktu presensi pulang telah berakhir (pukul 18.00 WITA).');
        }

        // Wajib mengisi aktivitas harian dulu
        $hasAktivitas = Aktivitas::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if (! $hasAktivitas) {
            return redirect()->back()->with('error', 'Anda belum mengisi aktivitas harian hari ini. Silakan isi aktivitas harian terlebih dahulu.');
        }

        $request->validate([
            'lokasi_keluar' => 'required|string',
            'foto_keluar' => 'required|string',
            'face_descriptor' => 'required|string',
            'keterangan_keluar' => 'nullable|string|max:255',
        ], [
            'lokasi_keluar.required' => 'Titik lokasi GPS kepulangan wajib terdeteksi.',
            'foto_keluar.required' => 'Foto selfie pulang wajib diambil.',
            'face_descriptor.required' => 'Verifikasi wajah belum selesai. Silakan ulangi.',
        ]);

        $inputAman = $request->except(['foto_keluar', 'face_descriptor']);

        // Onsite: cek radius kantor
        $officeLocation = $this->getOfficeLocation($user);
        if ($presensi->mode_kerja === 'onsite') {
            $coords = explode(',', $request->lokasi_keluar);
            if (count($coords) === 2) {
                $jarak = $this->calculateDistance(
                    $officeLocation['lat'], $officeLocation['lng'],
                    (float) trim($coords[0]), (float) trim($coords[1])
                );

                if ($jarak > $officeLocation['radius']) {
                    return redirect()->back()->withInput($inputAman)->with('error', "Titik lokasi kepulangan Anda berada di luar radius kantor ({$jarak} meter, batas maksimal: {$officeLocation['radius']} meter).");
                }
            }
        }

        // Verifikasi wajah
        $face = $this->checkFace($user, $request->face_descriptor);
        if ($face['error']) {
            Log::warning('Verifikasi wajah pulang gagal', ['user' => $user->id, 'distance' => $face['distance']]);

            return redirect()->back()->withInput($inputAman)->with('error', $face['error']);
        }

        // Simpan foto
        $fotoPath = str_starts_with($request->foto_keluar, 'data:image')
            ? $this->compressAndStoreImage($request->foto_keluar, 'presensi/keluar', 'keluar_'.$user->id.'_'.date('Ymd_His'))
            : null;

        if (! $fotoPath) {
            return redirect()->back()->withInput($inputAman)->with('error', 'Foto presensi gagal disimpan. Silakan ulangi verifikasi wajah.');
        }

        $finalKeterangan = $presensi->keterangan;
        if ($request->filled('keterangan_keluar')) {
            $finalKeterangan = $finalKeterangan
                ? $finalKeterangan.' | Pulang: '.$request->keterangan_keluar
                : 'Pulang: '.$request->keterangan_keluar;
        }

        $jamKeluar = Carbon::now()->format('H:i:s');

        $presensi->update([
            'jam_keluar' => $jamKeluar,
            'foto_keluar' => $fotoPath,
            'lokasi_keluar' => $request->lokasi_keluar,
            'face_distance_keluar' => $face['distance'],
            'keterangan' => $finalKeterangan,
        ]);

        return redirect()->route('presensi.index')
            ->with('success', 'Presensi pulang berhasil dicatat pukul '.$jamKeluar.' WITA.');
    }

    /**
     * Mencetak laporan rekapitulasi presensi pribadi (untuk Magang).
     */
    public function cetak(Request $request)
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        // Eager load relasi magang
        $user->load(['magang.divisi', 'magang.pembimbing']);

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
        $pembimbing = $user->magang?->pembimbing ?? Pembimbing::first();
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
            'lat' => -3.489563757755857,
            'lng' => 114.82525839853534,
            'radius' => 40,
            'nama' => 'Kantor Utama',
        ];

        if ($user && $user->magang && $user->magang->divisi) {
            $divisi = $user->magang->divisi;
            if ($divisi->latitude && $divisi->longitude) {
                return [
                    'lat' => (float) $divisi->latitude,
                    'lng' => (float) $divisi->longitude,
                    'radius' => (int) ($divisi->radius_meter ?? 40),
                    'nama' => $divisi->nama_divisi,
                ];
            }
        }

        $divisi = Divisi::whereNotNull('latitude')->whereNotNull('longitude')->first();
        if ($divisi && $divisi->latitude && $divisi->longitude) {
            return [
                'lat' => (float) $divisi->latitude,
                'lng' => (float) $divisi->longitude,
                'radius' => (int) ($divisi->radius_meter ?? 40),
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

    /** @return array{error: ?string, distance: ?float} */
    private function checkFace(Pengguna $user, ?string $probeJson): array
    {
        $enrolled = $user->magang?->face_descriptors;
        if (empty($enrolled)) {
            return ['error' => 'Wajah Anda belum terdaftar. Silakan daftarkan wajah terlebih dahulu.', 'distance' => null];
        }

        $probe = $this->face->parse($probeJson);
        if (! $probe) {
            return ['error' => 'Data verifikasi wajah tidak valid. Silakan ulangi verifikasi wajah.', 'distance' => null];
        }

        $r = $this->face->match($enrolled, $probe[0]);
        if (! $r['match']) {
            return ['error' => 'Wajah tidak cocok dengan data pendaftaran. Silakan coba lagi.', 'distance' => $r['distance']];
        }

        return ['error' => null, 'distance' => $r['distance']];
    }

    /**
     * Verifikasi instan (pre-check) kecocokan wajah via AJAX sebelum presensi disubmit.
     */
    public function verifikasiWajah(Request $request): JsonResponse
    {
        $request->validate([
            'face_descriptor' => 'required|string',
        ]);

        $user = Auth::user();
        $face = $this->checkFace($user, $request->face_descriptor);

        if ($face['error']) {
            return response()->json([
                'success' => false,
                'match' => false,
                'distance' => $face['distance'],
                'message' => $face['error'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'match' => true,
            'distance' => $face['distance'],
            'message' => 'Wajah terverifikasi dan cocok dengan data pendaftaran.',
        ]);
    }
}
