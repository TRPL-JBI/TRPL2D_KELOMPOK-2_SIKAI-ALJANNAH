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

    .btn-action-share {
        background-color: #25d366;
        color: #fff;
    }

    /* Share Modal */
    .share-modal-overlay {
        position: fixed; inset: 0; z-index: 9999;
        display: flex; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.45); opacity: 0; visibility: hidden;
        transition: opacity .25s, visibility .25s;
    }
    .share-modal-overlay.active { opacity: 1; visibility: visible; }
    .share-modal-box {
        background: #fff; border-radius: 16px; width: 90%; max-width: 500px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.25); overflow: hidden;
    }
    .share-modal-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 22px; border-bottom: 1px solid #efefef;
    }
    .share-modal-header h3 { font-size: 17px; font-weight: 700; color: #16423c; margin: 0; }
    .share-modal-close {
        background: none; border: none; font-size: 22px; cursor: pointer; color: #828282; padding: 0 4px;
    }
    .share-modal-body { padding: 22px; }
    .share-option {
        display: flex; align-items: center; gap: 16px; padding: 16px;
        border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer;
        transition: border-color .2s, background .2s; margin-bottom: 12px;
    }
    .share-option:last-child { margin-bottom: 0; }
    .share-option:hover { border-color: #25d366; background: #f0fdf4; }
    .share-option-icon {
        width: 48px; height: 48px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .share-option-icon.link { background: #e0e7ff; color: #4f46e5; }
    .share-option-icon.wa { background: #dcfce7; color: #16a34a; }
    .share-option-text h4 { font-size: 14px; font-weight: 600; color: #1f2937; margin: 0 0 2px; }
    .share-option-text p { font-size: 12px; color: #6b7280; margin: 0; }

    /* Member list */
    .member-select-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 12px;
    }
    .member-select-header label { font-size: 13px; font-weight: 600; color: #374151; }
    .member-select-header .count { font-size: 12px; color: #6b7280; }
    .member-search {
        width: 100%; padding: 9px 14px; border: 1.5px solid #d1d5db; border-radius: 10px;
        font-size: 13px; font-family: inherit; outline: none; margin-bottom: 10px;
    }
    .member-search:focus { border-color: #25d366; }
    .member-list {
        max-height: 280px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 10px;
    }
    .member-item {
        display: flex; align-items: center; gap: 10px; padding: 10px 14px;
        border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background .15s;
    }
    .member-item:last-child { border-bottom: none; }
    .member-item:hover { background: #f9fafb; }
    .member-item input[type="checkbox"] { accent-color: #25d366; width: 16px; height: 16px; cursor: pointer; }
    .member-info { flex: 1; min-width: 0; }
    .member-name { font-size: 13px; font-weight: 600; color: #1f2937; }
    .member-phone { font-size: 11px; color: #6b7280; }
    .share-footer { padding: 16px 22px; border-top: 1px solid #efefef; display: flex; gap: 10px; justify-content: flex-end; }
    .btn-share-cancel {
        padding: 9px 20px; border: 1.5px solid #d1d5db; border-radius: 10px;
        background: #fff; font-size: 13px; font-weight: 600; cursor: pointer; font-family: inherit;
    }
    .btn-share-send {
        padding: 9px 20px; border: none; border-radius: 10px;
        background: #25d366; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;
        font-family: inherit; transition: opacity .2s;
    }
    .btn-share-send:disabled { opacity: 0.5; cursor: not-allowed; }
    .btn-share-send:hover:not(:disabled) { opacity: 0.85; }
    .btn-share-copy {
        padding: 9px 20px; border: none; border-radius: 10px;
        background: #4f46e5; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;
        font-family: inherit; transition: opacity .2s; width: 100%;
    }
    .btn-share-copy:hover { opacity: 0.85; }
    .copy-link-box {
        display: flex; gap: 8px; align-items: center; margin-bottom: 16px;
    }
    .copy-link-input {
        flex: 1; padding: 9px 14px; border: 1.5px solid #d1d5db; border-radius: 10px;
        font-size: 13px; font-family: inherit; color: #374151; background: #f9fafb; outline: none;
    }
    .btn-copy-link {
        padding: 9px 16px; border: none; border-radius: 10px;
        background: #4f46e5; color: #fff; font-size: 13px; font-weight: 600; cursor: pointer;
        font-family: inherit; white-space: nowrap; transition: background .2s;
    }
    .btn-copy-link:hover { background: #4338ca; }
    .share-step { display: none; }
    .share-step.active { display: block; }
    .share-back {
        background: none; border: none; cursor: pointer; color: #6b7280; padding: 0;
        display: inline-flex; align-items: center; gap: 4px; font-size: 13px; font-weight: 500; margin-bottom: 14px;
    }
    .share-back:hover { color: #16423c; }
    .share-loading {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        padding: 30px; color: #6b7280; font-size: 13px;
    }
    .share-toast {
        position: fixed; top: 20px; right: 20px; z-index: 99999;
        background: #d8efdd; border: 1px solid #35ab50; border-radius: 12px;
        padding: 12px 18px; display: none; align-items: center; gap: 8px;
        font-size: 13px; color: #154420; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
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
                        <button type="button" class="btn-action btn-action-share" onclick="openShareModal({{ $post->id }}, '{{ addslashes($post->title) }}', '{{ url('/berita/' . $post->slug) }}')">
                            <span class="material-icons" style="font-size:14px;">share</span>
                            Share
                        </button>
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

<!-- Share Modal -->
<div class="share-modal-overlay" id="modalShare">
    <div class="share-modal-box">
        <!-- Step 1: Choose -->
        <div class="share-step active" id="shareStepChoose">
            <div class="share-modal-header">
                <h3>Bagikan Postingan</h3>
                <button class="share-modal-close" onclick="closeShareModal()">&times;</button>
            </div>
            <div class="share-modal-body">
                <div class="share-option" onclick="showCopyLink()">
                    <div class="share-option-icon link"><span class="material-icons">link</span></div>
                    <div class="share-option-text"><h4>Salin Link</h4><p>Salin URL postingan ke clipboard</p></div>
                </div>
                <div class="share-option" onclick="showWAStep()">
                    <div class="share-option-icon wa"><span class="material-icons">chat</span></div>
                    <div class="share-option-text"><h4>WhatsApp Blast</h4><p>Kirim link ke nomor anggota</p></div>
                </div>
            </div>
        </div>

        <!-- Step 2: Copy Link -->
        <div class="share-step" id="shareStepLink">
            <div class="share-modal-header">
                <h3>Salin Link</h3>
                <button class="share-modal-close" onclick="closeShareModal()">&times;</button>
            </div>
            <div class="share-modal-body">
                <button class="share-back" onclick="backToShareChoose()"><span class="material-icons" style="font-size:18px;">arrow_back</span> Kembali</button>
                <div class="copy-link-box">
                    <input type="text" id="copyLinkInput" class="copy-link-input" readonly>
                    <button class="btn-copy-link" onclick="copyPostLink()">Salin</button>
                </div>
            </div>
        </div>

        <!-- Step 3: WhatsApp Blast -->
        <div class="share-step" id="shareStepWA">
            <div class="share-modal-header">
                <h3>WhatsApp Blast</h3>
                <button class="share-modal-close" onclick="closeShareModal()">&times;</button>
            </div>
            <div class="share-modal-body">
                <button class="share-back" onclick="backToShareChoose()"><span class="material-icons" style="font-size:18px;">arrow_back</span> Kembali</button>
                <div class="member-select-header">
                    <label>Pilih Anggota</label>
                    <span class="count" id="memberCount">0 / 0 dipilih</span>
                </div>
                <input type="text" class="member-search" id="memberSearch" placeholder="Cari nama atau nomor..." oninput="renderMembers(allMembers)">
                <div style="padding:8px 14px;border-bottom:1px solid #f3f4f6;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12px;font-weight:600;color:#374151;">
                        <input type="checkbox" onchange="toggleAllMembers(this)" style="accent-color:#25d366;width:15px;height:15px;"> Pilih Semua
                    </label>
                </div>
                <div class="member-list" id="memberList"></div>
            </div>
            <div class="share-footer">
                <button class="btn-share-cancel" onclick="closeShareModal()">Batal</button>
                <button class="btn-share-send" id="btnSendWA" disabled onclick="sendWABlast()">Kirim WhatsApp</button>
            </div>
        </div>
    </div>
</div>

<!-- Share Toast -->
<div class="share-toast" id="shareToast">
    <span class="material-icons" style="color:#35ab50;font-size:18px;">check_circle</span>
    <span></span>
</div>

<script>
    function backToShareChoose() {
        document.getElementById('shareStepLink').classList.remove('active');
        document.getElementById('shareStepWA').classList.remove('active');
        document.getElementById('shareStepChoose').classList.add('active');
    }
</script>

<script>
    document.getElementById('modalKategori').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });

    // ── Share Post Logic ──
    var sharePostId = null, sharePostTitle = '', sharePostUrl = '';

    function openShareModal(id, title, url) {
        sharePostId = id; sharePostTitle = title; sharePostUrl = url;
        document.getElementById('shareStepChoose').classList.add('active');
        document.getElementById('shareStepLink').classList.remove('active');
        document.getElementById('shareStepWA').classList.remove('active');
        document.getElementById('modalShare').classList.add('active');
    }
    function closeShareModal() {
        document.getElementById('modalShare').classList.remove('active');
        sharePostId = null;
    }

    // Copy link
    function showCopyLink() {
        document.getElementById('shareStepChoose').classList.remove('active');
        document.getElementById('shareStepLink').classList.add('active');
        document.getElementById('copyLinkInput').value = sharePostUrl;
    }
    function copyPostLink() {
        var inp = document.getElementById('copyLinkInput');
        inp.select();
        navigator.clipboard.writeText(inp.value).then(function() {
            showShareToast('Link berhasil disalin!');
            closeShareModal();
        }).catch(function() {
            document.execCommand('copy');
            showShareToast('Link berhasil disalin!');
            closeShareModal();
        });
    }

    // WhatsApp Blast
    var allMembers = [];
    function showWAStep() {
        document.getElementById('shareStepChoose').classList.remove('active');
        document.getElementById('shareStepWA').classList.add('active');
        document.getElementById('memberList').innerHTML = '<div class="share-loading"><span class="material-icons" style="animation:spin 1s linear infinite;">sync</span> Memuat data anggota...</div>';
        document.getElementById('modalShare').classList.add('active');

        if (allMembers.length) { renderMembers(allMembers); return; }
        fetch('{{ route("adminweb.posts.members") }}')
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) { allMembers = res.data; renderMembers(allMembers); }
                else { document.getElementById('memberList').innerHTML = '<div class="share-loading">Gagal memuat data</div>'; }
            })
            .catch(function() { document.getElementById('memberList').innerHTML = '<div class="share-loading">Gagal memuat data</div>'; });
    }

    function renderMembers(members) {
        var search = (document.getElementById('memberSearch')?.value || '').toLowerCase();
        var filtered = members.filter(function(m) {
            return m.nama.toLowerCase().includes(search) || m.telepon.includes(search);
        });
        var html = '';
        filtered.forEach(function(m) {
            html += '<label class="member-item"><input type="checkbox" value="' + m.id + '" class="member-cb"><div class="member-info"><div class="member-name">' + m.nama + '</div><div class="member-phone">' + m.telepon + '</div></div></label>';
        });
        if (!filtered.length) html = '<div class="share-loading">Tidak ada anggota ditemukan</div>';
        document.getElementById('memberList').innerHTML = html;
        updateMemberCount();
    }

    function toggleAllMembers(cb) {
        document.querySelectorAll('.member-cb').forEach(function(c) { c.checked = cb.checked; });
        updateMemberCount();
    }
    function updateMemberCount() {
        var total = document.querySelectorAll('.member-cb').length;
        var checked = document.querySelectorAll('.member-cb:checked').length;
        document.getElementById('memberCount').textContent = checked + ' / ' + total + ' dipilih';
        document.getElementById('btnSendWA').disabled = checked === 0;
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('member-cb')) updateMemberCount();
    });

    function sendWABlast() {
        var ids = [];
        document.querySelectorAll('.member-cb:checked').forEach(function(c) { ids.push(parseInt(c.value)); });
        if (!ids.length) return;

        var btn = document.getElementById('btnSendWA');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons" style="font-size:14px;animation:spin 1s linear infinite;">sync</span> Mengirim...';

        fetch('/adminweb/posts/' + sharePostId + '/share-whatsapp', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ member_ids: ids })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            closeShareModal();
            showShareToast(res.message || 'Selesai');
            btn.disabled = false;
            btn.innerHTML = 'Kirim WhatsApp';
        })
        .catch(function(e) {
            closeShareModal();
            showShareToast('Gagal mengirim: ' + e.message);
            btn.disabled = false;
            btn.innerHTML = 'Kirim WhatsApp';
        });
    }

    function showShareToast(msg) {
        var t = document.getElementById('shareToast');
        t.querySelector('span:last-child').textContent = msg;
        t.style.display = 'flex';
        setTimeout(function() { t.style.display = 'none'; }, 3500);
    }

    // Close on backdrop click
    document.getElementById('modalShare').addEventListener('click', function(e) {
        if (e.target === this) closeShareModal();
    });
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>
@endsection
