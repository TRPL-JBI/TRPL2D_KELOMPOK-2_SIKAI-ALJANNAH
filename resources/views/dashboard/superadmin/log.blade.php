@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/superadmin', 'active' => 'superadmin'],
        ['label' => 'Manajemen User', 'url' => '/superadmin/user', 'active' => 'superadmin/user'],
        ['label' => 'Storage File', 'url' => '/superadmin/file', 'active' => 'superadmin/file'],
        ['label' => 'Log Aktivitas', 'url' => '/superadmin/log', 'active' => 'superadmin/log'],
    ]
])

@section('title', 'Log Superadmin')

@section('content')
    @if(session('success'))
        <div style="background-color: #d8efdd; border: 1px solid #35ab50; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-family: 'Segoe UI', sans-serif; font-size: 0.95rem; color: #154420;">
            <span class="material-icons" style="color: #35ab50; font-size: 20px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-400);">Audit Trail</span>
            <h1 style="font-size: 28px; font-weight: 800; color: var(--primary-500); margin: 4px 0 0 0;">Log Aktivitas Sistem</h1>
            <p style="font-size: 14px; color: var(--primary-500); margin-top: 6px;">Rekam jejak lengkap setiap aksi dengan IP dan timestamp</p>
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-icons" style="font-size: 16px;">delete_sweep</span> Bersihkan Log
            </button>
            <button class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <span class="material-icons" style="font-size: 16px;">file_download</span> Export CSV
            </button>
        </div>
    </div>

    {{-- Log Stats Cards --}}
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">TOTAL LOG</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">{{ $totalLogs }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">LOGIN</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--success-500);">{{ $loginLogs }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">UPLOAD/DOWNLOAD</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--info-500);">{{ $uploadDownloadLogs }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">ADMIN ACTION</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--warning-500);">{{ $adminActionLogs }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500); margin-bottom: 4px;">KEAMANAN</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--danger-500);">{{ $securityLogs }}</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('superadmin.log') }}">
        <div class="card" style="padding: 16px; margin-bottom: 16px; display: grid; grid-template-columns: 1fr 150px 150px auto; gap: 12px; align-items: center;">
            <div style="position: relative;">
                <span class="material-icons" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px; pointer-events: none;">search</span>
                <input type="text" name="search" placeholder="Cari berdasarkan user, aksi, atau IP..." class="form-input" style="padding-left: 40px; width: 100%;" value="{{ request('search') }}">
            </div>
            <select name="action" class="form-select" style="cursor: pointer;">
                <option value="">Semua Aksi</option>
                <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                <option value="upload" {{ request('action') == 'upload' ? 'selected' : '' }}>Upload</option>
                <option value="download" {{ request('action') == 'download' ? 'selected' : '' }}>Download</option>
                <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create User</option>
                <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
            </select>
            <select name="time" class="form-select" style="cursor: pointer;">
                <option value="">Semua Waktu</option>
                <option value="today" {{ request('time') == 'today' ? 'selected' : '' }}>Hari Ini</option>
                <option value="yesterday" {{ request('time') == 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                <option value="week" {{ request('time') == 'week' ? 'selected' : '' }}>7 Hari</option>
            </select>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-sm btn-primary" style="display: inline-flex; align-items: center; gap: 4px;">
                    <span class="material-icons" style="font-size: 14px;">search</span> Cari
                </button>
                <a href="{{ route('superadmin.log') }}" class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 4px; text-decoration: none;">
                    <span class="material-icons" style="font-size: 14px;">refresh</span> Reset
                </a>
            </div>
        </div>
    </form>

    {{-- Logs Table --}}
    <x-dashboard.data-table
        title="Daftar Log"
        :headers="['Waktu', 'User', 'Aksi', 'IP Address']"
        :paginator="$logs"
        :searchable="false"
    >
        @forelse($logs as $log)
            <tr>
                <td style="white-space: nowrap;">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td style="font-weight: 600; color: #1f2937;">{{ $log->user->nama ?? 'Tidak diketahui' }}</td>
                <td style="color: var(--primary-500);">{{ $log->deskripsi }}</td>
                <td>
                    <code style="background-color: #f0f0f0; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">
                        {{ $log->ip_address ?? '-' }}
                    </code>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; color: var(--primary-500); padding: 40px 16px;">
                    Tidak ada log ditemukan.
                </td>
            </tr>
        @endforelse
    </x-dashboard.data-table>
@endsection
