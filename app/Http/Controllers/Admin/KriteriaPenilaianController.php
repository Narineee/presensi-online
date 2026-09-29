<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KriteriaPenilaian;
use Illuminate\Http\Request;

class KriteriaPenilaianController extends Controller
{
    /**
     * Menampilkan daftar kriteria penilaian magang beserta fitur pencarian.
     */
    public function index(Request $request)
    {
        $query = KriteriaPenilaian::query();

        // Fitur pencarian berdasarkan nama kriteria
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%'.$request->search.'%');
        }

        $kriteria = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();

        // Total akumulasi bobot semua kriteria
        $totalBobot = KriteriaPenilaian::sum('bobot');

        return view('admin.kriteria.index', compact('kriteria', 'totalBobot'));
    }

    /**
     * Menampilkan formulir penambahan kriteria penilaian baru.
     */
    public function create()
    {
        return view('admin.kriteria.create');
    }

    /**
     * Menyimpan data kriteria penilaian baru ke database.
     */
    public function store(Request $request)
    {
        // Validasi input data
        $request->validate([
            'nama' => 'required|string|max:100',
            'bobot' => 'required|integer|min:1|max:100',
        ], [
            'nama.required' => 'Nama kriteria penilaian wajib diisi.',
            'nama.max' => 'Nama kriteria maksimal 100 karakter.',
            'bobot.required' => 'Bobot kriteria penilaian wajib diisi.',
            'bobot.integer' => 'Bobot harus berupa angka bulat.',
            'bobot.min' => 'Bobot minimal adalah 1.',
            'bobot.max' => 'Bobot maksimal adalah 100.',
        ]);

        $isPresensi = $request->boolean('is_presensi');
        if ($isPresensi) {
            KriteriaPenilaian::where('is_presensi', true)->update(['is_presensi' => false]);
        }

        // Simpan data kriteria baru
        KriteriaPenilaian::create([
            'nama' => $request->nama,
            'bobot' => $request->bobot,
            'is_presensi' => $isPresensi,
        ]);

        return redirect()->route('admin.kriteria.index')
            ->with('success', 'Kriteria penilaian berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail kriteria (dialihkan ke halaman edit).
     */
    public function show($id)
    {
        return redirect()->route('admin.kriteria.edit', $id);
    }

    /**
     * Menampilkan formulir edit kriteria penilaian.
     */
    public function edit($id)
    {
        $kriteria = KriteriaPenilaian::findOrFail($id);

        return view('admin.kriteria.edit', compact('kriteria'));
    }

    /**
     * Memperbarui data kriteria penilaian di database.
     */
    public function update(Request $request, $id)
    {
        // Validasi input data
        $request->validate([
            'nama' => 'required|string|max:100',
            'bobot' => 'required|integer|min:1|max:100',
        ], [
            'nama.required' => 'Nama kriteria penilaian wajib diisi.',
            'nama.max' => 'Nama kriteria maksimal 100 karakter.',
            'bobot.required' => 'Bobot kriteria penilaian wajib diisi.',
            'bobot.integer' => 'Bobot harus berupa angka bulat.',
            'bobot.min' => 'Bobot minimal adalah 1.',
            'bobot.max' => 'Bobot maksimal adalah 100.',
        ]);

        $kriteria = KriteriaPenilaian::findOrFail($id);

        $isPresensi = $request->boolean('is_presensi');
        if ($isPresensi) {
            KriteriaPenilaian::where('id', '!=', $id)->where('is_presensi', true)->update(['is_presensi' => false]);
        }

        // Update data kriteria
        $kriteria->update([
            'nama' => $request->nama,
            'bobot' => $request->bobot,
            'is_presensi' => $isPresensi,
        ]);

        return redirect()->route('admin.kriteria.index')
            ->with('success', 'Kriteria penilaian berhasil diperbarui!');
    }

    /**
     * Menghapus data kriteria penilaian.
     */
    public function destroy($id)
    {
        $kriteria = KriteriaPenilaian::findOrFail($id);

        // Cek apakah kriteria sudah pernah digunakan pada penilaian magang
        if ($kriteria->detailPenilaian()->exists()) {
            return redirect()->route('admin.kriteria.index')
                ->with('error', 'Kriteria ini sudah digunakan pada riwayat penilaian magang sehingga tidak dapat dihapus!');
        }

        $kriteria->delete();

        return redirect()->route('admin.kriteria.index')
            ->with('success', 'Kriteria penilaian berhasil dihapus!');
    }
}
