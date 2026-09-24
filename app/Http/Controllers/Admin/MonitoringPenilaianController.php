<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Models\Penilaian;
use Illuminate\Http\Request;

class MonitoringPenilaianController extends Controller
{
    /**
     * Menampilkan rekapitulasi penilaian seluruh anak magang.
     */
    public function index(Request $request)
    {
        $query = Magang::with(['divisi', 'pembimbing', 'penilaian.detail.kriteria']);

        // Filter divisi
        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        // Filter pembimbing
        if ($request->filled('pembimbing_id')) {
            $query->where('pembimbing_id', $request->pembimbing_id);
        }

        // Filter status penilaian (sudah / belum)
        if ($request->filled('status_nilai')) {
            if ($request->status_nilai === 'sudah') {
                $query->has('penilaian');
            } elseif ($request->status_nilai === 'belum') {
                $query->doesntHave('penilaian');
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

        // Data dropdown filter
        $divisiList = Divisi::orderBy('nama_divisi', 'asc')->get();
        $pembimbingList = Pembimbing::orderBy('nama_lengkap', 'asc')->get();

        // Statistik
        $totalMagang = Magang::count();
        $sudahDinilai = Magang::has('penilaian')->count();
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
     * Cetak rekapitulasi penilaian seluruh anak magang (tampilan cetak khusus kertas).
     */
    public function cetak(Request $request)
    {
        $query = Magang::with(['divisi', 'pembimbing', 'penilaian.detail.kriteria']);

        // Filter divisi
        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        // Filter pembimbing
        if ($request->filled('pembimbing_id')) {
            $query->where('pembimbing_id', $request->pembimbing_id);
        }

        // Filter status penilaian (sudah / belum)
        if ($request->filled('status_nilai')) {
            if ($request->status_nilai === 'sudah') {
                $query->has('penilaian');
            } elseif ($request->status_nilai === 'belum') {
                $query->doesntHave('penilaian');
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
        $pengaturan = Pengaturan::getPengaturan();

        $stats = [
            'total' => $magangList->count(),
            'sudah' => $magangList->filter(fn ($m) => $m->penilaian !== null)->count(),
            'belum' => $magangList->filter(fn ($m) => $m->penilaian === null)->count(),
            'rata_rata' => $magangList->filter(fn ($m) => $m->penilaian !== null)->avg(fn ($m) => $m->penilaian->total_nilai) ?? 0,
        ];

        return view('admin.penilaian.cetak', compact('magangList', 'pengaturan', 'stats'));
    }

    /**
     * Menampilkan lembar nilai anak magang untuk admin (termasuk cetak).
     */
    public function show($id)
    {
        $penilaian = Penilaian::with([
            'magang.divisi',
            'magang.pengguna',
            'pembimbing',
            'detail.kriteria',
        ])->findOrFail($id);

        $pengaturan = Pengaturan::getPengaturan();

        return view('admin.penilaian.show', compact('penilaian', 'pengaturan'));
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
