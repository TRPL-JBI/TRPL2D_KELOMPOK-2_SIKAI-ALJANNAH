@extends('layouts.dashboard')

@section('title', 'Dashboard Sekretaris')

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

@section('content')
    <div class="sekretaris-dashboard">
        @if(session('success'))
            <div class="toast-container" id="toastContainer">
                <div class="toast toast-success">
                    <div class="toast-icon" style="background-color: rgba(53, 171, 80, 0.2); color: #35ab50;">
                        <span class="material-icons">check_circle</span>
                    </div>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast-container" id="toastContainer">
                <div class="toast toast-error">
                    <div class="toast-icon" style="background-color: rgba(154, 0, 0, 0.2); color: #9a0000;">
                        <span class="material-icons">error</span>
                    </div>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <header class="dashboard-header">
            <div>
                <h1 class="dashboard-title">Dashboard Keanggotaan</h1>
                <p class="dashboard-subtitle">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }} — Ringkasan pengelolaan anggota</p>
            </div>
            <div class="dashboard-actions">
                <button class="btn btn-outline-primary" onclick="location.reload()">
                    <span class="material-icons">refresh</span>
                    Segarkan
                </button>
            </div>
        </header>

        <!-- Stats Grid -->
        <section class="stats-grid-new">
            <!-- Total Anggota Aktif -->
            <div class="stat-card-new">
                <div class="stat-card-glow" style="background-color: #16423c;"></div>
                <div class="stat-card-icon" style="background-color: rgba(22, 66, 60, 0.1); color: #16423c;">
                    <span class="material-icons">groups</span>
                </div>
                <div class="stat-card-value">{{ $totalAnggota }}</div>
                <div class="stat-card-label">Total Anggota Aktif</div>
                <div class="stat-card-trend" style="background-color: rgba(53, 171, 80, 0.1); color: #35ab50;">
                    <span class="material-icons">arrow_upward</span>
                    +{{ $diverifikasiBulanIni }} bulan ini
                </div>
            </div>

            <!-- Menunggu Verifikasi -->
            <div class="stat-card-new">
                <div class="stat-card-glow" style="background-color: #ffbb00;"></div>
                <div class="stat-card-icon" style="background-color: rgba(255, 187, 0, 0.1); color: #ffbb00;">
                    <span class="material-icons">pending_actions</span>
                </div>
                <div class="stat-card-value">{{ $menungguVerifikasi }}</div>
                <div class="stat-card-label">Menunggu Verifikasi</div>
                <div class="stat-card-trend" style="background-color: rgba(255, 187, 0, 0.1); color: #ffbb00;">
                    <span class="material-icons">remove</span>
                    {{ $dalamPembayaran }} dalam pembayaran
                </div>
            </div>

            <!-- Diverifikasi Bulan Ini -->
            <div class="stat-card-new">
                <div class="stat-card-glow" style="background-color: #35ab50;"></div>
                <div class="stat-card-icon" style="background-color: rgba(53, 171, 80, 0.1); color: #35ab50;">
                    <span class="material-icons">how_to_reg</span>
                </div>
                <div class="stat-card-value">{{ $diverifikasiBulanIni }}</div>
                <div class="stat-card-label">Diverifikasi Bulan Ini</div>
                <div class="stat-card-trend" style="background-color: rgba(53, 171, 80, 0.1); color: #35ab50;">
                    <span class="material-icons">arrow_upward</span>
                    Aktif
                </div>
            </div>

            <!-- Anggota Non-Aktif -->
            <div class="stat-card-new">
                <div class="stat-card-glow" style="background-color: #9a0000;"></div>
                <div class="stat-card-icon" style="background-color: rgba(154, 0, 0, 0.1); color: #9a0000;">
                    <span class="material-icons">person_off</span>
                </div>
                <div class="stat-card-value">{{ $anggotaNonAktif }}</div>
                <div class="stat-card-label">Anggota Non-Aktif</div>
                <div class="stat-card-trend" style="background-color: rgba(154, 0, 0, 0.1); color: #9a0000;">
                    <span class="material-icons">warning</span>
                    Perhatian
                </div>
            </div>

            <!-- Belum Bayar -->
            <div class="stat-card-new">
                <div class="stat-card-glow" style="background-color: #9a0000;"></div>
                <div class="stat-card-icon" style="background-color: rgba(154, 0, 0, 0.1); color: #9a0000;">
                    <span class="material-icons">lock</span>
                </div>
                <div class="stat-card-value">{{ $belumBayar }}</div>
                <div class="stat-card-label">Belum Bayar Pendaftaran</div>
                <div class="stat-card-trend" style="background-color: rgba(154, 0, 0, 0.1); color: #9a0000;">
                    <span class="material-icons">lock</span>
                    Verifikasi terkunci
                </div>
            </div>

            <!-- Tingkat Persetujuan -->
            <div class="stat-card-new">
                <div class="stat-card-glow" style="background-color: #16423c;"></div>
                <div class="stat-card-icon" style="background-color: rgba(22, 66, 60, 0.1); color: #16423c;">
                    <span class="material-icons">percent</span>
                </div>
                <div class="stat-card-value">{{ $totalAnggota > 0 ? round(($anggotaAktif / $totalAnggota) * 100) : 0 }}%</div>
                <div class="stat-card-label">Tingkat Persetujuan</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $totalAnggota > 0 ? ($anggotaAktif / $totalAnggota) * 100 : 0 }}%; background: linear-gradient(90deg, #16423c, #35ab50);"></div>
                </div>
            </div>
        </section>

        <!-- Quick Stats -->
        <section class="quick-stats">
            <div class="quick-stat">
                <div class="quick-stat-icon" style="background-color: rgba(22, 66, 60, 0.1); color: #16423c;">
                    <span class="material-icons">person_add</span>
                </div>
                <div>
                    <div class="quick-stat-value">{{ $menungguVerifikasi }}</div>
                    <div class="quick-stat-label">Menunggu Verifikasi</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon" style="background-color: rgba(53, 171, 80, 0.1); color: #35ab50;">
                    <span class="material-icons">check_circle</span>
                </div>
                <div>
                    <div class="quick-stat-value">{{ $dalamPembayaran }}</div>
                    <div class="quick-stat-label">Dalam Pembayaran</div>
                </div>
            </div>
            <div class="quick-stat">
                <div class="quick-stat-icon" style="background-color: rgba(255, 187, 0, 0.1); color: #ffbb00;">
                    <span class="material-icons">balance</span>
                </div>
                <div>
                    <div class="quick-stat-value">{{ $belumBayar }}</div>
                    <div class="quick-stat-label">Belum Diverifikasi</div>
                </div>
            </div>
        </section>

        <!-- Table Section -->
        <section class="table-section">
            <div class="table-header">
                <h2>
                    <span class="material-icons" style="color: #16423c;">person_add</span>
                    Daftar Calon Anggota
                </h2>
                <div class="table-actions">
                    <div class="search-box">
                        <span class="material-icons">search</span>
                        <input type="text" id="searchInput" placeholder="Cari nama atau email..." oninput="filterTable()">
                        <button type="button" onclick="filterTable()" style="padding:4px 12px;background:var(--primary-500);color:white;border:none;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;white-space:nowrap;">Cari</button>
                    </div>
                    <select class="filter-select" id="filterBayar" onchange="filterTable()">
                        <option value="all">Semua Status</option>
                        <option value="menunggu_verifikasi">Menunggu Verifikasi</option>
                        <option value="dalam_pembayaran">Dalam Pembayaran</option>
                    </select>
                </div>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Calon Anggota</th>
                            <th>No. Telepon</th>
                            <th>Tgl Daftar</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($calonAnggota as $index => $calon)
                            <tr class="table-row" data-nama="{{ strtolower($calon->nama) }}" data-email="{{ strtolower($calon->email) }}" data-status="{{ $calon->status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="member-info">
                                        <div class="member-avatar" style="background-color: #16423c;">
                                            {{ strtoupper(substr($calon->nama, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="member-name">{{ $calon->nama }}</div>
                                            <div class="member-email">{{ $calon->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $calon->telepon }}</td>
                                <td>{{ $calon->created_at->format('d M Y') }}</td>
                                <td>
                                    @if($calon->status === 'dalam_pembayaran')
                                        <span class="badge-new badge-warning">
                                            <span class="badge-dot"></span>
                                            Dalam Pembayaran
                                        </span>
                                    @elseif($calon->status === 'menunggu_verifikasi')
                                        <span class="badge-new badge-error">
                                            <span class="badge-dot"></span>
                                            Menunggu Verifikasi
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('sekretaris.formulir', $calon->id) }}" class="btn btn-info btn-sm" title="Lihat Formulir Pendaftaran" style="text-decoration: none;">
                                            <span class="material-icons">description</span>
                                            Formulir
                                        </a>
                                        @if($calon->status === 'menunggu_verifikasi')
                                            <form method="POST" action="{{ route('sekretaris.verifikasi', $calon->id) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Konfirmasi {{ $calon->nama }} masuk status Dalam Pembayaran? Pesan WA akan dikirim ke calon anggota dan bendahara.');">
                                                    <span class="material-icons">check_circle</span>
                                                    Verifikasi & Kirim WA
                                                </button>
                                            </form>
                                        @elseif($calon->status === 'dalam_pembayaran')
                                            <span class="badge-new badge-info" style="font-size: 10px; padding: 6px 10px;">
                                                <span class="material-icons" style="font-size: 14px;">hourglass_top</span>
                                                Menunggu Pembayaran
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-state">
                                    <span class="material-icons" style="font-size: 48px; opacity: 0.3;">inbox</span>
                                    <p>Tidak ada calon anggota yang menunggu verifikasi.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Bottom Section -->
        <section class="bottom-section">
            <div class="activity-card">
                <div class="card-header-new">
                    <h3>Aktivitas Terbaru</h3>
                    <span style="font-size: 12px; color: #6b7280;">Hari ini</span>
                </div>
                <div class="activity-list">
                    @forelse($activities as $activity)
                        <div class="activity-item">
                            <div class="activity-dot-wrapper">
                                <div class="activity-dot" style="background-color: #16423c;"></div>
                                @if(!$loop->last)
                                    <div class="activity-line"></div>
                                @endif
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">{{ $activity->deskripsi }}</div>
                                <div class="activity-time">{{ $activity->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="activity-empty">
                            <p>Belum ada aktivitas</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function filterTable() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const filterBayar = document.getElementById('filterBayar').value;
            const rows = document.querySelectorAll('.table-row');

            rows.forEach(row => {
                const nama = row.dataset.nama;
                const email = row.dataset.email;
                const status = row.dataset.status;

                const matchesSearch = nama.includes(searchValue) || email.includes(searchValue);
                const matchesBayar = filterBayar === 'all' || status === filterBayar;

                row.style.display = matchesSearch && matchesBayar ? '' : 'none';
            });
        }

        // Auto hide toast
        setTimeout(() => {
            const toast = document.querySelector('.toast');
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(30px)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);
    </script>
@endpush

@push('styles')
    <style>
        .sekretaris-dashboard {
            position: relative;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .dashboard-title {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: #16423c;
            margin-bottom: 4px;
        }

        .dashboard-subtitle {
            color: #6b7280;
            font-size: 14px;
        }

        .dashboard-actions {
            display: flex;
            gap: 8px;
        }

        /* Stats Grid New */
        .stats-grid-new {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .stat-card-new {
            background-color: #ffffff;
            border: 1px solid #e4e7ef;
            border-radius: 12px;
            padding: 16px;
            position: relative;
            overflow: hidden;
            transition: all 0.25s;
        }

        .stat-card-new:hover {
            border-color: rgba(22, 66, 60, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 10px 32px rgba(0, 0, 0, 0.08);
        }

        .stat-card-glow {
            position: absolute;
            top: -25px;
            right: -25px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            filter: blur(45px);
            opacity: 0.13;
        }

        .stat-card-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
        }

        .stat-card-icon .material-icons {
            font-size: 20px;
        }

        .stat-card-value {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 2px;
        }

        .stat-card-label {
            font-size: 11.5px;
            color: #6b7280;
            font-weight: 500;
        }

        .stat-card-trend {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 10px;
            font-weight: 600;
            margin-top: 8px;
            padding: 2px 7px;
            border-radius: 5px;
        }

        .stat-card-trend .material-icons {
            font-size: 10px;
        }

        .progress-bar {
            width: 100%;
            height: 5px;
            background-color: #e4e7ef;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 7px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 1.4s ease;
        }

        /* Quick Stats */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }

        .quick-stat {
            background-color: #ffffff;
            border: 1px solid #e4e7ef;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .quick-stat-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .quick-stat-icon .material-icons {
            font-size: 18px;
        }

        .quick-stat-value {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .quick-stat-label {
            font-size: 10.5px;
            color: #6b7280;
        }

        /* Table Section */
        .table-section {
            background-color: #ffffff;
            border: 1px solid #e4e7ef;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid #e4e7ef;
            flex-wrap: wrap;
            gap: 10px;
        }

        .table-header h2 {
            font-size: 14.5px;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .table-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 7px;
            background-color: #f7f7f7;
            border: 1px solid #e4e7ef;
            border-radius: 7px;
            padding: 7px 12px;
            min-width: 220px;
        }

        .search-box .material-icons {
            color: #6b7280;
            font-size: 18px;
        }

        .search-box input {
            background: transparent;
            border: none;
            outline: none;
            color: #111827;
            font-size: 12px;
            width: 100%;
            font-family: 'Segoe UI', sans-serif;
        }

        .search-box input::placeholder {
            color: #6b7280;
        }

        .filter-select {
            background-color: #f7f7f7;
            border: 1px solid #e4e7ef;
            border-radius: 7px;
            padding: 7px 10px;
            color: #111827;
            font-size: 11px;
            font-family: 'Segoe UI', sans-serif;
            cursor: pointer;
            outline: none;
        }

        .filter-select:focus {
            border-color: #16423c;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead th {
            text-align: left;
            padding: 10px 18px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            background-color: #f7f7f7;
            border-bottom: 1px solid #e4e7ef;
            white-space: nowrap;
        }

        .data-table tbody td {
            padding: 12px 18px;
            font-size: 12.5px;
            border-bottom: 1px solid #e4e7ef;
        }

        .data-table tbody tr {
            transition: background 0.12s;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: #f7f7f7;
        }

        .member-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .member-avatar {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 11.5px;
            color: #ffffff;
            flex-shrink: 0;
            font-family: 'Poppins', sans-serif;
        }

        .member-name {
            font-weight: 600;
            font-size: 12.5px;
            color: #111827;
        }

        .member-email {
            font-size: 10.5px;
            color: #6b7280;
            margin-top: 1px;
        }

        .badge-new {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 600;
        }

        .badge-success {
            background-color: rgba(53, 171, 80, 0.1);
            color: #35ab50;
        }

        .badge-error {
            background-color: rgba(154, 0, 0, 0.1);
            color: #9a0000;
        }

        .badge-warning {
            background-color: rgba(255, 187, 0, 0.1);
            color: #d4a000;
        }

        .badge-info {
            background-color: rgba(99, 102, 241, 0.1);
            color: #6366f1;
        }

        .badge-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            display: inline-block;
        }

        .badge-success .badge-dot {
            background-color: #35ab50;
        }

        .badge-error .badge-dot {
            background-color: #9a0000;
        }

        .badge-warning .badge-dot {
            background-color: #d4a000;
        }

        .badge-info .badge-dot {
            background-color: #6366f1;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 6px 12px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            font-family: 'Segoe UI', sans-serif;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-sm .material-icons {
            font-size: 16px;
        }

        .btn-whatsapp {
            background-color: #25d366;
            color: #ffffff;
        }

        .btn-whatsapp:hover {
            background-color: #1ebe5d;
        }

        .empty-state {
            text-align: center;
            padding: 36px !important;
            color: #6b7280;
        }

        /* Bottom Section */
        .bottom-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .activity-card {
            background-color: #ffffff;
            border: 1px solid #e4e7ef;
            border-radius: 12px;
            padding: 18px;
        }

        .card-header-new {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .card-header-new h3 {
            font-size: 14.5px;
            font-weight: 700;
            color: #111827;
        }

        .activity-list {
            max-height: 310px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #e4e7ef;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-dot-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 3px;
        }

        .activity-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .activity-line {
            width: 1px;
            flex: 1;
            background-color: #e4e7ef;
            margin-top: 5px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 12px;
            line-height: 1.5;
            color: #111827;
        }

        .activity-text strong {
            font-weight: 600;
        }

        .activity-time {
            font-size: 10px;
            color: #6b7280;
            margin-top: 1px;
        }

        .activity-empty {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
        }

        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .toast {
            background-color: #ffffff;
            border: 1px solid #e4e7ef;
            border-radius: 10px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 300px;
            box-shadow: 0 14px 44px rgba(0, 0, 0, 0.1);
            font-size: 12px;
            color: #111827;
            transition: all 0.3s;
        }

        .toast-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .toast-icon .material-icons {
            font-size: 18px;
        }

        @media (max-width: 1200px) {
            .stats-grid-new {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 768px) {
            .stats-grid-new {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-stats {
                grid-template-columns: 1fr;
            }

            .table-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: 100%;
            }

            .table-actions {
                width: 100%;
            }

            .filter-select {
                flex: 1;
            }
        }
    </style>
@endpush
