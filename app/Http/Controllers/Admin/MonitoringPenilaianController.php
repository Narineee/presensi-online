<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Models\Penilaian;
use App\Services\PdfService;
use App\Services\PresensiScoreService;
use Illuminate\Http\Request;

class MonitoringPenilaianController extends Controller
{
    public function __construct(
        private PresensiScoreService $presensiScoreService,
        private PdfService $pdfService
    ) {}

    /**
     * Menampilkan rekapitulasi penilaian seluruh anak magang.
     */
    public function index(Request $request)
    {
        $query = Magang::with([
            'divisi',
            'pembimbing',
            'penilaians.pembimbing',
            'penilaians.penempatanMagang.divisi',
            'penilaians.detail.kriteria',
            'penempatanMagang.divisi',
            'penempatanMagang.pembimbing',
        ]);

        // Filter divisi
        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        // Filter pembimbing
        if ($request->filled('pembimbing_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('pembimbing_id', $request->pembimbing_id)
                    ->orWhereHas('penempatanMagang', function ($pq) use ($request) {
                        $pq->where('pembimbing_id', $request->pembimbing_id);
                    });
            });
        }

        // Filter status penilaian (sudah / belum)
        if ($request->filled('status_nilai')) {
            if ($request->status_nilai === 'sudah') {
                $query->has('penilaians');
            } elseif ($request->status_nilai === 'belum') {
                $query->doesntHave('penilaians');
            }
        }

