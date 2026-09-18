<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Illuminate\Http\Request;

class PembimbingController extends Controller
{
    // Menampilkan daftar data pembimbing
    public function index()
    {
        // Ambil data pembimbing beserta relasi akun login pengguna dan jumlah anak magang
        $pembimbing = Pembimbing::with('pengguna')->withCount('magang')->latest()->paginate(10);

        return view('admin.pembimbing.index', compact('pembimbing'));
    }

    // Menampilkan form tambah pembimbing
    public function create()
    {
        return view('admin.pembimbing.create');
    }

    // Menyimpan data pembimbing baru beserta akun loginnya
    public function store(Request $request)
    {
        // Validasi input profil pembimbing dan akun login sekaligus
        $request->validate([
            // Validasi data profil
            'nip' => 'required|string|max:30|unique:pembimbing,nip',
            'nama_lengkap' => 'required|string|max:100',
            'jabatan' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',

            // Validasi data akun login
            'username' => 'required|string|max:50|unique:pengguna,username',
            'password' => 'required|string|min:6',
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'password.required' => 'Password login wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // 1. Buat akun login di tabel pengguna terlebih dahulu
        $pengguna = Pengguna::create([
            'username' => $request->username,
            'password' => $request->password, // Otomatis di-hash oleh cast 'hashed' di model Pengguna
            'role' => 'pembimbing',
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // 2. Buat profil pembimbing dan hubungkan dengan pengguna_id
        Pembimbing::create([
            'pengguna_id' => $pengguna->id,
            'nip' => $request->nip,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
        ]);

        return redirect()->route('admin.pembimbing.index')->with('success', 'Data pembimbing dan akun login berhasil ditambahkan!');
    }

    // Menampilkan detail pembimbing (dialihkan ke form edit)
    public function show($id)
    {
        return redirect()->route('admin.pembimbing.edit', $id);
    }

    // Menampilkan form edit pembimbing
    public function edit($id)
    {
        $pembimbing = Pembimbing::with('pengguna')->findOrFail($id);

        return view('admin.pembimbing.edit', compact('pembimbing'));
    }

    // Memperbarui data pembimbing dan akun loginnya
    public function update(Request $request, $id)
    {
        $pembimbing = Pembimbing::with('pengguna')->findOrFail($id);

        // Validasi input
        $request->validate([
            'nip' => 'required|string|max:30|unique:pembimbing,nip,'.$pembimbing->id,
            'nama_lengkap' => 'required|string|max:100',
            'jabatan' => 'nullable|string|max:50',
            'no_hp' => 'nullable|string|max:20',

            // Validasi username kecuali milik pengguna ini sendiri
            'username' => 'required|string|max:50|unique:pengguna,username,'.$pembimbing->pengguna_id,
            'password' => 'nullable|string|min:6', // Diisi hanya jika ingin mengganti password
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        // 1. Update data profil pembimbing
        $pembimbing->update([
            'nip' => $request->nip,
            'nama_lengkap' => $request->nama_lengkap,
            'jabatan' => $request->jabatan,
            'no_hp' => $request->no_hp,
        ]);

        // 2. Update data akun login di tabel pengguna
        $userData = [
            'username' => $request->username,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        // Jika kolom password diisi, ubah password akun
        if ($request->filled('password')) {
            $userData['password'] = $request->password;
        }

        $pembimbing->pengguna->update($userData);

        return redirect()->route('admin.pembimbing.index')->with('success', 'Data pembimbing dan akun login berhasil diperbarui!');
    }

    // Menghapus data pembimbing dan akun loginnya
    public function destroy($id)
    {
        $pembimbing = Pembimbing::findOrFail($id);
        $pengguna = $pembimbing->pengguna;

        // Hapus data pembimbing
        $pembimbing->delete();

        // Hapus juga akun login di tabel pengguna agar tidak menjadi akun sampah
        if ($pengguna) {
            $pengguna->delete();
        }

        return redirect()->route('admin.pembimbing.index')->with('success', 'Data pembimbing dan akun login berhasil dihapus!');
    }
}
