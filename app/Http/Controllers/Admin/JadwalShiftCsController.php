<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cs;
use App\Models\JadwalShiftCs;
use App\Models\Shift;
use Illuminate\Http\Request;

class JadwalShiftCsController extends Controller
{
    // Menampilkan daftar jadwal shift Customer Service (CS)
    public function index(Request $request)
    {
        $query = JadwalShiftCs::with(['cs', 'shift'])->orderBy('tanggal', 'desc');

        // Filter opsional berdasarkan CS jika ada
        if ($request->filled('cs_id')) {
            $query->where('cs_id', $request->cs_id);
        }

        // Filter opsional berdasarkan tanggal jika ada
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $jadwal = $query->paginate(10);
        $csList = Cs::orderBy('nama_lengkap')->get();

        return view('admin.jadwal-shift.index', compact('jadwal', 'csList'));
    }

    // Menampilkan form tambah jadwal shift baru
    public function create()
    {
        $csList = Cs::where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $shiftList = Shift::where('is_active', true)->orderBy('nama')->get();

        return view('admin.jadwal-shift.create', compact('csList', 'shiftList'));
    }

    // Menyimpan jadwal shift baru
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'cs_id' => 'required|exists:cs,id',
            'shift_id' => 'required|exists:shift,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:terjadwal,libur,izin,cuti',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'cs_id.required' => 'Pilih staf Customer Service (CS).',
            'cs_id.exists' => 'CS yang dipilih tidak valid.',
            'shift_id.required' => 'Pilih shift kerja.',
            'shift_id.exists' => 'Shift yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal jadwal shift wajib diisi.',
            'status.required' => 'Status shift wajib dipilih.',
        ]);

        // Cek batasan unik: Satu CS hanya boleh memiliki 1 jadwal shift per hari
        $cekJadwal = JadwalShiftCs::where('cs_id', $request->cs_id)
            ->whereDate('tanggal', $request->tanggal)
            ->first();

        if ($cekJadwal) {
            return back()->withErrors([
                'tanggal' => 'Staf CS tersebut sudah memiliki jadwal shift pada tanggal ini.',
            ])->withInput();
        }

        // Simpan ke database
        JadwalShiftCs::create([
            'cs_id' => $request->cs_id,
            'shift_id' => $request->shift_id,
            'tanggal' => $request->tanggal,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.jadwal-shift.index')->with('success', 'Jadwal shift CS berhasil ditambahkan!');
    }

    // Menampilkan detail jadwal (dialihkan ke form edit)
    public function show($id)
    {
        return redirect()->route('admin.jadwal-shift.edit', $id);
    }

    // Menampilkan form edit jadwal shift
    public function edit($id)
    {
        $jadwal = JadwalShiftCs::findOrFail($id);
        $csList = Cs::orderBy('nama_lengkap')->get();
        $shiftList = Shift::where('is_active', true)->orderBy('nama')->get();

        return view('admin.jadwal-shift.edit', compact('jadwal', 'csList', 'shiftList'));
    }

    // Memperbarui jadwal shift CS
    public function update(Request $request, $id)
    {
        $jadwal = JadwalShiftCs::findOrFail($id);

        // Validasi input
        $request->validate([
            'cs_id' => 'required|exists:cs,id',
            'shift_id' => 'required|exists:shift,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:terjadwal,libur,izin,cuti',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'cs_id.required' => 'Pilih staf Customer Service (CS).',
            'cs_id.exists' => 'CS yang dipilih tidak valid.',
            'shift_id.required' => 'Pilih shift kerja.',
            'shift_id.exists' => 'Shift yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal jadwal shift wajib diisi.',
            'status.required' => 'Status shift wajib dipilih.',
        ]);

        // Cek apakah tanggal & CS tersebut sudah dipakai pada jadwal yang lain
        $cekJadwal = JadwalShiftCs::where('cs_id', $request->cs_id)
            ->whereDate('tanggal', $request->tanggal)
            ->where('id', '!=', $id)
            ->first();

        if ($cekJadwal) {
            return back()->withErrors([
                'tanggal' => 'Staf CS tersebut sudah memiliki jadwal shift lain pada tanggal ini.',
            ])->withInput();
        }

        // Perbarui data
        $jadwal->update([
            'cs_id' => $request->cs_id,
            'shift_id' => $request->shift_id,
            'tanggal' => $request->tanggal,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.jadwal-shift.index')->with('success', 'Jadwal shift CS berhasil diperbarui!');
    }

    // Menghapus jadwal shift CS
    public function destroy($id)
    {
        $jadwal = JadwalShiftCs::findOrFail($id);
        $jadwal->delete();

        return redirect()->route('admin.jadwal-shift.index')->with('success', 'Jadwal shift CS berhasil dihapus!');
    }
}
