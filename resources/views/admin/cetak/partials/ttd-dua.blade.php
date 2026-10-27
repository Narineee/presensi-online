{{-- Tanda tangan ganda: Pembimbing Lapangan (kiri) & Kepala Bidang / Pimpinan Bidang Induk (kanan)
     Dipakai di cetak peserta. Butuh variabel: $user, $pembimbing, $divisi, $pengaturan --}}
@php
    $bidangTtd = $divisi?->bidang_induk ?? $divisi;
@endphp
<style>
    .ttd-dua { width:100%; margin-top:28px; border-collapse:collapse; page-break-inside:avoid; font-size:10pt; font-family: 'Times New Roman', Times, serif; }
    .ttd-dua td { width:50%; text-align:center; vertical-align:top; line-height:1.4; padding:0 10px; border:0; }
    .ttd-dua .ruang { height:85px; }
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
            <div>{{ $pengaturan->kota_surat ?? 'Banjarbaru' }}, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}</div>
            <div>{{ $bidangTtd->jabatan_pimpinan ?? 'Kepala Bidang' }}</div>
            <div class="ruang"></div>
            <div class="nm">{{ $bidangTtd->nama_pimpinan ?? '( .................................................. )' }}</div>
            <div>NIP. {{ $bidangTtd->nip_pimpinan ?? '-' }}</div>
            <div>{{ $bidangTtd->nama_divisi ?? ($pengaturan->nama_instansi ?? 'Dinas Terkait') }}</div>
        </td>
    </tr>
</table>