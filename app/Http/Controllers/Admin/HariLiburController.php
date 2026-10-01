<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Services\HariLiburService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HariLiburController extends Controller
{
    /**
     * Menampilkan daftar hari libur dengan filter tahun, jenis, sumber, dan pencarian.
     */
    public function index(Request $request, HariLiburService $service): View
    {
        $availableYears = $service->getAvailableYears();
        $currentYear = (int) now()->format('Y');

        // Default filter tahun: tahun berjalan jika ada, atau tahun pertama yang tersedia
        $selectedYear = $request->filled('tahun')
            ? ($request->tahun === 'semua' ? null : (int) $request->tahun)
            : $currentYear;

        $query = HariLibur::query();

        if ($selectedYear !== null) {
            $query->whereYear('tanggal', $selectedYear);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('sumber')) {
            $query->where('sumber', $request->sumber);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        // Hitung ringkasan statistik (berdasarkan tahun yang dipilih atau keseluruhan jika 'semua')
        $statsQuery = HariLibur::query();
        if ($selectedYear !== null) {
            $statsQuery->whereYear('tanggal', $selectedYear);
        }

        $totalLibur = (clone $statsQuery)->count();
        $totalNasional = (clone $statsQuery)->where('jenis', 'Hari Libur Nasional')->count();
        $totalCuti = (clone $statsQuery)->where('jenis', 'Cuti Bersama')->count();
        $totalApi = (clone $statsQuery)->where('sumber', 'api')->count();
        $totalManual = (clone $statsQuery)->where('sumber', 'manual')->count();

        $hariLiburList = $query->orderBy('tanggal', 'asc')
            ->paginate(15)
            ->withQueryString();

        $isApiConfigured = $service->isConfigured();

        return view('admin.hari-libur.index', [
            'hariLiburList' => $hariLiburList,
            'availableYears' => $availableYears,
            'selectedYear' => $selectedYear,
            'totalLibur' => $totalLibur,
            'totalNasional' => $totalNasional,
            'totalCuti' => $totalCuti,
            'totalApi' => $totalApi,
            'totalManual' => $totalManual,
            'isApiConfigured' => $isApiConfigured,
        ]);
    }

    /**
     * Menampilkan formulir tambah hari libur manual.
     */
    public function create(): View
    {
        return view('admin.hari-libur.create');
    }

    /**
     * Menyimpan data hari libur manual baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'unique:hari_libur,tanggal'],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:Hari Libur Nasional,Cuti Bersama'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal.required' => 'Tanggal libur wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.unique' => 'Hari libur pada tanggal ini sudah tercatat di sistem.',
            'nama.required' => 'Nama hari libur wajib diisi.',
            'jenis.required' => 'Jenis libur wajib dipilih.',
            'jenis.in' => 'Jenis libur harus Hari Libur Nasional atau Cuti Bersama.',
        ]);

        $keterangan = ! empty($validated['keterangan']) ? $validated['keterangan'] : $validated['nama'];

        HariLibur::create([
            'tanggal' => $validated['tanggal'],
            'nama' => $validated['nama'],
            'jenis' => $validated['jenis'],
            'keterangan' => $keterangan,
            'sumber' => 'manual',
        ]);

        $tahun = (int) Carbon::parse($validated['tanggal'])->format('Y');

        return redirect()->route('admin.hari-libur.index', ['tahun' => $tahun])
            ->with('success', "Hari libur \"{$validated['nama']}\" berhasil ditambahkan.");
    }

    /**
     * Menampilkan form edit hari libur.
     */
    public function edit(HariLibur $hariLibur): View
    {
        return view('admin.hari-libur.edit', [
            'hariLibur' => $hariLibur,
        ]);
    }

    /**
     * Memperbarui data hari libur.
     */
    public function update(Request $request, HariLibur $hariLibur): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'unique:hari_libur,tanggal,'.$hariLibur->id],
            'nama' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:Hari Libur Nasional,Cuti Bersama'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ], [
            'tanggal.required' => 'Tanggal libur wajib diisi.',
            'tanggal.date' => 'Format tanggal tidak valid.',
            'tanggal.unique' => 'Hari libur pada tanggal ini sudah digunakan oleh data lain.',
            'nama.required' => 'Nama hari libur wajib diisi.',
            'jenis.required' => 'Jenis libur wajib dipilih.',
            'jenis.in' => 'Jenis libur harus Hari Libur Nasional atau Cuti Bersama.',
        ]);

        $keterangan = ! empty($validated['keterangan']) ? $validated['keterangan'] : $validated['nama'];

        // Tandai sebagai manual setelah diubah oleh admin agar tidak tertimpa sinkronisasi otomatis
        $hariLibur->update([
            'tanggal' => $validated['tanggal'],
            'nama' => $validated['nama'],
            'jenis' => $validated['jenis'],
            'keterangan' => $keterangan,
            'sumber' => 'manual',
        ]);

        $tahun = (int) Carbon::parse($validated['tanggal'])->format('Y');

        return redirect()->route('admin.hari-libur.index', ['tahun' => $tahun])
            ->with('success', "Perubahan hari libur \"{$validated['nama']}\" berhasil disimpan.");
    }

    /**
     * Menghapus data hari libur.
     */
    public function destroy(HariLibur $hariLibur): RedirectResponse
    {
        $nama = $hariLibur->nama ?: $hariLibur->keterangan;
        $tahun = $hariLibur->tanggal ? (int) $hariLibur->tanggal->format('Y') : null;

        $hariLibur->delete();

        return redirect()->route('admin.hari-libur.index', array_filter(['tahun' => $tahun]))
            ->with('success', "Hari libur \"{$nama}\" berhasil dihapus.");
    }

    /**
     * Menjalankan proses sinkronisasi hari libur dari API untuk tahun yang dipilih.
     */
    public function sync(Request $request, HariLiburService $service): RedirectResponse
    {
        $request->validate([
            'tahun' => ['required', 'integer', 'min:2020', 'max:2035'],
        ], [
            'tahun.required' => 'Tahun sinkronisasi wajib ditentukan.',
            'tahun.integer' => 'Tahun harus berupa bilangan bulat valid.',
        ]);

        $tahun = $request->integer('tahun');

        try {
            $result = $service->syncByYear($tahun);

            return redirect()->route('admin.hari-libur.index', ['tahun' => $tahun])
                ->with('success', $result['message']);
        } catch (Exception $e) {
            return redirect()->route('admin.hari-libur.index', ['tahun' => $tahun])
                ->with('error', $e->getMessage());
        }
    }
}
