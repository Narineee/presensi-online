<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    // Menampilkan daftar master shift kerja CS
    public function index()
    {
        $shifts = Shift::latest()->paginate(10);

        return view('admin.shift.index', compact('shifts'));
    }

    // Menampilkan form tambah shift baru
    public function create()
    {
        return view('admin.shift.create');
    }

    // Menyimpan data shift baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:50',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'toleransi_masuk' => 'nullable|integer|min:0|max:120',
        ], [
            'nama.required' => 'Nama shift wajib diisi.',
            'jam_masuk.required' => 'Jam masuk shift wajib diisi.',
            'jam_masuk.date_format' => 'Format jam masuk harus HH:MM (contoh: 08:00).',
            'jam_keluar.required' => 'Jam keluar shift wajib diisi.',
            'jam_keluar.date_format' => 'Format jam keluar harus HH:MM (contoh: 17:00).',
            'toleransi_masuk.integer' => 'Toleransi keterlambatan harus berupa angka bulat dalam menit.',
        ]);

        Shift::create([
            'nama' => $request->nama,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'toleransi_masuk' => $request->toleransi_masuk ?? 0,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.shift.index')->with('success', 'Master shift kerja berhasil ditambahkan!');
    }

    // Menampilkan detail shift (dialihkan ke form edit)
    public function show($id)
    {
        return redirect()->route('admin.shift.edit', $id);
    }

    // Menampilkan form edit shift
    public function edit($id)
    {
        $shift = Shift::findOrFail($id);

        return view('admin.shift.edit', compact('shift'));
    }

    // Memperbarui data shift
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:50',
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
            'toleransi_masuk' => 'nullable|integer|min:0|max:120',
        ], [
            'nama.required' => 'Nama shift wajib diisi.',
            'jam_masuk.required' => 'Jam masuk shift wajib diisi.',
            'jam_masuk.date_format' => 'Format jam masuk harus HH:MM (contoh: 08:00).',
            'jam_keluar.required' => 'Jam keluar shift wajib diisi.',
            'jam_keluar.date_format' => 'Format jam keluar harus HH:MM (contoh: 17:00).',
            'toleransi_masuk.integer' => 'Toleransi keterlambatan harus berupa angka bulat dalam menit.',
        ]);

        $shift = Shift::findOrFail($id);
        $shift->update([
            'nama' => $request->nama,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'toleransi_masuk' => $request->toleransi_masuk ?? 0,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.shift.index')->with('success', 'Master shift kerja berhasil diperbarui!');
    }

    // Menghapus data shift
    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return redirect()->route('admin.shift.index')->with('success', 'Data shift berhasil dihapus!');
    }
}
