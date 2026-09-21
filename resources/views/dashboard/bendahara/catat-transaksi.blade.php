@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/bendahara', 'active' => 'bendahara'],
        ['label' => 'Catat Transaksi', 'url' => '/bendahara/catat-transaksi', 'active' => 'bendahara/catat-transaksi'],
        ['label' => 'Iuran Anggota', 'url' => '/bendahara/iuran', 'active' => 'bendahara/iuran'],
        ['label' => 'Laporan Keuangan', 'url' => '/bendahara/laporan', 'active' => 'bendahara/laporan'],
        ['label' => 'Verifikasi Pendaftaran', 'url' => '/bendahara/verifikasi', 'active' => 'bendahara/verifikasi'],
    ]
])

@section('title', 'Catat Transaksi')

@section('content')
<div class="page-transaksi">
<script>
    window._targetTable = 'transaksi';
@php
    $kPemasukanJson = $kategoriPemasukan->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama])->toJson();
    $kPengeluaranJson = $kategoriPengeluaran->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama])->toJson();
@endphp
    window._kategoriPemasukan = {!! $kPemasukanJson !!};
    window._kategoriPengeluaran = {!! $kPengeluaranJson !!};
</script>
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
        <h1>Catat Transaksi</h1>
        <p class="subtitle">Pencatatan transaksi keuangan RKM Al-Jannah</p>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="btn-secondary" onclick="openModalImport()" style="display:inline-flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:18px;">upload_file</span>
            Import
        </button>
        <button class="btn-secondary" onclick="openModalExport()" style="display:inline-flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:18px;">download</span>
            Export
        </button>
        <a href="{{ route('bendahara.catat-transaksi.template') }}" class="btn-secondary" style="display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
            <span class="material-icons" style="font-size:18px;">description</span>
            Template
        </a>
        <button class="btn-primary" onclick="openModalTransaksi()" data-loading data-loading-text="Membuka..." style="display:inline-flex;align-items:center;gap:6px;">
            <span class="material-icons" style="font-size:18px;">add</span>
            Tambah Transaksi
        </button>
    </div>
</div>

<div id="toastSuccess" class="toast-success">
    <span class="material-icons">check_circle</span>
    <span id="toastSuccessMsg">Transaksi berhasil dicatat</span>
</div>

<div id="toastImportError" class="toast-success" style="display:none;background:#dc2626;">
    <span class="material-icons">error</span>
    <span id="toastImportMsg"></span>
</div>

@if(session('export_error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('toastImportMsg');
    var box = document.getElementById('toastImportError');
    el.textContent = '{{ session('export_error') }}';
    box.style.display = 'flex';
    clearTimeout(box._t);
    box._t = setTimeout(function() { box.style.display = 'none'; }, 5000);
});
</script>
@endif

