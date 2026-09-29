{{-- resources/views/admin/cetak/partials/ttd-kepala.blade.php --}}
<div class="ttd-wrap">
    <div class="ttd-box">
        <div>{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
        <div>{{ $pengaturan->jabatan_kepala_dinas ?? 'Kepala Dinas' }}</div>
        <div>{{ $pengaturan->nama_instansi ?? 'Dinas Komunikasi dan Informatika' }}</div>
        <div class="ttd-space"></div>
        <div class="ttd-nama">{{ $pengaturan->nama_kepala_dinas ?? '( .................................................. )' }}</div>
        @if(!empty($pengaturan->pangkat_golongan))
            <div>{{ $pengaturan->pangkat_golongan }}</div>
        @endif
        <div>NIP. {{ $pengaturan->nip_kepala_dinas ?? '-' }}</div>
    </div>
</div>