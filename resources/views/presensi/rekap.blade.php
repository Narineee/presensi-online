{{-- Rekap presensi (route presensi.rekap). Kirim $presensi dari controller --}}
@extends('layouts.user')
@section('title','Rekap Presensi')
@section('content')
@include('layouts._topbar', ['title'=>'Rekapitulasi & Cetak Dokumen','sub'=>'Rekap presensi anda','back'=>route('magang.rekap')])
<form method="GET" class="glass">
  <div class="grid2"><div><label>Tanggal awal</label><input type="date" name="dari" value="{{ request('dari') }}"></div>
  <div><label>Tanggal selesai</label><input type="date" name="sampai" value="{{ request('sampai') }}"></div></div>
  <label>Mode kerja</label>
  <select name="mode_kerja"><option value="">Semua</option><option value="wfo" @selected(request('mode_kerja')=='wfo')>WFO</option><option value="wfh" @selected(request('mode_kerja')=='wfh')>WFH</option></select>
  <button class="btn" style="margin-top:12px">Terapkan filter</button>
</form>
<section class="glass">
  <b>Riwayat presensi saya</b>
  <div style="overflow-x:auto">
  <table class="list"><thead><tr><th>Tanggal</th><th>Masuk</th><th>Pulang</th><th>Status</th></tr></thead><tbody>
  @forelse($presensi ?? [] as $p)
    <tr><td>{{ \Carbon\Carbon::parse($p->tanggal)->format('d/m/y') }}</td><td>{{ $p->jam_masuk ? substr($p->jam_masuk,0,5) : '–' }}</td><td>{{ $p->jam_keluar ? substr($p->jam_keluar,0,5) : '–' }}</td><td>{{ ucfirst($p->status ?? 'hadir') }}</td></tr>
  @empty<tr><td colspan="4" class="empty">Belum ada data presensi pada rentang ini.</td></tr>@endforelse
  </tbody></table></div>
</section>
<a href="{{ route('presensi.cetak', request()->query()) }}" class="btn"><i class="bi bi-printer"></i> Cetak rekapitulasi</a>
@endsection
