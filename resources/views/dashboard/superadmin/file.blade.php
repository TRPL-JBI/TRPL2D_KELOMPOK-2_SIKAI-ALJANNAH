@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/superadmin', 'active' => 'superadmin'],
        ['label' => 'Manajemen User', 'url' => '/superadmin/user', 'active' => 'superadmin/user'],
        ['label' => 'Storage File', 'url' => '/superadmin/file', 'active' => 'superadmin/file'],
        ['label' => 'Log Aktivitas', 'url' => '/superadmin/log', 'active' => 'superadmin/log'],
    ]
])

@section('title', 'File Management')

@section('content')
@php
    function escapeJs($s) { return str_replace(["'", "\n", "\r"], ["\\'", "\\n", ""], $s); }
@endphp
    @if(session('success'))
        <div style="background-color: #d8efdd; border: 1px solid #35ab50; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-family: 'Segoe UI', sans-serif; font-size: 0.95rem; color: #154420;">
            <span class="material-icons" style="color: #35ab50; font-size: 20px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background-color: #fde8e8; border: 1px solid #e53e3e; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-family: 'Segoe UI', sans-serif; font-size: 0.95rem; color: #9b1c1c;">
            <span class="material-icons" style="color: #e53e3e; font-size: 20px;">error</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-400);">Storage Management</span>
            <h1 style="font-size: 28px; font-weight: 800; color: var(--primary-500); margin: 4px 0 0 0;">Daftar File Storage</h1>
            <p style="font-size: 14px; color: #6b7280; margin-top: 6px;">Kelola dan unduh file yang tersimpan di sistem</p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <a href="/superadmin/file/trash" style="display: inline-flex; align-items: center; gap: 6px; background: #fef2f2; color: #dc2626; padding: 8px 20px; border-radius: 100px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all .2s;">
                <span class="material-icons" style="font-size: 18px;">delete_sweep</span>
                Tempat Sampah
            </a>
            <button class="btn btn-sm btn-primary" onclick="openUploadModal()" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-icons" style="font-size: 16px;">cloud_upload</span> Upload File
            </button>
        </div>
    </div>

    {{-- Storage Overview Stats --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">TOTAL UKURAN</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">-</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">KUOTA TERPAKAI</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">-</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">TOTAL FILE</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">{{ $files->total() }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">UNDUHAN BULAN INI</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">-</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card" style="padding: 16px; margin-bottom: 16px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <span class="material-icons" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px; pointer-events: none;">search</span>
            <input type="text" name="search" placeholder="Cari nama file..." class="form-input" style="padding-left: 40px; width: 100%;" value="{{ request('search') }}">
        </div>
        <select class="form-select" style="min-width: 140px; cursor: pointer;">
            <option value="">Semua Tipe</option>
            <option value="pdf">PDF</option>
            <option value="doc">Dokumen</option>
            <option value="xls">Spreadsheet</option>
            <option value="img">Gambar</option>
            <option value="vid">Video</option>
            <option value="zip">Arsip</option>
        </select>
        <select class="form-select" style="min-width: 160px; cursor: pointer;">
            <option value="date-desc">Terbaru</option>
            <option value="date-asc">Terlama</option>
            <option value="size-desc">Ukuran (Besar)</option>
            <option value="size-asc">Ukuran (Kecil)</option>
            <option value="name-asc">Nama (A-Z)</option>
        </select>
        <span style="font-size: 12px; color: #6b7280; margin-left: auto;">{{ $files->total() }} hasil</span>
    </div>

    {{-- Category Filter Buttons --}}
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;">
        <a href="{{ route('superadmin.file') }}" class="btn btn-sm {{ !request('kategori') ? 'btn-primary' : 'btn-outline-primary' }}">
            Semua
        </a>
        @foreach(config('file-pemilah.labels') as $key => $label)
            <a href="{{ route('superadmin.file', ['kategori' => $key]) }}"
               class="btn btn-sm {{ request('kategori') === $key ? 'btn-primary' : 'btn-outline-primary' }}">
                {{ $label }}
            </a>
        @endforeach
        <a href="{{ route('superadmin.file', ['kategori' => 'media']) }}"
           class="btn btn-sm {{ request('kategori') === 'media' ? 'btn-primary' : 'btn-outline-primary' }}">
            Media Lain
        </a>
    </div>

    {{-- File Table --}}
    <x-dashboard.data-table
        title="Daftar File"
        :headers="['Nomor', 'Nama File', 'Sumber', 'Kategori', 'Ukuran', 'Tipe', 'Status', 'Diupload Oleh', 'Aksi']"
        :paginator="$files"
        search-placeholder="Cari file..."
    >
        @forelse($files as $index => $file)
            @php
                $sizeFormatted = $file->size >= 1048576
                    ? number_format($file->size / 1048576, 2) . ' MB'
                    : ($file->size >= 1024 ? number_format($file->size / 1024, 1) . ' KB' : $file->size . ' B');
                $extension = pathinfo($file->nama_file, PATHINFO_EXTENSION);
            @endphp
            <tr>
                <td>{{ $files->firstItem() + $index }}</td>
                <td>{{ $file->nama_file }}</td>
                <td><span style="font-size:12px;color:#6b7280;">{{ $file->source_label }}</span></td>
                <td>
                    <span class="badge {{ $file->kategori_badge }}">{{ $file->kategori_label }}</span>
                </td>
                <td style="white-space:nowrap;">{{ $sizeFormatted }}</td>
                <td style="font-size:12px;color:#6b7280;max-width:180px;word-break:break-all;">{{ $file->mime_type }}</td>
                <td>
                    <span class="badge {{ $file->status_badge }}">{{ $file->status_label }}</span>
                </td>
                <td>{{ $file->uploader_nama }}</td>
                <td>
                    <div style="display: flex; gap: 6px;">
                        <a href="{{ $file->download_url }}" class="btn btn-sm btn-outline-primary" title="Download">
                            <span class="material-icons" style="font-size: 16px;">download</span>
                        </a>
                        <button class="btn btn-sm btn-outline-primary" onclick="openFileInfoModal('{{ escapeJs($file->nama_file) }}', '{{ escapeJs($file->kategori_label) }}', '{{ escapeJs($file->status_label) }}', '{{ escapeJs($file->uploader_nama) }}', '{{ $file->created_at?->format('d/m/Y H:i') ?? '-' }}', '{{ $sizeFormatted }}', '{{ escapeJs($file->mime_type) }}', '{{ escapeJs($extension) }}', '{{ $file->media_id ?? '' }}', '{{ escapeJs($file->source_label) }}')" title="Info File">
                            <span class="material-icons" style="font-size: 16px;">info</span>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" style="text-align: center; color: var(--secondary-600); padding: 40px 16px;">
                    Tidak ada file ditemukan.
                </td>
            </tr>
        @endforelse
    </x-dashboard.data-table>

    {{-- Modal Detail File --}}
    <div class="modal-overlay" id="fileInfoModal">
        <div class="modal" style="max-width:520px;">
            <div class="modal-header">
                <h3 class="modal-title">Detail File</h3>
                <button class="modal-close" onclick="closeFileInfoModal()">&times;</button>
            </div>
            <div class="modal-body" style="padding:0;">
                <table style="width:100%;border-collapse:collapse;">
                    <tbody id="fileInfoBody"></tbody>
                </table>
                <div id="filePreviewContainer" style="padding:16px;text-align:center;border-top:1px solid #e5e7eb;display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" onclick="closeFileInfoModal()" style="font-weight:600;">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal Upload File --}}
    <div class="modal-overlay" id="uploadFileModal">
        <div class="modal" style="max-width: 520px;">
            <div class="modal-header">
                <h3 class="modal-title">Upload File Baru</h3>
                <button class="modal-close" onclick="closeUploadModal()">&times;</button>
            </div>
            <form method="POST" action="{{ route('superadmin.file.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file" class="form-label">Pilih File</label>
                        <input type="file" id="file" name="file" class="form-input" required style="padding: 10px;">
                    </div>
                    <div style="background-color: #f7f9fc; border-radius: 8px; padding: 12px 16px; margin-top: 16px; font-size: 0.85rem; color: #4a5568;">
                        <strong style="display: block; margin-bottom: 8px; color: #16423c;">Informasi Pemilahan:</strong>
                        <p style="margin: 4px 0;">
                            <span class="badge badge-success" style="font-size: 11px;">Diterima</span>
                            Dokumen, gambar, dan file teks aman.
                        </p>
                        <p style="margin: 4px 0;">
                            <span class="badge badge-warning" style="font-size: 11px;">Mencurigakan</span>
                            Executable, script, dan arsip.
                        </p>
                        <p style="margin: 4px 0;">
                            <span class="badge badge-danger" style="font-size: 11px;">Ditolak</span>
                            Script web, SQL, dan file sistem.
                        </p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" onclick="closeUploadModal()" style="font-weight: 600;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 600;">
                        <span class="material-icons" style="font-size: 16px; vertical-align: middle;">upload</span> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal() {
            document.getElementById('uploadFileModal').classList.add('active');
        }

        function closeUploadModal() {
            document.getElementById('uploadFileModal').classList.remove('active');
        }

        document.getElementById('uploadFileModal').addEventListener('click', function(e) {
            if (e.target === this) closeUploadModal();
        });

        function openFileInfoModal(nama, kategori, status, uploader, tanggal, ukuran, mime, ext, mediaId, sumber) {
            var rows = [
                { label: 'Nama File', value: nama },
                { label: 'Sumber', value: sumber },
                { label: 'Ekstensi', value: ext || '-' },
                { label: 'Ukuran', value: ukuran },
                { label: 'Tipe MIME', value: mime },
                { label: 'Kategori', value: kategori },
                { label: 'Status', value: status },
                { label: 'Diupload Oleh', value: uploader },
                { label: 'Tanggal Upload', value: tanggal },
                { label: 'Media ID', value: mediaId || '-' },
            ];

            var html = '';
            for (var i = 0; i < rows.length; i++) {
                var bg = i % 2 === 0 ? '#f9fafb' : '#ffffff';
                html += '<tr style="background:' + bg + ';">';
                html += '<td style="padding:10px 16px;font-size:12px;font-weight:600;color:#374151;white-space:nowrap;border-bottom:1px solid #e5e7eb;width:140px;">' + rows[i].label + '</td>';
                html += '<td style="padding:10px 16px;font-size:13px;color:#6b7280;border-bottom:1px solid #e5e7eb;word-break:break-all;">' + rows[i].value + '</td>';
                html += '</tr>';
            }

            document.getElementById('fileInfoBody').innerHTML = html;

            var preview = document.getElementById('filePreviewContainer');
            if (mime.startsWith('image/') && mediaId) {
                preview.innerHTML = '<img src="/spatie-media/' + mediaId + '" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid #e5e7eb;">';
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }

            document.getElementById('fileInfoModal').classList.add('active');
        }

        function closeFileInfoModal() {
            document.getElementById('fileInfoModal').classList.remove('active');
        }

        document.getElementById('fileInfoModal').addEventListener('click', function(e) {
            if (e.target === this) closeFileInfoModal();
        });
    </script>
@endsection
