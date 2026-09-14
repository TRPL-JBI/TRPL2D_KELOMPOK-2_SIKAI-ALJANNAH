@php
$iconMap = [
    'Dashboard'                => 'dashboard',
    'Beranda'                  => 'home',
    'Catat Transaksi'          => 'edit_note',
    'Iuran Anggota'            => 'savings',
    'Laporan Keuangan'         => 'analytics',
    'Verifikasi Pendaftaran'   => 'how_to_reg',
    'Kelola Anggota'           => 'group',
    'Log Aktivitas'            => 'history',
    'Data Anggota'             => 'people',
    'Keuangan'                 => 'account_balance_wallet',
    'Aset Organisasi'          => 'inventory_2',
    'Permintaan Izin'          => 'shield',
    'Stok Barang'              => 'warehouse',
    'Aset & Kendaraan'         => 'local_shipping',
    'Riwayat'                  => 'schedule',
    'Posts'                    => 'article',
    'Pages'                    => 'web',
    'Menus'                    => 'menu',
    'WhatsApp'                 => 'chat',
    'Files & Images'           => 'folder',
    'Manajemen User'           => 'manage_accounts',
    'Storage File'             => 'cloud',
];
@endphp

<aside class="sidebar">
    <nav class="sidebar-menu">
        @foreach($menuItems as $item)
            @php $icon = $iconMap[$item['label']] ?? 'circle'; @endphp
            <a 
                href="{{ $item['url'] }}" 
                class="sidebar-menu-item {{ request()->is($item['active']) ? 'active' : '' }}"
                title="{{ $item['label'] }}"
            >
                <span class="menu-icon">
                    <span class="material-icons">{{ $icon }}</span>
                </span>
                <span class="menu-label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>
