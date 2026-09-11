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

<div class="table-card" style="margin-top:24px;">
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
                        <button class="btn-edit-disabled" onclick="openModal(this)">
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
    function openModalTransaksi() {
        enableBtn();
        document.getElementById('modalTransaksi').classList.add('active');
        document.getElementById('formTipe').value = 'pemasukan';
        document.getElementById('formTanggal').value = '';
        document.getElementById('formKategori').innerHTML = '<option value="">Pilih kategori</option>';
        document.getElementById('formNominal').value = '';
        document.getElementById('formKeterangan').value = '';
        document.getElementById('formWaNama').value = '';
        document.getElementById('formWaPenerima').value = '';
        document.querySelector('#modalTransaksi .upload-placeholder').textContent = 'Upload here...';
        toggleKategori('pemasukan');
    }

    function closeModalTransaksi() {
        document.getElementById('modalTransaksi').classList.remove('active');
    }

    function toggleKategori(tipe) {
        var sel = document.getElementById('formKategori');
        var opts = tipe === 'pemasukan' ? (window._kategoriPemasukan || []) : (window._kategoriPengeluaran || []);
        sel.innerHTML = '<option value="">Pilih kategori</option>';
        for (var i = 0; i < opts.length; i++) {
            sel.innerHTML += '<option value="' + opts[i].id + '">' + opts[i].nama + '</option>';
        }
    }

    document.addEventListener('change', function(e) {
        if (e.target.id === 'formTipe') toggleKategori(e.target.value);
    });

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
        var tipe = document.getElementById('formTipe').value;
        var tanggal = document.getElementById('formTanggal').value;
        var kategori = document.getElementById('formKategori').value;
        var nominal = document.getElementById('formNominal').value;
        var keterangan = document.getElementById('formKeterangan').value;
        var file = document.querySelector('#modalTransaksi .file-upload input[type=file]');

        if (!tanggal || !kategori || !nominal) { enableBtn(btn); alert('Harap isi tanggal, kategori, dan nominal.'); return; }
        if (!file || !file.files[0]) { enableBtn(btn); alert('Harap upload bukti transaksi.'); return; }

        var formData = new FormData();
        formData.append('tipe', tipe);
        formData.append('tanggal', tanggal);
        formData.append('kategori_id', kategori);
        formData.append('jumlah', nominal.replace(/[^0-9]/g, ''));
        formData.append('keterangan', keterangan);
        formData.append('wa_nama', document.getElementById('formWaNama').value.trim());
        formData.append('wa_penerima', document.getElementById('formWaPenerima').value.trim());
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
            row.outerHTML = '<tr data-target-table="' + (d.tipe === 'Pemasukan' ? 'pemasukan' : 'pengeluaran') + '" data-jumlah="' + d.jumlah + '" data-kategori="' + (d.kategori || '') + '" data-keterangan="' + (d.keterangan || '') + '" data-tanggal="' + d.tanggal + '" data-tipe="' + d.tipe + '" data-file-url="' + (d.file_url || '') + '"><td data-target-id="' + d.id + '">' + d.tanggal + '</td><td><span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;' + (d.tipe === 'Pemasukan' ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;') + '">' + d.tipe + '</span></td><td class="nominal-value">Rp ' + Number(d.jumlah).toLocaleString('id-ID') + '</td><td class="name-cell">' + d.kategori + '</td><td>' + (d.keterangan || '-') + '</td><td><button class="btn-icon btn-info" onclick="openInfoModal(this)" title="Detail Transaksi"><span class="material-icons">info</span></button><button class="btn-edit-disabled" onclick="openModal(this)"><span class="material-icons">lock</span> Edit</button></td></tr>';
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
        if (res.errors && res.errors.length > 0) {
            var list = '<ul style="margin:8px 0 0 16px;font-size:12px;color:#dc2626;text-align:left;">';
            for (var i = 0; i < Math.min(res.errors.length, 10); i++) {
                list += '<li>' + res.errors[i] + '</li>';
            }
            if (res.errors.length > 10) {
                list += '<li>...dan ' + (res.errors.length - 10) + ' error lainnya</li>';
            }
            list += '</ul>';
            content.innerHTML = '<div style="color:' + (res.berhasil > 0 ? '#059669' : '#dc2626') + ';font-weight:600;margin-bottom:8px;">' + res.message + '</div>' + list;
        } else {
            content.innerHTML = '<div style="color:#059669;font-weight:600;">' + res.message + '</div>';
        }
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
<div id="modalTransaksi" class="modal-overlay">
    <div class="modal-bg"></div>
    <div class="modal-box" style="max-width:520px;">
        <div class="modal-icon">
            <span class="material-icons">receipt_long</span>
        </div>
        <h3>Tambah Transaksi Baru</h3>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Tipe Transaksi</label>
            <select id="formTipe" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;appearance:auto;">
                <option value="pemasukan">Pemasukan</option>
                <option value="pengeluaran">Pengeluaran</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Tanggal</label>
            <input type="date" id="formTanggal" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;">
        </div>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Kategori</label>
            <div class="select-wrap">
                <select id="formKategori" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;background:#fff;appearance:auto;">
                    <option value="">Pilih kategori</option>
                </select>
                <span class="material-icons dropdown-icon">expand_more</span>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Nominal</label>
            <div class="input-prefix">
                <span class="prefix">Rp</span>
                <input type="text" id="formNominal" placeholder="" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;">
                <span class="suffix">.00</span>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Keterangan</label>
            <textarea id="formKeterangan" placeholder="Tulis keterangan transaksi..." style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;resize:vertical;min-height:80px;box-sizing:border-box;"></textarea>
        </div>
        <div class="form-group" style="margin-bottom:12px;">
            <label>Nama Penerima Kwitansi (opsional)</label>
            <input type="text" id="formWaNama" placeholder="Contoh: Budi Santoso" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;">
            <div style="font-size:11px;color:#9ca3af;margin-top:4px;">Isi bersama No. WhatsApp untuk mengirim kwitansi pembayaran.</div>
        </div>
        <div class="form-group" style="margin-bottom:18px;">
            <label>No. WhatsApp Penerima Kwitansi (opsional)</label>
            <input type="text" id="formWaPenerima" placeholder="Contoh: 6281234567890 (tanpa 0 di depan)" style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;box-sizing:border-box;">
        </div>
        <div class="form-group" style="margin-bottom:18px;">
            <label>Upload Bukti Transaksi</label>
            <div class="file-upload" onclick="this.querySelector('input').click();" style="width:100%;padding:12px 16px;border:1px dashed #d1d5db;border-radius:8px;text-align:center;cursor:pointer;box-sizing:border-box;">
                <input type="file" required onchange="this.parentElement.querySelector('.upload-placeholder').textContent = this.files[0].name;" style="display:none;">
                <span class="upload-placeholder" style="font-size:13px;color:#9ca3af;">Upload here...</span>
                <div class="upload-icon">
                    <span class="material-icons" style="font-size:18px;vertical-align:middle;color:#6b7280;">cloud_upload</span>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn-batal" onclick="closeModalTransaksi()">Batal</button>
            <button class="btn-minta" data-loading onclick="simpanTransaksi()">Simpan</button>
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
