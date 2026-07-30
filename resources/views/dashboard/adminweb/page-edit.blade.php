@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages*'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@section('title', 'Edit Halaman')

@section('content')
<div style="max-width:720px;margin:0 auto;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <h1 style="font-size:24px;font-weight:700;color:#16423c;">Edit Halaman</h1>
        <a href="{{ route('adminweb.pages') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border:none;border-radius:999px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;background-color:#16423c;color:#fff;transition:opacity 0.2s;">
            <span class="material-icons" style="font-size:18px;">arrow_back</span>
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('adminweb.pages.update', $page->id) }}" style="background:#fff;border:1px solid #d4d4d4;border-radius:12px;overflow:hidden;">
        @csrf
        @method('PUT')
        <div style="padding:24px;">
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Judul Halaman</label>
                <input type="text" name="title" value="{{ $page->title }}" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:'Poppins',sans-serif;font-size:14px;color:#1a1a1a;background:#f8f9fa;outline:none;box-sizing:border-box;">
            </div>
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:6px;">Konten</label>
                <textarea name="content" rows="12" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:'Poppins',sans-serif;font-size:14px;color:#1a1a1a;background:#f8f9fa;outline:none;resize:vertical;box-sizing:border-box;">{{ $page->content }}</textarea>
            </div>
        </div>
        <div style="padding:16px 24px;border-top:1px solid #e8e8e8;display:flex;justify-content:flex-end;gap:12px;">
            <a href="{{ route('adminweb.pages') }}" style="display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border:none;border-radius:999px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;font-family:inherit;background-color:#d9d9d9;color:#2b2b2b;transition:opacity 0.2s;">Batal</a>
            <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:10px 24px;border:none;border-radius:999px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;background-color:#16423c;color:#fff;transition:opacity 0.2s;">
                <span class="material-icons" style="font-size:18px;">save</span>
                Simpan
            </button>
        </div>
    </form>
</div>
@endsection
