<?php

namespace App\Http\Controllers;

use App\Services\FaceVerificationService;
use App\Support\StoresBase64Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WajahController extends Controller
{
    use StoresBase64Image;

    public function create()
    {
        $magang = Auth::user()->magang;
        abort_unless($magang, 403);

        if ($magang->face_registered_at) {
            return redirect()->route('presensi.index');
        }

        return view('wajah.create');
    }

    public function store(Request $request, FaceVerificationService $face)
    {
        $magang = Auth::user()->magang;
        abort_unless($magang, 403);

        // Data wajah terkunci setelah tersimpan; hanya admin yang bisa mereset
        if ($magang->face_registered_at) {
            return redirect()->route('presensi.index')
                ->with('error', 'Wajah Anda sudah terdaftar. Hubungi admin jika perlu direset.');
        }

        $request->validate([
            'face_foto' => 'required|string|max:3000000',
            'face_descriptors' => 'required|string',
            'persetujuan' => 'accepted',
        ], [
            'face_foto.required' => 'Foto wajah belum diambil.',
            'face_descriptors.required' => 'Foto wajah belum diambil.',
            'persetujuan.accepted' => 'Centang persetujuan penggunaan data wajah terlebih dahulu.',
        ]);

        $descriptors = $face->parse($request->face_descriptors);
        if (! $descriptors) {
            return back()->withErrors(['face_descriptors' => 'Data wajah tidak valid. Silakan ambil foto ulang.']);
        }

        $path = $this->compressAndStoreImage(
            $request->face_foto,
            'wajah',
            'wajah_'.$magang->id.'_'.Str::random(8),
            'local'
        );

        if (! $path) {
            return back()->withErrors(['face_foto' => 'Foto gagal disimpan. Silakan ambil foto ulang.']);
        }

        $magang->update([
            'face_descriptors' => $descriptors,
            'face_foto' => $path,
            'face_registered_at' => now(),
        ]);

        return redirect()->route('presensi.index')
            ->with('success', 'Wajah berhasil didaftarkan. Sekarang Anda dapat melakukan presensi.');
    }
}