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
            <p style="font-size: 14px; color: #6b7280; margin-top: 6px;">Rekam jejak lengkap setiap aksi dengan IP dan timestamp</p>
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
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">TOTAL LOG</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--primary-500);">{{ $logs->total() }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">LOGIN</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--success-500);">-</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">UPLOAD/DOWNLOAD</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--info-500);">-</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">ADMIN ACTION</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--warning-500);">-</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; margin-bottom: 4px;">KEAMANAN</div>
            <div style="font-size: 20px; font-weight: 800; color: var(--danger-500);">-</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card" style="padding: 16px; margin-bottom: 16px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <span class="material-icons" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px; pointer-events: none;">search</span>
            <input type="text" name="search" placeholder="Cari berdasarkan user, aksi, atau IP..." class="form-input" style="padding-left: 40px; width: 100%;" value="{{ request('search') }}">
        </div>
        <select class="form-select" style="min-width: 160px; cursor: pointer;">
            <option value="">Semua Aksi</option>
            <option value="login">Login</option>
            <option value="logout">Logout</option>
            <option value="upload">Upload</option>
            <option value="download">Download</option>
            <option value="create">Create User</option>
            <option value="update">Update</option>
            <option value="delete">Delete</option>
        </select>
        <select class="form-select" style="min-width: 140px; cursor: pointer;">
            <option value="">Semua Waktu</option>
            <option value="today">Hari Ini</option>
            <option value="yesterday">Kemarin</option>
            <option value="week">7 Hari</option>
        </select>
        <span style="font-size: 12px; color: #6b7280; margin-left: auto;">{{ $logs->total() }} hasil</span>
    </div>

    {{-- Logs Table --}}
    <x-dashboard.data-table
        title="Daftar Log"
        :headers="['Waktu', 'User', 'Aksi', 'IP Address']"
        :paginator="$logs"
        search-placeholder="Cari log..."
    >
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $log->user->nama ?? 'Tidak diketahui' }}</td>
                <td>
                    {{ $log->deskripsi }}
                </td>
                <td>
                    <code style="background-color: #f0f0f0; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem;">
                        {{ $log->ip_address ?? '-' }}
                    </code>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; color: var(--secondary-600); padding: 40px 16px;">
                    Tidak ada log ditemukan.
                </td>
            </tr>
        @endforelse
    </x-dashboard.data-table>
@endsection
