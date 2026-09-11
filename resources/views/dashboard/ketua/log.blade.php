@extends('layouts.dashboard')
@php
$menuItems = [
    ['label' => 'Beranda', 'url' => route('ketua.dashboard'), 'active' => 'ketua'],
    ['label' => 'Data Anggota', 'url' => route('ketua.anggota'), 'active' => 'ketua/anggota*'],
    ['label' => 'Keuangan', 'url' => route('ketua.keuangan'), 'active' => 'ketua/keuangan*'],
    ['label' => 'Aset Organisasi', 'url' => route('ketua.aset'), 'active' => 'ketua/aset*'],
    ['label' => 'Log Aktivitas', 'url' => route('ketua.log'), 'active' => 'ketua/log*'],
    ['label' => 'Permintaan Izin', 'url' => route('ketua.izin'), 'active' => 'ketua/izin*'],
];
@endphp
@section('title', 'Log Aktivitas')
@push('styles')
<style>
body { background-color: #dbe7e4; }
.sidebar { background-color: #0d4f46 !important; }
.content-wrapper { padding: 28px 32px; }
.kt-top-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.kt-page-title { font-size: 24px; font-weight: 700; color: #1f5c52; margin: 0; }
.kt-top-right { display: flex; align-items: center; gap: 14px; padding-top: 4px; flex-shrink: 0; }
.kt-btn-refresh { width: 30px; height: 30px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; padding: 0; }
.kt-btn-refresh:hover { background: #f3f4f6; }
.kt-btn-refresh .material-symbols-outlined { font-size: 16px; color: #6b7280; }
.kt-btn-refresh.spinning .material-symbols-outlined { animation: kt-spin 0.6s ease-in-out; }
@keyframes kt-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.kt-last-update { font-size: 11px; color: #9ca3af; font-weight: 400; white-space: nowrap; }
.kt-card { background: #fff; border-radius: 16px; border: 1px solid #1f5c52; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; }
.kt-card-header { background: #1f5c52; padding: 14px 22px; }
.kt-card-header h2 { font-size: 15px; font-weight: 600; color: #fff; margin: 0; letter-spacing: 0.3px; }
.kt-card-body { padding: 18px 22px; }
.kt-search-box { display: flex; align-items: center; gap: 8px; border: 1px solid #d1d5db; border-radius: 24px; padding: 8px 16px; background: #fff; max-width: 320px; transition: border-color 0.2s; margin-bottom: 18px; }
.kt-search-box:focus-within { border-color: #1f5c52; }
.kt-search-box .material-symbols-outlined { font-size: 18px; color: #9ca3af; }
.kt-search-box input { border: none; outline: none; font-size: 13px; font-family: 'Poppins', sans-serif; color: #374151; width: 100%; background: transparent; }
.kt-search-box input::placeholder { color: #9ca3af; }
.kt-table-wrap { overflow-x: auto; }
.kt-table { width: 100%; border-collapse: collapse; }
.kt-table thead th { padding: 11px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #6b7280; border-bottom: 2px solid #e5e7eb; letter-spacing: 0.2px; white-space: nowrap; background: #f9fafb; }
.kt-table tbody td { padding: 12px 16px; font-size: 12px; color: #374151; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.kt-table tbody tr:last-child td { border-bottom: none; }
.kt-table tbody tr:hover { background: #f9fafb; }
.kt-table tbody tr { transition: background 0.15s; }
.kt-badge { display: inline-block; padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.kt-badge.hijau { background: #d1fae5; color: #065f46; }
.kt-badge.orange { background: #fed7aa; color: #9a3412; }
.kt-badge.hijau-tua { background: #dbeafe; color: #1e40af; }
.kt-badge.ungu { background: #ede9fe; color: #5b21b6; }
.kt-badge.biru { background: #e0f2fe; color: #0369a1; }
.kt-badge.abu { background: #f3f4f6; color: #374151; }
.kt-filter-bar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 18px; }
.kt-filter-bar .kt-search-box { margin-bottom: 0; flex: 1; min-width: 200px; max-width: none; }
.kt-filter-select { border: 1px solid #d1d5db; border-radius: 24px; padding: 8px 16px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #374151; background: #fff; outline: none; cursor: pointer; transition: border-color 0.2s; appearance: auto; }
.kt-filter-select:focus { border-color: #1f5c52; }
.kt-filter-btn { display: inline-flex; align-items: center; gap: 5px; border-radius: 24px; padding: 8px 18px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.2s; border: none; text-decoration: none; }
.kt-filter-btn .material-symbols-outlined { font-size: 16px; }
.kt-filter-btn-primary { background: #1f5c52; color: #fff; }
.kt-filter-btn-primary:hover { background: #0d4f46; }
.kt-filter-btn-outline { background: #fff; color: #1f5c52; border: 1.5px solid #1f5c52; }
.kt-filter-btn-outline:hover { background: #f0fdf4; }
.kt-filter-active { display: inline-flex; align-items: center; gap: 5px; background: #dbeafe; color: #1e40af; border-radius: 20px; padding: 4px 12px; font-size: 11px; font-weight: 600; }
.kt-filter-active .material-symbols-outlined { font-size: 14px; cursor: pointer; }
.kt-filter-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
.kt-pagination { display: flex; justify-content: center; align-items: center; gap: 6px; margin-top: 20px; }
.kt-pagination a, .kt-pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; border-radius: 8px; font-size: 12px; font-weight: 600; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; }
.kt-pagination a { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.kt-pagination a:hover { background: #f0fdf4; border-color: #1f5c52; color: #1f5c52; }
.kt-pagination span.current { background: #1f5c52; color: #fff; border: 1px solid #1f5c52; }
.kt-pagination span.disabled { background: #f9fafb; color: #d1d5db; border: 1px solid #e5e7eb; cursor: not-allowed; }
.kt-table-empty { text-align: center; padding: 48px 16px; color: #9ca3af; font-size: 13px; display: none; }
.kt-table-empty.show { display: table-row; }
@media (max-width: 768px) { .kt-top-row { flex-direction: column; gap: 12px; } .kt-search-box { max-width: 100%; } .content-wrapper { padding: 20px 16px; } }
</style>
@endpush
@section('content')
<div class="kt-top-row">
    <h1 class="kt-page-title">Log Aktivitas</h1>
    <div class="kt-top-right">
        <button class="kt-btn-refresh" id="refreshBtn" title="Refresh"><span class="material-symbols-outlined">refresh</span></button>
        <span class="kt-last-update" id="lastUpdate">Terakhir : --:--:--</span>
    </div>
</div>
<div class="kt-card">
    <div class="kt-card-header"><h2>Daftar Log</h2></div>
    <div class="kt-card-body">
        <form method="GET" action="{{ route('ketua.log') }}" id="filterForm">
            <div class="kt-filter-bar">
                <div class="kt-search-box"><span class="material-symbols-outlined">search</span><input type="text" name="search" placeholder="Cari log..." value="{{ request('search') }}"></div>
                <select name="role" class="kt-filter-select">
                    <option value="">Semua Role</option>
                    <option value="ketua" {{ request('role') == 'ketua' ? 'selected' : '' }}>Ketua</option>
                    <option value="sekretaris" {{ request('role') == 'sekretaris' ? 'selected' : '' }}>Sekretaris</option>
                    <option value="bendahara" {{ request('role') == 'bendahara' ? 'selected' : '' }}>Bendahara</option>
                    <option value="logistik" {{ request('role') == 'logistik' ? 'selected' : '' }}>Logistik</option>
                    <option value="adminweb" {{ request('role') == 'adminweb' ? 'selected' : '' }}>Admin Web</option>
                </select>
                <select name="modul" class="kt-filter-select">
                    <option value="">Semua Modul</option>
                    @foreach($modulList as $m)
                        <option value="{{ $m }}" {{ request('modul') == $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $m)) }}</option>
                    @endforeach
                </select>
                <select name="waktu" class="kt-filter-select">
                    <option value="">Semua Waktu</option>
                    <option value="hari" {{ request('waktu') == 'hari' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="kemarin" {{ request('waktu') == 'kemarin' ? 'selected' : '' }}>Kemarin</option>
                    <option value="7_hari" {{ request('waktu') == '7_hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30_hari" {{ request('waktu') == '30_hari' ? 'selected' : '' }}>30 Hari Terakhir</option>
                </select>
                <button type="submit" class="kt-filter-btn kt-filter-btn-primary"><span class="material-symbols-outlined">search</span> Cari</button>
                <a href="{{ route('ketua.log') }}" class="kt-filter-btn kt-filter-btn-outline"><span class="material-symbols-outlined">refresh</span> Reset</a>
            </div>
        </form>
        @if(request()->hasAny(['search', 'role', 'modul', 'waktu']))
            <div class="kt-filter-tags">
                @if(request('search'))
                    <span class="kt-filter-active">Pencarian: "{{ request('search') }}" <span class="material-symbols-outlined" onclick="removeFilter('search')">close</span></span>
                @endif
                @if(request('role'))
                    <span class="kt-filter-active">Role: {{ ucfirst(request('role')) }} <span class="material-symbols-outlined" onclick="removeFilter('role')">close</span></span>
                @endif
                @if(request('modul'))
                    <span class="kt-filter-active">Modul: {{ ucfirst(str_replace('_', ' ', request('modul'))) }} <span class="material-symbols-outlined" onclick="removeFilter('modul')">close</span></span>
                @endif
                @if(request('waktu'))
                    <span class="kt-filter-active">Waktu: {{ str_replace('_', ' ', request('waktu')) }} <span class="material-symbols-outlined" onclick="removeFilter('waktu')">close</span></span>
                @endif
            </div>
        @endif
        <div class="kt-table-wrap"><table class="kt-table" id="logTable"><thead><tr><th>Waktu</th><th>User</th><th>Role</th><th>Modul</th><th>Aksi</th></tr></thead><tbody id="logTbody">@forelse($logs as $l)<tr><td>{{ $l->created_at->format('d/m/Y H:i') }}</td><td>{{ $l->user_nama }}</td><td><span class="kt-badge {{ $l->user_role === 'ketua' ? 'hijau' : ($l->user_role === 'bendahara' ? 'orange' : ($l->user_role === 'sekretaris' ? 'hijau-tua' : ($l->user_role === 'logistik' ? 'ungu' : ($l->user_role === 'adminweb' ? 'biru' : 'abu')))) }}">{{ ucfirst($l->user_role ?? '-') }}</span></td><td>{{ $l->modul ?? '-' }}</td><td><span class="kt-badge hijau">{{ $l->deskripsi }}</span></td></tr>@empty<tr class="kt-table-empty show" id="logEmpty"><td colspan="5">Tidak ada log yang ditemukan</td></tr>@endforelse</tbody></table></div>
        @if($logs->hasPages())
        <div class="kt-pagination">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
@push('scripts')
<script>
function removeFilter(key) {
    var url = new URL(window.location);
    url.searchParams.delete(key);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
(function(){
    var b = document.getElementById('refreshBtn');
    var l = document.getElementById('lastUpdate');
    function u() {
        var n = new Date();
        l.textContent = 'Terakhir : ' + String(n.getHours()).padStart(2,'0') + '.' + String(n.getMinutes()).padStart(2,'0') + '.' + String(n.getSeconds()).padStart(2,'0');
    }
    if (b) b.addEventListener('click', function() {
        b.classList.remove('spinning');
        void b.offsetWidth;
        b.classList.add('spinning');
        u();
        setTimeout(function() { b.classList.remove('spinning'); }, 600);
    });
    u();
})();
</script>
@endpush
