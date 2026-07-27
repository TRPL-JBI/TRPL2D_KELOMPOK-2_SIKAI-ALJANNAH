@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages*'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'Files & Images', 'url' => '#', 'active' => 'adminweb/files'],
    ]
])

@section('title', 'Pages')

@section('content')
<style>
    .pages-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pages-title {
        font-size: 24px;
        font-weight: 700;
        color: #16423c;
    }

    .pages-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .pages-search {
        position: relative;
        width: 320px;
    }

    .pages-search .material-icons {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #2b2b2b;
        font-size: 20px;
        pointer-events: none;
    }

    .pages-search input {
        width: 100%;
        padding: 10px 14px 10px 44px;
        background-color: #d9d9d9;
        color: #2b2b2b;
        border: none;
        border-radius: 999px;
        font-size: 14px;
        font-family: inherit;
        outline: none;
    }

    .pages-search input::placeholder {
        color: #575757;
    }

    .btn-pages {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 24px;
        border: none;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: opacity 0.2s;
    }

    .btn-pages:hover {
        opacity: 0.85;
    }

    .btn-pages-primary {
        background-color: #16423c;
        color: #fff;
    }

    .pages-table-wrap {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        background: #fff;
    }

    .pages-table {
        width: 100%;
        border-collapse: collapse;
    }

    .pages-table th {
        background-color: #16423c;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-align: left;
        padding: 14px 16px;
        white-space: nowrap;
    }

    .pages-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: #2b2b2b;
        border-bottom: 1px solid #efefef;
        vertical-align: middle;
    }

    .pages-table tbody tr:hover {
        background-color: #f7f7f7;
    }

    .pages-table tbody tr.row-pinned {
        border-left: 5px solid #16423c;
        background-color: #f0f7f5;
        font-weight: 700;
    }

    .pages-table tbody tr.row-pinned td {
        font-weight: 600;
    }

    .pages-table tbody tr.row-pinned:hover {
        background-color: #e6f1ed;
    }

    .badge-pinned {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background-color: #16423c;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
        margin-left: 6px;
    }

    .badge-pinned .material-icons {
        font-size: 12px;
    }

    .pages-table tbody tr:last-child td {
        border-bottom: none;
    }

    .pages-title-cell {
        font-weight: 600;
    }

    .pages-updated {
        font-size: 12px;
        color: #828282;
    }

    .pages-actions {
        display: flex;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px 14px;
        border: none;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: opacity 0.2s;
        white-space: nowrap;
    }

    .btn-action:hover {
        opacity: 0.85;
    }

    .btn-action-edit {
        background-color: #16423c;
        color: #fff;
    }

    .btn-action-danger {
        background-color: #9a0000;
        color: #fff;
    }

    .btn-action-link {
        background-color: #e7e7e7;
        color: #2b2b2b;
    }

    .btn-action-secondary {
        background-color: #d9d9d9;
        color: #2b2b2b;
    }

    .btn-action-success {
        background-color: #35ab50;
        color: #fff;
    }

    .toast-copy {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        background: #16423c;
        color: #fff;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        z-index: 9999;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        opacity: 0;
        transition: opacity 0.3s;
        pointer-events: none;
    }

    .toast-copy.show {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .pages-search {
            width: 100%;
        }
        .pages-table-wrap {
            overflow-x: auto;
        }
    }
</style>

<div class="pages-header">
    <h1 class="pages-title">Pages</h1>
    <div class="pages-toolbar">
        <div class="pages-search">
            <span class="material-icons">search</span>
            <form method="GET" action="{{ route('adminweb.pages') }}" style="margin:0;">
                <input type="text" name="search" placeholder="Cari halaman..." value="{{ request('search') }}" onchange="this.form.submit()">
            </form>
        </div>
        <button onclick="openCreateModal()" class="btn-pages btn-pages-primary" style="border:none;">
            <span class="material-icons" style="font-size:18px;">add</span>
            Buat Halaman Baru
        </button>
    </div>
</div>

