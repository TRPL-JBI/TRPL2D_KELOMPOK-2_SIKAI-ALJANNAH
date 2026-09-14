@extends('layouts.dashboard')

@section('title', 'Kelola Anggota')

@php
    $menuItems = [
        ['label' => 'Beranda', 'url' => route('sekretaris.dashboard'), 'active' => 'sekretaris'],
        ['label' => 'Kelola Anggota', 'url' => route('sekretaris.anggota'), 'active' => 'sekretaris/anggota*'],
        ['label' => 'Log Aktivitas', 'url' => route('sekretaris.log'), 'active' => 'sekretaris/log*'],
    ];
@endphp

@push('styles')
<style>
    body { background-color: #dcfce7; }
    .sidebar { background-color: #0d4f46 !important; }
    .content-wrapper { padding: 28px 32px; }
    .sk-top-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
    .sk-page-title { font-size: 24px; font-weight: 700; color: #1f5c52; margin: 0; }
    .sk-card { background: #fff; border-radius: 16px; border: 1px solid #1f5c52; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; }
    .sk-card-header { background: #1f5c52; padding: 14px 22px; display: flex; justify-content: space-between; align-items: center; }
    .sk-card-header h2 { font-size: 15px; font-weight: 600; color: #fff; margin: 0; }
    .sk-card-body { padding: 0; }

    /* Filter bar */
    .sk-filter-bar { display: flex; align-items: center; gap: 10px; padding: 14px 22px; border-bottom: 1px solid #e5e7eb; flex-wrap: wrap; }
    .sk-filter-tabs { display: flex; gap: 4px; background: #f3f4f6; padding: 4px; border-radius: 10px; border: 1px solid #e5e7eb; }
    .sk-filter-tab { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif; border: none; cursor: pointer; transition: all 0.15s; background: transparent; color: #6b7280; }
    .sk-filter-tab.active { background: #fff; color: #1f5c52; border: 1px solid #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .sk-filter-tab:hover:not(.active) { color: #374151; }
    .sk-search-box { display: flex; align-items: center; gap: 8px; border: 1px solid #d1d5db; border-radius: 24px; padding: 7px 14px; background: #fff; flex: 1; max-width: 300px; transition: border-color 0.2s; }
    .sk-search-box:focus-within { border-color: #1f5c52; }
    .sk-search-box .material-icons { font-size: 18px; color: #9ca3af; }
    .sk-search-box input { border: none; outline: none; font-size: 13px; font-family: 'Poppins', sans-serif; color: #374151; width: 100%; background: transparent; }
    .sk-search-box input::placeholder { color: #9ca3af; }
    .sk-btn { display: inline-flex; align-items: center; gap: 5px; border-radius: 8px; padding: 7px 14px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.15s; border: none; text-decoration: none; }
    .sk-btn .material-icons { font-size: 16px; }
    .sk-btn-primary { background: #1f5c52; color: #fff; }
    .sk-btn-primary:hover { background: #0d4f46; }
    .sk-btn-outline { background: #fff; color: #1f5c52; border: 1.5px solid #1f5c52; }
    .sk-btn-outline:hover { background: #f0fdf4; }
    .sk-btn-success { background: #059669; color: #fff; }
    .sk-btn-success:hover { background: #047857; }
    .sk-btn-danger { background: #dc2626; color: #fff; }
    .sk-btn-danger:hover { background: #b91c1c; }
    .sk-btn-ghost { background: transparent; color: #6b7280; border: 1px solid #d1d5db; }
    .sk-btn-ghost:hover { background: #f9fafb; color: #374151; }

    /* Table */
    .sk-table-wrap { overflow-x: auto; }
    .sk-table { width: 100%; border-collapse: collapse; }
    .sk-table thead th { padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 2px solid #e5e7eb; background: #f9fafb; white-space: nowrap; }
    .sk-table tbody td { padding: 12px 16px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .sk-table tbody tr:last-child td { border-bottom: none; }
    .sk-table tbody tr:hover { background: #f9fafb; }
    .sk-table tbody tr { transition: background 0.15s; }

    /* Badges */
    .sk-badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .sk-badge-active { background: #d1fae5; color: #065f46; }
    .sk-badge-inactive { background: #fee2e2; color: #991b1b; }

    /* Action buttons in table */
    .sk-actions { display: flex; gap: 6px; justify-content: center; }
    .sk-action-btn { width: 30px; height: 30px; border-radius: 8px; border: none; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; text-decoration: none; }
    .sk-action-btn .material-icons { font-size: 16px; }
    .sk-action-btn-blue { background: #dbeafe; color: #2563eb; }
    .sk-action-btn-blue:hover { background: #bfdbfe; }
    .sk-action-btn-yellow { background: #fef3c7; color: #d97706; }
    .sk-action-btn-yellow:hover { background: #fde68a; }
    .sk-action-btn-red { background: #fee2e2; color: #dc2626; }
    .sk-action-btn-red:hover { background: #fecaca; }
    .sk-action-btn-green { background: #d1fae5; color: #059669; }
    .sk-action-btn-green:hover { background: #a7f3d0; }

    /* Pagination */
    .sk-pagination { display: flex; justify-content: center; align-items: center; gap: 6px; padding: 16px 22px; border-top: 1px solid #f3f4f6; }
    .sk-pagination a, .sk-pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; border-radius: 8px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; }
    .sk-pagination a { background: #fff; color: #374151; border: 1px solid #d1d5db; }
    .sk-pagination a:hover { background: #f0fdf4; border-color: #1f5c52; color: #1f5c52; }
    .sk-pagination span.current { background: #1f5c52; color: #fff; border: 1px solid #1f5c52; }
    .sk-pagination span.disabled { background: #f9fafb; color: #d1d5db; border: 1px solid #e5e7eb; cursor: not-allowed; }

    /* Empty state */
    .sk-empty { text-align: center; padding: 60px 20px; }
    .sk-empty .material-icons { font-size: 56px; color: #d1d5db; }
    .sk-empty p { font-size: 14px; color: #9ca3af; margin-top: 12px; }

    /* Modal */
    .sk-modal-bg { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
    .sk-modal { background: #fff; width: 480px; max-width: 90%; padding: 28px; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-height: 85vh; overflow-y: auto; }
    .sk-modal-lg { width: 560px; }
    .sk-modal h3 { margin: 0 0 6px; font-size: 17px; font-weight: 700; color: #1f2937; }
    .sk-modal p.desc { font-size: 13px; color: #6b7280; margin: 0 0 18px; line-height: 1.5; }
    .sk-modal-field { margin-bottom: 16px; }
    .sk-modal-field label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .sk-modal-field input, .sk-modal-field select { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; outline: none; color: #1f2937; transition: border-color 0.15s; box-sizing: border-box; }
    .sk-modal-field input:focus, .sk-modal-field select:focus { border-color: #1f5c52; }
    .sk-modal-info { padding: 12px; background: #f9fafb; border-radius: 8px; margin-bottom: 18px; }
    .sk-modal-info p { margin: 0 0 4px; font-size: 13px; color: #6b7280; }
    .sk-modal-info p:last-child { margin-bottom: 0; }
    .sk-modal-info strong { color: #111827; }
    .sk-modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }

    /* File upload */
    .sk-file-upload { width: 100%; padding: 14px 16px; border: 2px dashed #d1d5db; border-radius: 10px; text-align: center; cursor: pointer; transition: border-color 0.15s; }
    .sk-file-upload:hover { border-color: #1f5c52; }
    .sk-file-upload .material-icons { font-size: 20px; color: #9ca3af; vertical-align: middle; margin-left: 6px; }
    .sk-file-upload span.placeholder { font-size: 13px; color: #9ca3af; }

    /* Keluarga row */
    .sk-keluarga-row { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
    .sk-keluarga-row input[type="text"] { flex: 1; min-width: 100px; }
    .sk-keluarga-row select { width: auto; min-width: 90px; }
    .sk-keluarga-row input[type="date"] { width: auto; }
    .sk-keluarga-remove { background: #ef4444; border: none; width: 26px; height: 26px; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; }
    .sk-keluarga-remove .material-icons { font-size: 14px; color: #fff; }

    @media (max-width: 768px) {
        .sk-top-row { flex-direction: column; align-items: stretch; }
        .sk-filter-bar { flex-direction: column; align-items: stretch; }
        .sk-search-box { max-width: 100%; }
        .content-wrapper { padding: 20px 16px; }
    }
</style>
@endpush

@section('content')
<div class="sk-top-row">
    <h1 class="sk-page-title">Kelola Anggota</h1>
    <div style="display:flex;gap:8px;">
        <button class="sk-btn sk-btn-outline" onclick="openModalImport()">
            <span class="material-icons">upload_file</span> Import
        </button>
        <a href="{{ route('sekretaris.anggota.template') }}" class="sk-btn sk-btn-outline" style="text-decoration:none;">
            <span class="material-icons">description</span> Template
        </a>
    </div>
</div>

<div class="sk-card">
    <div class="sk-card-header">
        <h2>Daftar Anggota</h2>
        <span style="font-size:12px;color:#d1fae5;">{{ $totalAnggota }} total</span>
    </div>
    <div class="sk-card-body">
        <!-- Filter & Search -->
        <form method="GET" action="{{ route('sekretaris.anggota') }}" id="filterForm">
            <div class="sk-filter-bar">
                <div class="sk-filter-tabs">
                    <button type="submit" name="status" value="all" class="sk-filter-tab {{ $statusFilter === 'all' ? 'active' : '' }}">Semua ({{ $totalAnggota }})</button>
                    <button type="submit" name="status" value="aktif" class="sk-filter-tab {{ $statusFilter === 'aktif' ? 'active' : '' }}">Aktif ({{ $anggotaAktif }})</button>
                    <button type="submit" name="status" value="non_aktif" class="sk-filter-tab {{ $statusFilter === 'non_aktif' ? 'active' : '' }}">Non-Aktif ({{ $anggotaNonAktif }})</button>
                    <input type="hidden" name="search" value="{{ $search }}">
                </div>
                <div class="sk-search-box">
                    <span class="material-icons">search</span>
                    <input type="text" name="search" placeholder="Cari nama, nomor, telepon..." value="{{ $search }}">
                </div>
                <input type="hidden" name="status" value="{{ $statusFilter }}">
                <button type="submit" class="sk-btn sk-btn-primary"><span class="material-icons">search</span> Cari</button>
            </div>
        </form>

        <!-- Table -->
        <div class="sk-table-wrap">
            <table class="sk-table">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Nama Anggota</th>
                        <th>Keluarga</th>
                        <th>Telepon</th>
                        <th>Tanggal Daftar</th>
                        <th>Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($anggota as $item)
                        @php
                            $keluarga = optional($item->calonAnggota)->keluargaAnggota ?? collect();
                            $keluargaJson = $keluarga->map(fn($k) => [
                                'nama' => $k->nama,
                                'jenis_kelamin' => $k->jenis_kelamin,
                                'tanggal_lahir' => $k->tanggal_lahir,
                            ])->values()->toJson();
                            $isActive = strtolower($item->status) === 'aktif';
                        @endphp
                        <tr>
                            <td style="font-family:monospace;font-weight:600;color:#1f5c52;">{{ $item->nomor_anggota }}</td>
                            <td style="font-weight:500;">{{ $item->nama }}</td>
                            <td>
                                @forelse($keluarga as $k)
                                    <span style="display:inline-block;">{{ $k->nama }}</span>{{ $loop->last ? '' : ', ' }}
                                @empty
                                    <span style="color:#d1d5db;">—</span>
                                @endforelse
                            </td>
                            <td>{{ $item->telepon }}</td>
                            <td style="white-space:nowrap;">{{ $item->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="sk-badge {{ $isActive ? 'sk-badge-active' : 'sk-badge-inactive' }}">
                                    {{ $isActive ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td>
                                <div class="sk-actions">
                                    <a href="{{ route('sekretaris.anggota.formulir', $item->id) }}" class="sk-action-btn sk-action-btn-blue" title="Lihat Formulir">
                                        <span class="material-icons">description</span>
                                    </a>
                                    <a href="javascript:void(0)" data-id="{{ $item->id }}" data-nama="{{ $item->nama }}" data-telepon="{{ $item->telepon }}" data-keluarga="{{ $keluargaJson }}" onclick="openEditModal(this)" class="sk-action-btn sk-action-btn-yellow" title="Edit">
                                        <span class="material-icons">edit</span>
                                    </a>
                                    @if($isActive)
                                        <a href="javascript:void(0)" data-id="{{ $item->id }}" data-nama="{{ $item->nama }}" data-no="{{ $item->nomor_anggota }}" onclick="openNonaktifModal(this)" class="sk-action-btn sk-action-btn-red" title="Nonaktifkan">
                                            <span class="material-icons">person_off</span>
                                        </a>
                                    @else
                                        <a href="javascript:void(0)" data-id="{{ $item->id }}" data-nama="{{ $item->nama }}" data-no="{{ $item->nomor_anggota }}" onclick="openAktifkanModal(this)" class="sk-action-btn sk-action-btn-green" title="Aktifkan">
                                            <span class="material-icons">person_add</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="sk-empty">
                                    <span class="material-icons">group</span>
                                    <p>Belum ada anggota terdaftar</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($anggota->hasPages())
            <div class="sk-pagination">{{ $anggota->links() }}</div>
        @endif
    </div>
</div>

<!-- Modal Import -->
<div id="modalImport" class="sk-modal-bg">
    <div class="sk-modal">
        <h3>Import Data Anggota</h3>
        <p class="desc">Upload file Excel (.xlsx) untuk mengimport data anggota secara massal.<br><a href="{{ route('sekretaris.anggota.template') }}" style="color:#1f5c52;font-weight:600;">Download template</a> terlebih dahulu jika belum punya.</p>
        <div class="sk-modal-field">
            <label>Pilih File Excel</label>
            <div class="sk-file-upload" onclick="this.querySelector('input').click();">
                <input type="file" id="importFile" accept=".xlsx,.xls,.ods" required onchange="document.getElementById('importFilePlaceholder').textContent = this.files[0].name;" style="display:none;">
                <span id="importFilePlaceholder" class="placeholder">Klik untuk memilih file...</span>
                <span class="material-icons">cloud_upload</span>
            </div>
        </div>
        <div class="sk-modal-footer">
            <button onclick="closeModalImport()" class="sk-btn sk-btn-ghost">Tutup</button>
            <button id="btnImport" onclick="importAnggota()" class="sk-btn sk-btn-primary">Import</button>
        </div>
    </div>
</div>

<!-- Modal Hasil Import -->
<div id="modalImportResult" class="sk-modal-bg">
    <div class="sk-modal sk-modal-lg">
        <h3>Hasil Import Anggota</h3>
        <div id="importResultContent" style="font-size:13px;line-height:1.6;"></div>
        <div class="sk-modal-footer">
            <button onclick="closeModalImportResult()" class="sk-btn sk-btn-ghost">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Nonaktif -->
<div id="modalNonaktif" class="sk-modal-bg">
    <div class="sk-modal">
        <h3>Konfirmasi Nonaktifkan</h3>
        <p class="desc">Apakah Anda yakin ingin menonaktifkan anggota berikut?</p>
        <div class="sk-modal-info">
            <p>Nomor: <strong id="nonaktifId"></strong></p>
            <p>Nama: <strong id="nonaktifNama"></strong></p>
        </div>
        <div class="sk-modal-footer">
            <button onclick="closeNonaktifModal()" class="sk-btn sk-btn-ghost">Batal</button>
            <button id="btnKonfirmasiNonaktif" onclick="konfirmasiNonaktif()" class="sk-btn sk-btn-danger">Ya, Nonaktifkan</button>
        </div>
    </div>
</div>

<!-- Modal Aktifkan -->
<div id="modalAktifkan" class="sk-modal-bg">
    <div class="sk-modal">
        <h3>Konfirmasi Aktifkan</h3>
        <p class="desc">Apakah Anda yakin ingin mengaktifkan kembali anggota berikut?</p>
        <div class="sk-modal-info">
            <p>Nomor: <strong id="aktifkanId"></strong></p>
            <p>Nama: <strong id="aktifkanNama"></strong></p>
        </div>
        <div class="sk-modal-footer">
            <button onclick="closeAktifkanModal()" class="sk-btn sk-btn-ghost">Batal</button>
            <button id="btnKonfirmasiAktifkan" onclick="konfirmasiAktifkan()" class="sk-btn sk-btn-success">Ya, Aktifkan</button>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="sk-modal-bg">
    <div class="sk-modal sk-modal-lg">
        <h3>Edit Data Anggota</h3>
        <div class="sk-modal-field">
            <label>Nama Anggota</label>
            <input type="text" id="editNama">
        </div>
        <div class="sk-modal-field">
            <label>Nama yang Ditanggung</label>
            <div id="keluargaContainer" style="display:flex;flex-direction:column;gap:8px;"></div>
            <button type="button" onclick="tambahKeluarga()" class="sk-btn sk-btn-primary" style="margin-top:8px;padding:5px 12px;font-size:11px;">
                <span class="material-icons" style="font-size:14px;">add</span> Tambah
            </button>
        </div>
        <div class="sk-modal-field">
            <label>Telepon</label>
            <input type="text" id="editTelepon">
        </div>
        <div class="sk-modal-footer">
            <button onclick="closeEditModal()" class="sk-btn sk-btn-ghost">Batal</button>
            <button id="btnSimpanEdit" onclick="simpanEdit()" class="sk-btn sk-btn-primary">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    /* ── Nonaktif Modal ── */
    var _nonaktifId = null;
    function openNonaktifModal(el) {
        _nonaktifId = el.getAttribute('data-id');
        document.getElementById('nonaktifId').textContent = el.getAttribute('data-no');
        document.getElementById('nonaktifNama').textContent = el.getAttribute('data-nama');
        document.getElementById('modalNonaktif').style.display = 'flex';
    }
    function closeNonaktifModal() { document.getElementById('modalNonaktif').style.display = 'none'; _nonaktifId = null; }
    function konfirmasiNonaktif() {
        if (!_nonaktifId) return;
        var btn = document.getElementById('btnKonfirmasiNonaktif');
        btn.disabled = true; btn.innerHTML = '<span class="btn-spinner"></span> Memproses...';
        fetchAPI('/sekretaris/anggota/' + _nonaktifId + '/nonaktif', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' }
        })
        .then(function(res) {
            if (!res.success) { alert(res.message); btn.disabled = false; btn.innerHTML = 'Ya, Nonaktifkan'; return; }
            closeNonaktifModal(); alert(res.message); location.reload();
        })
        .catch(function(e) { alert('Gagal: ' + e.message); btn.disabled = false; btn.innerHTML = 'Ya, Nonaktifkan'; });
    }

    /* ── Aktifkan Modal ── */
    var _aktifkanId = null;
    function openAktifkanModal(el) {
        _aktifkanId = el.getAttribute('data-id');
        document.getElementById('aktifkanId').textContent = el.getAttribute('data-no');
        document.getElementById('aktifkanNama').textContent = el.getAttribute('data-nama');
        document.getElementById('modalAktifkan').style.display = 'flex';
    }
    function closeAktifkanModal() { document.getElementById('modalAktifkan').style.display = 'none'; _aktifkanId = null; }
    function konfirmasiAktifkan() {
        if (!_aktifkanId) return;
        var btn = document.getElementById('btnKonfirmasiAktifkan');
        btn.disabled = true; btn.innerHTML = '<span class="btn-spinner"></span> Memproses...';
        fetchAPI('/sekretaris/anggota/' + _aktifkanId + '/aktifkan', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' }
        })
        .then(function(res) {
            if (!res.success) { alert(res.message); btn.disabled = false; btn.innerHTML = 'Ya, Aktifkan'; return; }
            closeAktifkanModal(); alert(res.message); location.reload();
        })
        .catch(function(e) { alert('Gagal: ' + e.message); btn.disabled = false; btn.innerHTML = 'Ya, Aktifkan'; });
    }

    /* ── Edit Modal ── */
    var _editId = null;
    function openEditModal(el) {
        _editId = el.getAttribute('data-id');
        document.getElementById('editNama').value = el.getAttribute('data-nama');
        document.getElementById('editTelepon').value = el.getAttribute('data-telepon');
        var keluarga = [];
        try { keluarga = JSON.parse(el.getAttribute('data-keluarga') || '[]'); } catch(e) {}
        renderKeluarga(keluarga);
        document.getElementById('modalEdit').style.display = 'flex';
    }
    function closeEditModal() { document.getElementById('modalEdit').style.display = 'none'; _editId = null; }

    function renderKeluarga(list) {
        var c = document.getElementById('keluargaContainer'); c.innerHTML = '';
        list.forEach(function(item) {
            addKeluargaRow(item.nama || '', item.jenis_kelamin || 'laki-laki', item.tanggal_lahir || '');
        });
    }
    function tambahKeluarga() { addKeluargaRow('', 'laki-laki', ''); document.querySelector('#keluargaContainer input[type="text"]').focus(); }
    function addKeluargaRow(nama, jk, tgl) {
        var c = document.getElementById('keluargaContainer');
        var row = document.createElement('div'); row.className = 'sk-keluarga-row';
        row.innerHTML =
            '<input type="text" value="' + nama.replace(/"/g, '&quot;') + '" placeholder="Nama...">' +
            '<select><option value="laki-laki"' + (jk === 'laki-laki' ? ' selected' : '') + '>Laki-laki</option><option value="perempuan"' + (jk === 'perempuan' ? ' selected' : '') + '>Perempuan</option></select>' +
            '<input type="date" value="' + tgl + '">' +
            '<button type="button" onclick="this.parentElement.remove()" class="sk-keluarga-remove"><span class="material-icons">close</span></button>';
        c.appendChild(row);
    }
    function getKeluargaData() {
        var items = [];
        document.querySelectorAll('#keluargaContainer .sk-keluarga-row').forEach(function(row) {
            var nama = row.querySelector('input[type="text"]').value.trim();
            if (nama) items.push({ nama: nama, jenis_kelamin: row.querySelector('select').value, tanggal_lahir: row.querySelector('input[type="date"]').value });
        });
        return items;
    }
    function simpanEdit() {
        if (!_editId) return;
        var btn = document.getElementById('btnSimpanEdit');
        btn.disabled = true; btn.innerHTML = '<span class="btn-spinner"></span> Menyimpan...';
        fetchAPI('/sekretaris/anggota/' + _editId + '/edit', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
            body: JSON.stringify({ nama: document.getElementById('editNama').value, telepon: document.getElementById('editTelepon').value, anggota_keluarga: getKeluargaData() })
        })
        .then(function(res) {
            if (!res.success) { alert(res.message); btn.disabled = false; btn.innerHTML = 'Simpan'; return; }
            closeEditModal(); alert(res.message); location.reload();
        })
        .catch(function(e) { alert('Gagal: ' + e.message); btn.disabled = false; btn.innerHTML = 'Simpan'; });
    }

    /* ── Import ── */
    function openModalImport() {
        document.getElementById('modalImportResult').style.display = 'none';
        document.getElementById('modalImport').style.display = 'flex';
        document.getElementById('importFile').value = '';
        document.getElementById('importFilePlaceholder').textContent = 'Klik untuk memilih file...';
        document.getElementById('btnImport').disabled = false;
        document.getElementById('btnImport').innerHTML = 'Import';
    }
    function closeModalImport() { document.getElementById('modalImport').style.display = 'none'; }
    function closeModalImportResult() { document.getElementById('modalImportResult').style.display = 'none'; location.reload(); }

    function showImportResult(res) {
        var content = document.getElementById('importResultContent');
        var html = '';
        var isOk = res.berhasil > 0 && res.gagal === 0;
        var isPartial = res.berhasil > 0 && res.gagal > 0;
        var isAllFailed = res.berhasil === 0 && res.gagal > 0;
        var badgeColor = isOk ? '#059669' : (isPartial ? '#d97706' : '#dc2626');
        var badgeIcon = isOk ? 'check_circle' : (isPartial ? 'warning' : 'cancel');

        html += '<div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;background:' + badgeColor + '11;border:1px solid ' + badgeColor + '33;margin-bottom:12px;">';
        html += '<span class="material-icons" style="color:' + badgeColor + ';font-size:22px;">' + badgeIcon + '</span>';
        html += '<div><div style="font-weight:700;color:' + badgeColor + ';font-size:14px;">' + res.message + '</div>';
        if (res.berhasil > 0) html += '<div style="font-size:12px;color:#059669;margin-top:2px;">' + res.berhasil + ' data berhasil diimport</div>';
        if (res.gagal > 0) html += '<div style="font-size:12px;color:#dc2626;margin-top:2px;">' + res.gagal + ' data gagal / dibatalkan</div>';
        html += '</div></div>';

        if (res.errors && res.errors.length > 0) {
            html += '<div style="margin-top:8px;"><div style="font-weight:600;font-size:12px;color:#6b7280;margin-bottom:6px;">Detail Error (' + res.errors.length + '):</div>';
            html += '<div style="max-height:240px;overflow-y:auto;border:1px solid #f3f4f6;border-radius:8px;background:#fef2f2;">';
            html += '<ul style="margin:0;padding:12px 12px 12px 32px;font-size:12px;color:#991b1b;line-height:1.8;">';
            for (var i = 0; i < res.errors.length; i++) html += '<li style="border-bottom:1px solid #fecaca;padding-bottom:4px;">' + res.errors[i] + '</li>';
            html += '</ul></div>';
            if (isAllFailed) {
                html += '<div style="margin-top:10px;padding:10px 14px;border-radius:8px;background:#fef3c7;border:1px solid #fbbf24;font-size:12px;color:#92400e;">';
                html += '<span class="material-icons" style="font-size:16px;vertical-align:middle;margin-right:4px;">info</span>';
                html += '<strong>Semua data dibatalkan.</strong> Perbaiki semua error di atas, lalu import ulang.';
                html += '</div>';
            }
            html += '</div>';
        }
        content.innerHTML = html;
        document.getElementById('modalImportResult').style.display = 'flex';
    }

    function importAnggota() {
        var btn = document.getElementById('btnImport');
        var fileInput = document.getElementById('importFile');
        if (!fileInput.files[0]) { alert('Harap pilih file Excel terlebih dahulu.'); return; }
        btn.disabled = true; btn.innerHTML = '<span class="btn-spinner"></span> Mengimport...';
        var formData = new FormData(); formData.append('file_import', fileInput.files[0]);
        fetchAPI('{{ route('sekretaris.anggota.import') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(function(res) { closeModalImport(); showImportResult(res); btn.disabled = false; btn.innerHTML = 'Import'; })
        .catch(function(e) { btn.disabled = false; btn.innerHTML = 'Import'; document.getElementById('importResultContent').innerHTML = '<div style="color:#dc2626;font-weight:600;">Gagal: ' + e.message + '</div>'; document.getElementById('modalImportResult').style.display = 'flex'; });
    }

    /* ── Close modals on backdrop click / ESC ── */
    document.querySelectorAll('.sk-modal-bg').forEach(function(el) {
        el.addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') document.querySelectorAll('.sk-modal-bg').forEach(function(el) { el.style.display = 'none'; });
    });
</script>
@endpush
