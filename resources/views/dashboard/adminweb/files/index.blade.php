@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages*'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'WhatsApp', 'url' => '/adminweb/whatsapp', 'active' => 'adminweb/whatsapp*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@section('title', 'Files & Images')

@section('content')
<style>
    .page-files .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
    }
    .page-files .page-header h1 {
        font-size: 24px; font-weight: 700; color: var(--primary-500); margin: 0;
    }
    .page-files .page-header p {
        font-size: 13px; color: var(--secondary-700); margin: 2px 0 0;
    }
    .page-files .btn-upload {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--primary-500); color: #fff;
        font-size: 13px; font-weight: 700; padding: 10px 24px;
        border-radius: 10px; border: none; cursor: pointer;
        transition: all .2s; font-family: inherit;
    }
    .page-files .btn-upload:hover { opacity: .9; box-shadow: 0 4px 12px rgba(22,66,60,0.3); }
    .page-files .btn-upload:active { transform: scale(.97); }
    .page-files .btn-upload .material-icons { font-size: 18px; }

    .page-files .file-count {
        font-size: 12px; color: var(--secondary-700);
        background: var(--primary-100); padding: 6px 16px;
        border-radius: 100px; white-space: nowrap;
    }
    .page-files .file-count strong { color: var(--primary-500); }

    .page-files .toolbar {
        display: flex; align-items: center; gap: 12px;
        margin-bottom: 20px; flex-wrap: wrap;
    }
    .page-files .search-box {
        flex: 1; min-width: 200px; position: relative;
    }
    .page-files .search-box .material-icons {
        position: absolute; left: 14px; top: 50%;
        transform: translateY(-50%); font-size: 18px;
        color: var(--secondary-600); pointer-events: none;
    }
    .page-files .search-box input {
        width: 100%; padding: 10px 14px 10px 42px;
        background: #fff; border: 2px solid var(--primary-200);
        border-radius: 12px; font: 13px 'Inter', sans-serif;
        color: var(--secondary-900); outline: none;
        transition: border-color .2s; box-sizing: border-box;
    }
    .page-files .search-box input::placeholder { color: var(--secondary-600); }
    .page-files .search-box input:focus { border-color: var(--primary-500); }

    .page-files .filter-tabs {
        display: flex; background: #fff;
        border: 2px solid var(--primary-200); border-radius: 12px;
        overflow: hidden; flex-shrink: 0;
    }
    .page-files .filter-tab {
        padding: 9px 18px; font-size: 12px; font-weight: 600;
        color: var(--secondary-700); cursor: pointer; transition: all .2s;
        border: none; background: none; font-family: inherit;
        white-space: nowrap;
    }
    .page-files .filter-tab:hover { color: var(--primary-500); }
    .page-files .filter-tab.active {
        color: #fff; background: var(--primary-500);
    }
    .page-files .filter-tab + .filter-tab { border-left: 1px solid var(--primary-200); }

    .page-files .view-toggle {
        display: flex; background: #fff;
        border: 2px solid var(--primary-200); border-radius: 12px;
        overflow: hidden; flex-shrink: 0;
    }
    .page-files .view-btn {
        padding: 9px 14px; color: var(--secondary-700);
        cursor: pointer; transition: all .2s; border: none;
        background: none; font-size: 18px; display: flex;
        align-items: center; justify-content: center;
    }
    .page-files .view-btn:hover { color: var(--primary-500); }
    .page-files .view-btn.active { color: #fff; background: var(--primary-500); }

    .page-files .sort-select {
        padding: 9px 36px 9px 16px; background: #fff;
        border: 2px solid var(--primary-200); border-radius: 12px;
        color: var(--secondary-900); font-size: 12px; font-weight: 600;
        font-family: inherit; outline: none; cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 12px center;
        flex-shrink: 0;
    }
    .page-files .sort-select:focus { border-color: var(--primary-500); }

    .page-files .table-card {
        background: #fff; border-radius: 20px;
        border: 2px solid var(--primary-500);
        box-shadow: 0 4px 16px rgba(0,0,0,.05); overflow: hidden;
        position: relative;
    }

    .page-files .files-grid {
        display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 16px; padding: 20px;
    }
    .page-files .files-grid.list-view {
        grid-template-columns: 1fr; gap: 8px;
    }

    .page-files .file-card {
        background: #fff; border: 1.5px solid var(--primary-100);
        border-radius: 14px; overflow: hidden; cursor: pointer;
        transition: all .2s; position: relative;
    }
    .page-files .file-card:hover {
        border-color: var(--primary-300);
        box-shadow: 0 4px 16px rgba(22,66,60,0.08);
    }
    .page-files .file-card.selected {
        border-color: var(--primary-500);
        box-shadow: 0 0 0 2px rgba(22,66,60,0.15);
    }

    .page-files .file-thumb {
        width: 100%; aspect-ratio: 4/3; overflow: hidden;
        position: relative; background: var(--primary-100);
    }
    .page-files .file-thumb img {
        width: 100%; height: 100%; object-fit: cover;
    }
    .page-files .file-thumb-icon {
        width: 100%; height: 100%; display: flex;
        flex-direction: column; align-items: center;
        justify-content: center; gap: 6px;
    }
    .page-files .file-thumb-icon .material-icons {
        font-size: 40px; color: var(--secondary-500);
    }
    .page-files .file-thumb-icon span {
        font-size: 11px; font-weight: 700; color: var(--secondary-600);
        text-transform: uppercase; letter-spacing: .5px;
    }
    .page-files .file-type-badge {
        position: absolute; top: 8px; left: 8px;
        padding: 2px 8px; border-radius: 6px;
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .3px;
    }
    .page-files .badge-img { background: var(--primary-500); color: #fff; }
    .page-files .badge-pdf { background: #dc2626; color: #fff; }
    .page-files .badge-doc { background: #2563eb; color: #fff; }
    .page-files .badge-xls { background: #16a34a; color: #fff; }
    .page-files .badge-video { background: #d97706; color: #fff; }
    .page-files .badge-zip { background: #7c3aed; color: #fff; }
    .page-files .badge-other { background: var(--secondary-600); color: #fff; }

    .page-files .file-info {
        padding: 12px 14px;
    }
    .page-files .file-name {
        font-size: 12px; font-weight: 600; color: var(--secondary-900);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        margin-bottom: 4px;
    }
    .page-files .file-meta {
        display: flex; align-items: center; justify-content: space-between;
        font-size: 11px; color: var(--secondary-700);
    }

    .page-files .list-view .file-card {
        display: flex; flex-direction: row; border-radius: 12px;
    }
    .page-files .list-view .file-thumb {
        width: 72px; min-width: 72px; aspect-ratio: 1;
        border-radius: 12px 0 0 12px;
    }
    .page-files .list-view .file-info {
        flex: 1; display: flex; align-items: center;
        gap: 16px; padding: 10px 18px;
    }
    .page-files .list-view .file-name { margin-bottom: 0; flex: 1; }
    .page-files .list-view .file-meta { flex-shrink: 0; display: flex; gap: 20px; }
    .page-files .list-view .file-meta-item { display: flex; align-items: center; gap: 4px; }

    .page-files .empty-state {
        text-align: center; padding: 48px 20px; color: var(--secondary-700);
    }
    .page-files .empty-state .material-icons {
        font-size: 48px; color: var(--secondary-400); margin-bottom: 12px;
    }
    .page-files .empty-state h3 {
        font-size: 16px; font-weight: 600; color: var(--secondary-900); margin-bottom: 6px;
    }
    .page-files .empty-state p { font-size: 13px; color: var(--secondary-700); }

    /* Detail panel */
    .page-files .detail-panel {
        position: fixed; right: 0; top: 0; z-index: 1000;
        width: 400px; height: 100vh; background: #fff;
        border-left: 2px solid var(--primary-500);
        transform: translateX(100%); transition: transform .3s cubic-bezier(.4,0,.2,1);
        display: flex; flex-direction: column; overflow: hidden;
    }
    .page-files .detail-panel.open { transform: translateX(0); }
    .page-files .detail-overlay {
        position: fixed; inset: 0; z-index: 999;
        background: rgba(0,0,0,.3); opacity: 0; pointer-events: none;
        transition: opacity .3s;
    }
    .page-files .detail-overlay.show { opacity: 1; pointer-events: all; }

    .page-files .detail-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 24px; border-bottom: 2px solid var(--primary-100);
        flex-shrink: 0;
    }
    .page-files .detail-header h3 {
        font-size: 16px; font-weight: 700; color: var(--primary-500); margin: 0;
    }
    .page-files .detail-close {
        width: 32px; height: 32px; border-radius: 8px;
        border: none; background: var(--primary-100); color: var(--primary-500);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; transition: all .2s; font-size: 18px;
    }
    .page-files .detail-close:hover { background: var(--primary-200); }

    .page-files .detail-body {
        flex: 1; overflow-y: auto; padding: 24px;
    }
    .page-files .detail-preview {
        width: 100%; aspect-ratio: 16/9; border-radius: 14px;
        overflow: hidden; background: var(--primary-100);
        margin-bottom: 20px; border: 1.5px solid var(--primary-100);
    }
    .page-files .detail-preview img {
        width: 100%; height: 100%; object-fit: contain;
    }
    .page-files .detail-preview-icon {
        width: 100%; height: 100%; display: flex;
        flex-direction: column; align-items: center; justify-content: center; gap: 8px;
    }
    .page-files .detail-preview-icon .material-icons { font-size: 56px; color: var(--secondary-500); }
    .page-files .detail-preview-icon span { font-size: 13px; color: var(--secondary-700); font-weight: 600; }

    .page-files .detail-filename {
        font-size: 16px; font-weight: 700; color: var(--secondary-900);
        word-break: break-all; margin-bottom: 20px; line-height: 1.3;
    }
    .page-files .detail-section { margin-bottom: 20px; }
    .page-files .detail-section-title {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .8px; color: var(--secondary-700);
        margin-bottom: 10px; padding-bottom: 6px;
        border-bottom: 1px solid var(--primary-100);
    }
    .page-files .detail-row {
        display: flex; align-items: flex-start;
        justify-content: space-between; padding: 8px 0;
        font-size: 12px;
    }
    .page-files .detail-row + .detail-row { border-top: 1px solid var(--primary-100); }
    .page-files .detail-row .label {
        color: var(--secondary-700); display: flex;
        align-items: center; gap: 6px; flex-shrink: 0;
    }
    .page-files .detail-row .label .material-icons { font-size: 14px; }
    .page-files .detail-row .value {
        font-weight: 600; color: var(--secondary-900);
        text-align: right; max-width: 55%; word-break: break-all;
    }

    .page-files .detail-actions {
        padding: 16px 24px; border-top: 2px solid var(--primary-100);
        display: flex; gap: 8px; flex-shrink: 0;
    }
    .page-files .btn-detail {
        flex: 1; padding: 10px; border-radius: 10px;
        font-size: 12px; font-weight: 700; cursor: pointer;
        transition: all .2s; display: flex; align-items: center;
        justify-content: center; gap: 6px; font-family: inherit; border: none;
    }
    .page-files .btn-detail:active { transform: scale(.97); }
    .page-files .btn-download {
        background: var(--primary-500); color: #fff;
    }
    .page-files .btn-download:hover { opacity: .9; }
    .page-files .btn-copy {
        background: var(--primary-100); color: var(--primary-500);
    }
    .page-files .btn-copy:hover { background: var(--primary-200); }
    .page-files .btn-delete {
        background: #fef2f2; color: #dc2626;
    }
    .page-files .btn-delete:hover { background: #fee2e2; }

    /* Upload modal */
    .page-files .upload-overlay {
        position: fixed; inset: 0; z-index: 1100;
        background: rgba(0,0,0,.4); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .page-files .upload-overlay.show { opacity: 1; pointer-events: all; }

    .page-files .upload-modal {
        background: #fff; border-radius: 24px;
        border: 2px solid var(--primary-500); padding: 32px;
        width: 520px; max-width: 90vw; transform: scale(.9);
        transition: transform .3s cubic-bezier(.4,0,.2,1);
    }
    .page-files .upload-overlay.show .upload-modal { transform: scale(1); }
    .page-files .upload-modal h3 {
        font-size: 20px; font-weight: 700; color: var(--primary-500);
        margin-bottom: 20px;
    }

    .page-files .upload-zone {
        border: 2px dashed var(--primary-200); border-radius: 14px;
        padding: 40px 24px; text-align: center; cursor: pointer;
        transition: all .3s;
    }
    .page-files .upload-zone:hover,
    .page-files .upload-zone.dragover {
        border-color: var(--primary-500); background: var(--primary-100);
    }
    .page-files .upload-zone .material-icons {
        font-size: 40px; color: var(--secondary-500);
        margin-bottom: 12px; transition: color .3s;
    }
    .page-files .upload-zone.dragover .material-icons { color: var(--primary-500); }
    .page-files .upload-zone h4 {
        font-size: 15px; font-weight: 600; color: var(--secondary-900); margin-bottom: 6px;
    }
    .page-files .upload-zone p { font-size: 12px; color: var(--secondary-700); }
    .page-files .upload-zone .browse-link {
        color: var(--primary-500); font-weight: 700; cursor: pointer;
    }
    .page-files .upload-zone .browse-link:hover { text-decoration: underline; }
    .page-files .upload-zone .hint {
        margin-top: 12px; font-size: 11px; color: var(--secondary-700);
    }

    .page-files .upload-progress {
        display: none; margin-top: 20px;
    }
    .page-files .upload-progress.show { display: block; }
    .page-files .progress-bar {
        width: 100%; height: 8px; background: var(--primary-100);
        border-radius: 4px; overflow: hidden;
    }
    .page-files .progress-fill {
        height: 100%; background: var(--primary-500); border-radius: 4px;
        width: 0; transition: width .3s;
    }
    .page-files .progress-text {
        font-size: 12px; color: var(--secondary-700); margin-top: 8px; text-align: center;
    }

    .page-files .upload-modal-actions {
        display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;
    }
    .page-files .btn-cancel {
        padding: 10px 24px; border-radius: 10px;
        border: 2px solid var(--primary-200); background: transparent;
        color: var(--secondary-700); font-size: 13px; font-weight: 700;
        cursor: pointer; transition: all .2s; font-family: inherit;
    }
    .page-files .btn-cancel:hover { background: var(--primary-100); border-color: var(--primary-300); }

    /* Confirm modal */
    .page-files .confirm-overlay {
        position: fixed; inset: 0; z-index: 1200;
        background: rgba(0,0,0,.4); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none; transition: opacity .3s;
    }
    .page-files .confirm-overlay.show { opacity: 1; pointer-events: all; }
    .page-files .confirm-box {
        background: #fff; border-radius: 24px;
        border: 2px solid var(--danger-500); padding: 28px;
        width: 380px; max-width: 90vw; text-align: center;
        transform: scale(.9); transition: transform .3s cubic-bezier(.4,0,.2,1);
    }
    .page-files .confirm-overlay.show .confirm-box { transform: scale(1); }
    .page-files .confirm-box .icon-circle {
        width: 52px; height: 52px; border-radius: 50%;
        background: #fef2f2; display: flex; align-items: center;
        justify-content: center; margin: 0 auto 14px;
    }
    .page-files .confirm-box .icon-circle .material-icons { font-size: 24px; color: #dc2626; }
    .page-files .confirm-box h4 { font-size: 17px; font-weight: 700; color: var(--secondary-900); margin-bottom: 6px; }
    .page-files .confirm-box p { font-size: 13px; color: var(--secondary-700); margin-bottom: 20px; line-height: 1.5; }
    .page-files .confirm-btns { display: flex; gap: 10px; }
    .page-files .confirm-btns button { flex: 1; }

    .page-files .btn-danger {
        padding: 10px; border-radius: 10px; border: none;
        background: #dc2626; color: #fff; font-size: 13px; font-weight: 700;
        cursor: pointer; transition: all .2s; font-family: inherit;
    }
    .page-files .btn-danger:hover { background: #b91c1c; }

    /* Toast */
    .page-files .toast-container {
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        display: flex; flex-direction: column; gap: 8px;
    }
    .page-files .toast {
        background: #fff; border-radius: 12px; padding: 14px 20px;
        display: flex; align-items: center; gap: 10px;
        font-size: 13px; font-weight: 600; color: var(--secondary-900);
        box-shadow: 0 6px 24px rgba(0,0,0,.15);
        border: 2px solid var(--primary-500);
        animation: slideIn .3s ease, fadeOut .3s ease 2.7s forwards;
        min-width: 280px;
    }
    .page-files .toast .material-icons { font-size: 18px; }
    .page-files .toast.success { border-color: #16a34a; }
    .page-files .toast.success .material-icons { color: #16a34a; }
    .page-files .toast.error { border-color: #dc2626; }
    .page-files .toast.error .material-icons { color: #dc2626; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateX(40px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeOut {
        to { opacity: 0; transform: translateX(40px); }
    }

    @media (max-width: 768px) {
        .page-files .table-card { border-radius: 14px; }
        .page-files .files-grid { padding: 12px; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
        .page-files .filter-tabs { width: 100%; overflow-x: auto; }
        .page-files .filter-tab { padding: 8px 14px; font-size: 11px; }
        .page-files .detail-panel { width: 100%; }
        .page-files .upload-modal { padding: 24px 20px; }
        .page-files .list-view .file-info { flex-wrap: wrap; gap: 6px; }
        .page-files .list-view .file-meta { gap: 12px; }
    }

    @media (max-width: 480px) {
        .page-files .files-grid { grid-template-columns: repeat(2, 1fr); }
        .page-files .toolbar .search-box { min-width: 100%; }
    }
</style>

<div class="page-files">
    <div class="page-header">
        <div>
            <h1>Files &amp; Images</h1>
            <p>Kelola semua file dan gambar yang telah diunggah</p>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <span class="file-count" id="fileCount">Menampilkan <strong>0</strong> file</span>
            <button class="btn-upload" id="btnUpload">
                <span class="material-icons">cloud_upload</span>
                Unggah File
            </button>
        </div>
    </div>

    <div class="toolbar">
        <div class="search-box">
            <span class="material-icons">search</span>
            <input type="text" id="searchInput" placeholder="Cari file berdasarkan nama...">
        </div>
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">Semua</button>
            <button class="filter-tab" data-filter="image">Gambar</button>
            <button class="filter-tab" data-filter="document">Dokumen</button>
            <button class="filter-tab" data-filter="video">Video</button>
            <button class="filter-tab" data-filter="archive">Arsip</button>
        </div>
        <div class="view-toggle">
            <button class="view-btn active" data-view="grid"><span class="material-icons">grid_view</span></button>
            <button class="view-btn" data-view="list"><span class="material-icons">list</span></button>
        </div>
        <select class="sort-select" id="sortSelect">
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
            <option value="name-asc">Nama A-Z</option>
            <option value="name-desc">Nama Z-A</option>
            <option value="size-desc">Ukuran Terbesar</option>
            <option value="size-asc">Ukuran Terkecil</option>
        </select>
    </div>

    <div class="table-card">
        <div class="files-grid" id="filesGrid"></div>
        <div class="empty-state" id="emptyState" style="display:none;">
            <span class="material-icons">folder_open</span>
            <h3>Tidak Ada File</h3>
            <p>Belum ada file yang diunggah. Klik "Unggah File" untuk memulai.</p>
        </div>
    </div>

    {{-- Detail overlay --}}
    <div class="detail-overlay" id="detailOverlay"></div>

    {{-- Detail panel --}}
    <aside class="detail-panel" id="detailPanel">
    <div class="detail-header">
        <h3>Detail File</h3>
        <button class="detail-close" id="detailClose"><span class="material-icons">close</span></button>
    </div>
    <div class="detail-body" id="detailBody"></div>
    <div class="detail-actions">
        <button class="btn-detail btn-download" id="btnDownload"><span class="material-icons">download</span> Unduh</button>
        <button class="btn-detail btn-copy" id="btnCopyLink"><span class="material-icons">link</span> Salin</button>
        <button class="btn-detail btn-delete" id="btnDelete"><span class="material-icons">delete</span> Hapus</button>
    </div>
</aside>

{{-- Upload modal --}}
<div class="upload-overlay" id="uploadOverlay">
    <div class="upload-modal">
        <h3>Unggah File Baru</h3>
        <div class="upload-zone" id="uploadZone">
            <span class="material-icons">cloud_upload</span>
            <h4>Seret &amp; Lepas File di Sini</h4>
            <p>atau <span class="browse-link" id="browseLink">pilih file dari perangkat</span></p>
            <p class="hint">Mendukung JPG, PNG, GIF, SVG, WebP, PDF, DOC, XLS, ZIP — Maks 20MB</p>
            <input type="file" id="fileInput" multiple accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.7z" style="display:none;">
        </div>
        <div class="upload-progress" id="uploadProgress">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <div class="progress-text" id="progressText">Mengunggah...</div>
        </div>
        <div class="upload-modal-actions">
            <button class="btn-cancel" id="uploadCancel">Batal</button>
        </div>
    </div>
</div>

{{-- Confirm delete --}}
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="icon-circle"><span class="material-icons">delete</span></div>
        <h4>Hapus File?</h4>
        <p id="confirmText">File akan dipindahkan ke tempat sampah. SuperAdmin dapat memulihkannya.</p>
        <div class="confirm-btns">
            <button class="btn-cancel" id="confirmCancel">Batal</button>
            <button class="btn-danger" id="confirmDelete">Ya, Hapus</button>
        </div>
    </div>

    {{-- Toast container --}}
    <div class="toast-container" id="toastContainer"></div>
</div>

<script>
// === Data dari backend ===
@php
$filesJson = $files->map(function($m) {
    $ext = strtolower(pathinfo($m->file_name, PATHINFO_EXTENSION));
    $type = match(true) {
        in_array($ext, ['jpg','jpeg','png','gif','bmp','webp','svg','tiff','tif','psd','ai','eps']) => 'image',
        in_array($ext, ['pdf']) => 'document',
        in_array($ext, ['doc','docx','odt']) => 'document',
        in_array($ext, ['xls','xlsx','ods']) => 'document',
        in_array($ext, ['ppt','pptx','odp']) => 'document',
        in_array($ext, ['mp4','avi','mov','mkv','wmv','webm','flv']) => 'video',
        in_array($ext, ['zip','rar','7z','tar','gz']) => 'archive',
        default => 'other'
    };
    return [
        'id' => $m->id,
        'name' => $m->file_name,
        'type' => $type,
        'ext' => strtoupper($ext),
        'size' => (int) $m->size,
        'mime' => $m->mime_type,
        'created_at' => $m->created_at ? $m->created_at->toISOString() : null,
        'collection' => $m->collection_name,
        'url' => route('media.spatie', $m->id),
        'download_url' => route('media.download', $m->id),
    ];
});
@endphp
const filesData = @json($filesJson);

// === State ===
let selectedFileId = null;
let currentFilter = 'all';
let currentView = 'grid';
let currentSort = 'newest';
let searchQuery = '';
let deleteTargetId = null;

// === DOM ===
const grid = document.getElementById('filesGrid');
const empty = document.getElementById('emptyState');
const count = document.getElementById('fileCount');
const panel = document.getElementById('detailPanel');
const overlay = document.getElementById('detailOverlay');
const body = document.getElementById('detailBody');
const search = document.getElementById('searchInput');
const sort = document.getElementById('sortSelect');

// === Utils ===
function fmtSize(bytes) {
    if (!bytes || bytes === 0) return '0 B';
    const k = 1024;
    const s = ['B','KB','MB','GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + s[i];
}

function fmtDate(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    return d.toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' });
}

function timeAgo(iso) {
    if (!iso) return '-';
    const now = Date.now();
    const d = new Date(iso).getTime();
    const diff = Math.floor((now - d) / 1000);
    if (diff < 60) return 'Baru saja';
    if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
    if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
    if (diff < 604800) return Math.floor(diff / 86400) + ' hari lalu';
    return fmtDate(iso);
}

function badgeClass(type, ext) {
    if (type === 'image') return 'badge-img';
    if (ext === 'PDF') return 'badge-pdf';
    if (['DOC','DOCX','ODT'].includes(ext)) return 'badge-doc';
    if (['XLS','XLSX','ODS'].includes(ext)) return 'badge-xls';
    if (type === 'video') return 'badge-video';
    if (type === 'archive') return 'badge-zip';
    return 'badge-other';
}

function fileIcon(type, ext) {
    if (type === 'image') return 'image';
    if (ext === 'PDF') return 'picture_as_pdf';
    if (['DOC','DOCX','ODT'].includes(ext)) return 'description';
    if (['XLS','XLSX','ODS'].includes(ext)) return 'table_chart';
    if (['PPT','PPTX','ODP'].includes(ext)) return 'slideshow';
    if (type === 'video') return 'videocam';
    if (type === 'archive') return 'folder_zip';
    return 'insert_drive_file';
}

// === Render ===
function render() {
    let data = [...filesData];

    if (currentFilter !== 'all') data = data.filter(f => f.type === currentFilter);
    if (searchQuery) {
        const q = searchQuery.toLowerCase();
        data = data.filter(f => f.name.toLowerCase().includes(q));
    }

    switch (currentSort) {
        case 'newest': data.sort((a,b) => new Date(b.created_at||0) - new Date(a.created_at||0)); break;
        case 'oldest': data.sort((a,b) => new Date(a.created_at||0) - new Date(b.created_at||0)); break;
        case 'name-asc': data.sort((a,b) => a.name.localeCompare(b.name)); break;
        case 'name-desc': data.sort((a,b) => b.name.localeCompare(a.name)); break;
        case 'size-desc': data.sort((a,b) => b.size - a.size); break;
        case 'size-asc': data.sort((a,b) => a.size - b.size); break;
    }

    count.innerHTML = 'Menampilkan <strong>' + data.length + '</strong> file';

    if (data.length === 0) {
        grid.style.display = 'none';
        empty.style.display = 'block';
        return;
    }
    grid.style.display = '';
    empty.style.display = 'none';
    grid.className = 'files-grid' + (currentView === 'list' ? ' list-view' : '');

    grid.innerHTML = data.map(f => {
        const sel = f.id === selectedFileId ? ' selected' : '';
        const isImg = f.type === 'image' && f.url;
        const thumb = isImg
            ? `<img src="${f.url}" alt="${f.name}" loading="lazy">`
            : `<div class="file-thumb-icon"><span class="material-icons">${fileIcon(f.type, f.ext)}</span><span>${f.ext}</span></div>`;

        if (currentView === 'list') {
            return `<div class="file-card${sel}" data-id="${f.id}">
                <div class="file-thumb">${thumb}<span class="file-type-badge ${badgeClass(f.type, f.ext)}">${f.ext}</span></div>
                <div class="file-info">
                    <div class="file-name" title="${f.name}">${f.name}</div>
                    <div class="file-meta">
                        <span class="file-meta-item"><span class="material-icons" style="font-size:14px;">storage</span> ${fmtSize(f.size)}</span>
                        <span class="file-meta-item"><span class="material-icons" style="font-size:14px;">schedule</span> ${timeAgo(f.created_at)}</span>
                    </div>
                </div>
            </div>`;
        }

        return `<div class="file-card${sel}" data-id="${f.id}">
            <div class="file-thumb">${thumb}<span class="file-type-badge ${badgeClass(f.type, f.ext)}">${f.ext}</span></div>
            <div class="file-info">
                <div class="file-name" title="${f.name}">${f.name}</div>
                <div class="file-meta">
                    <span>${fmtSize(f.size)}</span>
                    <span>${timeAgo(f.created_at)}</span>
                </div>
            </div>
        </div>`;
    }).join('');
}

// === Detail ===
function renderDetail(f) {
    const isImg = f.type === 'image' && f.url;
    const preview = isImg
        ? `<div class="detail-preview"><img src="${f.url}" alt="${f.name}"></div>`
        : `<div class="detail-preview"><div class="detail-preview-icon"><span class="material-icons" style="font-size:56px;color:var(--secondary-500)">${fileIcon(f.type, f.ext)}</span><span>File ${f.ext}</span></div></div>`;

    let rows = `
        <div class="detail-row"><span class="label"><span class="material-icons">tag</span> Tipe</span><span class="value">${f.ext} File</span></div>
        <div class="detail-row"><span class="label"><span class="material-icons">storage</span> Ukuran</span><span class="value">${fmtSize(f.size)}</span></div>
        <div class="detail-row"><span class="label"><span class="material-icons">calendar_today</span> Diunggah</span><span class="value">${fmtDate(f.created_at)}</span></div>
        <div class="detail-row"><span class="label"><span class="material-icons">schedule</span> Waktu Relatif</span><span class="value">${timeAgo(f.created_at)}</span></div>
        <div class="detail-row"><span class="label"><span class="material-icons">collections_bookmark</span> Koleksi</span><span class="value">${f.collection}</span></div>
        <div class="detail-row"><span class="label"><span class="material-icons">fingerprint</span> ID File</span><span class="value">#${f.id}</span></div>
    `;

    body.innerHTML = `
        ${preview}
        <div class="detail-filename">${f.name}</div>
        <div class="detail-section">
            <div class="detail-section-title">Informasi File</div>
            ${rows}
        </div>
    `;

    panel.classList.add('open');
    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function openDetail(id) {
    const f = filesData.find(x => x.id === id);
    if (!f) return;
    selectedFileId = id;
    renderDetail(f);
    render();
}

function closeDetail() {
    selectedFileId = null;
    panel.classList.remove('open');
    overlay.classList.remove('show');
    document.body.style.overflow = '';
    render();
}

// === Events ===
grid.addEventListener('click', e => {
    const card = e.target.closest('.file-card');
    if (!card) return;
    const id = parseInt(card.dataset.id);
    if (selectedFileId === id) { closeDetail(); }
    else { openDetail(id); }
});

document.getElementById('detailClose').addEventListener('click', closeDetail);
overlay.addEventListener('click', closeDetail);

// === Filter tabs ===
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        currentFilter = tab.dataset.filter;
        render();
    });
});

// === View toggle ===
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentView = btn.dataset.view;
        render();
    });
});

// === Sort ===
sort.addEventListener('change', () => { currentSort = sort.value; render(); });

// === Search ===
let searchTimer;
search.addEventListener('input', () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { searchQuery = search.value.trim(); render(); }, 200);
});

// === Toast ===
function toast(msg, type = 'success') {
    const icon = type === 'success' ? 'check_circle' : 'error';
    const el = document.createElement('div');
    el.className = 'toast ' + type;
    el.innerHTML = '<span class="material-icons">' + icon + '</span><span>' + msg + '</span>';
    document.getElementById('toastContainer').appendChild(el);
    setTimeout(() => el.remove(), 3000);
}

// === Download ===
document.getElementById('btnDownload').addEventListener('click', () => {
    if (!selectedFileId) return;
    const f = filesData.find(x => x.id === selectedFileId);
    if (f && f.download_url) window.open(f.download_url, '_blank');
});

// === Copy link ===
document.getElementById('btnCopyLink').addEventListener('click', () => {
    if (!selectedFileId) return;
    const f = filesData.find(x => x.id === selectedFileId);
    if (f && f.download_url) {
        navigator.clipboard.writeText(window.location.origin + f.download_url).then(() => {
            toast('Link disalin ke clipboard!');
        }).catch(() => {
            toast('Gagal menyalin link', 'error');
        });
    }
});

// === Delete ===
document.getElementById('btnDelete').addEventListener('click', () => {
    if (!selectedFileId) return;
    deleteTargetId = selectedFileId;
    document.getElementById('confirmText').textContent = 'File "' + (filesData.find(x=>x.id===selectedFileId)?.name || '') + '" akan dipindahkan ke tempat sampah. SuperAdmin dapat memulihkannya.';
    document.getElementById('confirmOverlay').classList.add('show');
});

document.getElementById('confirmCancel').addEventListener('click', () => {
    document.getElementById('confirmOverlay').classList.remove('show');
    deleteTargetId = null;
});

document.getElementById('confirmDelete').addEventListener('click', () => {
    if (!deleteTargetId) return;
    const id = deleteTargetId;
    document.getElementById('confirmOverlay').classList.remove('show');

    fetch('{{ url('/adminweb/files') }}/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            toast(res.message);
            const idx = filesData.findIndex(x => x.id === id);
            if (idx !== -1) filesData.splice(idx, 1);
            if (selectedFileId === id) closeDetail();
            else render();
        } else {
            toast(res.message || 'Gagal menghapus file', 'error');
        }
    })
    .catch(() => toast('Gagal menghapus file', 'error'))
    .finally(() => { deleteTargetId = null; });
});

// === Upload modal ===
const uploadOverlay = document.getElementById('uploadOverlay');
document.getElementById('btnUpload').addEventListener('click', () => {
    uploadOverlay.classList.add('show');
    document.getElementById('uploadProgress').classList.remove('show');
    document.getElementById('progressFill').style.width = '0';
    document.getElementById('progressText').textContent = 'Mengunggah...';
});
document.getElementById('uploadCancel').addEventListener('click', () => uploadOverlay.classList.remove('show'));
uploadOverlay.addEventListener('click', e => { if (e.target === uploadOverlay) uploadOverlay.classList.remove('show'); });

const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
document.getElementById('browseLink').addEventListener('click', e => { e.stopPropagation(); fileInput.click(); });
uploadZone.addEventListener('click', () => fileInput.click());

uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));

uploadZone.addEventListener('drop', e => {
    e.preventDefault();
    uploadZone.classList.remove('dragover');
    if (e.dataTransfer.files.length) uploadFiles(e.dataTransfer.files);
});

fileInput.addEventListener('change', () => {
    if (fileInput.files.length) uploadFiles(fileInput.files);
});

function uploadFiles(files) {
    const prog = document.getElementById('uploadProgress');
    const fill = document.getElementById('progressFill');
    const txt = document.getElementById('progressText');
    prog.classList.add('show');

    let uploaded = 0;
    let hasError = false;

    Array.from(files).forEach((file, i) => {
        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('editor.upload') }}');
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                const pct = Math.round((e.loaded / e.total) * 100);
                fill.style.width = pct + '%';
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                const res = JSON.parse(xhr.responseText);
                if (res.success && res.file) {
                    // Upload to editor.upload saves to FileOrganisasi, not directly to media table.
                    // Reload page to fetch updated data from server
                }
            } else {
                hasError = true;
                try {
                    const err = JSON.parse(xhr.responseText);
                    txt.textContent = err.errors?.file?.[0] || 'Gagal mengunggah';
                } catch(e) {
                    txt.textContent = 'Gagal mengunggah file';
                }
            }
            uploaded++;
            if (uploaded === files.length) {
                if (!hasError) {
                    txt.textContent = 'Semua file berhasil diunggah!';
                    setTimeout(() => { location.reload(); }, 800);
                } else {
                    txt.textContent = 'Beberapa file gagal diunggah.';
                    setTimeout(() => { location.reload(); }, 1500);
                }
            }
        };

        xhr.onerror = function() {
            hasError = true;
            uploaded++;
            txt.textContent = 'Gagal mengunggah file (network error)';
            if (uploaded === files.length) {
                setTimeout(() => { location.reload(); }, 1500);
            }
        };

        xhr.send(formData);
    });
}

// === Init ===
render();
</script>
@endsection
