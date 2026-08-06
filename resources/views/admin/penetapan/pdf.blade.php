<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Penetapan {{ $pengajuan->nomor_penetapan }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #111827; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 24px; }
        .kop h1 { margin: 0; font-size: 18px; }
        .kop p { margin: 2px 0; font-size: 12px; }
        .title { font-size: 16px; text-align: center; margin: 16px 0; font-weight: bold; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .row { display: flex; justify-content: space-between; padding: 3px 0; }
        .sign { margin-top: 48px; }
        .sign td { border: none; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="kop">
        <h1>PEMERINTAH PROVINSI KALIMANTAN TIMUR</h1>
        <p>Surat Tugas Penetapan Pembayaran Pajak Kendaraan Dinas</p>
        <p>Nomor: {{ $pengajuan->nomor_penetapan }}</p>
    </div>

    <p class="title">SURAT PENETAPAN</p>

    <table>
        <tr><th style="width:35%">Nomor Penetapan</th><td>{{ $pengajuan->nomor_penetapan }}</td></tr>
        <tr><th>Tanggal Penetapan</th><td>{{ $pengajuan->disetujui_at?->format('d F Y') }}</td></tr>
        <tr><th>Nomor Polisi</th><td>{{ $pengajuan->kendaraan?->nopol }}</td></tr>
        <tr><th>Merk / Tipe</th><td>{{ $pengajuan->kendaraan?->merk }} {{ $pengajuan->kendaraan?->tipe }}</td></tr>
        <tr><th>OPD Pengguna</th><td>{{ $pengajuan->opd?->nama }}</td></tr>
        <tr><th>Tahun Pajak</th><td>{{ $pengajuan->tahun_pajak }}</td></tr>
        <tr><th>Nilai PKB</th><td>Rp {{ number_format($pengajuan->kendaraan?->nilai_pkb ?? 0, 0, ',', '.') }}</td></tr>
        <tr><th>SWDKLLJ</th><td>Rp {{ number_format($pengajuan->kendaraan?->nilai_swdkllj ?? 0, 0, ',', '.') }}</td></tr>
        <tr><th>Catatan</th><td>{{ $pengajuan->catatan ?? '-' }}</td></tr>
    </table>

    <table class="sign">
        <tr>
            <td class="text-center">Mengetahui,<br>Kepala OPD<br><br><br><br><br><br>______________________</td>
            <td class="text-center">Samarinda, {{ $pengajuan->disetujui_at?->format('d F Y') }}<br>Petugas Penetapan<br><br><br><br><br><br>______________________</td>
        </tr>
    </table>

    <p class="text-center" style="margin-top:24px; font-size:10px; color:#6b7280;">
        Dokumen ini diterbitkan otomatis oleh SIPPAKADIS. Nomor: {{ $pengajuan->nomor_penetapan }}
    </p>
</body>
</html>
