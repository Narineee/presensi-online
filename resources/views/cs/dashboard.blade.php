@extends('layouts.auth')

@section('title', 'Dashboard CS')

@section('content')
<div class="bg-white rounded-2xl shadow-xl p-6 text-center space-y-4">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-teal-100 text-teal-600 font-bold">
        CS
    </div>
    <h1 class="text-xl font-bold text-slate-800">Dashboard Customer Service</h1>
    <p class="text-sm text-slate-500">Halo, {{ Auth::user()->username }}. Halaman presensi, shift & aktivitas CS akan dikembangkan pada tahap selanjutnya.</p>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl">
            Keluar
        </button>
    </form>
</div>
@endsection