        // Filter pencarian teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('no_induk', 'like', "%{$search}%")
                    ->orWhere('instansi_pendidikan', 'like', "%{$search}%");
            });
        }

        $magangList = $query->orderBy('nama_lengkap', 'asc')
            ->paginate(15)
            ->withQueryString();

        foreach ($magangList as $m) {
            $m->penilaian_akumulasi = $m->getPenilaianAkumulasi();
        }

        // Data dropdown filter
        $divisiList = Divisi::orderBy('nama_divisi', 'asc')->get();
        $pembimbingList = Pembimbing::orderBy('nama_lengkap', 'asc')->get();

        // Statistik
        $totalMagang = Magang::count();
        $sudahDinilai = Magang::has('penilaians')->count();
        $belumDinilai = $totalMagang - $sudahDinilai;
        $rataRataNilai = Penilaian::avg('total_nilai');

        return view('admin.penilaian.index', compact(
            'magangList',
            'divisiList',
            'pembimbingList',
            'totalMagang',
            'sudahDinilai',
            'belumDinilai',
            'rataRataNilai'
        ));
    }

    /**
     * Cetak rekapitulasi penilaian seluruh anak magang langsung dalam format PDF A4.
     */
    public function cetak(Request $request)
    {
        $query = Magang::with([
            'divisi',
            'pembimbing',
            'penilaians.pembimbing',
            'penilaians.penempatanMagang.divisi',
            'penilaians.detail.kriteria',
            'penempatanMagang.divisi',
            'penempatanMagang.pembimbing',
        ]);

        // Filter divisi
        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        // Filter pembimbing
        if ($request->filled('pembimbing_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('pembimbing_id', $request->pembimbing_id)
                    ->orWhereHas('penempatanMagang', function ($pq) use ($request) {
                        $pq->where('pembimbing_id', $request->pembimbing_id);
                    });
            });
        }

        // Filter status penilaian (sudah / belum)
        if ($request->filled('status_nilai')) {
            if ($request->status_nilai === 'sudah') {
                $query->has('penilaians');
            } elseif ($request->status_nilai === 'belum') {
                $query->doesntHave('penilaians');
            }
        }

        // Filter pencarian teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('no_induk', 'like', "%{$search}%")
                    ->orWhere('instansi_pendidikan', 'like', "%{$search}%");
            });
        }

        $magangList = $query->orderBy('nama_lengkap', 'asc')->get();

        // Ambil seluruh kriteria penilaian dan petakan ke huruf A, B, C, dst.
        $kriteriaList = KriteriaPenilaian::orderBy('id', 'asc')->get();
        $letters = range('A', 'Z');
        $kriteriaMap = [];
        $kriteriaLegends = [];

        foreach ($kriteriaList as $index => $k) {
            $code = $letters[$index] ?? ('K'.($index + 1));
            $kriteriaMap[$k->id] = $code;
            $kriteriaLegends[] = [
                'code' => $code,
                'nama' => $k->nama,
                'bobot' => $k->bobot,
                'label' => $code.' = '.$k->nama.($k->bobot ? ' ('.$k->bobot.'%)' : ''),
            ];
        }

        $rekapList = $magangList->map(function ($magang) use ($kriteriaList, $kriteriaMap) {
            $penilaian = $magang->getPenilaianAkumulasi();
            $kriteriaText = '-';
            $nilaiAkhir = '-';
            $predikat = '-';
            $keteranganPredikat = '';
            $isDinilai = false;
            $skorAngka = null;

            $nilaiPerKriteria = [];
            foreach ($kriteriaList as $k) {
                $nilaiPerKriteria[$k->id] = '-';
            }

            if ($penilaian) {
                $isDinilai = true;
                $skorAngka = $penilaian->nilai_akhir ?? $penilaian->total_nilai;
                $detailKeyed = collect($penilaian->detail)->keyBy('kriteria_id');
                $kriteriaParts = [];

                foreach ($kriteriaList as $k) {
                    $code = $kriteriaMap[$k->id];
                    $dt = $detailKeyed->get($k->id);
                    if ($dt && $dt->nilai !== null) {
                        $val = is_numeric($dt->nilai) ? (int) round($dt->nilai) : $dt->nilai;
                        $kriteriaParts[] = "{$code} = {$val}";
                        $nilaiPerKriteria[$k->id] = $val;
                    } else {
                        $kriteriaParts[] = "{$code} = -";
                        $nilaiPerKriteria[$k->id] = '-';
                    }
                }

                $kriteriaText = implode(', ', $kriteriaParts);
                $nilaiAkhir = is_numeric($skorAngka) ? round($skorAngka, 1) : ($skorAngka ?? '-');
                $predikat = $penilaian->predikat ?? '-';
                $keteranganPredikat = $penilaian->keterangan_predikat ?? '';
            }

            return (object) [
                'id' => $magang->id,
                'nama_lengkap' => $magang->nama_lengkap,
                'no_induk' => $magang->no_induk,
                'divisi' => $magang->divisi,
                'kriteria_teks' => $kriteriaText,
                'nilai_per_kriteria' => $nilaiPerKriteria,
                'nilai_akhir' => $nilaiAkhir,
                'predikat' => $predikat,
                'keterangan_predikat' => $keteranganPredikat,
                'is_dinilai' => $isDinilai,
                'skor_angka' => $skorAngka,
            ];
        });

        $pengaturan = Pengaturan::getPengaturan();

        $logoPath = public_path('storage/images/logo-kalsel.png');
        if (! file_exists($logoPath)) {
            $logoPath = storage_path('app/public/images/logo-kalsel.png');
        }
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($logoPath))
            : null;

        $sudahCount = $rekapList->where('is_dinilai', true)->count();
        $belumCount = $rekapList->where('is_dinilai', false)->count();
        $nilaiCollection = $rekapList->filter(fn ($item) => $item->is_dinilai && is_numeric($item->skor_angka))->pluck('skor_angka');
        $avgNilai = $nilaiCollection->isNotEmpty() ? round($nilaiCollection->avg(), 1) : 0;

        $stats = [
            'total' => $rekapList->count(),
            'sudah' => $sudahCount,
            'belum' => $belumCount,
            'rata_rata' => $avgNilai,
        ];

        $viewData = compact(
            'rekapList',
            'kriteriaList',
            'kriteriaMap',
            'kriteriaLegends',
            'pengaturan',
            'stats',
            'logoBase64'
        );

        if ($request->input('format') === 'html') {
            return view('admin.penilaian.cetak', $viewData);
        }

        $filename = 'Laporan-Rekapitulasi-Penilaian-Magang.pdf';

        return $this->pdfService->renderView('admin.penilaian.cetak', $viewData, $filename, 'P');
    }

    /**
     * Menampilkan lembar nilai anak magang untuk admin (termasuk cetak).
     */
    public function show($id)
    {
        $penilaian = Penilaian::with([
            'magang.divisi',
            'magang.pengguna',
            'magang.penilaians.pembimbing',
            'magang.penilaians.penempatanMagang.divisi',
            'magang.penilaians.detail.kriteria',
            'magang.penempatanMagang.divisi',
            'magang.penempatanMagang.pembimbing',
            'pembimbing',
            'penempatanMagang.divisi',
            'detail.kriteria',
        ])->findOrFail($id);

        $penilaianAkumulasi = $penilaian->magang->getPenilaianAkumulasi();
        $pengaturan = Pengaturan::getPengaturan();
        $presensiScore = $this->presensiScoreService->calculateScore($penilaian->magang, $penilaian->penempatanMagang);

        return view('admin.penilaian.show', compact('penilaian', 'penilaianAkumulasi', 'pengaturan', 'presensiScore'));
    }

    /**
     * Menghapus penilaian (reset nilai) dari sisi admin.
     */
    public function destroy($id)
    {
        $penilaian = Penilaian::findOrFail($id);
        $nama = $penilaian->magang->nama_lengkap ?? 'Magang';

        $penilaian->delete();

        return redirect()->route('admin.penilaian.index')
            ->with('success', 'Data penilaian untuk '.$nama.' berhasil dihapus.');
    }
}
