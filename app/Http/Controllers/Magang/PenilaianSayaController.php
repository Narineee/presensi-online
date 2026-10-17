<?php

namespace App\Http\Controllers\Magang;

use App\Http\Controllers\Controller;
use App\Services\PresensiScoreService;
use Illuminate\Support\Facades\Auth;

class PenilaianSayaController extends Controller
{
    public function __construct(private PresensiScoreService $presensiScoreService) {}

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
}
