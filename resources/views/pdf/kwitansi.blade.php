<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    * { font-family: 'DejaVu Sans', sans-serif; }
    body { margin: 0; padding: 0; color: #111; }
    .kwitansi { width: 100%; }
    .header { text-align: center; border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 16px; }
    .header .org { font-size: 20px; font-weight: bold; letter-spacing: 1px; }
    .header .title { font-size: 14px; font-weight: bold; margin-top: 2px; text-transform: uppercase; }
    .header .addr { font-size: 10px; color: #555; }
    .body td { padding: 4px 0; font-size: 13px; vertical-align: top; }
    .body .label { width: 120px; font-weight: bold; }
    .amount { text-align: center; margin: 18px 0; }
    .amount .box { border: 2px dashed #111; padding: 12px 24px; font-size: 18px; font-weight: bold; }
    .footer { margin-top: 30px; text-align: right; font-size: 12px; }
    .footer .sign { margin-top: 42px; font-weight: bold; }
</style>
</head>
<body>
<div class="kwitansi">
    <div class="header">
        <div class="org">RKM AL-JANNAH</div>
        <div class="title">Kwitansi Pembayaran</div>
        <div class="addr">Remaja Karang Mekar Masjid Al-Jannah</div>
    </div>

    <table class="body">
        <tr>
            <td class="label">No. Kwitansi</td>
            <td>: {{ $no }}</td>
        </tr>
        <tr>
            <td class="label">Nama</td>
            <td>: {{ $nama }}</td>
        </tr>
        <tr>
            <td class="label">Untuk Pembayaran</td>
            <td>: {{ $peruntukan }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td>: {{ $tanggal }}</td>
        </tr>
    </table>

    <div class="amount">
        <div class="box">Rp {{ number_format($nominal, 0, ',', '.') }},-</div>
    </div>

    <div class="footer">
        <div>Dibayar oleh: {{ $dibayar_oleh }}</div>
        <div class="sign">( {{ $dibayar_oleh }} )</div>
    </div>
</div>
</body>
</html>