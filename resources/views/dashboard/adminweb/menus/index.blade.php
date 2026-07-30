@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages*'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@section('title', 'Menu Navigasi')

@section('content')
<style>
    .page-menus .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
    }
    .page-menus .page-header h1 {
        font-size: 24px; font-weight: 700; color: var(--primary-500); margin: 0;
    }
    .page-menus .btn-tambah {
        display: inline-flex; align-items: center; gap: 8px;
        background: var(--primary-500); color: #fff;
        font-size: 13px; font-weight: 700; padding: 10px 24px;
        border-radius: 10px; text-decoration: none; transition: all .2s;
        border: none; cursor: pointer;
    }
    .page-menus .btn-tambah:hover { opacity: .9; box-shadow: 0 4px 12px rgba(22,66,60,0.3); }
    .page-menus .btn-tambah:active { transform: scale(.97); }
    .page-menus .btn-tambah .material-icons { font-size: 18px; }

    .page-menus .table-card {
        background: #fff; border-radius: 20px;
        border: 2px solid var(--primary-500);
        box-shadow: 0 4px 16px rgba(0,0,0,.05); overflow: hidden;
    }
    .page-menus .table-card table { width: 100%; border-collapse: collapse; }
    .page-menus .table-card thead { background: var(--primary-100); }
    .page-menus .table-card thead th {
        text-align: left; padding: 14px 20px;
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .6px; color: var(--primary-500);
    }
    .page-menus .table-card thead th:first-child { padding-left: 28px; }
    .page-menus .table-card thead th:last-child { padding-right: 28px; }
    .page-menus .table-card tbody tr {
        border-bottom: 1px solid var(--primary-100); transition: background .15s;
    }
    .page-menus .table-card tbody tr:last-child { border-bottom: none; }
    .page-menus .table-card tbody tr:hover { background: #f8faf9; }
    .page-menus .table-card tbody td {
        padding: 14px 20px; color: var(--secondary-800);
        font-size: 13px; vertical-align: middle;
    }
    .page-menus .table-card tbody td:first-child { padding-left: 28px; }
    .page-menus .table-card tbody td:last-child { padding-right: 28px; }

    .page-menus .menu-name {
        font-weight: 600; color: var(--secondary-900);
        display: flex; align-items: center; gap: 8px;
    }
    .page-menus .menu-name .tree-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; border-radius: 6px;
        background: var(--primary-500); color: #fff;
        font-size: 10px; font-weight: 700; flex-shrink: 0;
    }
    .page-menus .menu-name-child {
        padding-left: 36px; position: relative;
    }
    .page-menus .menu-name-child::before {
        content: ''; position: absolute; left: 16px; top: 50%;
        width: 12px; height: 1px; background: var(--primary-300);
    }
    .page-menus .menu-name-child::after {
        content: ''; position: absolute; left: 16px; top: 0;
        width: 1px; height: 50%; background: var(--primary-300);
    }
    .page-menus .menu-name-child:last-of-type::after { display: none; }

    .page-menus .menu-url {
        font-size: 12px; color: var(--secondary-700);
        max-width: 240px; overflow: hidden;
        text-overflow: ellipsis; white-space: nowrap; display: block;
    }
    .page-menus .menu-url-empty {
        font-size: 11px; color: var(--secondary-500); font-style: italic;
    }

    .page-menus .toggle-wrap { display: flex; align-items: center; }
    .page-menus .toggle {
        position: relative; display: inline-block;
        width: 38px; height: 20px; flex-shrink: 0; cursor: pointer;
    }
    .page-menus .toggle input { opacity: 0; width: 0; height: 0; }
    .page-menus .toggle .slider {
        position: absolute; inset: 0;
        background: var(--secondary-500); border-radius: 20px; transition: .25s;
    }
    .page-menus .toggle .slider::before {
        content: ''; position: absolute; left: 2px; bottom: 2px;
        width: 16px; height: 16px; background: #fff;
        border-radius: 50%; transition: .25s;
    }
    .page-menus .toggle input:checked + .slider { background: var(--success-500); }
    .page-menus .toggle input:checked + .slider::before { transform: translateX(18px); }
    .page-menus .toggle-label {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .3px; margin-left: 6px; color: var(--secondary-600);
    }
    .page-menus .toggle input:checked ~ .toggle-label { color: var(--success-500); }

    .page-menus .actions { display: flex; gap: 6px; flex-wrap: nowrap; }
    .page-menus .btn-action {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 4px; padding: 6px 14px; border: none; border-radius: 8px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        text-decoration: none; font-family: inherit;
        transition: all .2s; white-space: nowrap;
    }
    .page-menus .btn-action:hover { opacity: .85; }
    .page-menus .btn-action-edit {
        background: var(--primary-500); color: #fff;
    }
    .page-menus .btn-action-danger {
        background: var(--danger-500); color: #fff;
    }

    .page-menus .empty-state {
        text-align: center; padding: 48px 20px; color: var(--secondary-700);
    }
    .page-menus .empty-state .icon {
        font-size: 48px; color: var(--secondary-400); margin-bottom: 12px;
    }
    .page-menus .empty-state h3 {
        font-size: 16px; font-weight: 600; color: var(--secondary-900); margin-bottom: 6px;
    }
    .page-menus .empty-state p {
        font-size: 13px; color: var(--secondary-700); margin-bottom: 20px;
    }

    .page-menus .child-group { background: var(--primary-100); }
    .page-menus .child-group td { padding-top: 10px; padding-bottom: 10px; }
    .page-menus .parent-row td { padding-bottom: 6px; }
    .page-menus .parent-row + .child-group td { border-top: none; }

    @media (max-width: 768px) {
        .page-menus .table-card { border-radius: 14px; }
        .page-menus .table-card-wrap { overflow-x: auto; }
        .page-menus .table-card thead th,
        .page-menus .table-card tbody td { padding: 10px 12px; white-space: nowrap; }
        .page-menus .table-card thead th:first-child,
        .page-menus .table-card tbody td:first-child { padding-left: 16px; }
        .page-menus .table-card thead th:last-child,
        .page-menus .table-card tbody td:last-child { padding-right: 16px; }
        .page-menus .actions { flex-direction: column; gap: 4px; }
        .page-menus .btn-action { padding: 5px 10px; font-size: 11px; }
    }
