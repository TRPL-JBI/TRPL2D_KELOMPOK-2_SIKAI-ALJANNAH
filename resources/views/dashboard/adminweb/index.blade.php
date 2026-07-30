@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@section('title', 'Dashboard Admin Web')

@section('content')
<style>
    .adminweb-cards {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .adminweb-card {
        background: linear-gradient(135deg, #16423C 0%, #236b61 100%);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(22, 66, 60, 0.15);
        display: flex;
        flex-direction: column;
        gap: 12px;
        color: #fff;
    }

    .adminweb-card-label {
        font-size: 14px;
        font-weight: 600;
        opacity: 0.9;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .adminweb-card-value {
        font-size: 36px;
        font-weight: 700;
        line-height: 1;
    }

    .adminweb-card-actions {
        display: flex;
        gap: 10px;
        margin-top: 4px;
    }

    .adminweb-card-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px 16px;
        border-radius: 999px;
        border: none;
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.2s;
        font-family: inherit;
    }

    .adminweb-card-btn:hover {
        background: rgba(255, 255, 255, 0.35);
    }

    .adminweb-card-btn .material-icons {
        font-size: 16px;
    }

    @media (max-width: 1024px) {
        .adminweb-cards {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .adminweb-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<h1 style="font-size: 24px; font-weight: 700; color: #16423c; margin-bottom: 30px;">Dashboard</h1>

<div class="card" style="padding: 30px; border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
    <div class="adminweb-cards">
        <div class="adminweb-card">
            <div class="adminweb-card-label">Pages</div>
            <div class="adminweb-card-value">{{ $jumlahPages }}</div>
            <div class="adminweb-card-actions">
                <a href="#" class="adminweb-card-btn">
                    <span class="material-icons">add</span>
                </a>
                <a href="#" class="adminweb-card-btn">View All</a>
            </div>
        </div>

        <div class="adminweb-card">
            <div class="adminweb-card-label">Posts</div>
            <div class="adminweb-card-value">{{ $jumlahPosts }}</div>
            <div class="adminweb-card-actions">
                <a href="#" class="adminweb-card-btn">
                    <span class="material-icons">add</span>
                </a>
                <a href="#" class="adminweb-card-btn">View All</a>
            </div>
        </div>

        <div class="adminweb-card">
            <div class="adminweb-card-label">Pengunjung</div>
            <div class="adminweb-card-value">{{ $jumlahPengunjung }}</div>
            <div class="adminweb-card-actions">
                <a href="#" class="adminweb-card-btn">
                    <span class="material-icons">add</span>
                </a>
                <a href="#" class="adminweb-card-btn">View All</a>
            </div>
        </div>

        <div class="adminweb-card">
            <div class="adminweb-card-label">Categories</div>
            <div class="adminweb-card-value">{{ $jumlahKategori }}</div>
            <div class="adminweb-card-actions">
                <a href="#" class="adminweb-card-btn">
                    <span class="material-icons">add</span>
                </a>
                <a href="#" class="adminweb-card-btn">View All</a>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 24px; border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-top: 24px;">
        <h2 style="font-size: 18px; font-weight: 700; color: #16423c; margin-bottom: 20px;">Statistik Pengunjung</h2>
        <canvas id="visitorChart" height="100"></canvas>
    </div>
</div>

<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('visitorChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($bulan),
                datasets: [{
                    label: 'Pengunjung',
                    data: @json($pengunjungPerBulan),
                    borderColor: '#16423C',
                    backgroundColor: 'rgba(22, 66, 60, 0.08)',
                    borderWidth: 3,
                    pointBackgroundColor: '#16423C',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#6B7280',
                            font: { size: 12 },
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)',
                        },
                    },
                    x: {
                        ticks: {
                            color: '#6B7280',
                            font: { size: 12 },
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });
    });
</script>
@endsection
