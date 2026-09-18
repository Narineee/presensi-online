<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cs;
use App\Models\Divisi;
use App\Models\KriteriaPenilaian;
use App\Models\Magang;
use App\Models\Pembimbing;
use App\Models\Pengguna;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama Super Admin beserta ringkasan status sistem.
     */
    public function index(): View
    {
        $stats = [
            'total_pengguna' => Pengguna::count(),
            'total_divisi' => Divisi::count(),
            'total_pembimbing' => Pembimbing::count(),
            'total_magang' => Magang::count(),
            'total_cs' => Cs::count(),
            'total_kriteria' => KriteriaPenilaian::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
