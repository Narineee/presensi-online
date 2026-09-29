{{-- Tanda tangan ganda: Pembimbing Lapangan (kiri) & Pimpinan Divisi (kanan)
     Dipakai di cetak peserta. Butuh variabel: $user, $pembimbing, $divisi, $pengaturan --}}
<style>
    .ttd-dua { width:100%; margin-top:24px; border-collapse:collapse; page-break-inside:avoid; font-size:10.5pt; }
    .ttd-dua td { width:50%; text-align:center; vertical-align:top; line-height:1.4; padding:0 10px; border:0; }
    .ttd-dua .ruang { height:70px; }
    .ttd-dua .nm { font-weight:bold; text-decoration:underline; }
</style>
<table class="ttd-dua">
    <tr>
        <td>
            <div>Mengetahui &amp; Mengesahkan,</div>
            <div>Pembimbing Lapangan</div>
            <div class="ruang"></div>
            <div class="nm">{{ $pembimbing->nama_lengkap ?? ($user->magang?->pembimbing?->nama_lengkap ?? '( .................................................. )') }}</div>
            <div>NIP. {{ $pembimbing->nip ?? ($user->magang?->pembimbing?->nip ?? '-') }}</div>
            <div>{{ $pembimbing->jabatan ?? ($user->magang?->pembimbing?->jabatan ?? 'Pembimbing Lapangan') }}</div>
        </td>
        <td>
            <div>{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
            <div>{{ $divisi->jabatan_pimpinan ?? 'Kepala Sub-Bagian / Divisi' }}</div>
            <div class="ruang"></div>
            <div class="nm">{{ $divisi->nama_pimpinan ?? '( .................................................. )' }}</div>
            <div>NIP. {{ $divisi->nip_pimpinan ?? '-' }}</div>
            <div>{{ $divisi->nama_divisi ?? 'Divisi Terkait' }}</div>
        </td>
    </tr>
</table>