<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang;
use App\Models\PenempatanMagang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PenempatanMagangController extends Controller
{
    /**
     * Menyimpan data riwayat penempatan divisi baru untuk peserta magang.
     */
    public function store(Request $request, $magangId)
    {
        $magang = Magang::findOrFail($magangId);

        $request->validate([
            'divisi_id' => 'required|exists:divisi,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'divisi_id.required' => 'Divisi penempatan wajib dipilih.',
            'divisi_id.exists' => 'Divisi yang dipilih tidak ditemukan dalam sistem.',
            'tanggal_mulai.required' => 'Tanggal mulai penempatan wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai penempatan wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $this->validatePeriodeDanOverlap($request, $magang);

        PenempatanMagang::create([
            'magang_id' => $magang->id,
            'divisi_id' => $request->divisi_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        // Jika penempatan yang baru ditambahkan aktif hari ini, perbarui juga divisi_id default magang agar konsisten
        $today = Carbon::today()->toDateString();
        if ($request->tanggal_mulai <= $today && $request->tanggal_selesai >= $today) {
            $magang->update(['divisi_id' => $request->divisi_id]);
        }

        return redirect()->route('admin.magang.show', ['magang' => $magang->id, 'tab' => 'penempatan'])
            ->with('success', 'Penempatan divisi berhasil ditambahkan.');
    }

    /**
     * Memperbarui data riwayat penempatan divisi peserta magang.
     */
    public function update(Request $request, $magangId, $id)
    {
        $magang = Magang::findOrFail($magangId);
        $penempatan = PenempatanMagang::where('magang_id', $magang->id)->findOrFail($id);

        $request->validate([
            'divisi_id' => 'required|exists:divisi,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'divisi_id.required' => 'Divisi penempatan wajib dipilih.',
            'divisi_id.exists' => 'Divisi yang dipilih tidak ditemukan dalam sistem.',
            'tanggal_mulai.required' => 'Tanggal mulai penempatan wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai penempatan wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $this->validatePeriodeDanOverlap($request, $magang, $penempatan->id);

        $penempatan->update([
            'divisi_id' => $request->divisi_id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);

        // Sinkronisasi divisi_id default magang jika penempatan ini aktif hari ini
        $today = Carbon::today()->toDateString();
        if ($request->tanggal_mulai <= $today && $request->tanggal_selesai >= $today) {
            $magang->update(['divisi_id' => $request->divisi_id]);
        }

        return redirect()->route('admin.magang.show', ['magang' => $magang->id, 'tab' => 'penempatan'])
            ->with('success', 'Riwayat penempatan divisi berhasil diperbarui.');
    }

    /**
     * Menghapus riwayat penempatan divisi peserta magang.
     */
    public function destroy($magangId, $id)
    {
        $magang = Magang::findOrFail($magangId);
        $penempatan = PenempatanMagang::where('magang_id', $magang->id)->findOrFail($id);

        $penempatan->delete();

        // Refresh divisi default peserta berdasarkan penempatan aktif lainnya jika ada
        $penempatanAktif = $magang->penempatanMagang()
            ->where('tanggal_mulai', '<=', Carbon::today())
            ->where('tanggal_selesai', '>=', Carbon::today())
            ->first();

        if ($penempatanAktif) {
            $magang->update(['divisi_id' => $penempatanAktif->divisi_id]);
        }

        return redirect()->route('admin.magang.show', ['magang' => $magang->id, 'tab' => 'penempatan'])
            ->with('success', 'Penempatan divisi berhasil dihapus.');
    }

    /**
     * Validasi batas periode magang dan pengecekan tabrakan (overlap) rentang tanggal.
     */
    protected function validatePeriodeDanOverlap(Request $request, Magang $magang, ?int $excludeId = null): void
    {
        $mulai = $request->tanggal_mulai;
        $selesai = $request->tanggal_selesai;

        $magangMulai = $magang->tanggal_mulai ? $magang->tanggal_mulai->toDateString() : null;
        $magangSelesai = $magang->tanggal_selesai ? $magang->tanggal_selesai->toDateString() : null;

        // 1. Tanggal mulai tidak boleh lebih awal dari periode magang
        if ($magangMulai && $mulai < $magangMulai) {
            throw ValidationException::withMessages([
                'tanggal_mulai' => 'Tanggal mulai penempatan ('.Carbon::parse($mulai)->format('d/m/Y').') tidak boleh lebih awal dari awal periode magang peserta ('.Carbon::parse($magangMulai)->format('d/m/Y').').',
            ]);
        }

        // 2. Tanggal selesai tidak boleh melewati akhir periode magang
        if ($magangSelesai && $selesai > $magangSelesai) {
            throw ValidationException::withMessages([
                'tanggal_selesai' => 'Tanggal selesai penempatan ('.Carbon::parse($selesai)->format('d/m/Y').') tidak boleh melewati batas akhir periode magang peserta ('.Carbon::parse($magangSelesai)->format('d/m/Y').').',
            ]);
        }

        // 3. Tabrakan rentang tanggal dengan penempatan peserta yang sudah ada (overlap check)
        $overlap = PenempatanMagang::with('divisi')
            ->where('magang_id', $magang->id)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($mulai, $selesai) {
                $q->where('tanggal_mulai', '<=', $selesai)
                    ->where('tanggal_selesai', '>=', $mulai);
            })
            ->first();

        if ($overlap) {
            $namaDivisi = $overlap->divisi->nama_divisi ?? 'Divisi Terdaftar';
            $overlapMulai = $overlap->tanggal_mulai->format('d/m/Y');
            $overlapSelesai = $overlap->tanggal_selesai->format('d/m/Y');

            throw ValidationException::withMessages([
                'tanggal_mulai' => "Rentang tanggal bertabrakan dengan riwayat penempatan {$namaDivisi} ({$overlapMulai} s/d {$overlapSelesai}). Silakan pilih rentang tanggal yang berbeda.",
            ]);
        }
    }
}
