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
            <p style="font-size: 14px; color: var(--primary-500); margin-top: 6px;">Kelola dan unduh file yang tersimpan di sistem</p>
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
    @php
        $totalSizeFormatted = $totalSize >= 1073741824
            ? number_format($totalSize / 1073741824, 2) . ' GB'
            : ($totalSize >= 1048576 ? number_format($totalSize / 1048576, 2) . ' MB' : number_format($totalSize / 1024, 1) . ' KB');
    @endphp
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">TOTAL UKURAN</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">{{ $totalSizeFormatted }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">KUOTA TERPAKAI</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">{{ $totalSizeFormatted }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">TOTAL FILE</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">{{ $totalFiles }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">UNDUHAN BULAN INI</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">-</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('superadmin.file') }}">
        <div class="card" style="padding: 16px; margin-bottom: 16px; display: grid; grid-template-columns: 1fr 150px 150px auto; gap: 12px; align-items: center;">
            <div style="position: relative;">
                <span class="material-icons" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px; pointer-events: none;">search</span>
                <input type="text" name="search" placeholder="Cari nama file..." class="form-input" style="padding-left: 40px; width: 100%;" value="{{ request('search') }}">
            </div>
            <select name="type" class="form-select" style="cursor: pointer;">
                <option value="">Semua Tipe</option>
                <option value="pdf" {{ request('type') == 'pdf' ? 'selected' : '' }}>PDF</option>
                <option value="doc" {{ request('type') == 'doc' ? 'selected' : '' }}>Dokumen</option>
                <option value="xls" {{ request('type') == 'xls' ? 'selected' : '' }}>Spreadsheet</option>
                <option value="img" {{ request('type') == 'img' ? 'selected' : '' }}>Gambar</option>
                <option value="vid" {{ request('type') == 'vid' ? 'selected' : '' }}>Video</option>
                <option value="zip" {{ request('type') == 'zip' ? 'selected' : '' }}>Arsip</option>
                <option value="lainnya" {{ request('type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
            </select>
            <select name="sort" class="form-select" style="cursor: pointer;">
                <option value="date-desc" {{ request('sort') == 'date-desc' ? 'selected' : '' }}>Terbaru</option>
                <option value="date-asc" {{ request('sort') == 'date-asc' ? 'selected' : '' }}>Terlama</option>
                <option value="size-desc" {{ request('sort') == 'size-desc' ? 'selected' : '' }}>Ukuran (Besar)</option>
                <option value="size-asc" {{ request('sort') == 'size-asc' ? 'selected' : '' }}>Ukuran (Kecil)</option>
                <option value="name-asc" {{ request('sort') == 'name-asc' ? 'selected' : '' }}>Nama (A-Z)</option>
            </select>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-icons" style="font-size: 14px;">search</span> Cari
                </button>
                <a href="{{ route('superadmin.file') }}" class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                    <span class="material-icons" style="font-size: 14px;">refresh</span> Reset
                </a>
            </div>
        </div>
    </form>

    {{-- File Table --}}
    <x-dashboard.data-table
        title="Daftar File"
        :headers="['Nomor', 'Nama File', 'Tipe', 'Ukuran', 'Terakhir Diubah', 'Aksi']"
        :paginator="$files"
        :searchable="false"
    >
        @forelse($files as $index => $file)
            @php
                $sizeFormatted = $file->size >= 1048576
                    ? number_format($file->size / 1048576, 2) . ' MB'
                    : ($file->size >= 1024 ? number_format($file->size / 1024, 1) . ' KB' : $file->size . ' B');
                $typeLabels = ['pdf' => 'PDF', 'doc' => 'Dokumen', 'xls' => 'Spreadsheet', 'img' => 'Gambar', 'vid' => 'Video', 'zip' => 'Arsip', 'lainnya' => 'Lainnya'];
                $typeBadge = ['pdf' => 'badge-danger', 'doc' => 'badge-info', 'xls' => 'badge-success', 'img' => 'badge-warning', 'vid' => 'badge-info', 'zip' => 'badge-info', 'lainnya' => 'badge-info'];
            @endphp
            <tr>
                <td>{{ $files->firstItem() + $index }}</td>
                <td style="font-weight: 600; color: #1f2937;">{{ $file->nama_file }}</td>
                <td>
                    <span class="badge {{ $typeBadge[$file->kategori] ?? 'badge-info' }}">{{ $typeLabels[$file->kategori] ?? ucfirst($file->kategori) }}</span>
                </td>
                <td style="white-space: nowrap;">{{ $sizeFormatted }}</td>
                <td style="color: var(--primary-500);">{{ $file->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn btn-sm btn-outline-primary" onclick="openFileInfoModal('{{ escapeJs($file->nama_file) }}', '{{ escapeJs($typeLabels[$file->kategori] ?? $file->kategori) }}', '{{ $sizeFormatted }}', '{{ escapeJs($file->mime_type) }}', '{{ escapeJs($file->extension) }}', '{{ $file->created_at->format('d/m/Y H:i') }}', '{{ escapeJs($file->file_path) }}')" title="Info File">
                            <span class="material-icons" style="font-size: 16px;">info</span>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="text-align: center; color: var(--primary-500); padding: 40px 16px;">
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

        function openFileInfoModal(nama, tipe, ukuran, mime, ext, tanggal, path) {
            var rows = [
                { label: 'Nama File', value: nama },
                { label: 'Tipe', value: tipe },
                { label: 'Ekstensi', value: ext || '-' },
                { label: 'Ukuran', value: ukuran },
                { label: 'Tipe MIME', value: mime },
                { label: 'Tanggal Diubah', value: tanggal },
                { label: 'Lokasi', value: path },
            ];

            var html = '';
            for (var i = 0; i < rows.length; i++) {
                var bg = i % 2 === 0 ? '#f9fafb' : '#ffffff';
                html += '<tr style="background:' + bg + ';">';
                html += '<td style="padding:10px 16px;font-size:12px;font-weight:600;color:#374151;white-space:nowrap;border-bottom:1px solid #e5e7eb;width:140px;">' + rows[i].label + '</td>';
                html += '<td style="padding:10px 16px;font-size:13px;color:var(--primary-500);border-bottom:1px solid #e5e7eb;word-break:break-all;">' + rows[i].value + '</td>';
                html += '</tr>';
            }

            document.getElementById('fileInfoBody').innerHTML = html;
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
