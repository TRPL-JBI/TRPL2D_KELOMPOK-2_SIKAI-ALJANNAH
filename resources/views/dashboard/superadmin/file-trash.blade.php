@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/superadmin', 'active' => 'superadmin'],
        ['label' => 'File Manager', 'url' => '/superadmin/file', 'active' => 'superadmin/file'],
        ['label' => 'Tempat Sampah', 'url' => '/superadmin/file/trash', 'active' => 'superadmin/file/trash*'],
        ['label' => 'Log Aktivitas', 'url' => '/superadmin/log', 'active' => 'superadmin/log'],
    ]
])

@section('title', 'Tempat Sampah — File')

@section('content')
<style>
    .page-trash .page-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 28px; flex-wrap: wrap; gap: 12px;
    }
    .page-trash .page-header h1 {
        font-size: 24px; font-weight: 700; color: var(--primary-500); margin: 0;
    }
    .page-trash .page-header p { font-size: 13px; color: var(--secondary-700); margin: 2px 0 0; }
    .page-trash .btn-back {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--primary-500); color: #fff; padding: 10px 22px;
        border-radius: 100px; font-size: 13px; font-weight: 700;
        text-decoration: none; transition: all .2s; font-family: inherit;
    }
    .page-trash .btn-back:hover { opacity: .9; box-shadow: 0 4px 14px rgba(22,66,60,.3); }
    .page-trash .btn-back .material-icons { font-size: 18px; }

    .page-trash .table-card {
        background: #fff; border-radius: 20px;
        border: 2px solid var(--primary-500);
        box-shadow: 0 4px 16px rgba(0,0,0,.05); overflow: hidden;
    }
    .page-trash .table-card table { width: 100%; border-collapse: collapse; }
    .page-trash .table-card thead { background: var(--primary-100); }
    .page-trash .table-card thead th {
        text-align: left; padding: 14px 20px;
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .6px; color: var(--primary-500);
    }
    .page-trash .table-card thead th:first-child { padding-left: 28px; }
    .page-trash .table-card thead th:last-child { padding-right: 28px; }
    .page-trash .table-card tbody tr {
        border-bottom: 1px solid var(--primary-100); transition: background .15s;
    }
    .page-trash .table-card tbody tr:last-child { border-bottom: none; }
    .page-trash .table-card tbody tr:hover { background: #f8faf9; }
    .page-trash .table-card tbody td {
        padding: 14px 20px; color: var(--secondary-800);
        font-size: 13px; vertical-align: middle;
    }
    .page-trash .table-card tbody td:first-child { padding-left: 28px; }
    .page-trash .table-card tbody td:last-child { padding-right: 28px; }

    .page-trash .btn-restore {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 18px; border: none; border-radius: 8px;
        font-size: 12px; font-weight: 700; cursor: pointer;
        text-decoration: none; font-family: inherit; transition: all .2s;
        background: var(--primary-100); color: var(--primary-500);
    }
    .page-trash .btn-restore:hover { background: var(--primary-500); color: #fff; }

    .page-trash .empty-state {
        text-align: center; padding: 60px 20px; color: var(--secondary-700);
    }
    .page-trash .empty-state .material-icons {
        font-size: 48px; color: var(--secondary-400); margin-bottom: 12px;
    }
    .page-trash .empty-state h3 {
        font-size: 16px; font-weight: 600; color: var(--secondary-900); margin-bottom: 6px;
    }
    .page-trash .empty-state p { font-size: 13px; color: var(--secondary-700); }

    .page-trash .deleted-badge {
        display: inline-flex; align-items: center; gap: 4px;
        font-size: 11px; color: #dc2626; font-weight: 600;
    }
    .page-trash .deleted-badge .material-icons { font-size: 14px; }

    @media (max-width: 768px) {
        .page-trash .table-card { border-radius: 14px; overflow-x: auto; }
        .page-trash .table-card thead th, .page-trash .table-card tbody td { padding: 10px 12px; white-space: nowrap; }
        .page-trash .table-card thead th:first-child, .page-trash .table-card tbody td:first-child { padding-left: 16px; }
    }
</style>

<div class="page-trash">
    <div class="page-header">
        <div>
            <h1>Tempat Sampah</h1>
            <p>File yang dihapus oleh AdminWeb — dapat dipulihkan</p>
        </div>
        <a href="/superadmin/file" class="btn-back">
            <span class="material-icons">arrow_back</span>
            File Manager
        </a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Nama File</th>
                    <th>Sumber</th>
                    <th>Ukuran</th>
                    <th>Diupload Oleh</th>
                    <th>Dihapus</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($files as $file)
                <tr>
                    <td><strong>{{ $file->nama_file }}</strong></td>
                    <td>{{ $file->source_label }}</td>
                    <td>{{ $file->size > 0 ? number_format($file->size / 1024, 1) . ' KB' : '-' }}</td>
                    <td>{{ $file->uploader_nama }}</td>
                    <td>
                        <span class="deleted-badge">
                            <span class="material-icons">delete</span>
                            {{ $file->deleted_at ? $file->deleted_at->diffForHumans() : '-' }}
                        </span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.file.restore', $file->id) }}" onsubmit="return confirm('Pulihkan file ini?')">
                            @csrf
                            <button type="submit" class="btn-restore">
                                <span class="material-icons" style="font-size:16px;">restore</span>
                                Pulihkan
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <span class="material-icons">delete_sweep</span>
                            <h3>Tempat Sampah Kosong</h3>
                            <p>Tidak ada file yang dihapus. File akan muncul di sini setelah dihapus dari halaman Files &amp; Images.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
