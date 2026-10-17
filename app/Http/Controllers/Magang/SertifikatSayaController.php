<?php

namespace App\Http\Controllers\Magang;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SertifikatSayaController extends Controller
{
    /**
     * Menampilkan sertifikat kelulusan magang untuk peserta yang sedang login.
     */
    public function index(Request $request)
    {
        $magang = Auth::user()->magang;

        if (! $magang) {
            return redirect()->route('presensi.index')->with('error', 'Profil magang tidak ditemukan.');
        }

        $magang->load([
            'divisi',
            'pengguna',
            'pembimbing',
            'penilaians.pembimbing',
            'penilaians.detail.kriteria',
        ]);

        $penilaian = $magang->getPenilaianAkumulasi();

        $pengaturan = Pengaturan::getPengaturan();

        $tanggalMulai = $magang->tanggal_mulai
            ? Carbon::parse($magang->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y')
            : '-';
        $tanggalSelesai = $magang->tanggal_selesai
            ? Carbon::parse($magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y')
            : '-';
        $tanggalCetak = Carbon::now()->locale('id')->isoFormat('D MMMM Y');

        return view('magang.sertifikat.index', compact(
            'magang',
            'penilaian',
            'pengaturan',
            'tanggalMulai',
            'tanggalSelesai',
            'tanggalCetak'
        ));
    }
}
