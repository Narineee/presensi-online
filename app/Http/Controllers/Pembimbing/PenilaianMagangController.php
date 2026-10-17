<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\DetailPenilaian;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pengaturan;
use App\Models\Penilaian;
use App\Services\PresensiScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenilaianMagangController extends Controller
{
    public function __construct(private PresensiScoreService $presensiScoreService) {}

    /**
     * Menampilkan daftar anak magang binaan dan status penilaian akhirnya.
     */
    public function index(Request $request)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $query = Magang::where(function ($q) use ($pembimbing) {
            $q->where('pembimbing_id', $pembimbing->id)
                ->orWhereHas('penempatanMagang', function ($pq) use ($pembimbing) {
                    $pq->where('pembimbing_id', $pembimbing->id);
                });
        })->with([
            'divisi',
            'penilaians.detail.kriteria',
            'penilaians.pembimbing',
            'penilaians.penempatanMagang.divisi',
            'penempatanMagang.divisi',
            'penempatanMagang.pembimbing',
            'pengguna',
        ]);

        // Filter pencarian nama / no induk / instansi
        $search = $request->input('q', $request->input('search'));
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('no_induk', 'like', "%{$search}%")
                    ->orWhere('instansi_pendidikan', 'like', "%{$search}%");
            });
        }

        // Filter tanggal mulai & selesai
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai', $request->input('tanggal_akhir'));

        if ($tanggalMulai && $tanggalSelesai) {
            $query->where(function ($dq) use ($tanggalMulai, $tanggalSelesai) {
                $dq->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                    ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                    ->orWhere(function ($overlap) use ($tanggalMulai, $tanggalSelesai) {
                        $overlap->where('tanggal_mulai', '<=', $tanggalMulai)
                            ->where('tanggal_selesai', '>=', $tanggalSelesai);
                    });
            });
        } elseif ($tanggalMulai) {
            $query->whereDate('tanggal_mulai', '>=', $tanggalMulai);
        } elseif ($tanggalSelesai) {
            $query->whereDate('tanggal_selesai', '<=', $tanggalSelesai);
        }

        // Filter status penilaian terhadap pembimbing yang sedang login (sudah / belum)
        $statusNilai = $request->input('status_nilai');
        if ($statusNilai === 'sudah') {
            $query->whereHas('penilaians', function ($pq) use ($pembimbing) {
                $pq->where('pembimbing_id', $pembimbing->id);
            });
        } elseif ($statusNilai === 'belum') {
            $query->whereDoesntHave('penilaians', function ($pq) use ($pembimbing) {
                $pq->where('pembimbing_id', $pembimbing->id);
            });
        }

        $allMagang = $query->orderBy('nama_lengkap', 'asc')->get();

        // Siapkan atribut pembimbing untuk setiap peserta
        foreach ($allMagang as $m) {
            $m->penilaian_saya = $m->penilaians->where('pembimbing_id', $pembimbing->id)->first();
            $m->penilaian_akumulasi = $m->getPenilaianAkumulasi();
        }

        // Pisahkan menjadi Perlu Dinilai dan Sudah Dinilai dari sudut pandang pembimbing ini
        $perluDinilai = $allMagang->filter(fn ($m) => $m->penilaian_saya === null);
        $sudahDinilaiList = $allMagang->filter(fn ($m) => $m->penilaian_saya !== null);

        // Statistik
        $totalBinaan = $allMagang->count();
        $sudahDinilai = $sudahDinilaiList->count();
        $belumDinilai = $perluDinilai->count();
        $totalKriteria = KriteriaPenilaian::count();

        $magangList = $allMagang;

        return view('pembimbing.penilaian.index', compact(
            'perluDinilai',
            'sudahDinilaiList',
            'magangList',
            'totalBinaan',
            'sudahDinilai',
            'belumDinilai',
            'totalKriteria',
            'search',
            'statusNilai',
            'tanggalMulai',
            'tanggalSelesai'
        ));
    }

    /**
     * Mencetak rekapitulasi penilaian magang binaan.
     */
    public function cetak(Request $request)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $query = Magang::where(function ($q) use ($pembimbing) {
            $q->where('pembimbing_id', $pembimbing->id)
                ->orWhereHas('penempatanMagang', function ($pq) use ($pembimbing) {
                    $pq->where('pembimbing_id', $pembimbing->id);
                });
        })->with([
            'divisi',
            'penilaians.detail.kriteria',
            'penilaians.pembimbing',
            'penilaians.penempatanMagang.divisi',
            'penempatanMagang.divisi',
            'penempatanMagang.pembimbing',
            'pengguna',
        ]);

        $search = $request->input('q', $request->input('search'));
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('no_induk', 'like', "%{$search}%")
                    ->orWhere('instansi_pendidikan', 'like', "%{$search}%");
            });
        }

        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai', $request->input('tanggal_akhir'));

        if ($tanggalMulai && $tanggalSelesai) {
            $query->where(function ($dq) use ($tanggalMulai, $tanggalSelesai) {
                $dq->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                    ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai]);
            });
        } elseif ($tanggalMulai) {
            $query->whereDate('tanggal_mulai', '>=', $tanggalMulai);
        } elseif ($tanggalSelesai) {
            $query->whereDate('tanggal_selesai', '<=', $tanggalSelesai);
        }

        $statusNilai = $request->input('status_nilai');
        if ($statusNilai === 'sudah') {
            $query->whereHas('penilaians', function ($pq) use ($pembimbing) {
                $pq->where('pembimbing_id', $pembimbing->id);
            });
        } elseif ($statusNilai === 'belum') {
            $query->whereDoesntHave('penilaians', function ($pq) use ($pembimbing) {
                $pq->where('pembimbing_id', $pembimbing->id);
            });
        }

        // Filter peserta magang tertentu jika dipilih
        $selectedMagang = null;
        if ($request->filled('magang_id')) {
            $selectedMagang = Magang::find($request->magang_id);
            if ($selectedMagang) {
                $query->where('id', $selectedMagang->id);
            }
        }

        $magangList = $query->orderBy('nama_lengkap', 'asc')->get();
        foreach ($magangList as $m) {
            $m->penilaian_saya = $m->penilaians->where('pembimbing_id', $pembimbing->id)->first();
            $m->penilaian_akumulasi = $m->getPenilaianAkumulasi();
        }

        $kriteriaList = KriteriaPenilaian::orderBy('id', 'asc')->get();
        $pengaturan = Pengaturan::getPengaturan();

        $stats = [
            'total' => $magangList->count(),
            'sudah' => $magangList->filter(fn ($m) => $m->penilaian_saya !== null)->count(),
            'belum' => $magangList->filter(fn ($m) => $m->penilaian_saya === null)->count(),
            'rata_rata' => $magangList->filter(fn ($m) => $m->penilaian_akumulasi !== null)->avg(fn ($m) => $m->penilaian_akumulasi->total_nilai) ?? 0,
        ];

        return view('pembimbing.penilaian.cetak', compact('magangList', 'pengaturan', 'stats', 'pembimbing', 'kriteriaList', 'selectedMagang'));
    }

    /**
     * Menampilkan formulir penilaian untuk anak magang tertentu.
     */
    public function create(Request $request)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $magangId = $request->get('magang_id');
        $penempatanId = $request->get('penempatan_magang_id');

        if (! $magangId) {
            return redirect()->route('pembimbing.penilaian.index')->with('error', 'Pilih anak magang yang ingin dinilai.');
        }

        $magang = Magang::where('id', $magangId)
            ->where(function ($q) use ($pembimbing) {
                $q->where('pembimbing_id', $pembimbing->id)
                    ->orWhereHas('penempatanMagang', function ($pq) use ($pembimbing) {
                        $pq->where('pembimbing_id', $pembimbing->id);
                    });
            })
            ->with(['divisi', 'penempatanMagang.divisi', 'penempatanMagang.pembimbing'])
            ->firstOrFail();

        // Cari penempatan terkait yang diasuh pembimbing ini
        $penempatan = null;
        if ($penempatanId) {
            $penempatan = $magang->penempatanMagang()->find($penempatanId);
        } else {
            $penempatan = $magang->penempatanMagang()
                ->where('pembimbing_id', $pembimbing->id)
                ->first()
                ?? $magang->getPenempatanAt(today())
                ?? $magang->penempatanMagang()->first();
        }

        // Cek jika pembimbing ini sudah memberikan penilaian untuk penempatan ini
        $existingPenilaian = Penilaian::where('magang_id', $magang->id)
            ->where('pembimbing_id', $pembimbing->id)
            ->when($penempatan, fn ($q) => $q->where('penempatan_magang_id', $penempatan->id))
            ->first();

        if ($existingPenilaian) {
            return redirect()->route('pembimbing.penilaian.show', $existingPenilaian->id)
                ->with('info', 'Anda sudah memberikan penilaian untuk peserta ini pada unit penempatan terkait.');
        }

        // Ambil seluruh kriteria penilaian aktif
        $kriteriaList = KriteriaPenilaian::orderBy('id', 'asc')->get();

        if ($kriteriaList->isEmpty()) {
            return redirect()->route('pembimbing.penilaian.index')
                ->with('error', 'Belum ada kriteria penilaian yang dibuat admin. Hubungi admin sistem.');
        }

        // Hitung skor presensi objektif (sesuai periode penempatan jika ada)
        $presensiScore = $this->presensiScoreService->calculateScore($magang, $penempatan);

        return view('pembimbing.penilaian.create', compact('magang', 'kriteriaList', 'presensiScore', 'penempatan'));
    }

    /**
     * Menyimpan data penilaian akhir dan rincian nilainya.
     */
    public function store(Request $request)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        // Validasi input
        $request->validate([
            'magang_id' => 'required|exists:magang,id',
            'penempatan_magang_id' => 'nullable|exists:penempatan_magang,id',
            'nilai' => 'required|array',
            'nilai.*' => 'required|numeric|min:0|max:100',
            'status_magang' => 'nullable|in:aktif,selesai',
        ], [
            'magang_id.required' => 'Data magang wajib dipilih.',
            'nilai.required' => 'Nilai per kriteria wajib diisi.',
            'nilai.*.required' => 'Semua nilai kriteria wajib diisi.',
            'nilai.*.numeric' => 'Nilai harus berupa angka.',
            'nilai.*.min' => 'Nilai minimal 0.',
            'nilai.*.max' => 'Nilai maksimal 100.',
        ]);

        $magang = Magang::where('id', $request->magang_id)
            ->where(function ($q) use ($pembimbing) {
                $q->where('pembimbing_id', $pembimbing->id)
                    ->orWhereHas('penempatanMagang', function ($pq) use ($pembimbing) {
                        $pq->where('pembimbing_id', $pembimbing->id);
                    });
            })
            ->firstOrFail();

        $penempatanId = $request->penempatan_magang_id;
        $penempatan = null;
        if ($penempatanId) {
            $penempatan = $magang->penempatanMagang()->find($penempatanId);
        } else {
            $penempatan = $magang->penempatanMagang()
                ->where('pembimbing_id', $pembimbing->id)
                ->first();
            $penempatanId = $penempatan?->id;
        }

        // Cek jika sudah pernah dinilai oleh pembimbing ini untuk penempatan ini
        $existingPenilaian = Penilaian::where('magang_id', $magang->id)
            ->where('pembimbing_id', $pembimbing->id)
            ->when($penempatanId, fn ($q) => $q->where('penempatan_magang_id', $penempatanId))
            ->first();

        if ($existingPenilaian) {
            return redirect()->route('pembimbing.penilaian.show', $existingPenilaian->id)
                ->with('error', 'Penilaian Anda untuk peserta pada penempatan ini sudah ada.');
        }

        // Hitung skor objektif presensi digital (berdasarkan penempatan jika ada)
        $presensiScore = $this->presensiScoreService->calculateScore($magang, $penempatan);
        $presensiKriteriaId = $presensiScore['kriteria_presensi']?->id;

        $kriteriaList = KriteriaPenilaian::all();

        // Hitung total nilai berbobot
        $totalBobot = 0;
        $sumBobotNilai = 0;

        foreach ($kriteriaList as $kriteria) {
            $bobot = $kriteria->bobot;

            // Jika ini kriteria presensi, gunakan skor objektif sistem
            if ($kriteria->is_presensi || $kriteria->id === $presensiKriteriaId) {
                $nilaiInput = $presensiScore['nilai_angka'];
            } else {
                $nilaiInput = (int) ($request->nilai[$kriteria->id] ?? 0);
            }

            $sumBobotNilai += ($nilaiInput * $bobot);
            $totalBobot += $bobot;
        }

        $totalNilai = $totalBobot > 0 ? (int) round($sumBobotNilai / $totalBobot) : 0;

        // Simpan tabel utama penilaian
        $penilaian = Penilaian::create([
            'magang_id' => $magang->id,
            'pembimbing_id' => $pembimbing->id,
            'penempatan_magang_id' => $penempatanId,
            'total_nilai' => $totalNilai,
        ]);

        // Simpan setiap rincian kriteria ke detail_penilaian
        foreach ($kriteriaList as $kriteria) {
            if ($kriteria->is_presensi || $kriteria->id === $presensiKriteriaId) {
                $nilaiInput = $presensiScore['nilai_angka'];
            } else {
                $nilaiInput = (int) ($request->nilai[$kriteria->id] ?? 0);
            }

            DetailPenilaian::create([
                'penilaian_id' => $penilaian->id,
                'kriteria_id' => $kriteria->id,
                'nilai' => $nilaiInput,
            ]);
        }

        // Update status magang jika dipilih 'selesai'
        if ($request->filled('status_magang')) {
            $magang->update([
                'status' => $request->status_magang,
            ]);
        }

        return redirect()->route('pembimbing.penilaian.show', $penilaian->id)
            ->with('success', 'Penilaian akhir berhasil disimpan! Total nilai: '.$totalNilai.' ('.$penilaian->predikat.')');
    }

    /**
     * Menampilkan lembar nilai akhir anak magang beserta rinciannya.
     */
    public function show($id)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $penilaian = Penilaian::where('id', $id)
            ->with([
                'magang.divisi',
                'magang.pengguna',
                'magang.penempatanMagang.divisi',
                'magang.penempatanMagang.pembimbing',
                'magang.penilaians.pembimbing',
                'magang.penilaians.penempatanMagang.divisi',
                'magang.penilaians.detail.kriteria',
                'pembimbing',
                'penempatanMagang.divisi',
                'detail.kriteria',
            ])
            ->firstOrFail();

        // Otorisasi: hanya pembimbing pembuat atau pembimbing yang membina peserta ini
        $isMyAssessment = ($penilaian->pembimbing_id === $pembimbing->id);
        $isMyIntern = ($penilaian->magang->pembimbing_id === $pembimbing->id) ||
            $penilaian->magang->penempatanMagang()->where('pembimbing_id', $pembimbing->id)->exists();

        if (! $isMyAssessment && ! $isMyIntern) {
            abort(403, 'Anda tidak memiliki akses ke lembar penilaian ini.');
        }

        $penilaianAkumulasi = $penilaian->magang->getPenilaianAkumulasi();
        $presensiScore = $this->presensiScoreService->calculateScore($penilaian->magang, $penilaian->penempatanMagang);

        return view('pembimbing.penilaian.show', compact('penilaian', 'penilaianAkumulasi', 'presensiScore'));
    }

    /**
     * Menampilkan form edit nilai.
     */
    public function edit($id)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $penilaian = Penilaian::where('id', $id)
            ->where('pembimbing_id', $pembimbing->id)
            ->with([
                'magang.divisi',
                'penempatanMagang.divisi',
                'detail.kriteria',
            ])
            ->firstOrFail();

        $kriteriaList = KriteriaPenilaian::orderBy('id', 'asc')->get();

        // Hitung skor objektif presensi
        $presensiScore = $this->presensiScoreService->calculateScore($penilaian->magang, $penilaian->penempatanMagang);

        // Format nilai per kriteria ID agar mudah diakses di form blade
        $nilaiMap = $penilaian->detail->pluck('nilai', 'kriteria_id')->toArray();

        return view('pembimbing.penilaian.edit', compact('penilaian', 'kriteriaList', 'nilaiMap', 'presensiScore'));
    }

    /**
     * Memperbarui nilai magang.
     */
    public function update(Request $request, $id)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $penilaian = Penilaian::where('id', $id)
            ->where('pembimbing_id', $pembimbing->id)
            ->firstOrFail();

        $request->validate([
            'nilai' => 'required|array',
            'nilai.*' => 'required|numeric|min:0|max:100',
            'status_magang' => 'nullable|in:aktif,selesai',
        ], [
            'nilai.required' => 'Nilai per kriteria wajib diisi.',
            'nilai.*.required' => 'Semua nilai kriteria wajib diisi.',
            'nilai.*.numeric' => 'Nilai harus berupa angka.',
            'nilai.*.min' => 'Nilai minimal 0.',
            'nilai.*.max' => 'Nilai maksimal 100.',
        ]);

        // Hitung skor objektif presensi
        $presensiScore = $this->presensiScoreService->calculateScore($penilaian->magang, $penilaian->penempatanMagang);
        $presensiKriteriaId = $presensiScore['kriteria_presensi']?->id;

        $kriteriaList = KriteriaPenilaian::all();

        $totalBobot = 0;
        $sumBobotNilai = 0;

        foreach ($kriteriaList as $kriteria) {
            $bobot = $kriteria->bobot;

            // Kriteria presensi terkunci otomatis
            if ($kriteria->is_presensi || $kriteria->id === $presensiKriteriaId) {
                $nilaiInput = $presensiScore['nilai_angka'];
            } else {
                $nilaiInput = (int) ($request->nilai[$kriteria->id] ?? 0);
            }

            $sumBobotNilai += ($nilaiInput * $bobot);
            $totalBobot += $bobot;

            // Update atau buat detail nilai jika ada kriteria baru ditambahkan
            DetailPenilaian::updateOrCreate(
                [
                    'penilaian_id' => $penilaian->id,
                    'kriteria_id' => $kriteria->id,
                ],
                [
                    'nilai' => $nilaiInput,
                ]
            );
        }

        $totalNilai = $totalBobot > 0 ? (int) round($sumBobotNilai / $totalBobot) : 0;

        $penilaian->update([
            'total_nilai' => $totalNilai,
        ]);

        if ($request->filled('status_magang')) {
            $penilaian->magang->update([
                'status' => $request->status_magang,
            ]);
        }

        return redirect()->route('pembimbing.penilaian.show', $penilaian->id)
            ->with('success', 'Nilai magang berhasil diperbarui! Total nilai: '.$totalNilai.' ('.$penilaian->predikat.')');
    }

    /**
     * Menghapus penilaian (reset nilai).
     */
    public function destroy($id)
    {
        $pembimbing = Auth::user()->pembimbing;
        if (! $pembimbing) {
            return redirect()->route('pembimbing.dashboard')->with('error', 'Profil pembimbing tidak ditemukan.');
        }

        $penilaian = Penilaian::where('id', $id)
            ->where('pembimbing_id', $pembimbing->id)
            ->firstOrFail();

        $nama = $penilaian->magang->nama_lengkap ?? 'Magang';

        // Hapus penilaian (detail penilaian akan otomatis terhapus karena cascade on delete)
        $penilaian->delete();

        return redirect()->route('pembimbing.penilaian.index')
            ->with('success', 'Data penilaian untuk '.$nama.' berhasil dihapus.');
    }
}
