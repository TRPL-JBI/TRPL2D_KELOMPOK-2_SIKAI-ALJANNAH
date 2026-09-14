@extends('layouts.dashboard')

@section('title', 'Log Aktivitas')

@php
    $menuItems = [
        [
            'label' => 'Beranda',
            'url' => route('sekretaris.dashboard'),
            'active' => 'sekretaris'
        ],
        [
            'label' => 'Kelola Anggota',
            'url' => route('sekretaris.anggota'),
            'active' => 'sekretaris/anggota*'
        ],
        [
            'label' => 'Log Aktivitas',
            'url' => route('sekretaris.log'),
            'active' => 'sekretaris/log*'
        ],
    ];
@endphp

@push('styles')
<style>
    body { background-color: #dcfce7; }
    .sidebar { background-color: #0d4f46 !important; }
    .content-wrapper { padding: 28px 32px; }
    .sk-top-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
    .sk-page-title { font-size: 24px; font-weight: 700; color: #1f5c52; margin: 0; }
    .sk-card { background: #fff; border-radius: 16px; border: 1px solid #1f5c52; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; }
    .sk-card-header { background: #1f5c52; padding: 14px 22px; }
    .sk-card-header h2 { font-size: 15px; font-weight: 600; color: #fff; margin: 0; }
    .sk-card-body { padding: 18px 22px; }
    .sk-search-box { display: flex; align-items: center; gap: 8px; border: 1px solid #d1d5db; border-radius: 24px; padding: 8px 16px; background: #fff; max-width: 360px; transition: border-color 0.2s; }
    .sk-search-box:focus-within { border-color: #1f5c52; }
    .sk-search-box .material-icons { font-size: 18px; color: #9ca3af; }
    .sk-search-box input { border: none; outline: none; font-size: 13px; font-family: 'Poppins', sans-serif; color: #374151; width: 100%; background: transparent; }
    .sk-search-box input::placeholder { color: #9ca3af; }
    .sk-table-wrap { overflow-x: auto; }
    .sk-table { width: 100%; border-collapse: collapse; }
    .sk-table thead th { padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; border-bottom: 2px solid #e5e7eb; background: #f9fafb; white-space: nowrap; }
    .sk-table tbody td { padding: 12px 16px; font-size: 12px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .sk-table tbody tr:last-child td { border-bottom: none; }
    .sk-table tbody tr:hover { background: #f9fafb; }
    .sk-badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .sk-badge-create { background: #d1fae5; color: #065f46; }
    .sk-badge-update { background: #dbeafe; color: #1e40af; }
    .sk-badge-delete { background: #fee2e2; color: #991b1b; }
    .sk-badge-import { background: #ede9fe; color: #5b21b6; }
    .sk-badge-login { background: #d1fae5; color: #065f46; }
    .sk-badge-logout { background: #f3f4f6; color: #374151; }
    .sk-badge-verify { background: #dbeafe; color: #1e40af; }
    .sk-badge-approve { background: #d1fae5; color: #065f46; }
    .sk-badge-reject { background: #fee2e2; color: #991b1b; }
    .sk-badge-default { background: #f3f4f6; color: #374151; }
    .sk-pagination { display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 20px; }
    .sk-pagination a, .sk-pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; border-radius: 8px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; }
    .sk-pagination a { background: #fff; color: #374151; border: 1px solid #d1d5db; }
    .sk-pagination a:hover { background: #f0fdf4; border-color: #1f5c52; color: #1f5c52; }
    .sk-pagination span.current { background: #1f5c52; color: #fff; border: 1px solid #1f5c52; }
    .sk-pagination span.disabled { background: #f9fafb; color: #d1d5db; border: 1px solid #e5e7eb; cursor: not-allowed; }
    @media (max-width: 768px) { .sk-top-row { flex-direction: column; gap: 12px; } .sk-search-box { max-width: 100%; } .content-wrapper { padding: 20px 16px; } }
</style>
@endpush

@section('content')
<div class="sk-top-row">
    <h1 class="sk-page-title">Log Aktivitas</h1>
</div>

<div class="sk-card">
    <div class="sk-card-header"><h2>Daftar Aktivitas</h2></div>
    <div class="sk-card-body">
        <form method="GET" action="{{ route('sekretaris.log') }}" style="margin-bottom: 18px;">
            <div class="sk-search-box">
                <span class="material-icons">search</span>
                <input type="text" name="search" placeholder="Cari aktivitas..." value="{{ $search ?? '' }}">
            </div>
        </form>

        <div class="sk-table-wrap">
            <table class="sk-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        @php
                            $aksi = strtolower($activity->aksi ?? '');
                            $badgeClass = match(true) {
                                str_contains($aksi, 'create') => 'sk-badge-create',
                                str_contains($aksi, 'import') => 'sk-badge-import',
                                str_contains($aksi, 'update') => 'sk-badge-update',
                                str_contains($aksi, 'delete') => 'sk-badge-delete',
                                str_contains($aksi, 'login') => 'sk-badge-login',
                                str_contains($aksi, 'logout') => 'sk-badge-logout',
                                str_contains($aksi, 'verif') => 'sk-badge-verify',
                                str_contains($aksi, 'setuju') => 'sk-badge-approve',
                                str_contains($aksi, 'tolak') => 'sk-badge-reject',
                                default => 'sk-badge-default',
                            };
                            $aksiLabel = match($activity->aksi) {
                                'create' => 'Dibuat',
                                'update' => 'Diperbarui',
                                'delete' => 'Dihapus',
                                'import' => 'Import',
                                'login' => 'Login',
                                'logout' => 'Logout',
                                'verifikasi' => 'Verifikasi',
                                'setuju' => 'Disetujui',
                                'tolak' => 'Ditolak',
                                'nonaktif' => 'Dinonaktifkan',
                                'aktif' => 'Diaktifkan',
                                default => ucfirst(str_replace('_', ' ', $activity->aksi ?? '-')),
                            };
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="sk-badge {{ $badgeClass }}">{{ $aksiLabel }}</span></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $activity->modul ?? '-')) }}</td>
                            <td>{{ $activity->deskripsi ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;padding:48px 16px;color:#9ca3af;font-size:13px;">
                                <span class="material-icons" style="font-size:48px;display:block;margin-bottom:8px;">history</span>
                                Belum ada aktivitas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="sk-pagination">{{ $activities->links() }}</div>
        @endif
    </div>
</div>
@endsection