<div class="pages-table-wrap">
    <table class="pages-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Nama Halaman</th>
                <th>Terupdate</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pages as $page)
            <tr class="{{ $page->slug === 'beranda' ? 'row-pinned' : '' }}">
                <td>{{ $page->created_at->format('d/m/Y') }}</td>
                <td class="pages-title-cell">
                    {{ $page->title }}
                    @if($page->slug === 'beranda')
                        <span class="badge-pinned">
                            <span class="material-icons">push_pin</span>
                            Pinned
                        </span>
                    @endif
                </td>
                <td>
                    <span class="pages-updated" title="{{ $page->updated_at->format('d/m/Y H:i') }}">
                        {{ $page->updated_at->locale('id')->diffForHumans() }}
                    </span>
                </td>
                <td>
                    <div class="pages-actions">
                        @if($page->slug === 'beranda')
                        <a href="{{ route('adminweb.pages.edit', $page->id) }}" class="btn-action btn-action-edit">
                            <span class="material-icons" style="font-size:14px;">edit</span>
                            Edit
                        </a>
                        @else
                        <a href="{{ route('adminweb.pages.builder', $page->id) }}" class="btn-action btn-action-edit">
                            <span class="material-icons" style="font-size:14px;">palette</span>
                            Builder
                        </a>
                        @endif
                        <form method="POST" action="{{ route('adminweb.pages.destroy', $page->id) }}" style="display:inline;" onsubmit="return confirm('Hapus halaman ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action-danger">
                                <span class="material-icons" style="font-size:14px;">delete</span>
                                Hapus
                            </button>
                        </form>
                        <button type="button" class="btn-action btn-action-link" onclick="copyPageLink('{{ $page->slug === 'beranda' ? '/' : '/page/'.$page->slug }}', this)">
                            <span class="material-icons" style="font-size:14px;">link</span>
                            Link
                        </button>
                        @if($page->status === 'published')
                            <span class="btn-action btn-action-secondary">
                                Diterapkan
                            </span>
                        @else
                            <form method="POST" action="{{ route('adminweb.pages.publish', $page->id) }}" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn-action btn-action-success">
                                    Terapkan
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center;padding:40px 16px;color:#828282;">
                    <span class="material-icons" style="font-size:40px;display:block;margin-bottom:8px;">description</span>
                    Belum ada halaman
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($pages->hasPages())
    <div style="padding:16px;border-top:1px solid #efefef;">
        {{ $pages->links() }}
    </div>
    @endif
</div>

<div id="toastCopy" class="toast-copy">Link disalin!</div>

{{-- ─── Create Page Modal ─── --}}
<div class="modal-overlay" id="createModal">
    <div class="modal-bg" onclick="closeCreateModal()"></div>
    <div class="modal" style="max-width:480px;">
        <div class="modal-header">
            <h3 class="modal-title">Buat Halaman Baru</h3>
            <button class="modal-close" onclick="closeCreateModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('adminweb.pages.store') }}" style="margin:0;">
            @csrf
            <div class="modal-body">
                <div class="form-group" style="margin-bottom:20px;">
                    <label class="form-label">Nama Halaman</label>
                    <input type="text" class="form-input" id="createTitle" name="title" required placeholder="Mis: Tentang Kami">
                </div>
                <div class="form-group" style="margin-bottom:8px;">
                    <label class="form-label">Slug</label>
                    <input type="text" class="form-input" id="createSlug" name="slug" required placeholder="tentang-kami">
                    <p style="font-size:11px;color:var(--primary-400);margin-top:6px;">Slug akan terisi otomatis dari nama halaman. Bisa diubah manual.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()" style="padding:10px 24px;font-size:13px;">Batal</button>
                <button type="submit" class="btn btn-primary" style="padding:10px 24px;font-size:13px;">
                    <span class="material-icons" style="font-size:16px;">arrow_forward</span>
                    Lanjut ke Builder
                </button>
            </div>
        </form>
    </div>
</div>

@if(session('success'))
<div style="position:fixed;top:20px;right:20px;z-index:9999;background-color:#d8efdd;border:1px solid #35ab50;border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:0.75rem;font-family:'Segoe UI',sans-serif;font-size:0.95rem;color:#154420;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
    <span class="material-icons" style="color:#35ab50;font-size:20px;">check_circle</span>
    {{ session('success') }}
</div>
<script>
    setTimeout(function(){ document.querySelector('[style*="position:fixed"][style*="top:20px"]')?.remove(); }, 4000);
</script>
@endif

<script>
    // ── Create Page Modal ──
    function openCreateModal() {
        document.getElementById('createModal').classList.add('active');
        document.getElementById('createTitle').focus();
    }
    function closeCreateModal() {
        document.getElementById('createModal').classList.remove('active');
    }
    document.getElementById('createTitle')?.addEventListener('input', function() {
        var slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        document.getElementById('createSlug').value = slug;
    });

    function copyPageLink(path, btn) {
        var url = window.location.origin + path;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                showCopyToast();
            }).catch(function() {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            showCopyToast();
        } catch (e) {}
        document.body.removeChild(ta);
    }

    function showCopyToast() {
        var toast = document.getElementById('toastCopy');
        toast.classList.add('show');
        setTimeout(function() { toast.classList.remove('show'); }, 2000);
    }
</script>
@endsection