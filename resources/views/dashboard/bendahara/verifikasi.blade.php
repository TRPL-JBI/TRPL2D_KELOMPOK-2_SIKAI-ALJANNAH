@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/bendahara', 'active' => 'bendahara'],
        ['label' => 'Catat Transaksi', 'url' => '/bendahara/catat-transaksi', 'active' => 'bendahara/catat-transaksi'],
        ['label' => 'Iuran Anggota', 'url' => '/bendahara/iuran', 'active' => 'bendahara/iuran'],
        ['label' => 'Laporan Keuangan', 'url' => '/bendahara/laporan', 'active' => 'bendahara/laporan'],
        ['label' => 'Verifikasi Pembayaran', 'url' => '/bendahara/verifikasi', 'active' => 'bendahara/verifikasi'],
    ]
])

@section('title', 'Verifikasi Pembayaran Pendaftaran')

@section('content')
<div class="page-vp">
    <div class="vp-header">
        <h1>Verifikasi Pembayaran Pendaftaran</h1>
        <p class="vp-subheader">Konfirmasi pembayaran pendaftaran anggota baru sebesar Rp 30.000</p>
    </div>

    <div id="vpToast" class="toast-notif" style="display:none;">
        <span class="material-icons">check_circle</span>
        <span id="vpToastMsg">Pembayaran terverifikasi</span>
    </div>

    <div class="vp-card">
        <div class="vp-card-header">
            <h3>Calon Anggota Menunggu Pembayaran</h3>
        </div>
        <div class="vp-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Daftar</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="vpBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function() {
        if (!document.getElementById('vpBody')) return;

        var data = [];

        function showVpToast(msg) {
            var el = document.getElementById('vpToastMsg');
            var box = document.getElementById('vpToast');
            el.textContent = msg;
            box.style.display = 'flex';
            clearTimeout(box._t);
            box._t = setTimeout(function() { box.style.display = 'none'; }, 4000);
        }

        function fetchVerifikasiData() {
            fetch('/bendahara/verifikasi/data')
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    data = res.data;
                    renderVerifikasiTable();
                });
        }

        function renderVerifikasiTable() {
            var tbody = document.getElementById('vpBody'), html = '';
            for (var i = 0; i < data.length; i++) {
                var row = data[i];
                html += '<tr>' +
                    '<td>' + row.no + '</td>' +
                    '<td>' + row.tanggal + '</td>' +
                    '<td style="font-weight:600;">' + row.nama + '</td>' +
                    '<td>' + row.telepon + '</td>' +
                    '<td><span class="badge-status belum-lunas">' + row.status + '</span></td>' +
                    '<td><button class="btn-action sudah-dibayar" data-loading onclick="verifikasi(' + row.id + ')">Sudah Membayar</button></td>' +
                '</tr>';
            }
            if (!data.length) {
                html = '<tr><td colspan="6" style="padding:60px 32px;text-align:center;color:#9ca3af;">Semua calon anggota sudah terverifikasi</td></tr>';
            }
            tbody.innerHTML = html;
        }

        window.verifikasi = function(id) {
            var btn = window._loadingBtn;
            if (!confirm('Konfirmasi pembayaran Rp 30.000 untuk calon anggota ini?')) { enableBtn(btn); return; }
            fetchAPI('/bendahara/verifikasi/' + id, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            })
            .then(function(res) {
                if (res.success) {
                    fetchVerifikasiData();
                    var msg = 'Pembayaran terverifikasi';
                    if (res.wa === 'terkirim') msg += ' & kwitansi terkirim via WhatsApp';
                    else if (res.wa === 'gagal') msg += ' (kwitansi WhatsApp gagal terkirim)';
                    showVpToast(msg);
                } else {
                    alert(res.message || 'Gagal verifikasi');
                }
                enableBtn(btn);
            })
            .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); enableBtn(btn); });
        };

        fetchVerifikasiData();
    })();
</script>
@endsection
