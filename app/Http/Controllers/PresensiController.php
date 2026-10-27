<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\Divisi;
use App\Models\HariLibur;
use App\Models\Pembimbing;
use App\Models\PengajuanIzin;
use App\Models\PengajuanTugasLuar;
use App\Models\Pengaturan;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Services\FaceVerificationService;
use App\Services\PdfService;
use App\Services\PresensiScoreService;
use App\Support\StoresBase64Image;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PresensiController extends Controller
{
    use StoresBase64Image;

    public function __construct(
        private FaceVerificationService $face,
        private PdfService $pdfService
    ) {}

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
            ->with(['pengajuanTugasLuar', 'pengajuanTugasLuars'])
            ->first();

        // Ambil data pengajuan tugas luar hari ini jika ada
        $todayTugasLuar = PengajuanTugasLuar::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->latest()
            ->first();

        // Ambil riwayat presensi pengguna dengan pagination
        $historyQuery = Presensi::where('pengguna_id', $user->id)->with('pengajuanTugasLuar');

        // Filter bulan jika dipilih
        if ($request->filled('bulan') && preg_match('/^\d{4}-\d{2}$/', $request->bulan)) {
            $historyQuery->whereYear('tanggal', substr($request->bulan, 0, 4))
                ->whereMonth('tanggal', substr($request->bulan, 5, 2));
        }

        $riwayat = $historyQuery->orderBy('tanggal', 'desc')->paginate(10)->withQueryString();

        // Hitung statistik presensi bulan ini
        $stats = $this->getMonthlyStats($user);

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

        $minTanggalSelesai = HariLibur::getBatasMundurHariKerja(3)->toDateString();

        return view('presensi.kehadiran', compact('user', 'todayPresensi', 'todayTugasLuar', 'riwayat', 'stats', 'hasAktivitasToday', 'countAktivitasToday', 'officeLocation', 'timeStatus', 'minTanggalSelesai'));
    }

    /**
     * Menampilkan pusat Rekapitulasi & Dokumen peserta magang (sesuai PRD Rekap-riwayat.png).
     */
    public function rekapHub(Request $request)
    {
        $user = Auth::user();
        $magang = $user->magang;

        $targetHari = 80;
        $totalHadir = 0;
        $jamAktual = 0;
        $targetJam = 640;
        $hariKerjaBerlalu = 0;
        $progresPeriode = 0;
        $persenKehadiran = 0;
        $persenJam = 0;

        if ($magang && $magang->tanggal_mulai && $magang->tanggal_selesai) {
            $mulai = Carbon::parse($magang->tanggal_mulai)->startOfDay();
            $selesai = Carbon::parse($magang->tanggal_selesai)->startOfDay();
            $today = Carbon::today();

            // Hitung skor presensi komprehensif menggunakan service resmi
            /** @var PresensiScoreService $scoreService */
            $scoreService = app(PresensiScoreService::class);
            $scoreData = $scoreService->calculateScore($magang);

            $targetHari = max(1, $scoreData['target_hari']);
            $targetJam = max(1, (int) round($scoreData['target_menit'] / 60));
            $totalHadir = $scoreData['total_hari_hadir'] + ($scoreData['total_hari_lupa_checkout'] ?? 0);

            // Jam aktual dari menit realisasi yang valid
            $jamAktual = (int) round($scoreData['total_menit_realisasi'] / 60);

            // Hitung hari kerja yang sudah berlalu (Senin-Jumat di luar hari libur nasional)
            // sejak tanggal mulai sampai dengan hari ini (dibatasi maksimal tanggal selesai)
            if ($today->gte($mulai)) {
                $current = $mulai->copy();
                $batasAkhir = $today->gt($selesai) ? $selesai : $today;
                while ($current->lte($batasAkhir)) {
                    if (! $current->isWeekend() && ! HariLibur::isTanggalMerah($current)) {
                        $hariKerjaBerlalu++;
                    }
                    $current->addDay();
                }
            }

            $progresPeriode = min(100, (int) round(($hariKerjaBerlalu / $targetHari) * 100));
            $persenKehadiran = min(100, (int) round(($totalHadir / $targetHari) * 100));
            $persenJam = min(100, (int) round(($jamAktual / $targetJam) * 100));
        } else {
            // Fallback jika belum ada data periode magang
            $totalHadir = Presensi::where('pengguna_id', $user->id)
                ->where('status', 'hadir')
                ->count();
            $jamAktual = $totalHadir * 8;
            $persenKehadiran = min(100, (int) round(($totalHadir / max(1, $targetHari)) * 100));
            $persenJam = min(100, (int) round(($jamAktual / max(1, $targetJam)) * 100));
        }

        $totalAktivitas = Aktivitas::where('pengguna_id', $user->id)->count();

        $stats = [
            'hari_kerja_berlalu' => $hariKerjaBerlalu,
            'target_hari' => $targetHari,
            'progres_periode' => $progresPeriode,
            'total_hadir' => $totalHadir,
            'persen_kehadiran' => $persenKehadiran,
            'jam_aktual' => $jamAktual,
            'target_jam' => $targetJam,
            'persen_jam' => $persenJam,
            // Alias kompatibilitas
            'jam_magang' => $jamAktual,
            'total_aktivitas' => $totalAktivitas,
        ];

        return view('magang.rekap', compact('user', 'magang', 'stats'));
    }

    /**
     * Menampilkan riwayat presensi harian milik peserta magang (sesuai PRD detail-presensi (2).png).
     */
    public function riwayat(Request $request)
    {
        /** @var Pengguna $user */
        $user = Auth::user();
        $magang = $user->magang;

        $query = Presensi::where('pengguna_id', $user->id)
            ->with('pengajuanTugasLuar');

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalSelesai = $request->input('tanggal_selesai');
        $modeKerja = $request->input('mode_kerja');

        if ($tanggalAwal && $tanggalSelesai) {
            $query->whereBetween('tanggal', [$tanggalAwal, $tanggalSelesai]);
        } elseif ($tanggalAwal) {
            $query->whereDate('tanggal', '>=', $tanggalAwal);
        } elseif ($tanggalSelesai) {
            $query->whereDate('tanggal', '<=', $tanggalSelesai);
        }

        if ($modeKerja && in_array($modeKerja, ['onsite', 'wfh', 'tugas_luar'])) {
            $query->where('mode_kerja', $modeKerja);
        }

        $presensiList = $query->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

        return view('presensi.riwayat', compact('user', 'magang', 'presensiList', 'tanggalAwal', 'tanggalSelesai', 'modeKerja'));
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

        $rules = [
            'mode_kerja' => 'required|in:onsite,wfh,tugas_luar',
            'lokasi_masuk' => 'required|string',
            'foto_masuk' => 'required|string',
            'face_descriptor' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ];

        $messages = [
            'mode_kerja.required' => 'Pilih mode kerja (Onsite, WFH, atau Tugas Luar).',
            'lokasi_masuk.required' => 'Titik lokasi GPS wajib terdeteksi. Silakan izinkan akses lokasi di browser Anda.',
            'foto_masuk.required' => 'Foto selfie wajib diambil.',
            'face_descriptor.required' => 'Verifikasi wajah belum selesai. Silakan ulangi.',
            'keterangan.max' => 'Keterangan maksimal 255 karakter.',
        ];

        if ($request->mode_kerja === 'tugas_luar') {
            $isLegacyTimeSent = $request->filled('waktu_mulai');
            $rules['tujuan'] = 'required|string|max:255';
            $rules['keperluan'] = 'required|string';
            $rules['tanggal_selesai'] = $isLegacyTimeSent ? 'nullable|date' : 'required|date';
            $rules['tanggal_mulai'] = $isLegacyTimeSent ? 'nullable|date|before_or_equal:tanggal_selesai' : 'required|date|before_or_equal:tanggal_selesai';
            $rules['waktu_mulai'] = 'nullable|string';
            $rules['waktu_selesai'] = 'nullable|string';
            $rules['bukti_tugas_luar'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120';

            $messages['tujuan.required'] = 'Tujuan/lokasi tugas luar wajib diisi.';
            $messages['keperluan.required'] = 'Keperluan/kegiatan tugas luar wajib diisi.';
            $messages['tanggal_selesai.required'] = 'Tanggal selesai penugasan wajib diisi.';
            $messages['tanggal_mulai.required'] = 'Tanggal mulai penugasan wajib diisi.';
            $messages['tanggal_mulai.before_or_equal'] = 'Tanggal mulai tidak boleh melewati tanggal selesai.';
            $messages['bukti_tugas_luar.mimes'] = 'Bukti tugas luar harus berformat PDF, JPG, JPEG, atau PNG.';
            $messages['bukti_tugas_luar.max'] = 'Ukuran file bukti tugas luar maksimal 5MB.';
        }

        $request->validate($rules, $messages);

        $inputAman = $request->except(['foto_masuk', 'face_descriptor', 'bukti_tugas_luar']);

        if ($request->mode_kerja === 'tugas_luar') {
            $tanggalSelesai = $request->input('tanggal_selesai', $today);
            $tanggalMulai = $request->input('tanggal_mulai', $tanggalSelesai);
            $minTanggalSelesai = HariLibur::getBatasMundurHariKerja(3)->toDateString();

            if ($tanggalSelesai < $minTanggalSelesai) {
                return redirect()->back()->withInput($inputAman)->with('error', 'Tanggal selesai tugas luar maksimal 3 hari kerja ke belakang.');
            }

            if ($tanggalMulai > $tanggalSelesai) {
                return redirect()->back()->withInput($inputAman)->with('error', 'Tanggal mulai penugasan tidak boleh melewati tanggal selesai.');
            }
        }

        // Onsite: cek radius kantor (Jika Tugas Luar atau WFH, radius tidak dicek)
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

        // Simpan bukti tugas luar jika diunggah
        $buktiPath = null;
        if ($request->hasFile('bukti_tugas_luar')) {
            $buktiPath = $request->file('bukti_tugas_luar')->store('tugas_luar', 'public');
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

        if ($request->mode_kerja === 'tugas_luar') {
            $ketTL = 'Tugas Luar: '.$request->tujuan;
            $keteranganTambahan = $keteranganTambahan ? $ketTL.' | '.$keteranganTambahan : $ketTL;
        }

        $presensi = Presensi::updateOrCreate(
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

        // Jika mode kerja Tugas Luar (Skenario 1), buat record pengajuan tugas luar
        if ($request->mode_kerja === 'tugas_luar') {
            $tanggalSelesai = $request->input('tanggal_selesai', $today);
            $tanggalMulai = $request->input('tanggal_mulai', $tanggalSelesai);

            PengajuanTugasLuar::create([
                'presensi_id' => $presensi->id,
                'pengguna_id' => $user->id,
                'magang_id' => $user->magang?->id,
                'tanggal' => $today,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'tujuan' => $request->tujuan,
                'keperluan' => $request->keperluan,
                'waktu_mulai' => $request->waktu_mulai ?: $jamMasukStr,
                'waktu_selesai' => $request->waktu_selesai ?: '16:00:00',
                'bukti' => $buktiPath,
                'status_verifikasi' => 'menunggu',
            ]);
        }

        if ($request->mode_kerja === 'tugas_luar') {
            $pesanSukses = 'Presensi masuk Tugas Luar berhasil dicatat pukul '.$jamMasukStr.' WITA dan sedang menunggu verifikasi pembimbing.';
        } else {
            $pesanSukses = 'Presensi masuk berhasil dicatat pukul '.$jamMasukStr.' WITA.';
            if ($menitTerlambat > 0) {
                $pesanSukses .= " Tercatat terlambat {$menitTerlambat} menit dari batas 08.00 WITA.";
            }
        }

        return redirect()->route('presensi.index')->with('success', $pesanSukses);
    }

    /**
     * Mengajukan Tugas Luar bagi peserta yang sudah melakukan presensi masuk (Skenario 2).
     */
    public function storeTugasLuar(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $nowTimeStr = Carbon::now()->format('H:i:s');
        $isLegacyTimeSent = $request->filled('waktu_mulai');

        $request->validate([
            'tujuan' => 'required|string|max:255',
            'keperluan' => 'required|string',
            'tanggal_selesai' => $isLegacyTimeSent ? 'nullable|date' : 'required|date',
            'tanggal_mulai' => $isLegacyTimeSent ? 'nullable|date|before_or_equal:tanggal_selesai' : 'required|date|before_or_equal:tanggal_selesai',
            'waktu_mulai' => 'nullable|string',
            'waktu_selesai' => 'nullable|string',
            'bukti_tugas_luar' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'tujuan.required' => 'Tujuan/lokasi tugas luar wajib diisi.',
            'keperluan.required' => 'Keperluan/kegiatan tugas luar wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai penugasan wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai penugasan wajib diisi.',
            'tanggal_mulai.before_or_equal' => 'Tanggal mulai tidak boleh melewati tanggal selesai.',
            'bukti_tugas_luar.mimes' => 'Bukti tugas luar harus berformat PDF, JPG, JPEG, atau PNG.',
            'bukti_tugas_luar.max' => 'Ukuran file bukti tugas luar maksimal 5MB.',
        ]);

        $tanggalSelesai = $request->input('tanggal_selesai', $today);
        $tanggalMulai = $request->input('tanggal_mulai', $tanggalSelesai);
        $minTanggalSelesai = HariLibur::getBatasMundurHariKerja(3)->toDateString();

        if ($tanggalSelesai < $minTanggalSelesai) {
            return redirect()->back()->withInput()->with('error', 'Tanggal selesai tugas luar maksimal 3 hari kerja ke belakang.');
        }

        if ($tanggalMulai > $tanggalSelesai) {
            return redirect()->back()->withInput()->with('error', 'Tanggal mulai penugasan tidak boleh melewati tanggal selesai.');
        }

        $existing = PengajuanTugasLuar::where('pengguna_id', $user->id)
            ->where(function ($q) use ($tanggalSelesai) {
                $q->whereDate('tanggal', $tanggalSelesai)
                    ->orWhereDate('tanggal_selesai', $tanggalSelesai);
            })
            ->whereIn('status_verifikasi', ['menunggu', 'disetujui'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan Tugas Luar untuk hari ini.');
        }

        $buktiPath = null;
        if ($request->hasFile('bukti_tugas_luar')) {
            $buktiPath = $request->file('bukti_tugas_luar')->store('tugas_luar', 'public');
        }

        $waktuMulai = $request->waktu_mulai ?: $nowTimeStr;
        $waktuSelesai = $request->waktu_selesai ?: '16:00:00';

        $isToday = ($tanggalSelesai === $today);
        $presensiId = null;

        if ($isToday) {
            $todayPresensi = Presensi::where('pengguna_id', $user->id)
                ->whereDate('tanggal', $today)
                ->first();

            if (! $todayPresensi) {
                $todayPresensi = Presensi::create([
                    'pengguna_id' => $user->id,
                    'tanggal' => $today,
                    'jam_masuk' => $nowTimeStr,
                    'status' => 'hadir',
                    'mode_kerja' => 'tugas_luar',
                    'keterangan' => 'Tugas Luar: '.$request->tujuan,
                ]);
            } elseif (! $todayPresensi->jam_masuk) {
                $todayPresensi->update([
                    'jam_masuk' => $nowTimeStr,
                    'status' => 'hadir',
                    'mode_kerja' => 'tugas_luar',
                ]);
            }

            // Jika peserta sudah absen masuk pagi dan waktu mulai TL berbeda, waktu keluar kantor dicatat waktu pengajuan/mulai TL
            if ($todayPresensi->jam_masuk && $todayPresensi->jam_masuk !== $waktuMulai) {
                $todayPresensi->update([
                    'jam_keluar' => $waktuMulai,
                ]);
            } elseif (! $todayPresensi->jam_keluar) {
                $todayPresensi->update([
                    'jam_keluar' => $waktuSelesai,
                    'mode_kerja' => 'tugas_luar',
                ]);
            }

            $presensiId = $todayPresensi->id;
        } else {
            // Jika TL diajukan untuk kemarin atau besok, jangan sentuh presensi hari ini
            $targetPresensi = Presensi::where('pengguna_id', $user->id)
                ->whereDate('tanggal', $tanggalSelesai)
                ->first();

            $presensiId = $targetPresensi?->id;
        }

        PengajuanTugasLuar::create([
            'presensi_id' => $presensiId,
            'pengguna_id' => $user->id,
            'magang_id' => $user->magang?->id,
            'tanggal' => $tanggalSelesai,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'tujuan' => $request->tujuan,
            'keperluan' => $request->keperluan,
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'bukti' => $buktiPath,
            'status_verifikasi' => 'menunggu',
        ]);

        return redirect()->route('presensi.index')->with('success', 'Pengajuan Tugas Luar berhasil dikirim dan menunggu verifikasi pembimbing.');
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

        // Batas jam pulang: 16.00 sampai 18.00 WITA (kecuali pulang awal dengan alasan Sakit atau Izin)
        $jamSekarang = Carbon::now();
        $bukaPulang = $jamSekarang->copy()->setTime(16, 0, 0);
        $tutupPulang = $jamSekarang->copy()->setTime(18, 0, 0);
        $isPulangAwal = $jamSekarang->lessThan($bukaPulang);

        if ($isPulangAwal) {
            $alasan = $request->input('alasan_pulang');
            if (! in_array($alasan, ['Sakit', 'Izin'])) {
                return redirect()->back()->with('error', 'Presensi pulang belum dibuka. Presensi pulang normal dapat dilakukan pukul 16.00 sampai 18.00 WITA. Silakan pilih alasan Sakit atau Izin jika ingin melakukan presensi pulang sebelum pukul 16.00 WITA.');
            }
        }

        if ($jamSekarang->greaterThan($tutupPulang)) {
            return redirect()->back()->with('error', 'Batas waktu presensi pulang telah berakhir (pukul 18.00 WITA).');
        }

        // Wajib mengisi aktivitas harian dulu
        $hasAktivitas = Aktivitas::where('pengguna_id', $user->id)
            ->whereDate('tanggal', $today)
            ->exists();

        if (! $hasAktivitas) {
            return redirect()->back()->with('error', 'Anda belum mengisi aktivitas harian hari ini. Silakan isi aktivitas harian terlebih dahulu sebelum melakukan presensi pulang.');
        }

        $rules = [
            'lokasi_keluar' => 'required|string',
            'foto_keluar' => 'required|string',
            'face_descriptor' => 'required|string',
            'keterangan_keluar' => 'nullable|string|max:255',
        ];

        if ($isPulangAwal) {
            $rules['alasan_pulang'] = 'required|in:Sakit,Izin';
            $rules['keterangan_keluar'] = 'required|string|max:255';
        }

        $request->validate($rules, [
            'lokasi_keluar.required' => 'Titik lokasi GPS kepulangan wajib terdeteksi.',
            'foto_keluar.required' => 'Foto selfie pulang wajib diambil.',
            'face_descriptor.required' => 'Verifikasi wajah belum selesai. Silakan ulangi.',
            'alasan_pulang.required' => 'Kategori alasan pulang sebelum pukul 16.00 (Sakit atau Izin) wajib dipilih.',
            'alasan_pulang.in' => 'Kategori kepulangan harus berupa Sakit atau Izin.',
            'keterangan_keluar.required' => 'Alasan kepulangan sebelum pukul 16.00 wajib diisi.',
            'keterangan_keluar.max' => 'Alasan kepulangan maksimal 255 karakter.',
        ]);

        $inputAman = $request->except(['foto_keluar', 'face_descriptor']);

        // Cek apakah peserta memiliki Tugas Luar hari ini (tidak kena validasi radius kantor)
        $isTugasLuarToday = ($presensi->mode_kerja === 'tugas_luar')
            || PengajuanTugasLuar::where('pengguna_id', $user->id)
                ->whereDate('tanggal', $today)
                ->whereIn('status_verifikasi', ['disetujui', 'menunggu'])
                ->exists();

        // Onsite biasa: cek radius kantor (hanya jika bukan sedang Tugas Luar)
        $officeLocation = $this->getOfficeLocation($user);
        if (! $isTugasLuarToday && $presensi->mode_kerja === 'onsite') {
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
        if ($isPulangAwal) {
            $catatanPulang = $request->alasan_pulang;
            if ($request->filled('keterangan_keluar')) {
                $catatanPulang .= ' ('.$request->keterangan_keluar.')';
            }
            $finalKeterangan = $finalKeterangan
                ? $finalKeterangan.' | Pulang: '.$catatanPulang
                : $catatanPulang;
        } elseif ($request->filled('keterangan_keluar')) {
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

        $ketMsg = $isPulangAwal ? ' (Alasan: '.$request->alasan_pulang.')' : '';

        return redirect()->route('presensi.index')
            ->with('success', 'Presensi pulang berhasil dicatat pukul '.$jamKeluar.' WITA'.$ketMsg.'.');
    }

    /**
     * Mencetak laporan rekapitulasi presensi pribadi (untuk Magang).
     */
    public function cetak(Request $request)
    {
        /** @var Pengguna $user */
        $user = Auth::user();

        // Eager load relasi magang beserta penempatan untuk optimasi query
        $user->load([
            'magang.divisi',
            'magang.pembimbing',
            'magang.penempatanMagang.divisi',
            'magang.penempatanMagang.pembimbing',
        ]);

        $query = Presensi::where('pengguna_id', $user->id)->with('pengajuanTugasLuar');

        $bulan = $request->input('bulan');
        $tanggalMulai = $request->input('tanggal_mulai') ?? $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir') ?? $request->input('tanggal_selesai');

        $isBulanSpecified = $bulan && preg_match('/^\d{4}-\d{2}$/', $bulan);
        $bulanPresensiCount = 0;
        if ($isBulanSpecified) {
            $bulanPresensiCount = Presensi::where('pengguna_id', $user->id)
                ->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2))
                ->count();
        }

        if ($tanggalMulai && $tanggalAkhir && strtotime($tanggalMulai) && strtotime($tanggalAkhir)) {
            $query->whereBetween('tanggal', [$tanggalMulai, $tanggalAkhir]);
            $periodeText = Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMMM Y').' s/d '.Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y');
        } elseif ($tanggalMulai && strtotime($tanggalMulai)) {
            $query->whereDate('tanggal', '>=', $tanggalMulai);
            $periodeText = 'Mulai '.Carbon::parse($tanggalMulai)->locale('id')->isoFormat('D MMMM Y');
        } elseif ($tanggalAkhir && strtotime($tanggalAkhir)) {
            $query->whereDate('tanggal', '<=', $tanggalAkhir);
            $periodeText = 'Sampai '.Carbon::parse($tanggalAkhir)->locale('id')->isoFormat('D MMMM Y');
        } elseif ($isBulanSpecified && $bulanPresensiCount > 0) {
            $query->whereYear('tanggal', substr($bulan, 0, 4))
                ->whereMonth('tanggal', substr($bulan, 5, 2));
            $periodeText = Carbon::createFromFormat('Y-m', $bulan)->locale('id')->isoFormat('MMMM Y');
        } else {
            // Tampilkan seluruh rekaman presensi selama periode magang peserta agar rekapan tidak kosong
            $magang = $user->magang;
            if ($magang && $magang->tanggal_mulai && $magang->tanggal_selesai) {
                $periodeText = Carbon::parse($magang->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y').' s/d '.Carbon::parse($magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y');
            } else {
                $firstPresensi = Presensi::where('pengguna_id', $user->id)->min('tanggal');
                $lastPresensi = Presensi::where('pengguna_id', $user->id)->max('tanggal');
                if ($firstPresensi && $lastPresensi) {
                    $periodeText = Carbon::parse($firstPresensi)->locale('id')->isoFormat('D MMMM Y').' s/d '.Carbon::parse($lastPresensi)->locale('id')->isoFormat('D MMMM Y');
                } else {
                    $periodeText = Carbon::today()->locale('id')->isoFormat('MMMM Y');
                }
            }
        }

        if ($request->filled('mode_kerja') && in_array($request->mode_kerja, ['onsite', 'wfh', 'tugas_luar'])) {
            $query->where('mode_kerja', $request->mode_kerja);
        }

        $presensi = $query->orderBy('tanggal', 'asc')
            ->orderBy('jam_masuk', 'asc')
            ->get();

        $totalTL = $presensi->filter(fn ($p) => ($p->is_tugas_luar || $p->mode_kerja === 'tugas_luar') && ! in_array($p->status, ['sakit', 'izin', 'cuti']))->count();
        $totalOnsite = $presensi->filter(fn ($p) => $p->mode_kerja === 'onsite' && ! $p->is_tugas_luar && ! in_array($p->status, ['sakit', 'izin', 'cuti']))->count();
        $totalWfh = $presensi->filter(fn ($p) => $p->mode_kerja === 'wfh' && ! in_array($p->status, ['sakit', 'izin', 'cuti']))->count();

        $stats = [
            'total_hadir' => $presensi->filter(fn ($p) => ($p->status === 'hadir' || $p->is_tugas_luar) && ! in_array($p->status, ['sakit', 'izin', 'cuti']))->count(),
            'total_onsite' => $totalOnsite,
            'total_wfh' => $totalWfh,
            'total_tugas_luar' => $totalTL,
        ];

        $targetDate = $tanggalMulai ?? ($isBulanSpecified ? Carbon::parse($bulan.'-01')->toDateString() : Carbon::today()->toDateString());
        $divisi = $user->magang?->getDivisiAt($targetDate) ?? $user->magang?->divisi ?? Divisi::first();
        $pembimbing = $user->magang?->getPembimbingAt($targetDate) ?? $user->magang?->pembimbing ?? Pembimbing::first();
        $pengaturan = Pengaturan::getPengaturan();

        $magang = $user->magang;
        $periodeMagangText = ($magang && $magang->tanggal_mulai && $magang->tanggal_selesai)
            ? Carbon::parse($magang->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y').' s/d '.Carbon::parse($magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y')
            : $periodeText;

        $nomorSurat = $request->input('nomor_surat', '');
        $tanggalSurat = Carbon::now()->locale('id')->isoFormat('D MMMM Y');

        $viewData = compact(
            'user',
            'magang',
            'presensi',
            'stats',
            'periodeText',
            'periodeMagangText',
            'bulan',
            'divisi',
            'pembimbing',
            'pengaturan',
            'nomorSurat',
            'tanggalSurat'
        );

        if ($request->input('format') === 'html') {
            return view('presensi.cetak', $viewData);
        }

        $cleanPeriode = str_replace([' ', '/', '\\'], '-', $periodeText);
        $filename = 'Laporan-Presensi-'.$cleanPeriode.'.pdf';

        return $this->pdfService->renderView('presensi.cetak', $viewData, $filename, 'P');
    }

    /**
     * Menghitung ringkasan statistik presensi bulan berjalan untuk peserta magang.
     *
     * @return array<string, int>
     */
    private function getMonthlyStats(Pengguna $user): array
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // Ambil seluruh presensi user pada bulan ini
        $presensiList = Presensi::where('pengguna_id', $user->id)
            ->whereYear('tanggal', $currentYear)
            ->whereMonth('tanggal', $currentMonth)
            ->get();
        $userPresensi = $presensiList->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

        // Ambil izin yang disetujui pada bulan ini
        $userApprovedIzin = PengajuanIzin::where('pengguna_id', $user->id)
            ->where('status_approval', 'disetujui')
            ->whereDate('tanggal_mulai', '<=', $endOfMonth->toDateString())
            ->whereDate('tanggal_selesai', '>=', $startOfMonth->toDateString())
            ->get();

        // Ambil tugas luar yang disetujui pada bulan ini
        $userApprovedTugasLuar = PengajuanTugasLuar::where('pengguna_id', $user->id)
            ->where('status_verifikasi', 'disetujui')
            ->whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->get()
            ->keyBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

        // Lookup hari libur nasional
        $allHolidays = array_merge(
            HariLibur::getDaftarLiburNasionalBawaan(),
            HariLibur::whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->pluck('nama', 'tanggal')
                ->mapWithKeys(fn ($nama, $tgl) => [Carbon::parse($tgl)->toDateString() => $nama])
                ->toArray()
        );

        $magang = $user->magang;
        $unitStart = $magang?->tanggal_mulai ? Carbon::parse($magang->tanggal_mulai)->startOfDay() : null;
        $unitEnd = $magang?->tanggal_selesai ? Carbon::parse($magang->tanggal_selesai)->startOfDay() : null;

        $totalHadir = 0;
        $tepatWaktu = 0;
        $totalIzin = 0;
        $totalSakit = 0;
        $totalAlfa = 0;

        $cur = $startOfMonth->copy();
        while ($cur->lte($endOfMonth)) {
            $dateStr = $cur->toDateString();
            $presensi = $userPresensi->get($dateStr);
            $tugasLuar = $userApprovedTugasLuar->get($dateStr);
            $izinResmi = $userApprovedIzin->first(function ($item) use ($cur) {
                return $cur->betweenIncluded(
                    Carbon::parse($item->tanggal_mulai)->startOfDay(),
                    Carbon::parse($item->tanggal_selesai)->startOfDay()
                );
            });

            $isWeekendOrHoliday = $cur->isWeekend() || isset($allHolidays[$dateStr]);
            $isFuture = $cur->gt($today);
            $isActiveDay = $magang && ($unitStart === null || $cur->gte($unitStart)) && ($unitEnd === null || $cur->lte($unitEnd));

            // Jika akhir pekan / libur / masa depan tanpa ada rekaman presensi/tugas luar/izin, lewati
            if (($isWeekendOrHoliday || $isFuture) && ! $presensi && ! $tugasLuar && ! $izinResmi) {
                $cur->addDay();

                continue;
            }

            if ($izinResmi) {
                if ($izinResmi->jenis_izin === 'sakit') {
                    $totalSakit++;
                } else {
                    $totalIzin++;
                }
            } elseif ($presensi || $tugasLuar) {
                $isTugasLuar = $tugasLuar
                    || ($presensi && (
                        $presensi->status === 'tugas_luar'
                        || $presensi->mode_kerja === 'tugas_luar'
                        || stripos((string) $presensi->keterangan, 'tugas luar') !== false
                        || stripos((string) $presensi->keterangan, 'dinas luar') !== false
                    ));

                if ($isTugasLuar) {
                    $totalHadir++;
                    if ($presensi?->jam_masuk && substr($presensi->jam_masuk, 0, 8) <= '08:00:00') {
                        $tepatWaktu++;
                    }
                } elseif ($presensi->status === 'sakit') {
                    $totalSakit++;
                } elseif ($presensi->status === 'cuti' || $presensi->status === 'izin') {
                    $totalIzin++;
                } elseif ($presensi->status === 'hadir' || ! empty($presensi->jam_masuk)) {
                    $totalHadir++;
                    if ($presensi->jam_masuk && substr($presensi->jam_masuk, 0, 8) <= '08:00:00') {
                        $tepatWaktu++;
                    }
                } elseif ($presensi->status === 'alpa') {
                    $totalAlfa++;
                } else {
                    $totalAlfa++;
                }
            } else {
                // Hari kerja aktif tanpa presensi dan tanpa izin
                if ($isActiveDay && ! $isWeekendOrHoliday && ! $isFuture) {
                    if ($cur->lt($today) || $now->hour >= 16) {
                        $totalAlfa++;
                    }
                }
            }

            $cur->addDay();
        }

        $totalOnsite = $presensiList->where('mode_kerja', 'onsite')->whereNotIn('status', ['sakit', 'izin', 'cuti'])->count();
        $totalWfh = $presensiList->where('mode_kerja', 'wfh')->whereNotIn('status', ['sakit', 'izin', 'cuti'])->count();

        return [
            'total_hadir' => $totalHadir,
            'tepat_waktu' => $tepatWaktu,
            'total_izin' => $totalIzin,
            'total_sakit' => $totalSakit,
            'total_alfa' => $totalAlfa,
            'total_alpa' => $totalAlfa,
            'total_onsite' => $totalOnsite,
            'total_wfh' => $totalWfh,
        ];
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

        if ($user && $user->magang) {
            // Mengambil divisi aktif hari ini berdasarkan penempatan divisi peserta
            $divisi = $user->magang->getDivisiAt(Carbon::today()) ?? $user->magang->divisi;
            if ($divisi && $divisi->latitude && $divisi->longitude) {
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

    public function formMasuk()
    {
        $user = Auth::user();
        if (! $user->magang?->face_registered_at) {
            return redirect()->route('wajah.create');
        }
        $sudah = Presensi::where('pengguna_id', $user->id)
            ->whereDate('tanggal', today())->whereNotNull('jam_masuk')->exists();
        if ($sudah) {
            return redirect()->route('presensi.index')->with('error', 'Anda sudah melakukan presensi masuk hari ini.');
        }
        $officeLocation = $this->getOfficeLocation($user);
        $minTanggalSelesai = HariLibur::getBatasMundurHariKerja(3)->toDateString();

        return view('presensi.masuk', compact('officeLocation', 'minTanggalSelesai'));
    }

    public function formPulang()
    {
        $user = Auth::user();
        $presensi = Presensi::where('pengguna_id', $user->id)->whereDate('tanggal', today())->first();
        if (! $presensi?->jam_masuk || $presensi->jam_keluar) {
            return redirect()->route('presensi.index')->with('error', 'Presensi pulang tidak tersedia saat ini.');
        }

        $now = Carbon::now();
        $bukaPulang = $now->copy()->setTime(16, 0, 0);
        $tutupPulang = $now->copy()->setTime(18, 0, 0);

        if ($now->greaterThan($tutupPulang)) {
            return redirect()->route('presensi.index')->with('error', 'Batas waktu presensi pulang telah berakhir (pukul 18.00 WITA).');
        }

        $isPulangAwal = $now->lessThan($bukaPulang);
        $hasAktivitas = Aktivitas::where('pengguna_id', $user->id)->whereDate('tanggal', today())->exists();
        $isTL = $presensi->mode_kerja === 'tugas_luar'
            || PengajuanTugasLuar::where('pengguna_id', $user->id)->whereDate('tanggal', today())
                ->whereIn('status_verifikasi', ['disetujui', 'menunggu'])->exists();
        $cekRadius = $presensi->mode_kerja === 'onsite' && ! $isTL;   // sama dengan logika storeKeluar
        $officeLocation = $this->getOfficeLocation($user);

        return view('presensi.pulang', compact('hasAktivitas', 'cekRadius', 'officeLocation', 'isPulangAwal'));
    }
}
