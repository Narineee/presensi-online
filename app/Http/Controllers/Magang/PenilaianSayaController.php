<?php

namespace App\Http\Controllers\Magang;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Pembimbing;
use App\Models\Pengaturan;
use App\Services\PdfService;
use App\Services\PresensiScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PenilaianSayaController extends Controller
{
    public function __construct(
        private PresensiScoreService $presensiScoreService,
        private PdfService $pdfService
    ) {}

    /**
     * Menampilkan lembar nilai akhir anak magang yang sedang login.
     */
    public function index()
    {
        $magang = Auth::user()->magang;

        if (! $magang) {
            return redirect()->route('presensi.index')->with('error', 'Profil magang tidak ditemukan.');
        }

        $magang->load([
            'divisi',
            'pengguna',
            'pembimbing',
            'penempatanMagang.divisi',
            'penempatanMagang.pembimbing',
            'penilaians.pembimbing',
            'penilaians.penempatanMagang.divisi',
            'penilaians.detail.kriteria',
        ]);

        $penilaianAkumulasi = $magang->getPenilaianAkumulasi();
        $penilaian = $penilaianAkumulasi;
        $evaluasiList = $magang->penilaians;

        $presensiScore = $this->presensiScoreService->calculateScore($magang);

        return view('magang.penilaian.index', compact('magang', 'penilaian', 'penilaianAkumulasi', 'evaluasiList', 'presensiScore'));
    }

    /**
     * Cetak lembar penilaian akhir magang menggunakan mPDF.
     */
    public function cetak(Request $request)
    {
        $magang = Auth::user()->magang;

        if (! $magang) {
            return redirect()->route('presensi.index')->with('error', 'Profil magang tidak ditemukan.');
        }

        $magang->load([
            'divisi',
            'pengguna',
            'pembimbing',
            'penempatanMagang.divisi',
            'penempatanMagang.pembimbing',
            'penilaians.pembimbing',
            'penilaians.penempatanMagang.divisi',
            'penilaians.detail.kriteria',
        ]);

        $penilaianAkumulasi = $magang->getPenilaianAkumulasi();
        $penilaian = $penilaianAkumulasi;
        $evaluasiList = $magang->penilaians;

        if (! $penilaian) {
            return redirect()->route('magang.penilaian.index')->with('error', 'Penilaian akhir magang belum diterbitkan oleh pembimbing.');
        }

        $divisi = $penilaian->penempatanMagang?->divisi ?? $magang->divisi ?? Divisi::first();
        $pembimbing = $penilaian->pembimbing ?? $magang->pembimbing ?? Pembimbing::first();
        $pengaturan = Pengaturan::getPengaturan();

        $viewData = compact('magang', 'penilaian', 'penilaianAkumulasi', 'evaluasiList', 'divisi', 'pembimbing', 'pengaturan');

        if ($request->input('format') === 'html') {
            return view('magang.penilaian.cetak', $viewData);
        }

        $cleanNama = str_replace([' ', '/', '\\'], '-', $magang->nama_lengkap ?? 'Peserta');
        $filename = 'Lembar-Penilaian-Akhir-Magang-'.$cleanNama.'.pdf';

        return $this->pdfService->renderView('magang.penilaian.cetak', $viewData, $filename, 'P');
    }
}
