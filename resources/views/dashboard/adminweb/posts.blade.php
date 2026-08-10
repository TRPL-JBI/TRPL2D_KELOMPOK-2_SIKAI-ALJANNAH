@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'WhatsApp', 'url' => '/adminweb/whatsapp', 'active' => 'adminweb/whatsapp*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@section('title', 'Posts')

@section('content')
<style>
    .posts-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .posts-title {
        font-size: 24px;
        font-weight: 700;
        color: #16423c;
    }

    .posts-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .posts-search {
        position: relative;
        width: 320px;
    }

    .posts-search .material-icons {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #2b2b2b;
        font-size: 20px;
        pointer-events: none;
    }

    .posts-search input {
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

    .posts-search input::placeholder {
        color: #575757;
    }

    .btn-posts {
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

    .btn-posts:hover {
        opacity: 0.85;
    }

    .btn-posts-primary {
        background-color: #16423c;
        color: #fff;
    }

    .posts-table-wrap {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        background: #fff;
    }

    .posts-table {
        width: 100%;
        border-collapse: collapse;
    }

    .posts-table th {
        background-color: #16423c;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        text-align: left;
        padding: 14px 16px;
        white-space: nowrap;
    }

    .posts-table td {
        padding: 14px 16px;
        font-size: 13px;
        color: #2b2b2b;
        border-bottom: 1px solid #efefef;
        vertical-align: middle;
    }

    .posts-table tbody tr:hover {
        background-color: #f7f7f7;
    }

    .posts-table tbody tr:last-child td {
        border-bottom: none;
    }

    .posts-thumb {
        width: 56px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        background-color: #e7e7e7;
        display: block;
    }

    .posts-thumb-placeholder {
        width: 56px;
        height: 40px;
        border-radius: 8px;
        background-color: #e7e7e7;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #828282;
        font-size: 20px;
    }

    .tag-category {
        display: inline-block;
        padding: 4px 14px;
        background-color: #16423c;
        color: #fff;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
    }

    .posts-actions {
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

    .btn-action-secondary {
        background-color: #d9d9d9;
        color: #2b2b2b;
    }

    .btn-action-success {
        background-color: #35ab50;
        color: #fff;
    }

    @media (max-width: 768px) {
        .posts-search {
            width: 100%;
        }
        .posts-table-wrap {
            overflow-x: auto;
        }
    }
</style>

<div class="posts-header">
    <h1 class="posts-title">Posts</h1>
    <div class="posts-toolbar">
        <div class="posts-search">
            <span class="material-icons">search</span>
            <form method="GET" action="{{ route('adminweb.posts') }}" style="margin:0;">
                <input type="text" name="search" placeholder="Cari postingan..." value="{{ request('search') }}" onchange="this.form.submit()">
            </form>
        </div>
        <a href="{{ route('adminweb.posts.create') }}" class="btn-posts btn-posts-primary">
            <span class="material-icons" style="font-size:18px;">add</span>
            Buat Postingan
        </a>
        <a href="#" class="btn-posts btn-posts-primary" onclick="event.preventDefault(); document.getElementById('modalKategori').classList.add('active');">
            <span class="material-icons" style="font-size:18px;">add</span>
            Buat Kategori
        </a>
    </div>
</div>

<div class="posts-table-wrap">
    <table class="posts-table">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Thumbnail</th>
                <th>Judul</th>
                <th>Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($posts as $post)
            <tr>
                <td>{{ $post->created_at->format('d/m/Y') }}</td>
                <td>
                    @if($post->getThumbnailUrl())
                        <img src="{{ $post->getThumbnailUrl() }}" alt="{{ $post->title }}" class="posts-thumb">
                    @else
                        <div class="posts-thumb-placeholder">
                            <span class="material-icons">image</span>
                        </div>
                    @endif
                </td>
                <td style="font-weight:600;">{{ $post->title }}</td>
                <td>
                    @if($post->category)
                        <span class="tag-category">{{ $post->category->name }}</span>
                    @else
                        <span style="color:#828282;font-size:12px;">-</span>
                    @endif
                </td>
                <td>
                    <div class="posts-actions">
                        <a href="{{ route('adminweb.posts.edit', $post->id) }}" class="btn-action btn-action-edit">
                            <span class="material-icons" style="font-size:14px;">edit</span>
                            Edit
                        </a>
                        <form method="POST" action="{{ route('adminweb.posts.destroy', $post->id) }}" style="display:inline;" onsubmit="return confirm('Hapus postingan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action-danger">
                                <span class="material-icons" style="font-size:14px;">delete</span>
                                Hapus
                            </button>
                        </form>
                        @if($post->status === 'published')
                            <span class="btn-action btn-action-secondary">
                                Diterapkan
                            </span>
                        @else
                            <form method="POST" action="{{ route('adminweb.posts.publish', $post->id) }}" style="display:inline;">
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
                <td colspan="5" style="text-align:center;padding:40px 16px;color:#828282;">
                    <span class="material-icons" style="font-size:40px;display:block;margin-bottom:8px;">article</span>
                    Belum ada postingan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($posts->hasPages())
    <div style="padding:16px;border-top:1px solid #efefef;">
        {{ $posts->links() }}
    </div>
    @endif
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

<div class="modal-overlay" id="modalKategori">
    <div class="modal" style="max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
        <div class="modal-header">
            <h3 class="modal-title">Buat Kategori Baru</h3>
            <button class="modal-close" onclick="document.getElementById('modalKategori').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" action="{{ route('adminweb.kategori.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="namaKategori" class="form-label">Nama Kategori</label>
                    <input type="text" id="namaKategori" name="name" class="form-input" placeholder="Masukkan nama kategori" required maxlength="255" value="{{ old('name') }}">
                    @error('name')
                        <p style="color:#9a0000;font-size:13px;margin-top:6px;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('modalKategori').classList.remove('active')" style="font-weight:600;">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight:600;">Simpan</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>
    document.getElementById('modalKategori').classList.add('active');
</script>
@endif

<script>
    document.getElementById('modalKategori').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
</script>
@endsection
