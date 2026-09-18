<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use Illuminate\Http\Request;

class DivisiController extends Controller
{
    // Menampilkan daftar semua divisi
    public function index()
    {
        $divisi = Divisi::latest()->paginate(10);

        return view('admin.divisi.index', compact('divisi'));
    }

    // Menampilkan form tambah divisi
    public function create()
    {
        return view('admin.divisi.create');
    }

    // Menyimpan data divisi baru
    public function store(Request $request)
    {
        // Validasi input form langsung di controller
        $request->validate([
            'nama_divisi' => 'required|string|max:100',
            'nama_pimpinan' => 'required|string|max:100',
            'nip_pimpinan' => 'nullable|string|max:30',
            'jabatan_pimpinan' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meter' => 'nullable|integer',
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_pimpinan.required' => 'Nama pimpinan wajib diisi.',
        ]);

        // Simpan ke database
        Divisi::create([
            'nama_divisi' => $request->nama_divisi,
            'nama_pimpinan' => $request->nama_pimpinan,
            'nip_pimpinan' => $request->nip_pimpinan,
            'jabatan_pimpinan' => $request->jabatan_pimpinan,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter ?? 100,
        ]);

        return redirect()->route('admin.divisi.index')->with('success', 'Data divisi berhasil ditambahkan!');
    }

    // Menampilkan detail divisi (dialihkan ke edit)
    public function show($id)
    {
        return redirect()->route('admin.divisi.edit', $id);
    }

    // Menampilkan form edit divisi
    public function edit($id)
    {
        $divisi = Divisi::findOrFail($id);

        return view('admin.divisi.edit', compact('divisi'));
    }

    // Memperbarui data divisi
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'nama_divisi' => 'required|string|max:100',
            'nama_pimpinan' => 'required|string|max:100',
            'nip_pimpinan' => 'nullable|string|max:30',
            'jabatan_pimpinan' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meter' => 'nullable|integer',
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'nama_pimpinan.required' => 'Nama pimpinan wajib diisi.',
        ]);

        // Cari data dan update
        $divisi = Divisi::findOrFail($id);
        $divisi->update([
            'nama_divisi' => $request->nama_divisi,
            'nama_pimpinan' => $request->nama_pimpinan,
            'nip_pimpinan' => $request->nip_pimpinan,
            'jabatan_pimpinan' => $request->jabatan_pimpinan,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter ?? 100,
        ]);

        return redirect()->route('admin.divisi.index')->with('success', 'Data divisi berhasil diperbarui!');
    }

    // Menghapus data divisi
    public function destroy($id)
    {
        $divisi = Divisi::findOrFail($id);
        $divisi->delete();

        return redirect()->route('admin.divisi.index')->with('success', 'Data divisi berhasil dihapus!');
    }
}
