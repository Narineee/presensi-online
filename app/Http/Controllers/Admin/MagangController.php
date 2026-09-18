<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MagangController extends Controller
{
    // Menampilkan daftar semua anak magang
    public function index()
    {
        // Ambil data magang beserta relasi akun pengguna, pembimbing, dan divisi
        $magang = Magang::with(['pengguna', 'pembimbing', 'divisi'])
            ->latest()
            ->paginate(10);

        return view('admin.magang.index', compact('magang'));
    }

    // Menampilkan form tambah anak magang
    public function create()
    {
        // Ambil data pembimbing dan divisi untuk pilihan dropdown
        $pembimbing = Pembimbing::orderBy('nama_lengkap')->get();
        $divisi = Divisi::orderBy('nama_divisi')->get();

        return view('admin.magang.create', compact('pembimbing', 'divisi'));
    }

    // Menyimpan data anak magang baru beserta akun loginnya
    public function store(Request $request)
    {
        // Validasi input profil dan akun login
        $request->validate([
            // Profil Magang
            'nama_lengkap' => 'required|string|max:100',
            'no_induk' => 'nullable|string|max:30',
            'jurusan' => 'nullable|string|max:100',
            'instansi_pendidikan' => 'nullable|string|max:150',
            'no_hp' => 'nullable|string|max:20',
            'pembimbing_id' => 'required|exists:pembimbing,id',
            'divisi_id' => 'nullable|exists:divisi,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,selesai,cuti',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Akun Login Pengguna
            'username' => 'required|string|max:50|unique:pengguna,username',
            'password' => 'required|string|min:6',
        ], [
            'nama_lengkap.required' => 'Nama lengkap magang wajib diisi.',
            'pembimbing_id.required' => 'Pilih pembimbing untuk anak magang.',
            'pembimbing_id.exists' => 'Pembimbing yang dipilih tidak ditemukan.',
            'tanggal_mulai.required' => 'Tanggal mulai magang wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai magang wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'username.required' => 'Username akun login wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih username lain.',
            'password.required' => 'Password akun login wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'foto.image' => 'File foto harus berupa gambar (jpg, jpeg, png).',
            'foto.max' => 'Ukuran file foto maksimal 2MB.',
        ]);

        // Simpan foto jika ada yang diunggah
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('foto-magang', 'public');
        }

        // 1. Buat akun login di tabel pengguna
        $pengguna = Pengguna::create([
            'username' => $request->username,
            'password' => $request->password, // Otomatis di-hash oleh model Pengguna
            'role' => 'magang',
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        // 2. Buat profil magang di tabel magang
        Magang::create([
            'pengguna_id' => $pengguna->id,
            'pembimbing_id' => $request->pembimbing_id,
            'divisi_id' => $request->divisi_id,
            'no_induk' => $request->no_induk,
            'nama_lengkap' => $request->nama_lengkap,
            'jurusan' => $request->jurusan,
            'instansi_pendidikan' => $request->instansi_pendidikan,
            'no_hp' => $request->no_hp,
            'foto' => $fotoPath,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'status' => $request->status ?? 'aktif',
        ]);

        return redirect()->route('admin.magang.index')->with('success', 'Data magang dan akun login berhasil ditambahkan!');
    }

    // Menampilkan detail magang (dialihkan ke form edit)
    public function show($id)
    {
        return redirect()->route('admin.magang.edit', $id);
    }

    // Menampilkan form edit anak magang
    public function edit($id)
    {
        $magang = Magang::with('pengguna')->findOrFail($id);
        $pembimbing = Pembimbing::orderBy('nama_lengkap')->get();
        $divisi = Divisi::orderBy('nama_divisi')->get();

        return view('admin.magang.edit', compact('magang', 'pembimbing', 'divisi'));
    }

    // Memperbarui data anak magang dan akun loginnya
    public function update(Request $request, $id)
    {
        $magang = Magang::with('pengguna')->findOrFail($id);

        // Validasi input
        $request->validate([
            // Profil Magang
            'nama_lengkap' => 'required|string|max:100',
            'no_induk' => 'nullable|string|max:30',
            'jurusan' => 'nullable|string|max:100',
            'instansi_pendidikan' => 'nullable|string|max:150',
            'no_hp' => 'nullable|string|max:20',
            'pembimbing_id' => 'required|exists:pembimbing,id',
            'divisi_id' => 'nullable|exists:divisi,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:aktif,selesai,cuti',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Akun Login
            'username' => 'required|string|max:50|unique:pengguna,username,'.$magang->pengguna_id,
            'password' => 'nullable|string|min:6', // Opsional saat edit
        ], [
            'nama_lengkap.required' => 'Nama lengkap magang wajib diisi.',
            'pembimbing_id.required' => 'Pilih pembimbing untuk anak magang.',
            'pembimbing_id.exists' => 'Pembimbing yang dipilih tidak ditemukan.',
            'tanggal_mulai.required' => 'Tanggal mulai magang wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai magang wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'username.required' => 'Username login wajib diisi.',
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'password.min' => 'Password baru minimal 6 karakter.',
        ]);

        // Cek jika ada unggahan foto baru
        $fotoPath = $magang->foto;
        if ($request->hasFile('foto')) {
            // Hapus file foto lama jika ada
            if ($magang->foto && Storage::disk('public')->exists($magang->foto)) {
                Storage::disk('public')->delete($magang->foto);
            }
            $fotoPath = $request->file('foto')->store('foto-magang', 'public');
        }

        // 1. Update data profil magang
        $magang->update([
            'pembimbing_id' => $request->pembimbing_id,
            'divisi_id' => $request->divisi_id,
            'no_induk' => $request->no_induk,
            'nama_lengkap' => $request->nama_lengkap,
            'jurusan' => $request->jurusan,
            'instansi_pendidikan' => $request->instansi_pendidikan,
            'no_hp' => $request->no_hp,
            'foto' => $fotoPath,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'status' => $request->status,
        ]);

        // 2. Update akun login di tabel pengguna
        $userData = [
            'username' => $request->username,
            'is_active' => $request->has('is_active') ? true : false,
        ];

        if ($request->filled('password')) {
            $userData['password'] = $request->password;
        }

        $magang->pengguna->update($userData);

        return redirect()->route('admin.magang.index')->with('success', 'Data magang dan akun login berhasil diperbarui!');
    }

    // Menghapus data anak magang dan akun loginnya
    public function destroy($id)
    {
        $magang = Magang::findOrFail($id);
        $pengguna = $magang->pengguna;

        // Hapus foto profil di storage jika ada
        if ($magang->foto && Storage::disk('public')->exists($magang->foto)) {
            Storage::disk('public')->delete($magang->foto);
        }

        // Hapus profil magang
        $magang->delete();

        // Hapus akun pengguna terkait
        if ($pengguna) {
            $pengguna->delete();
        }

        return redirect()->route('admin.magang.index')->with('success', 'Data magang dan akun login berhasil dihapus!');
    }
}
