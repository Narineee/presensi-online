<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cs;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Illuminate\Http\Request;

class CsController extends Controller
{
    // Menampilkan daftar data Customer Service (CS)
    public function index()
    {
        $cs = Cs::with(['pengguna', 'pembimbing'])->latest()->paginate(10);

        return view('admin.cs.index', compact('cs'));
    }

    // Menampilkan form tambah data CS
    public function create()
    {
        $pembimbing = Pembimbing::orderBy('nama_lengkap')->get();

        return view('admin.cs.create', compact('pembimbing'));
    }

    // Menyimpan data CS baru beserta akun loginnya
    public function store(Request $request)
    {
        $request->validate([
            // Profil CS
            'nik' => 'required|string|max:30|unique:cs,nik',
            'nama_lengkap' => 'required|string|max:100',
            'pembimbing_id' => 'required|exists:pembimbing,id',
            'jabatan' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'tanggal_bergabung' => 'nullable|date',
            'status' => 'required|in:aktif,non_aktif',

            // Akun Login Pengguna
            'username' => 'required|string|max:50|unique:pengguna,username',
            'password' => 'required|string|min:6',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'NIK ini sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap CS wajib diisi.',
            'pembimbing_id.required' => 'Pilih pembimbing/validator untuk CS.',
            'pembimbing_id.exists' => 'Pembimbing yang dipilih tidak ditemukan.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'password.required' => 'Password login wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // 1. Buat akun login di tabel pengguna
        $pengguna = Pengguna::create([
            'username' => $request->username,
            'password' => $request->password, // Otomatis di-hash oleh model Pengguna
            'role' => 'cs',
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // 2. Buat profil CS
        Cs::create([
            'pengguna_id' => $pengguna->id,
            'pembimbing_id' => $request->pembimbing_id,
            'nik' => $request->nik,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'tanggal_bergabung' => $request->tanggal_bergabung,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.cs.index')->with('success', 'Data CS dan akun login berhasil ditambahkan!');
    }

    // Menampilkan detail CS (dialihkan ke form edit)
    public function show($id)
    {
        return redirect()->route('admin.cs.edit', $id);
    }

    // Menampilkan form edit data CS
    public function edit($id)
    {
        $cs = Cs::with('pengguna')->findOrFail($id);
        $pembimbing = Pembimbing::orderBy('nama_lengkap')->get();

        return view('admin.cs.edit', compact('cs', 'pembimbing'));
    }

    // Memperbarui data CS dan akun loginnya
    public function update(Request $request, $id)
    {
        $cs = Cs::with('pengguna')->findOrFail($id);

        $request->validate([
            // Profil CS
            'nik' => 'required|string|max:30|unique:cs,nik,'.$cs->id,
            'nama_lengkap' => 'required|string|max:100',
            'pembimbing_id' => 'required|exists:pembimbing,id',
            'jabatan' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'tanggal_bergabung' => 'nullable|date',
            'status' => 'required|in:aktif,non_aktif',

            // Akun Login Pengguna
            'username' => 'required|string|max:50|unique:pengguna,username,'.$cs->pengguna_id,
            'password' => 'nullable|string|min:6', // Opsional saat edit
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.unique' => 'NIK ini sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap CS wajib diisi.',
            'pembimbing_id.required' => 'Pilih pembimbing/validator untuk CS.',
            'pembimbing_id.exists' => 'Pembimbing yang dipilih tidak ditemukan.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        // 1. Update profil CS
        $cs->update([
            'pembimbing_id' => $request->pembimbing_id,
            'nik' => $request->nik,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
            'tanggal_bergabung' => $request->tanggal_bergabung,
            'status' => $request->status,
        ]);

        // 2. Update akun login
        $userData = [
            'username' => $request->username,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->filled('password')) {
            $userData['password'] = $request->password;
        }

        $cs->pengguna->update($userData);

        return redirect()->route('admin.cs.index')->with('success', 'Data CS dan akun login berhasil diperbarui!');
    }

    // Menghapus data CS dan akun loginnya
    public function destroy($id)
    {
        $cs = Cs::findOrFail($id);
        $pengguna = $cs->pengguna;

        $cs->delete();

        if ($pengguna) {
            $pengguna->delete();
        }

        return redirect()->route('admin.cs.index')->with('success', 'Data CS dan akun login berhasil dihapus!');
    }
}
