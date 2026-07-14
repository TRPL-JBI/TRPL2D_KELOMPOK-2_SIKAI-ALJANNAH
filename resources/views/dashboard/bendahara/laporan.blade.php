@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/bendahara', 'active' => 'bendahara'],
        ['label' => 'Catat Transaksi', 'url' => '/bendahara/catat-transaksi', 'active' => 'bendahara/catat-transaksi'],
        ['label' => 'Iuran Anggota', 'url' => '/bendahara/iuran', 'active' => 'bendahara/iuran'],
        ['label' => 'Laporan Keuangan', 'url' => '/bendahara/laporan', 'active' => 'bendahara/laporan'],
        ['label' => 'Verifikasi Pendaftaran', 'url' => '/bendahara/verifikasi', 'active' => 'bendahara/verifikasi'],
    ]
])

@section('title', 'Laporan Keuangan')

@section('content')
@php
    $currentYear = date('Y');
    $years = range($currentYear, $currentYear - 5);
@endphp
<div class="page-laporan">
    <div class="laporan-header">
        <h1>Laporan Keuangan</h1>
        <div class="date-dropdown">
            <span id="headerDate">{{ $currentYear }}</span>
            <span class="arrow">▾</span>
        </div>
    </div>

    <div class="stat-cards">
        <div class="stat-card green">
            <div class="stat-label">Pemasukan</div>
            <div class="stat-value">Rp 0</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Pengeluaran</div>
            <div class="stat-value">Rp 0</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Saldo Akhir</div>
            <div class="stat-value">Rp 0</div>
        </div>
    </div>

    <div class="table-container">
        <div class="table-search">
            <div class="search-wrap">
                <span class="material-icons search-icon">search</span>
                <input type="text" id="searchInput" placeholder="Cari transaksi...">
            </div>
            <div class="filter-select-wrap">
                <select id="yearSelect">
                    @foreach ($years as $y)
                    <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <span class="material-icons dropdown-icon">expand_more</span>
            </div>
            <div class="filter-select-wrap">
                <select id="periodSelect">
                    <option value="semua">Semua Waktu</option>
                    <option value="hari">Harian</option>
                    <option value="minggu">Mingguan</option>
                    <option value="bulan">Bulanan</option>
                </select>
                <span class="material-icons dropdown-icon">expand_more</span>
            </div>
            <button class="btn-export" onclick="openExportModal()">
                <span class="material-icons" style="font-size:16px;">file_download</span> Export
            </button>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
        <div class="table-footer">
            <div class="pagination" id="pagination"></div>
            <span>Total <span id="dataCount">0</span> transaksi</span>
        </div>
    </div>
</div>

