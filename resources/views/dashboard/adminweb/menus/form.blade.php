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

@section('title', isset($menu) ? 'Edit Menu' : 'Tambah Menu')

@section('content')
<style>
    .page-menus-form .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
    }
    .page-menus-form .page-header h1 {
        font-size: 24px; font-weight: 700; color: var(--primary-500); margin: 0;
    }
    .page-menus-form .page-header .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--primary-500); color: #fff; padding: 10px 22px;
        border-radius: 100px; font-size: 13px; font-weight: 700;
        text-decoration: none; transition: all .2s; border: none; cursor: pointer;
        font-family: inherit;
    }
    .page-menus-form .page-header .btn-back:hover {
        opacity: .9; box-shadow: 0 4px 14px rgba(22,66,60,0.3);
    }
    .page-menus-form .page-header .btn-back .material-icons { font-size: 18px; }
    .page-menus-form .page-header .btn-back:active { transform: scale(.97); }

    .page-menus-form .form-card {
        background: #fff; border-radius: 28px; border: 2px solid var(--primary-500);
        padding: 36px 40px; box-shadow: 0 4px 16px rgba(0,0,0,.05);
        margin-bottom: 36px;
    }
    .page-menus-form .form-group { margin-bottom: 22px; }
    .page-menus-form .form-group:last-child { margin-bottom: 0; }
    .page-menus-form .form-group label {
        display: block; font-size: 12px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .5px;
        color: var(--primary-400); margin-bottom: 8px;
    }
    .page-menus-form .form-group .required-star { color: var(--danger-500); }
    .page-menus-form .form-group input,
    .page-menus-form .form-group select {
        width: 100%; padding: 12px 16px;
        border: 2px solid var(--primary-200); border-radius: 14px;
        font: 14px 'Inter', sans-serif; color: #1f2937;
        background: #fff; transition: border-color .2s;
        outline: none; box-sizing: border-box;
    }
    .page-menus-form .form-group input:focus,
    .page-menus-form .form-group select:focus {
        border-color: var(--primary-500);
    }
    .page-menus-form .form-group select {
        appearance: none;
        background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right 14px center;
        padding-right: 36px; cursor: pointer;
    }
    .page-menus-form .form-group input[type="number"] { width: 140px; }
    .page-menus-form .form-group .hint {
        font-size: 11px; color: var(--secondary-700); margin-top: 6px;
    }
    .page-menus-form .form-group .hint code {
        background: var(--primary-100); padding: 1px 6px;
        border-radius: 4px; font-size: 11px; color: var(--primary-500);
    }
    .page-menus-form .checkbox-row {
        display: flex; align-items: center; gap: 10px;
    }
    .page-menus-form .checkbox-row input[type="checkbox"] {
        width: 20px; height: 20px; accent-color: var(--primary-500);
        cursor: pointer; flex-shrink: 0;
    }
    .page-menus-form .checkbox-row span { font-size: 14px; color: var(--secondary-800); }

    .page-menus-form .field-error {
        font-size: 12px; color: var(--danger-500); margin-top: 5px;
        display: flex; align-items: center; gap: 4px;
    }
    .page-menus-form .field-error::before {
        content: 'warning'; font-family: 'Material Icons'; font-size: 14px;
    }

    .page-menus-form .form-actions {
        display: flex; gap: 12px; justify-content: flex-end;
        margin-top: 28px;
    }
    .page-menus-form .form-actions .btn {
        display: inline-flex; align-items: center; gap: 10px;
        font-size: 14px; font-weight: 700; padding: 14px 36px;
        border-radius: 100px; border: none; cursor: pointer;
        transition: all .2s; text-decoration: none; font-family: inherit;
    }
    .page-menus-form .form-actions .btn:active { transform: scale(.97); }
    .page-menus-form .form-actions .btn-primary {
        background: var(--primary-500); color: #fff;
    }
    .page-menus-form .form-actions .btn-primary:hover {
        opacity: .9; box-shadow: 0 4px 14px rgba(22,66,60,0.3);
    }
    .page-menus-form .form-actions .btn-secondary {
        background: transparent; color: var(--secondary-700);
        border: 2px solid var(--primary-200);
    }
    .page-menus-form .form-actions .btn-secondary:hover {
        background: var(--primary-100); border-color: var(--primary-300);
    }

    @media (max-width: 768px) {
        .page-menus-form .form-card { padding: 24px 20px; border-radius: 20px; }
        .page-menus-form .form-actions { flex-direction: column; }
        .page-menus-form .form-actions .btn { justify-content: center; }
    }
</style>

<div class="page-menus-form">
    <div class="page-header">
        <h1>{{ isset($menu) ? 'Edit Menu' : 'Tambah Menu Baru' }}</h1>
        <a href="{{ route('adminweb.menus') }}" class="btn-back">
            <span class="material-icons">arrow_back</span>
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ isset($menu) ? route('adminweb.menus.update', $menu->id) : route('adminweb.menus.store') }}">
        @csrf
        @if(isset($menu))
            @method('PUT')
        @endif

        <div class="form-card">
            <div class="form-group">
                <label for="label">Nama Menu <span class="required-star">*</span></label>
                <input type="text" id="label" name="label" value="{{ old('label', $menu->label ?? '') }}" placeholder="Contoh: Tentang Kami" required>
                @error('label')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="custom_url">URL / Link</label>
                <input type="text" id="custom_url" name="custom_url" value="{{ old('custom_url', $menu->custom_url ?? '') }}" placeholder="Contoh: /tentang-kami, https://example.com, #section">
                <div class="hint">
                    Bisa berupa path internal (<code>/tentang-kami</code>), URL eksternal (<code>https://...</code>),
                    atau anchor (<code>#section</code>). Kosongi jika tidak diperlukan.
                </div>
                @error('custom_url')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="parent_id">Induk Menu</label>
                <select id="parent_id" name="parent_id">
                    <option value="">— Tidak ada (menu utama) —</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $menu->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->label }}
                        </option>
                    @endforeach
                </select>
                <div class="hint">Pilih "Tidak ada" jika ini menu utama. Pilih menu lain jika ini sub-menu.</div>
                @error('parent_id')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="sort_order">Urutan</label>
                <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $menu->sort_order ?? '') }}" placeholder="Otomatis" min="0">
                <div class="hint">Angka lebih kecil tampil lebih dulu. Kosongi untuk urutan otomatis.</div>
                @error('sort_order')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="checkbox-row">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $menu->is_active ?? true) ? 'checked' : '' }}>
                    <span>Aktif</span>
                </label>
                <div class="hint">Nonaktifkan untuk menyembunyikan menu dari navbar tanpa menghapus.</div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('adminweb.menus') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <span class="material-icons">save</span>
                {{ isset($menu) ? 'Simpan Perubahan' : 'Tambah Menu' }}
            </button>
        </div>
    </form>
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
