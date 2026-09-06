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
            <p style="font-size: 14px; color: #6b7280; margin-top: 6px;">Monitor seluruh aktivitas dan kapasitas data sistem secara real-time.</p>
        </div>
    </div>

    {{-- Stats Grid: 4 cards --}}
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-400);">Total User</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--primary-500); margin-top: 4px;">{{ $totalUsers }}</div>
                </div>
                <span class="material-icons" style="color: var(--primary-400); font-size: 18px;">users</span>
            </div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-400);">Total Files</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--primary-500); margin-top: 4px;">{{ $files->count() }}</div>
                </div>
                <span class="material-icons" style="color: var(--info-400); font-size: 18px;">description</span>
            </div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-400);">Aktivitas</div>
                    <div style="font-size: 20px; font-weight: 800; color: var(--success-500); margin-top: 4px;">{{ $logs->count() }}</div>
                </div>
                <span class="material-icons" style="color: var(--success-400); font-size: 18px;">activity</span>
            </div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-400);">User Aktif</div>
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
            <div style="display: flex; gap: 12px; height: 200px; align-items: flex-end;">
                @php
                    $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    $values = [65, 45, 75, 55, 85, 40, 20];
                    $maxVal = max($values);
                @endphp
                @foreach($days as $i => $day)
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px;">
                        <div style="width: 100%; background: linear-gradient(180deg, var(--primary-400), var(--primary-600)); border-radius: 6px 6px 0 0; height: {{ ($values[$i] / $maxVal) * 150 }}px; min-height: 20px; transition: height 0.5s ease;"></div>
                        <span style="font-size: 10px; font-weight: 600; color: var(--secondary-400);">{{ $day }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Activity Feed --}}
        <div class="card" style="padding: 20px;">
            <h2 style="font-size: 14px; font-weight: 700; color: var(--primary-500); margin: 0 0 16px 0;">Feed Aktivitas</h2>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                @forelse($logs->take(5) as $log)
                    <div style="display: flex; gap: 12px;">
                        <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--primary-400); margin-top: 6px; flex-shrink: 0;"></div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: var(--primary-500);">{{ $log->user->nama ?? 'System' }}</div>
                            <div style="font-size: 12px; color: var(--secondary-400); margin-top: 2px;">{{ $log->deskripsi }}</div>
                            <div style="font-size: 10px; color: var(--secondary-300); margin-top: 4px;">{{ $log->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div style="font-size: 12px; color: var(--secondary-400); text-align: center; padding: 20px;">Belum ada aktivitas</div>
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
                $storageCategories = [
                    'Dokumen' => ['size' => 142, 'color' => 'var(--primary-400)'],
                    'Gambar' => ['size' => 84, 'color' => 'var(--success-400)'],
                    'Lainnya' => ['size' => 24, 'color' => 'var(--secondary-300)'],
                ];
                $totalStorage = 500;
                $usedStorage = array_sum(array_column($storageCategories, 'size'));
            @endphp
            <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--secondary-400); margin-bottom: 8px;">
                <span>Digunakan {{ $usedStorage }} MB</span>
                <span>Total {{ $totalStorage }} MB</span>
            </div>
            <div style="height: 8px; background: var(--secondary-100); border-radius: 4px; overflow: hidden; margin-bottom: 16px;">
                <div style="width: {{ ($usedStorage / $totalStorage) * 100 }}%; height: 100%; background: linear-gradient(90deg, var(--primary-400), var(--primary-600)); border-radius: 4px; transition: width 0.5s ease;"></div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($storageCategories as $label => $data)
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 10px; height: 10px; border-radius: 3px; background: {{ $data['color'] }};"></div>
                        <span style="font-size: 12px; color: var(--secondary-400); flex: 1;">{{ $label }}</span>
                        <span style="font-size: 12px; font-weight: 600; color: var(--primary-500);">{{ $data['size'] }} MB</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Role Distribution --}}
        <div class="card" style="padding: 20px;">
            <h2 style="font-size: 14px; font-weight: 700; color: var(--primary-500); margin: 0 0 16px 0;">Role Distribution</h2>
            @php
                $roles = ['superadmin' => 2, 'ketua' => 3, 'sekretaris' => 4, 'bendahara' => 5];
                $roleColors = ['superadmin' => 'var(--danger-400)', 'ketua' => 'var(--info-400)', 'sekretaris' => 'var(--warning-400)', 'bendahara' => 'var(--success-400)'];
                $roleLabels = ['superadmin' => 'Super Admin', 'ketua' => 'Ketua', 'sekretaris' => 'Sekretaris', 'bendahara' => 'Bendahara'];
                $totalRoles = array_sum($roles);
            @endphp
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($roles as $role => $count)
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: var(--secondary-400); margin-bottom: 4px;">
                            <span>{{ $roleLabels[$role] }}</span>
                            <span>{{ $count }}</span>
                        </div>
                        <div style="height: 6px; background: var(--secondary-100); border-radius: 3px; overflow: hidden;">
                            <div style="width: {{ ($count / $totalRoles) * 100 }}%; height: 100%; background: {{ $roleColors[$role] }}; border-radius: 3px; transition: width 0.5s ease;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
