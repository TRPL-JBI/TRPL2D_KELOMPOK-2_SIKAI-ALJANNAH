@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/superadmin', 'active' => 'superadmin'],
        ['label' => 'Manajemen User', 'url' => '/superadmin/user', 'active' => 'superadmin/user'],
        ['label' => 'Storage File', 'url' => '/superadmin/file', 'active' => 'superadmin/file'],
        ['label' => 'Log Aktivitas', 'url' => '/superadmin/log', 'active' => 'superadmin/log'],
    ]
])

@section('title', 'Dashboard Superadmin')

@section('content')
    {{-- Page Header --}}
    <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-400);">Manajemen Pengguna</span>
            <h1 style="font-size: 28px; font-weight: 800; color: var(--primary-500); margin: 4px 0 0 0;">Dashboard Monitoring</h1>
            <p style="font-size: 14px; color: var(--primary-500); margin-top: 6px;">Monitor seluruh aktivitas dan kapasitas data sistem secara real-time.</p>
        </div>
    </div>

    {{-- Stats Grid: 4 cards --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500);">Total User</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--primary-500); margin-top: 4px;">{{ $totalUsers }}</div>
                </div>
                <span class="material-icons" style="color: var(--primary-400); font-size: 18px;">users</span>
            </div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500);">Total Files</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--primary-500); margin-top: 4px;">{{ $totalFiles }}</div>
                </div>
                <span class="material-icons" style="color: var(--info-400); font-size: 18px;">description</span>
            </div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500);">Aktivitas</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--success-500); margin-top: 4px;">{{ $totalLogs }}</div>
                </div>
                <span class="material-icons" style="color: var(--success-400); font-size: 18px;">activity</span>
            </div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-500);">User Aktif</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--success-500); margin-top: 4px;">{{ $activeUsers }}</div>
                </div>
                <span class="material-icons" style="color: var(--warning-400); font-size: 18px;">person_check</span>
            </div>
        </div>
    </div>

    {{-- Chart + Activity --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px;">
        {{-- Weekly Activity --}}
        <div class="card" style="padding: 20px;">
            <h2 style="font-size: 14px; font-weight: 700; color: var(--primary-500); margin: 0 0 20px 0;">Aktivitas Mingguan</h2>
            @php
                $maxVal = max(array_column($weeklyActivity, 'count'));
                if ($maxVal === 0) $maxVal = 1; // Prevent division by zero
            @endphp
            <div style="display: flex; gap: 12px; height: 200px; align-items: flex-end;">
                @foreach($weeklyActivity as $item)
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;">
                        <div style="width: 100%; background: linear-gradient(180deg, var(--primary-400), var(--primary-600)); border-radius: 6px 6px 0 0; height: {{ $maxVal > 0 ? ($item['count'] / $maxVal) * 150 : 0 }}px; min-height: {{ $item['count'] > 0 ? '20px' : '4px' }}; transition: height 0.5s ease;"></div>
                        <span style="font-size: 10px; font-weight: 600; color: var(--primary-500);">{{ $item['day'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Activity Feed --}}
        <div class="card" style="padding: 20px;">
            <h2 style="font-size: 14px; font-weight: 700; color: var(--primary-500); margin: 0 0 16px 0;">Feed Aktivitas</h2>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @forelse($recentLogs as $log)
                    <div style="display: flex; gap: 12px;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary-400); margin-top: 6px; flex-shrink: 0;"></div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--primary-500);">{{ $log->user->nama ?? 'System' }}</div>
                            <div style="font-size: 12px; color: var(--primary-500); margin-top: 2px;">{{ $log->deskripsi }}</div>
                            <div style="font-size: 10px; color: var(--primary-400); margin-top: 4px;">{{ $log->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div style="font-size: 12px; color: var(--primary-500); text-align: center; padding: 20px;">Belum ada aktivitas</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Storage + Roles --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
        {{-- Storage Usage --}}
        <div class="card" style="padding: 20px;">
            <h2 style="font-size: 14px; font-weight: 700; color: var(--primary-500); margin: 0 0 16px 0;">Storage Usage</h2>
            @php
                $storageColors = [
                    'pdf' => 'var(--danger-400)',
                    'doc' => 'var(--primary-400)',
                    'xls' => 'var(--success-400)',
                    'img' => 'var(--info-400)',
                    'vid' => 'var(--warning-400)',
                    'zip' => 'var(--secondary-400)',
                    'lainnya' => 'var(--secondary-300)',
                ];
                $storageLabels = [
                    'pdf' => 'PDF',
                    'doc' => 'Dokumen',
                    'xls' => 'Spreadsheet',
                    'img' => 'Gambar',
                    'vid' => 'Video',
                    'zip' => 'Archive',
                    'lainnya' => 'Lainnya',
                ];
            @endphp
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--primary-500); margin-bottom: 8px;">
                <span>{{ $totalFiles }} Total File</span>
                <span>{{ number_format($totalSize / 1024, 1) }} KB</span>
            </div>
            <div style="height: 8px; background: var(--secondary-100); border-radius: 4px; overflow: hidden; margin-bottom: 16px;">
                <div style="width: {{ $totalFiles > 0 ? 100 : 0 }}%; height: 100%; background: linear-gradient(90deg, var(--primary-400), var(--primary-600)); border-radius: 4px; transition: width 0.5s ease;"></div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($storageCategories as $kategori => $count)
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 10px; height: 10px; border-radius: 3px; background: {{ $storageColors[$kategori] ?? 'var(--secondary-300)' }};"></div>
                         <span style="font-size: 12px; color: var(--primary-500); flex: 1;">{{ $storageLabels[$kategori] ?? ucfirst($kategori) }}</span>
                        <span style="font-size: 12px; font-weight: 600; color: var(--primary-500);">{{ $count }} File</span>
                    </div>
                @empty
                    <div style="font-size: 12px; color: var(--primary-500); text-align: center; padding: 20px;">Belum ada file</div>
                @endforelse
            </div>
        </div>

        {{-- Role Distribution --}}
        <div class="card" style="padding: 20px;">
            <h2 style="font-size: 14px; font-weight: 700; color: var(--primary-500); margin: 0 0 16px 0;">Role Distribution</h2>
            @php
                $roleColors = [
                    'superadmin' => 'var(--danger-400)',
                    'ketua' => 'var(--info-400)',
                    'sekretaris' => 'var(--warning-400)',
                    'bendahara' => 'var(--success-400)',
                    'logistik' => 'var(--primary-400)',
                    'adminweb' => 'var(--secondary-400)',
                ];
                $roleLabels = [
                    'superadmin' => 'Super Admin',
                    'ketua' => 'Ketua',
                    'sekretaris' => 'Sekretaris',
                    'bendahara' => 'Bendahara',
                    'logistik' => 'Logistik',
                    'adminweb' => 'Admin Web',
                ];
                $totalRoles = array_sum($roleDistribution);
            @endphp
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($roleDistribution as $role => $count)
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--primary-500); margin-bottom: 4px;">
                            <span>{{ $roleLabels[$role] ?? ucfirst($role) }}</span>
                            <span>{{ $count }}</span>
                        </div>
                        <div style="height: 6px; background: var(--secondary-100); border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ $totalRoles > 0 ? ($count / $totalRoles) * 100 : 0 }}%; height: 100%; background: {{ $roleColors[$role] ?? 'var(--secondary-400)' }}; border-radius: 3px; transition: width 0.5s ease;"></div>
                        </div>
                    </div>
                @empty
                    <div style="font-size: 12px; color: var(--primary-500); text-align: center; padding: 20px;">Belum ada data role</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
