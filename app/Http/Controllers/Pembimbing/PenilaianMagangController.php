<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\DetailPenilaian;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
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

        $query = Magang::where('pembimbing_id', $pembimbing->id)
            ->with(['divisi', 'penilaian']);

        // Filter pencarian nama / no induk
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('no_induk', 'like', "%{$search}%")
                    ->orWhere('instansi_pendidikan', 'like', "%{$search}%");
            });
        }

        // Filter status penilaian (sudah_dinilai / belum_dinilai)
        if ($request->filled('status_nilai')) {
            if ($request->status_nilai === 'sudah') {
                $query->has('penilaian');
            } elseif ($request->status_nilai === 'belum') {
                $query->doesntHave('penilaian');
            }
        }

        $magangList = $query->orderBy('nama_lengkap', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Statistik
        $totalBinaan = Magang::where('pembimbing_id', $pembimbing->id)->count();
        $sudahDinilai = Magang::where('pembimbing_id', $pembimbing->id)->has('penilaian')->count();
        $belumDinilai = $totalBinaan - $sudahDinilai;

        return view('pembimbing.penilaian.index', compact(
            'magangList',
            'totalBinaan',
            'sudahDinilai',
            'belumDinilai'
        ));
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
        if (! $magangId) {
            return redirect()->route('pembimbing.penilaian.index')->with('error', 'Pilih anak magang yang ingin dinilai.');
        }

        $magang = Magang::where('id', $magangId)
            ->where('pembimbing_id', $pembimbing->id)
            ->with('divisi')
            ->firstOrFail();

        // Jika sudah pernah dinilai, arahkan ke halaman detail nilai
        if ($magang->penilaian) {
            return redirect()->route('pembimbing.penilaian.show', $magang->penilaian->id)
                ->with('info', 'Anak magang ini sudah memiliki penilaian akhir.');
        }

        // Ambil seluruh kriteria penilaian aktif
        $kriteriaList = KriteriaPenilaian::orderBy('id', 'asc')->get();

        if ($kriteriaList->isEmpty()) {
            return redirect()->route('pembimbing.penilaian.index')
                ->with('error', 'Belum ada kriteria penilaian yang dibuat admin. Hubungi admin sistem.');
        }

        // Hitung skor presensi objektif
        $presensiScore = $this->presensiScoreService->calculateScore($magang);

        return view('pembimbing.penilaian.create', compact('magang', 'kriteriaList', 'presensiScore'));
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

        // Validasi input sederhana
        $request->validate([
            'magang_id' => 'required|exists:magang,id',
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
            ->where('pembimbing_id', $pembimbing->id)
            ->firstOrFail();

        // Cek jika sudah pernah dinilai
        if ($magang->penilaian) {
            return redirect()->route('pembimbing.penilaian.show', $magang->penilaian->id)
                ->with('error', 'Penilaian untuk anak magang ini sudah ada.');
        }

        // Hitung skor objektif presensi digital (Opsi A)
        $presensiScore = $this->presensiScoreService->calculateScore($magang);
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
            ->where('pembimbing_id', $pembimbing->id)
            ->with([
                'magang.divisi',
                'magang.pengguna',
                'pembimbing',
                'detail.kriteria',
            ])
            ->firstOrFail();

        $presensiScore = $this->presensiScoreService->calculateScore($penilaian->magang);

        return view('pembimbing.penilaian.show', compact('penilaian', 'presensiScore'));
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
                'detail.kriteria',
            ])
            ->firstOrFail();

        $kriteriaList = KriteriaPenilaian::orderBy('id', 'asc')->get();

        // Hitung skor objektif presensi
        $presensiScore = $this->presensiScoreService->calculateScore($penilaian->magang);

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
        $presensiScore = $this->presensiScoreService->calculateScore($penilaian->magang);
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
