<?php

namespace App\Http\Controllers\Magang;

use App\Http\Controllers\Controller;
use App\Models\Penilaian;
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

        $penilaian = Penilaian::where('magang_id', $magang->id)
            ->with([
                'magang.divisi',
                'magang.pengguna',
                'pembimbing',
                'detail.kriteria',
            ])
            ->first();

        $presensiScore = $this->presensiScoreService->calculateScore($magang);

        return view('magang.penilaian.index', compact('magang', 'penilaian', 'presensiScore'));
    }
}
