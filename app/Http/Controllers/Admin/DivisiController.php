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
        $divisi = Divisi::with('parent')->latest()->paginate(15);

        return view('admin.divisi.index', compact('divisi'));
    }

    // Menampilkan form tambah divisi
    public function create()
    {
        $bidangList = Divisi::whereNull('parent_id')->orderBy('nama_divisi')->get();

        return view('admin.divisi.create', compact('bidangList'));
    }

    // Menyimpan data divisi baru
    public function store(Request $request)
    {
        // Validasi input form langsung di controller
        $request->validate([
            'nama_divisi' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:divisi,id',
            'nama_pimpinan' => 'required|string|max:100',
            'nip_pimpinan' => 'nullable|string|max:30',
            'jabatan_pimpinan' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meter' => 'nullable|integer',
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'parent_id.exists' => 'Bidang induk yang dipilih tidak valid.',
            'nama_pimpinan.required' => 'Nama pimpinan wajib diisi.',
        ]);

        // Simpan ke database
        Divisi::create([
            'nama_divisi' => $request->nama_divisi,
            'parent_id' => $request->parent_id,
            'nama_pimpinan' => $request->nama_pimpinan,
            'nip_pimpinan' => $request->nip_pimpinan,
            'jabatan_pimpinan' => $request->jabatan_pimpinan,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter ?? 40,
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
        $divisi = Divisi::with('parent')->findOrFail($id);
        $bidangList = Divisi::whereNull('parent_id')->where('id', '!=', $id)->orderBy('nama_divisi')->get();

        return view('admin.divisi.edit', compact('divisi', 'bidangList'));
    }

    // Memperbarui data divisi
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'nama_divisi' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:divisi,id',
            'nama_pimpinan' => 'required|string|max:100',
            'nip_pimpinan' => 'nullable|string|max:30',
            'jabatan_pimpinan' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius_meter' => 'nullable|integer',
        ], [
            'nama_divisi.required' => 'Nama divisi wajib diisi.',
            'parent_id.exists' => 'Bidang induk yang dipilih tidak valid.',
            'nama_pimpinan.required' => 'Nama pimpinan wajib diisi.',
        ]);

        // Cari data dan update
        $divisi = Divisi::findOrFail($id);
        $divisi->update([
            'nama_divisi' => $request->nama_divisi,
            'parent_id' => $request->parent_id,
            'nama_pimpinan' => $request->nama_pimpinan,
            'nip_pimpinan' => $request->nip_pimpinan,
            'jabatan_pimpinan' => $request->jabatan_pimpinan,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius_meter' => $request->radius_meter ?? 40,
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