<div class="filter-card" style="background:#fff;border-radius:20px;border:2px solid var(--primary-500);box-shadow:0 4px 16px rgba(0,0,0,.05);padding:20px 24px;margin-top:24px;">
    <form id="filterForm" method="GET" action="{{ route('bendahara.catat-transaksi') }}">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:12px;margin-bottom:12px;">
            <div class="form-group" style="margin-bottom:0;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--primary-400);margin-bottom:6px;">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" id="filterTanggalMulai" value="{{ $filter['tanggal_mulai'] ?? '' }}" style="width:100%;padding:10px 14px;border:2px solid var(--primary-200);border-radius:12px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;color:#1f2937;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--primary-400);margin-bottom:6px;">Tanggal Sampai</label>
                <input type="date" name="tanggal_sampai" id="filterTanggalSampai" value="{{ $filter['tanggal_sampai'] ?? '' }}" style="width:100%;padding:10px 14px;border:2px solid var(--primary-200);border-radius:12px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;color:#1f2937;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--primary-400);margin-bottom:6px;">Tipe Transaksi</label>
                <select name="tipe" id="filterTipe" onchange="toggleFilterKategori(this.value)" style="width:100%;padding:10px 14px;border:2px solid var(--primary-200);border-radius:12px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;appearance:auto;color:#1f2937;">
                    <option value="">Semua</option>
                    <option value="pemasukan" {{ ($filter['tipe'] ?? '') === 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="pengeluaran" {{ ($filter['tipe'] ?? '') === 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--primary-400);margin-bottom:6px;">Kategori</label>
                <select name="kategori" id="filterKategori" style="width:100%;padding:10px 14px;border:2px solid var(--primary-200);border-radius:12px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;appearance:auto;color:#1f2937;">
                    <option value="">Semua Kategori</option>
                </select>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto auto;gap:12px;align-items:end;">
            <div class="form-group" style="margin-bottom:0;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--primary-400);margin-bottom:6px;">Cari Keterangan</label>
                <input type="text" name="keterangan" id="filterKeterangan" value="{{ $filter['keterangan'] ?? '' }}" placeholder="Masukkan keterangan..." style="width:100%;padding:10px 14px;border:2px solid var(--primary-200);border-radius:12px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;color:#1f2937;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--primary-400);margin-bottom:6px;">Nominal Minimum</label>
                <input type="number" name="nominal_min" id="filterNominalMin" value="{{ $filter['nominal_min'] ?? '' }}" placeholder="0" min="0" style="width:100%;padding:10px 14px;border:2px solid var(--primary-200);border-radius:12px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;color:#1f2937;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label style="display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--primary-400);margin-bottom:6px;">Nominal Maksimum</label>
                <input type="number" name="nominal_max" id="filterNominalMax" value="{{ $filter['nominal_max'] ?? '' }}" placeholder="∞" min="0" style="width:100%;padding:10px 14px;border:2px solid var(--primary-200);border-radius:12px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;color:#1f2937;">
            </div>
            <button type="submit" class="btn-primary" style="padding:10px 24px;font-size:13px;border-radius:12px;white-space:nowrap;">
                <span class="material-icons" style="font-size:16px;">filter_list</span>
                Filter
            </button>
            <a href="{{ route('bendahara.catat-transaksi') }}" class="btn-secondary" style="padding:10px 24px;font-size:13px;border-radius:12px;white-space:nowrap;text-decoration:none;">
                <span class="material-icons" style="font-size:16px;">restart_alt</span>
                Reset
            </a>
        </div>
    </form>
</div>

<div class="table-card" style="margin-top:16px;">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Nominal</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="riwayatTransaksiBody">
                @forelse($transaksi as $item)
                <tr data-target-table="{{ strtolower($item['tipe']) }}" data-jumlah="{{ $item['jumlah'] }}" data-kategori="{{ $item['kategori'] }}" data-keterangan="{{ $item['keterangan'] ?? '' }}" data-tanggal="{{ $item['tanggal'] }}" data-tipe="{{ $item['tipe'] }}" data-file-url="{{ $item['file_url'] ?? '' }}">
                    <td data-target-id="{{ $item['id'] }}">{{ $item['tanggal'] }}</td>
                    <td><span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;{{ $item['tipe'] === 'Pemasukan' ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;' }}">{{ $item['tipe'] }}</span></td>
                    <td class="nominal-value">Rp {{ number_format($item['jumlah'], 0, ',', '.') }}</td>
                    <td class="name-cell">{{ $item['kategori'] }}</td>
                    <td>{{ $item['keterangan'] ?? '-' }}</td>
                    <td>
                        <button class="btn-icon btn-info" onclick="openInfoModal(this)" title="Detail Transaksi">
                            <span class="material-icons">info</span>
                        </button>
                        <button class="btn-edit" onclick="openModal(this)">
                            <span class="material-icons">lock</span>
                            Edit
                        </button>
                    </td>
                </tr>
                @empty
                <tr id="emptyRowTransaksi">
                    <td colspan="6" style="padding:60px 32px;text-align:center;color:#9ca3af;">
                        <span class="material-icons" style="font-size:40px;display:block;margin-bottom:8px;color:#d1d5db;">receipt_long</span>
                        Belum ada transaksi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:16px 24px;">
        {{ $transaksi->links() }}
    </div>
</div>

</div>

<script>
    function toggleFilterKategori(tipe) {
        var sel = document.getElementById('filterKategori');
        var currentVal = '{{ $filter["kategori"] ?? "" }}';
        var html = '<option value="">Semua Kategori</option>';
        var kPemasukan = window._kategoriPemasukan || [];
        var kPengeluaran = window._kategoriPengeluaran || [];
        if (tipe === 'pemasukan') {
            for (var i = 0; i < kPemasukan.length; i++) {
                var val = 'pemasukan_' + kPemasukan[i].id;
                html += '<option value="' + val + '"' + (val === currentVal ? ' selected' : '') + '>' + kPemasukan[i].nama + '</option>';
            }
        } else if (tipe === 'pengeluaran') {
            for (var i = 0; i < kPengeluaran.length; i++) {
                var val = 'pengeluaran_' + kPengeluaran[i].id;
                html += '<option value="' + val + '"' + (val === currentVal ? ' selected' : '') + '>' + kPengeluaran[i].nama + '</option>';
            }
        } else {
            for (var i = 0; i < kPemasukan.length; i++) {
                var val = 'pemasukan_' + kPemasukan[i].id;
                html += '<option value="' + val + '"' + (val === currentVal ? ' selected' : '') + '>' + kPemasukan[i].nama + '</option>';
            }
            for (var i = 0; i < kPengeluaran.length; i++) {
                var val = 'pengeluaran_' + kPengeluaran[i].id;
                html += '<option value="' + val + '"' + (val === currentVal ? ' selected' : '') + '>' + kPengeluaran[i].nama + '</option>';
            }
        }
        sel.innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var tipe = document.getElementById('filterTipe').value;
        toggleFilterKategori(tipe);
    });

    document.getElementById('filterForm').addEventListener('submit', function(e) {
        var tglMulai = document.getElementById('filterTanggalMulai').value;
        var tglSampai = document.getElementById('filterTanggalSampai').value;
        var nomMin = document.getElementById('filterNominalMin').value;
        var nomMax = document.getElementById('filterNominalMax').value;

        if (tglMulai && tglSampai && tglMulai > tglSampai) {
            e.preventDefault();
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal sampai.');
            return false;
        }
        if (nomMin && nomMax && parseFloat(nomMin) > parseFloat(nomMax)) {
            e.preventDefault();
            alert('Nominal minimum tidak boleh lebih besar dari nominal maksimum.');
            return false;
        }
    });

    /* ═══ 2-Step Transaksi Modal ═══ */
    var _txSelectedType = null;

    function openModalTransaksi() {
        enableBtn();
        _txSelectedType = null;
        document.getElementById('modalTransaksi').classList.add('active');
        txShowStep(1);
        // Reset step1 selection
        var btns = document.querySelectorAll('#txStep1 .tx-type-btn');
        btns.forEach(function(b) { b.classList.remove('selected'); });
        document.getElementById('txConfirmBtn').disabled = true;
        // Reset step2 fields
        document.getElementById('formTanggal').value = '';
        document.getElementById('formKategori').innerHTML = '<option value="">Pilih kategori</option>';
        document.getElementById('formNominal').value = '';
        document.getElementById('formKeterangan').value = '';
        document.getElementById('formWaNama').value = '';
        document.getElementById('formWaPenerima').value = '';
        var fp = document.querySelector('#txStep2 .tx-file-upload .upload-placeholder');
        if (fp) fp.textContent = 'Pilih file bukti transaksi';
    }

    function closeModalTransaksi() {
        document.getElementById('modalTransaksi').classList.remove('active');
        _txSelectedType = null;
    }

    function txShowStep(n) {
        document.getElementById('txStep1').classList.toggle('active', n === 1);
        document.getElementById('txStep2').classList.toggle('active', n === 2);
    }

    function txSelectType(tipe, el) {
        _txSelectedType = tipe;
        var btns = document.querySelectorAll('#txStep1 .tx-type-btn');
        btns.forEach(function(b) { b.classList.remove('selected'); });
        el.classList.add('selected');
        document.getElementById('txConfirmBtn').disabled = false;
    }

    function txGoStep2() {
        if (!_txSelectedType) return;
        // Set title
        var isPemasukan = _txSelectedType === 'pemasukan';
        document.getElementById('txFormTitle').textContent = isPemasukan ? 'Pemasukan Baru' : 'Pengeluaran Baru';
        // Toggle WA fields
        document.getElementById('waFieldsGroup').style.display = isPemasukan ? 'block' : 'none';
        // Populate kategori
        var sel = document.getElementById('formKategori');
        var opts = isPemasukan ? (window._kategoriPemasukan || []) : (window._kategoriPengeluaran || []);
        sel.innerHTML = '<option value="">Pilih kategori</option>';
        for (var i = 0; i < opts.length; i++) {
            sel.innerHTML += '<option value="' + opts[i].id + '">' + opts[i].nama + '</option>';
        }
        txShowStep(2);
    }

    function txBackStep1() {
        txShowStep(1);
    }

    function showTransaksiToast(msg) {
        var el = document.getElementById('toastSuccessMsg');
        var box = document.getElementById('toastSuccess');
        el.textContent = msg;
        box.style.display = 'flex';
        clearTimeout(box._t);
        box._t = setTimeout(function() { box.style.display = 'none'; }, 4000);
    }

    function simpanTransaksi() {
        var btn = window._loadingBtn;
        var tipe = _txSelectedType;
        var tanggal = document.getElementById('formTanggal').value;
        var kategori = document.getElementById('formKategori').value;
        var nominal = document.getElementById('formNominal').value;
        var keterangan = document.getElementById('formKeterangan').value;
        var file = document.querySelector('#txStep2 input[type=file]');

        if (!tipe) { enableBtn(btn); alert('Pilih tipe transaksi.'); return; }
        if (!tanggal || !kategori || !nominal) { enableBtn(btn); alert('Harap isi tanggal, kategori, dan nominal.'); return; }
        if (!file || !file.files[0]) { enableBtn(btn); alert('Harap upload bukti transaksi.'); return; }

        var formData = new FormData();
        formData.append('tipe', tipe);
        formData.append('tanggal', tanggal);
        formData.append('kategori_id', kategori);
        formData.append('jumlah', nominal.replace(/[^0-9]/g, ''));
        formData.append('keterangan', keterangan);
        if (tipe === 'pemasukan') {
            formData.append('wa_nama', document.getElementById('formWaNama').value.trim());
            formData.append('wa_penerima', document.getElementById('formWaPenerima').value.trim());
        }
        formData.append('file_bukti', file.files[0]);

        fetchAPI('/bendahara/catat-transaksi', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(function(res) {
            closeModalTransaksi();
            if (!res.success) { enableBtn(btn); return alert(res.message || 'Gagal menyimpan'); }
            var d = res.data;
            var emptyRow = document.getElementById('emptyRowTransaksi');
            if (emptyRow) emptyRow.remove();
            var tbody = document.getElementById('riwayatTransaksiBody');
            var row = document.createElement('tr');
            tbody.insertBefore(row, tbody.firstChild);
            row.outerHTML = '<tr data-target-table="' + (d.tipe === 'Pemasukan' ? 'pemasukan' : 'pengeluaran') + '" data-jumlah="' + d.jumlah + '" data-kategori="' + (d.kategori || '') + '" data-keterangan="' + (d.keterangan || '') + '" data-tanggal="' + d.tanggal + '" data-tipe="' + d.tipe + '" data-file-url="' + (d.file_url || '') + '"><td data-target-id="' + d.id + '">' + d.tanggal + '</td><td><span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;' + (d.tipe === 'Pemasukan' ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;') + '">' + d.tipe + '</span></td><td class="nominal-value">Rp ' + Number(d.jumlah).toLocaleString('id-ID') + '</td><td class="name-cell">' + d.kategori + '</td><td>' + (d.keterangan || '-') + '</td><td><button class="btn-icon btn-info" onclick="openInfoModal(this)" title="Detail Transaksi"><span class="material-icons">info</span></button><button class="btn-edit" onclick="openModal(this)"><span class="material-icons">lock</span> Edit</button></td></tr>';
            var waMsg = d.wa === 'terkirim' ? ' & kwitansi terkirim via WA' : (d.wa === 'gagal' ? ' (kwitansi WA gagal)' : '');
            showTransaksiToast('Transaksi berhasil dicatat' + waMsg);
            enableBtn(btn);
        })
        .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); enableBtn(btn); });
    }

    function openInfoModal(btn) {
        var row = btn.closest('tr');
        var tipe = row.getAttribute('data-tipe') || '';
        var tanggal = row.getAttribute('data-tanggal') || '';
        var kategori = row.getAttribute('data-kategori') || '';
        var jumlah = row.getAttribute('data-jumlah') || '';
        var keterangan = row.getAttribute('data-keterangan') || '';
        var fileUrl = row.getAttribute('data-file-url') || '';

        document.getElementById('infoTipe').textContent = tipe;
        document.getElementById('infoTanggal').textContent = tanggal;
        document.getElementById('infoKategori').textContent = kategori;
        document.getElementById('infoNominal').textContent = 'Rp ' + Number(jumlah).toLocaleString('id-ID');
        document.getElementById('infoKeterangan').textContent = keterangan || '-';

        var container = document.getElementById('infoFileContainer');
        if (fileUrl) {
            var ext = fileUrl.split('.').pop().toLowerCase();
            if (['jpg','jpeg','png','gif','webp','svg'].indexOf(ext) !== -1) {
                container.innerHTML = '<img src="' + fileUrl + '" alt="Bukti Transaksi" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid #e5e7eb;object-fit:contain;">';
            } else {
                container.innerHTML = '<a href="' + fileUrl + '" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;background:#f3f4f6;border-radius:8px;color:var(--primary-500);text-decoration:none;font-size:13px;font-weight:500;"><span class="material-icons" style="font-size:16px;">download</span> Download File</a>';
            }
        } else {
            container.innerHTML = '<span style="color:#9ca3af;font-size:13px;">Tidak ada bukti transaksi</span>';
        }

        document.getElementById('modalInfo').classList.add('active');
    }

    function closeInfoModal() {
        document.getElementById('modalInfo').classList.remove('active');
    }

    function openModalImport() {
        document.getElementById('modalImportResult').classList.remove('active');
        document.getElementById('modalImport').classList.add('active');
        document.getElementById('importFile').value = '';
        document.getElementById('importFilePlaceholder').textContent = 'Upload here...';
    }

    function closeModalImport() {
        document.getElementById('modalImport').classList.remove('active');
    }

    function closeModalImportResult() {
        document.getElementById('modalImportResult').classList.remove('active');
        location.reload();
    }

    function showImportResult(res) {
        var content = document.getElementById('importResultContent');
        var html = '';

        // Summary badge
        var isOk = res.berhasil > 0 && res.gagal === 0;
        var isPartial = res.berhasil > 0 && res.gagal > 0;
        var isAllFailed = res.berhasil === 0 && res.gagal > 0;
        var badgeColor = isOk ? '#059669' : (isPartial ? '#d97706' : '#dc2626');
        var badgeIcon = isOk ? 'check_circle' : (isPartial ? 'warning' : 'cancel');

        html += '<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;background:' + badgeColor + '11;border:1px solid ' + badgeColor + '33;margin-bottom:12px;">';
        html += '<span class="material-icons" style="color:' + badgeColor + ';font-size:22px;">' + badgeIcon + '</span>';
        html += '<div><div style="font-weight:700;color:' + badgeColor + ';font-size:14px;">' + res.message + '</div>';

        if (res.berhasil > 0) {
            html += '<div style="font-size:12px;color:#059669;margin-top:2px;">' + res.berhasil + ' data berhasil diimport</div>';
        }
        if (res.gagal > 0) {
            html += '<div style="font-size:12px;color:#dc2626;margin-top:2px;">' + res.gagal + ' data gagal / dibatalkan</div>';
        }
        html += '</div></div>';

        // New categories created
        if (res.new_categories && res.new_categories.length > 0) {
            html += '<div style="margin-top:8px;padding:10px 14px;border-radius:8px;background:#dbeafe;border:1px solid #93c5fd;font-size:12px;color:#1e40af;">';
            html += '<span class="material-icons" style="font-size:16px;vertical-align:middle;margin-right:4px;">add_circle</span>';
            html += '<strong>Kategori baru dibuat:</strong><ul style="margin:4px 0 0 20px;padding:0;">';
            for (var c = 0; c < res.new_categories.length; c++) {
                html += '<li>' + res.new_categories[c] + '</li>';
            }
            html += '</ul></div>';
        }

        // Error list
        if (res.errors && res.errors.length > 0) {
            html += '<div style="margin-top:8px;">';
            html += '<div style="font-weight:600;font-size:12px;color:#6b7280;margin-bottom:6px;">Detail Error (' + res.errors.length + '):</div>';
            html += '<div style="max-height:240px;overflow-y:auto;border:1px solid #f3f4f6;border-radius:8px;background:#fef2f2;">';
            html += '<ul style="margin:0;padding:12px 12px 12px 32px;font-size:12px;color:#991b1b;line-height:1.8;">';
            for (var i = 0; i < res.errors.length; i++) {
                html += '<li style="border-bottom:1px solid #fecaca;padding-bottom:4px;">' + res.errors[i] + '</li>';
            }
            html += '</ul></div>';

            if (isAllFailed) {
                html += '<div style="margin-top:10px;padding:10px 14px;border-radius:8px;background:#fef3c7;border:1px solid #fbbf24;font-size:12px;color:#92400e;">';
                html += '<span class="material-icons" style="font-size:16px;vertical-align:middle;margin-right:4px;">info</span>';
                html += '<strong>Semua data dibatalkan.</strong> Perbaiki semua error di atas, lalu import ulang. Tidak ada data yang tersimpan ke database.';
                html += '</div>';
            }
            html += '</div>';
        }

        content.innerHTML = html;
        document.getElementById('modalImportResult').classList.add('active');
    }

    function importTransaksi() {
        var btn = document.querySelector('#modalImport [data-loading]');
        var fileInput = document.getElementById('importFile');
        if (!fileInput.files[0]) { alert('Harap pilih file Excel terlebih dahulu.'); return; }

        var formData = new FormData();
        formData.append('file_import', fileInput.files[0]);

        btn.disabled = true;
        btn.innerHTML = '<span class="btn-spinner"></span> Mengimport...';

        fetchAPI('{{ route('bendahara.catat-transaksi.import') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(function(res) {
            closeModalImport();
            showImportResult(res);
            btn.disabled = false;
            btn.innerHTML = 'Import';
        })
        .catch(function(e) {
            btn.disabled = false;
            btn.innerHTML = 'Import';
            var content = document.getElementById('importResultContent');
            content.innerHTML = '<div style="color:#dc2626;font-weight:600;">Gagal: ' + e.message + '</div>';
            document.getElementById('modalImportResult').classList.add('active');
        });
    }

    function openModalExport() {
        document.getElementById('modalExport').classList.add('active');
        document.getElementById('exportTipe').value = '';
        document.getElementById('exportTanggalMulai').value = '';
        document.getElementById('exportTanggalAkhir').value = '';
        toggleExportKategori('');
    }

    function closeModalExport() {
        document.getElementById('modalExport').classList.remove('active');
    }

    function toggleExportKategori(tipe) {
        var sel = document.getElementById('exportKategori');
        var html = '<option value="">Semua Kategori</option>';
        var kPemasukan = window._kategoriPemasukan || [];
        var kPengeluaran = window._kategoriPengeluaran || [];
        if (tipe === 'pemasukan') {
            for (var i = 0; i < kPemasukan.length; i++) {
                html += '<option value="pemasukan_' + kPemasukan[i].id + '">' + kPemasukan[i].nama + '</option>';
            }
        } else if (tipe === 'pengeluaran') {
            for (var i = 0; i < kPengeluaran.length; i++) {
                html += '<option value="pengeluaran_' + kPengeluaran[i].id + '">' + kPengeluaran[i].nama + '</option>';
            }
        } else {
            for (var i = 0; i < kPemasukan.length; i++) {
                html += '<option value="pemasukan_' + kPemasukan[i].id + '">' + kPemasukan[i].nama + '</option>';
            }
            for (var i = 0; i < kPengeluaran.length; i++) {
                html += '<option value="pengeluaran_' + kPengeluaran[i].id + '">' + kPengeluaran[i].nama + '</option>';
            }
        }
        sel.innerHTML = html;
    }

    document.addEventListener('change', function(e) {
        if (e.target.id === 'exportTipe') toggleExportKategori(e.target.value);
    });

    function exportTransaksi() {
        var tipe = document.getElementById('exportTipe').value;
        var kategoriVal = document.getElementById('exportKategori').value;
        var tanggalMulai = document.getElementById('exportTanggalMulai').value;
        var tanggalAkhir = document.getElementById('exportTanggalAkhir').value;

        if (tanggalMulai && tanggalAkhir && tanggalMulai > tanggalAkhir) {
            alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir.');
            return;
        }

        var params = [];
        if (tipe) params.push('tipe=' + encodeURIComponent(tipe));
        if (kategoriVal) {
            var parts = kategoriVal.split('_');
            var kategoriType = parts[0];
            var kategoriId = parts.slice(1).join('_');
            params.push('kategori_type=' + encodeURIComponent(kategoriType));
            params.push('kategori_id=' + encodeURIComponent(kategoriId));
        }
        if (tanggalMulai) params.push('tanggal_mulai=' + encodeURIComponent(tanggalMulai));
        if (tanggalAkhir) params.push('tanggal_akhir=' + encodeURIComponent(tanggalAkhir));

        var url = '{{ route("bendahara.catat-transaksi.export") }}' + (params.length ? '?' + params.join('&') : '');

        closeModalExport();
        window.location.href = url;
    }
</script>
@endsection

@push('modals')
<style>
/* ── Transaksi modal 2-step ──────────────────────────────────────── */
.tx-step { display: none; }
.tx-step.active { display: block; }
.tx-step-choose { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 8px 0 4px; }
.tx-type-btn {
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    padding: 24px 16px; border-radius: 16px; border: 2px solid #e5e7eb;
    background: #fff; cursor: pointer; transition: all .2s; font-family: 'Poppins', sans-serif;
}
.tx-type-btn:hover { border-color: var(--primary-400); background: #f0fdf4; }
.tx-type-btn.selected { border-color: var(--primary-500); background: #ecfdf5; box-shadow: 0 0 0 3px rgba(16,185,129,.15); }
.tx-type-btn .tx-icon {
    width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center;
}
.tx-type-btn .tx-icon.income { background: #d1fae5; color: #065f46; }
.tx-type-btn .tx-icon.expense { background: #fee2e2; color: #991b1b; }
.tx-type-btn .tx-label { font-size: 14px; font-weight: 600; color: #1f2937; }
.tx-type-btn .tx-desc { font-size: 11px; color: #9ca3af; text-align: center; line-height: 1.4; }
.tx-confirm-row { display: flex; justify-content: flex-end; gap: 10px; padding-top: 8px; }

.tx-form-group { margin-bottom: 14px; }
.tx-form-group label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 5px; }
.tx-form-input {
    width: 100%; padding: 11px 14px; border: 1.5px solid #d1d5db; border-radius: 10px;
    font-size: 14px; font-family: 'Poppins', sans-serif; outline: none; box-sizing: border-box;
    background: #fff; color: #1f2937; transition: border-color .2s;
}
.tx-form-input:focus { border-color: var(--primary-400); }
.tx-form-input::placeholder { color: #c0c4cc; }
textarea.tx-form-input { resize: vertical; min-height: 72px; }
select.tx-form-input { appearance: auto; }
.tx-form-hint { font-size: 11px; color: #9ca3af; margin-top: 3px; }

.tx-file-upload {
    width: 100%; padding: 14px; border: 1.5px dashed #d1d5db; border-radius: 10px;
    text-align: center; cursor: pointer; transition: border-color .2s; box-sizing: border-box;
}
.tx-file-upload:hover { border-color: var(--primary-400); }

.tx-modal-box {
    max-width: 520px; width: calc(100% - 32px); max-height: 90vh; overflow-y: auto;
    padding: 28px 24px 20px; box-sizing: border-box;
}
.tx-modal-actions {
    display: flex; gap: 10px; justify-content: flex-end; padding-top: 16px;
    border-top: 1px solid #f3f4f6; margin-top: 8px; position: sticky; bottom: -28px;
    background: #fff; padding-bottom: 4px;
}
.tx-btn {
    padding: 10px 24px; border-radius: 10px; font-size: 13px; font-weight: 600;
    font-family: 'Poppins', sans-serif; border: none; cursor: pointer; transition: all .15s;
    display: inline-flex; align-items: center; gap: 6px;
}
.tx-btn-secondary { background: #f3f4f6; color: #374151; }
.tx-btn-secondary:hover { background: #e5e7eb; }
.tx-btn-primary { background: var(--primary-500); color: #fff; }
.tx-btn-primary:hover { background: var(--primary-600); }
.tx-btn:disabled { opacity: .5; cursor: not-allowed; }

.tx-back-btn {
    background: none; border: none; cursor: pointer; color: #6b7280; padding: 4px;
    display: inline-flex; align-items: center; font-size: 13px; gap: 2px; font-family: 'Poppins', sans-serif;
}
.tx-back-btn:hover { color: #374151; }

@media (max-width: 480px) {
    .tx-modal-box { padding: 20px 16px 16px; border-radius: 16px 16px 0 0; width: 100%; max-width: 100%; max-height: 88vh; }
    .tx-step-choose { gap: 10px; }
    .tx-type-btn { padding: 18px 12px; }
    .tx-type-btn .tx-icon { width: 44px; height: 44px; border-radius: 12px; }
    .tx-modal-actions { gap: 8px; }
    .tx-btn { padding: 10px 18px; font-size: 13px; }
}
</style>

<div id="modalTransaksi" class="modal-overlay">
    <div class="modal-bg"></div>
    <div class="tx-modal-box modal-box">

        {{-- ═══ STEP 1: Pilih Tipe ═══ --}}
        <div id="txStep1" class="tx-step active">
            <h3 style="text-align:center;margin:0 0 4px;font-size:17px;">Tambah Transaksi</h3>
            <p style="text-align:center;font-size:12px;color:#9ca3af;margin:0 0 16px;">Pilih jenis transaksi yang ingin dicatat</p>
            <div class="tx-step-choose">
                <button type="button" class="tx-type-btn" onclick="txSelectType('pemasukan', this)">
                    <div class="tx-icon income"><span class="material-icons" style="font-size:26px;">add_circle</span></div>
                    <div class="tx-label">Pemasukan</div>
                    <div class="tx-desc">Kwitansi & notifikasi WA otomatis</div>
                </button>
                <button type="button" class="tx-type-btn" onclick="txSelectType('pengeluaran', this)">
                    <div class="tx-icon expense"><span class="material-icons" style="font-size:26px;">remove_circle</span></div>
                    <div class="tx-label">Pengeluaran</div>
                    <div class="tx-desc">Catat pengeluaran organisasi</div>
                </button>
            </div>
            <div class="tx-confirm-row">
                <button class="tx-btn tx-btn-secondary" onclick="closeModalTransaksi()">Batal</button>
                <button class="tx-btn tx-btn-primary" id="txConfirmBtn" disabled onclick="txGoStep2()">
                    Lanjut
                    <span class="material-icons" style="font-size:16px;">arrow_forward</span>
                </button>
            </div>
        </div>

        {{-- ═══ STEP 2: Form Isian ═══ --}}
        <div id="txStep2" class="tx-step">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                <button type="button" class="tx-back-btn" onclick="txBackStep1()">
                    <span class="material-icons" style="font-size:20px;">arrow_back</span>
                    Kembali
                </button>
                <h3 id="txFormTitle" style="margin:0;font-size:17px;flex:1;">Form Transaksi</h3>
            </div>

            <div class="tx-form-group">
                <label>Tanggal</label>
                <input type="date" id="formTanggal" class="tx-form-input">
            </div>
            <div class="tx-form-group">
                <label>Kategori</label>
                <select id="formKategori" class="tx-form-input">
                    <option value="">Pilih kategori</option>
                </select>
            </div>
            <div class="tx-form-group">
                <label>Nominal</label>
                <div style="position:relative;">
                    <span style="position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:13px;color:#9ca3af;font-weight:600;">Rp</span>
                    <input type="text" id="formNominal" class="tx-form-input" style="padding-left:36px;" placeholder="0">
                </div>
            </div>
            <div class="tx-form-group">
                <label>Keterangan</label>
                <textarea id="formKeterangan" class="tx-form-input" placeholder="Tulis keterangan..."></textarea>
            </div>

            {{-- WA fields: hanya untuk pemasukan --}}
            <div id="waFieldsGroup">
                <div class="tx-form-group">
                    <label>Nama Penerima Kwitansi <span style="font-weight:400;color:#9ca3af;">(opsional)</span></label>
                    <input type="text" id="formWaNama" class="tx-form-input" placeholder="Budi Santoso">
                </div>
                <div class="tx-form-group">
                    <label>No. WhatsApp <span style="font-weight:400;color:#9ca3af;">(opsional)</span></label>
                    <input type="text" id="formWaPenerima" class="tx-form-input" placeholder="6281234567890">
                    <div class="tx-form-hint">Tanpa 0 di depan. Kwitansi PDF akan dikirim otomatis.</div>
                </div>
            </div>

            <div class="tx-form-group">
                <label>Upload Bukti <span style="font-weight:400;color:#dc2626;">*</span></label>
                <div class="tx-file-upload" onclick="this.querySelector('input').click();">
                    <input type="file" required onchange="this.parentElement.querySelector('.upload-placeholder').textContent = this.files[0].name;" style="display:none;">
                    <span class="material-icons" style="font-size:22px;color:#d1d5db;display:block;margin-bottom:4px;">cloud_upload</span>
                    <span class="upload-placeholder" style="font-size:13px;color:#9ca3af;">Pilih file bukti transaksi</span>
                </div>
            </div>

            <div class="tx-modal-actions">
                <button class="tx-btn tx-btn-secondary" onclick="closeModalTransaksi()">Batal</button>
                <button class="tx-btn tx-btn-primary" data-loading onclick="simpanTransaksi()">
                    <span class="material-icons" style="font-size:16px;">save</span>
                    Simpan
                </button>
            </div>
        </div>

    </div>
</div>

<div id="modalInfo" class="modal-overlay">
    <div class="modal-bg"></div>
    <div class="modal-box" style="max-width:520px;text-align:left;">
        <div class="modal-icon">
            <span class="material-icons">receipt_long</span>
        </div>
        <h3 style="text-align:center;margin-bottom:20px;">Detail Transaksi</h3>
        <div id="infoContent" style="display:flex;flex-direction:column;gap:14px;">
            <div class="info-row" style="display:flex;gap:12px;">
                <div class="info-field" style="flex:1;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Tipe</label>
                    <span id="infoTipe" style="font-size:14px;font-weight:500;"></span>
                </div>
                <div class="info-field" style="flex:1;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Tanggal</label>
                    <span id="infoTanggal" style="font-size:14px;font-weight:500;"></span>
                </div>
            </div>
            <div class="info-row" style="display:flex;gap:12px;">
                <div class="info-field" style="flex:1;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Kategori</label>
                    <span id="infoKategori" style="font-size:14px;font-weight:500;"></span>
                </div>
                <div class="info-field" style="flex:1;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Nominal</label>
                    <span id="infoNominal" style="font-size:14px;font-weight:500;"></span>
                </div>
            </div>
            <div class="info-field">
                <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Keterangan</label>
                <span id="infoKeterangan" style="font-size:14px;font-weight:500;"></span>
            </div>
            <div class="info-field">
                <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Bukti Transaksi</label>
                <div id="infoFileContainer" style="margin-top:4px;"></div>
            </div>
        </div>
        <div class="modal-actions" style="margin-top:24px;">
            <button class="btn-batal" onclick="closeInfoModal()">Tutup</button>
        </div>
    </div>
</div>

<div id="modalImport" class="modal-overlay">
    <div class="modal-bg"></div>
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-icon">
            <span class="material-icons">upload_file</span>
        </div>
        <h3>Import Data Transaksi & Iuran</h3>
        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;text-align:center;">
            Upload file Excel (.xlsx) untuk mengimport data pemasukan, pengeluaran, dan iuran anggota secara massal.
            <br><a href="{{ route('bendahara.catat-transaksi.template') }}" style="color:var(--primary-500);font-weight:500;">Download template</a> terlebih dahulu jika belum punya.
        </p>
        <div class="form-group" style="margin-bottom:18px;">
            <label>Pilih File Excel</label>
            <div class="file-upload" onclick="this.querySelector('input').click();" style="width:100%;padding:12px 16px;border:1px dashed #d1d5db;border-radius:8px;text-align:center;cursor:pointer;box-sizing:border-box;">
                <input type="file" id="importFile" accept=".xlsx,.xls,.ods" required onchange="document.getElementById('importFilePlaceholder').textContent = this.files[0].name;" style="display:none;">
                <span id="importFilePlaceholder" class="upload-placeholder" style="font-size:13px;color:#9ca3af;">Upload here...</span>
                <div class="upload-icon">
                    <span class="material-icons" style="font-size:18px;vertical-align:middle;color:#6b7280;">cloud_upload</span>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn-batal" onclick="closeModalImport()">Tutup</button>
            <button class="btn-minta" data-loading onclick="importTransaksi()">Import</button>
        </div>
    </div>
</div>

<div id="modalImportResult" class="modal-overlay">
    <div class="modal-bg"></div>
    <div class="modal-box" style="max-width:520px;max-height:80vh;overflow-y:auto;">
        <div class="modal-icon">
            <span class="material-icons">assignment</span>
        </div>
        <h3>Hasil Import Transaksi & Iuran</h3>
        <div id="importResultContent" style="font-size:13px;line-height:1.6;text-align:left;"></div>
        <div class="modal-actions" style="margin-top:16px;">
            <button class="btn-batal" onclick="closeModalImportResult()">Tutup</button>
        </div>
    </div>
</div>

<div id="modalExport" class="modal-overlay">
    <div class="modal-bg"></div>
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-icon">
            <span class="material-icons">download</span>
        </div>
        <h3>Export Data Transaksi</h3>
        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;text-align:center;">
            Pilih filter untuk menentukan data transaksi yang ingin diekspor ke file Excel (.xlsx).
        </p>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Jenis Transaksi</label>
            <select id="exportTipe" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;appearance:auto;">
                <option value="">Semua Jenis Transaksi</option>
                <option value="pemasukan">Pemasukan</option>
                <option value="pengeluaran">Pengeluaran</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Kategori</label>
            <select id="exportKategori" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;appearance:auto;">
                <option value="">Semua Kategori</option>
            </select>
        </div>
        <div class="form-row" style="display:flex;gap:12px;margin-bottom:12px;">
            <div class="form-group" style="flex:1;">
                <label>Tanggal Mulai</label>
                <input type="date" id="exportTanggalMulai" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;">
            </div>
            <div class="form-group" style="flex:1;">
                <label>Tanggal Akhir</label>
                <input type="date" id="exportTanggalAkhir" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;">
            </div>
        </div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:18px;">Filter tanggal bersifat opsional. Jika tidak dipilih, semua data sesuai filter lainnya akan diekspor.</div>
        <div class="modal-actions">
            <button class="btn-batal" onclick="closeModalExport()">Tutup</button>
            <button class="btn-minta" onclick="exportTransaksi()" style="display:inline-flex;align-items:center;gap:6px;">
                <span class="material-icons" style="font-size:16px;">download</span>
                Export
            </button>
        </div>
    </div>
</div>

<div id="modalEdit" class="modal-overlay">
    <div class="modal-bg"></div>
    <div class="modal-box">
        <div class="modal-icon">
            <span class="material-icons">lock_outline</span>
        </div>
        <h3>Meminta akses edit ke ketua</h3>
        <p>Anda akan mengirimkan permintaan akses untuk mengedit data transaksi ini. Ketua akan menyetujui permintaan Anda.</p>
        <input type="hidden" id="modalTargetTable" value="">
        <input type="hidden" id="modalTargetId" value="">
        <div class="form-group" style="margin-bottom:12px;">
            <label>Kolom yang ingin diubah</label>
            <select id="modalFieldName" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;appearance:auto;">
                <option value="">Pilih kolom</option>
                <option value="nominal">Nominal</option>
                <option value="kategori">Kategori</option>
                <option value="keterangan">Keterangan</option>
            </select>
        </div>
        <div class="form-row" style="display:flex;gap:12px;margin-bottom:12px;">
            <div class="form-group" style="flex:1;">
                <label>Nilai Lama</label>
                <input type="text" id="modalOldValue" placeholder="Nilai sebelum diubah" readonly style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#f3f4f6;cursor:not-allowed;">
            </div>
            <div class="form-group" style="flex:1;">
                <label>Nilai Baru</label>
                <input type="text" id="modalNewValue" placeholder="Nilai setelah diubah" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;">
                <select id="modalNewKategori" style="display:none;width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;appearance:auto;">
                    <option value="">Pilih kategori</option>
                </select>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:18px;">
            <label>Alasan perubahan</label>
            <textarea id="modalAlasan" placeholder="Jelaskan alasan mengapa data ini perlu diubah..." style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;resize:vertical;min-height:80px;box-sizing:border-box;"></textarea>
        </div>
        <div class="modal-actions">
            <button class="btn-batal" onclick="closeModal()">Batal</button>
            <button class="btn-minta" data-loading onclick="requestAccess()">Kirim Permintaan</button>
        </div>
    </div>
</div>
@endpush