</style>

<div class="page-menus">
    <div class="page-header">
        <h1>Menu Navigasi</h1>
        <a href="{{ route('adminweb.menus.create') }}" class="btn-tambah">
            <span class="material-icons">add</span>
            Tambah Menu
        </a>
    </div>

    <div class="table-card">
        <div class="table-card-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:60px;">Aktif</th>
                        <th>Nama Menu</th>
                        <th>URL</th>
                        <th style="width:160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                    <tr class="parent-row">
                        <td>
                            <form method="POST" action="{{ route('adminweb.menus.toggle', $menu->id) }}" style="display:inline;">
                                @csrf @method('PUT')
                                <label class="toggle">
                                    <input type="checkbox" {{ $menu->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                    <span class="slider"></span>
                                </label>
                            </form>
                        </td>
                        <td>
                            <div class="menu-name">
                                <span class="tree-badge">M</span>
                                {{ $menu->label }}
                            </div>
                        </td>
                        <td>
                            @if($menu->custom_url)
                                <span class="menu-url" title="{{ $menu->custom_url }}">{{ $menu->custom_url }}</span>
                            @else
                                <span class="menu-url-empty">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('adminweb.menus.edit', $menu->id) }}" class="btn-action btn-action-edit">
                                    <span class="material-icons" style="font-size:13px;">edit</span>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('adminweb.menus.destroy', $menu->id) }}" style="display:inline;" onsubmit="return confirm('Hapus menu ini beserta sub-menunya?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-danger">
                                        <span class="material-icons" style="font-size:13px;">delete</span>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @if($menu->children && $menu->children->count() > 0)
                        @foreach($menu->children as $child)
                        <tr class="child-group">
                            <td>
                                <form method="POST" action="{{ route('adminweb.menus.toggle', $child->id) }}" style="display:inline;">
                                    @csrf @method('PUT')
                                    <label class="toggle">
                                        <input type="checkbox" {{ $child->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                                        <span class="slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td>
                                <div class="menu-name menu-name-child">
                                    {{ $child->label }}
                                </div>
                            </td>
                            <td>
                                @if($child->custom_url)
                                    <span class="menu-url" title="{{ $child->custom_url }}">{{ $child->custom_url }}</span>
                                @else
                                    <span class="menu-url-empty">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('adminweb.menus.edit', $child->id) }}" class="btn-action btn-action-edit">
                                        <span class="material-icons" style="font-size:13px;">edit</span>
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('adminweb.menus.destroy', $child->id) }}" style="display:inline;" onsubmit="return confirm('Hapus sub-menu ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-action btn-action-danger">
                                            <span class="material-icons" style="font-size:13px;">delete</span>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                    @empty
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <div class="material-icons icon">menu</div>
                                <h3>Belum Ada Menu</h3>
                                <p>Tambahkan menu navigasi untuk mulai membangun navbar website.</p>
                                <a href="{{ route('adminweb.menus.create') }}" class="btn-tambah" style="display:inline-flex;">
                                    <span class="material-icons" style="font-size:18px;">add</span>
                                    Tambah Menu Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
@endsection
