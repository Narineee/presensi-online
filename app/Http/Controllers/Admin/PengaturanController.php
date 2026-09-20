<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{
    /**
     * Menampilkan halaman formulir pengaturan aplikasi dan identitas pimpinan dinas.
     */
    public function index()
    {
        $pengaturan = Pengaturan::getPengaturan();

        return view('admin.pengaturan.index', compact('pengaturan'));
    }

    /**
     * Memperbarui data pengaturan aplikasi dan identitas pimpinan dinas.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_kepala_dinas' => 'required|string|max:150',
            'nip_kepala_dinas' => 'nullable|string|max:50',
            'jabatan_kepala_dinas' => 'required|string|max:150',
            'pangkat_golongan' => 'nullable|string|max:100',
            'nama_instansi' => 'required|string|max:150',
            'nama_aplikasi' => 'required|string|max:150',
            'kota_surat' => 'required|string|max:100',
            'alamat_instansi' => 'nullable|string|max:500',
            'telepon' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:150',
        ], [
            'nama_kepala_dinas.required' => 'Nama Kepala / Ketua Dinas wajib diisi.',
            'jabatan_kepala_dinas.required' => 'Jabatan Kepala Dinas wajib diisi.',
            'nama_instansi.required' => 'Nama instansi / dinas wajib diisi.',
            'nama_aplikasi.required' => 'Nama aplikasi wajib diisi.',
            'kota_surat.required' => 'Kota penandatanganan surat wajib diisi.',
            'email.email' => 'Format email instansi tidak valid.',
        ]);

        $pengaturan = Pengaturan::getPengaturan();
        $pengaturan->update($validated);

        return redirect()->route('admin.pengaturan.index')
            ->with('success', 'Pengaturan aplikasi dan data Ketua / Kepala Dinas berhasil disimpan!');
    }
}
