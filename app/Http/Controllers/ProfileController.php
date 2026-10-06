<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Menampilkan form edit profil peserta magang.
     */
    public function edit(): View
    {
        $user = Auth::user();
        $magang = $user->magang;

        // Cek apakah profil masih belum lengkap (sesuai PRD tampilan.md "kalau masih kosong diwajibkan isi terlebih dahulu")
        $isProfileIncomplete = false;
        if ($user->role === 'magang' && $magang) {
            $isProfileIncomplete = empty($magang->instansi_pendidikan) || empty($magang->jurusan) || empty($magang->no_hp) || empty($magang->foto);
        }

        return view('profil.edit', compact('user', 'magang', 'isProfileIncomplete'));
    }

    /**
     * Menyimpan perubahan profil peserta magang.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role === 'magang' && $user->magang) {
            $request->validate([
                'nama_lengkap' => 'required|string|max:100',
                'jenis_kelamin' => 'nullable|in:L,P',
                'instansi_pendidikan' => 'required|string|max:150',
                'jurusan' => 'required|string|max:100',
                'no_hp' => 'required|string|max:20',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                'password' => ['nullable', 'string', 'min:6'],
            ], [
                'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
                'jenis_kelamin.in' => 'Pilihan jenis kelamin harus Laki-laki (L) atau Perempuan (P).',
                'instansi_pendidikan.required' => 'Nama asal instansi / kampus / sekolah wajib diisi.',
                'jurusan.required' => 'Jurusan / program studi wajib diisi.',
                'no_hp.required' => 'Nomor kontak / WhatsApp aktif wajib diisi.',
                'foto.image' => 'File foto harus berupa format gambar valid (JPG/PNG).',
                'foto.max' => 'Ukuran foto maksimal 2MB.',
                'password.min' => 'Password baru minimal 6 karakter.',
            ]);

            $magang = $user->magang;
            $dataToUpdate = [
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'instansi_pendidikan' => $request->instansi_pendidikan,
                'jurusan' => $request->jurusan,
                'no_hp' => $request->no_hp,
            ];

            if ($request->hasFile('foto')) {
                if ($magang->foto && Storage::disk('public')->exists($magang->foto)) {
                    Storage::disk('public')->delete($magang->foto);
                }
                $dataToUpdate['foto'] = $request->file('foto')->store('foto-magang', 'public');
            }

            $magang->update($dataToUpdate);
        }

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('profil.edit')->with('success', 'Profil Anda berhasil diperbarui!');
    }
}
