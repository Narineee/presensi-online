<?php

namespace App\Http\Controllers\Magang;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SertifikatSayaController extends Controller
{
    public function __construct(
        private PdfService $pdfService
    ) {}

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
        $tanggalCetak = $magang->tanggal_selesai
            ? Carbon::parse($magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y')
            : Carbon::now()->locale('id')->isoFormat('D MMMM Y');

        $bahan1Path = public_path('images/sertifikat/bahan-1.png');
        $bahan2Path = public_path('images/sertifikat/bahan-2.png');

        $bahan1Base64 = file_exists($bahan1Path)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($bahan1Path))
            : null;
        $bahan2Base64 = file_exists($bahan2Path)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($bahan2Path))
            : null;

        return view('magang.sertifikat.index', compact(
            'magang',
            'penilaian',
            'pengaturan',
            'tanggalMulai',
            'tanggalSelesai',
            'tanggalCetak',
            'bahan1Base64',
            'bahan2Base64'
        ));
    }

    /**
     * Cetak sertifikat kelulusan magang dalam format PDF (A4 Landscape) via mPDF.
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
            'penilaians.pembimbing',
            'penilaians.detail.kriteria',
        ]);

        $penilaian = $magang->getPenilaianAkumulasi();

        if (! $penilaian) {
            return redirect()->route('magang.sertifikat.index')->with('error', 'Sertifikat kelulusan belum dapat dicetak karena evaluasi nilai akhir belum diterbitkan.');
        }

        $pengaturan = Pengaturan::getPengaturan();

        $tanggalMulai = $magang->tanggal_mulai
            ? Carbon::parse($magang->tanggal_mulai)->locale('id')->isoFormat('D MMMM Y')
            : '-';
        $tanggalSelesai = $magang->tanggal_selesai
            ? Carbon::parse($magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y')
            : '-';
        $tanggalCetak = $magang->tanggal_selesai
            ? Carbon::parse($magang->tanggal_selesai)->locale('id')->isoFormat('D MMMM Y')
            : Carbon::now()->locale('id')->isoFormat('D MMMM Y');

        $bahan1Path = public_path('images/sertifikat/bahan-1.png');
        $bahan2Path = public_path('images/sertifikat/bahan-2.png');

        $bahan1Base64 = file_exists($bahan1Path)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($bahan1Path))
            : null;
        $bahan2Base64 = file_exists($bahan2Path)
            ? 'data:image/png;base64,'.base64_encode(file_get_contents($bahan2Path))
            : null;

        $viewData = compact(
            'magang',
            'penilaian',
            'pengaturan',
            'tanggalMulai',
            'tanggalSelesai',
            'tanggalCetak',
            'bahan1Base64',
            'bahan2Base64'
        );

        if ($request->input('format') === 'html') {
            return view('magang.sertifikat.cetak', $viewData);
        }

        $cleanNama = str_replace([' ', '/', '\\'], '-', $magang->nama_lengkap ?? 'Peserta');
        $filename = 'Sertifikat-Kelulusan-Magang-'.$cleanNama.'.pdf';

        return $this->pdfService->renderView(
            'magang.sertifikat.cetak',
            $viewData,
            $filename,
            'L',
            [
                'format' => 'A4-L',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'margin_header' => 0,
                'margin_footer' => 0,
                'autoPageBreak' => true,
            ]
        );
    }
}