@push('modals')
<div id="modalExport" class="modal-overlay">
    <div class="modal-bg" onclick="closeExportModal()"></div>
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-icon">
            <span class="material-icons">file_download</span>
        </div>
        <h3>Export Laporan Keuangan</h3>
        <p style="font-size:13px;color:#6b7280;text-align:center;margin-bottom:20px;">Pilih periode laporan yang ingin di-export ke Excel.</p>
        <div class="form-group">
            <label>Pilih Tahun</label>
            <select id="exportYear">
                @foreach ($years as $y)
                <option value="{{ $y }}" {{ $y == $currentYear ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label>Pilih Bulan (Opsional)</label>
            <select id="exportMonth">
                <option value="">Semua Bulan (Laporan Tahunan)</option>
                @foreach (['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                <option value="{{ $i + 1 }}">{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="modal-actions">
            <button class="btn-batal" onclick="closeExportModal()">Batal</button>
            <button class="btn-minta" data-loading onclick="doExport()">Export Excel</button>
        </div>
    </div>
</div>
@endpush

<script>
    (function() {
        if (!document.getElementById('tableBody')) return;

        var tableBody = document.getElementById('tableBody');
        var searchInput = document.getElementById('searchInput');
        var yearSelect = document.getElementById('yearSelect');
        var periodSelect = document.getElementById('periodSelect');
        var dataCount = document.getElementById('dataCount');
        var pagination = document.getElementById('pagination');
        var statValueEls = document.querySelectorAll('.stat-value');
        var currentPage = 1;

        function fetchLaporanData() {
            var params = [];
            var search = searchInput.value;
            var year = yearSelect.value;
            var period = periodSelect.value;
            if (search) params.push('search=' + encodeURIComponent(search));
            params.push('year=' + encodeURIComponent(year));
            if (period && period !== 'semua') params.push('period=' + encodeURIComponent(period));
            params.push('page=' + currentPage);
            var url = '/bendahara/laporan/data' + '?' + params.join('&');
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (statValueEls[0]) statValueEls[0].textContent = 'Rp ' + Number(res.totalPemasukan).toLocaleString('id-ID');
                    if (statValueEls[1]) statValueEls[1].textContent = 'Rp ' + Number(res.totalPengeluaran).toLocaleString('id-ID');
                    if (statValueEls[2]) statValueEls[2].textContent = 'Rp ' + Number(res.saldo).toLocaleString('id-ID');
                    renderTable(res.transaksi);
                    dataCount.textContent = res.totalData;
                    renderPagination(res.currentPage, res.lastPage);
                    document.getElementById('headerDate').textContent = year;
                });
        }

        function renderTable(data) {
            if (!data || !data.length) { tableBody.innerHTML = '<tr><td colspan="4" style="padding:40px 22px;text-align:center;color:#9ca3af;">Tidak ada data ditemukan</td></tr>'; return; }
            var html = '';
            data.forEach(function(row) {
                html += '<tr><td>' + row.tanggal + '</td><td style="font-weight:600;">' + row.kategori + '</td><td class="' + (row.type === 'masuk' ? 'nominal-plus' : 'nominal-minus') + '">' + row.nominal + '</td><td><span class="badge-status ' + (row.type === 'masuk' ? 'masuk' : 'keluar') + '">' + (row.type === 'masuk' ? 'Pemasukan' : 'Pengeluaran') + '</span></td></tr>';
            });
            tableBody.innerHTML = html;
        }

        function renderPagination(page, lastPage) {
            var html = '';
            html += '<span class="page-arrow" data-page="' + (page > 1 ? page - 1 : 1) + '"' + (page <= 1 ? ' style="opacity:.3;cursor:default;"' : '') + '>‹</span>';
            for (var i = 1; i <= lastPage; i++) {
                html += '<span class="page-num' + (i === page ? ' active' : '') + '" data-page="' + i + '">' + i + '</span>';
            }
            html += '<span class="page-arrow" data-page="' + (page < lastPage ? page + 1 : lastPage) + '"' + (page >= lastPage ? ' style="opacity:.3;cursor:default;"' : '') + '>›</span>';
            pagination.innerHTML = html;

            pagination.querySelectorAll('.page-num, .page-arrow').forEach(function(el) {
                el.addEventListener('click', function() {
                    var p = parseInt(this.getAttribute('data-page'));
                    if (p && p !== currentPage) { currentPage = p; fetchLaporanData(); }
                });
            });
        }

        searchInput.addEventListener('input', function() { currentPage = 1; fetchLaporanData(); });
        yearSelect.addEventListener('change', function() { currentPage = 1; fetchLaporanData(); });
        periodSelect.addEventListener('change', function() { currentPage = 1; fetchLaporanData(); });

        fetchLaporanData();
    })();

    function openExportModal() { document.getElementById('modalExport').classList.add('active'); }
    function closeExportModal() { document.getElementById('modalExport').classList.remove('active'); }

    function doExport() {
        var btn = document.querySelector('#modalExport .btn-minta');
        var year = document.getElementById('exportYear').value;
        var month = document.getElementById('exportMonth').value;
        var url = '/bendahara/laporan/export?year=' + encodeURIComponent(year);
        if (month) url += '&month=' + encodeURIComponent(month);

        btn.disabled = true;
        btn.innerHTML = '<span class="btn-spinner"></span> Menyiapkan...';

        window.location.href = url;

        setTimeout(function() {
            btn.disabled = false;
            btn.innerHTML = 'Export Excel';
        }, 5000);
    }
</script>
@endsection
