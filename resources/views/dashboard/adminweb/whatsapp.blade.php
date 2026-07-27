@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'WhatsApp', 'url' => '/adminweb/whatsapp', 'active' => 'adminweb/whatsapp*'],
        ['label' => 'Files & Images', 'url' => '#', 'active' => 'adminweb/files'],
    ]
])

@section('title', 'WhatsApp Management')

@section('content')
<style>
    .wa-page * { font-family: 'Poppins', sans-serif; }
    .wa-page .wa-header { margin-bottom: 28px; }
    .wa-page .wa-header h1 { font-size: 24px; font-weight: 700; color: var(--primary-600); margin: 0; }
    .wa-page .wa-header p { font-size: 14px; color: var(--primary-400); margin-top: 4px; }
    .wa-page .wa-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .wa-page .wa-card { background: #fff; border-radius: 20px; border: 2px solid var(--primary-500); padding: 28px; box-shadow: 0 4px 16px rgba(0,0,0,.05); }
    .wa-page .wa-card.full { grid-column: 1 / -1; }
    .wa-page .wa-card h3 { font-size: 16px; font-weight: 700; color: var(--primary-600); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .wa-page .wa-card h3 .material-icons { font-size: 20px; color: var(--primary-500); }
    .wa-status { display: flex; align-items: center; gap: 12px; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 14px; }
    .wa-status.connected { background: var(--success-100); color: var(--success-700); border: 1px solid var(--success-200); }
    .wa-status.disconnected { background: var(--danger-100); color: var(--danger-700); border: 1px solid var(--danger-200); }
    .wa-status.connecting { background: var(--warning-100); color: var(--warning-700); border: 1px solid var(--warning-200); }
    .wa-status.qr_ready { background: var(--info-100); color: var(--info-700); border: 1px solid var(--info-200); }
    .wa-status.pairing { background: var(--info-100); color: var(--info-700); border: 1px solid var(--info-200); }
    .wa-status .status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .wa-status.connected .status-dot { background: var(--success-500); }
    .wa-status.disconnected .status-dot { background: var(--danger-500); }
    .wa-status.connecting .status-dot { background: var(--warning-500); animation: pulse 1s infinite; }
    .wa-status.qr_ready .status-dot { background: var(--info-500); }
    .wa-status.pairing .status-dot { background: var(--info-500); }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }
    .wa-info-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--primary-100); font-size: 14px; }
    .wa-info-item:last-child { border-bottom: none; }
    .wa-info-item .label { color: var(--primary-400); font-weight: 500; }
    .wa-info-item .value { font-weight: 600; color: #1f2937; }
    .wa-page .form-group { margin-bottom: 16px; }
    .wa-page .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--primary-400); margin-bottom: 6px; }
    .wa-page .form-group input { width: 100%; padding: 11px 16px; border: 2px solid var(--primary-100); border-radius: 12px; font: 14px 'Poppins', sans-serif; color: #1f2937; outline: none; transition: border-color .2s; background: #fff; box-sizing: border-box; }
    .wa-page .form-group input:focus { border-color: var(--primary-600); }
    .wa-page .btn-wa-primary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: var(--primary-500); color: #fff; border: none; border-radius: 100px; font: 700 14px 'Poppins', sans-serif; cursor: pointer; transition: all .2s; text-decoration: none; }
    .wa-page .btn-wa-primary:hover { opacity: .9; box-shadow: 0 4px 14px rgba(22,66,60,.3); }
    .wa-page .btn-wa-danger { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: var(--danger-500); color: #fff; border: none; border-radius: 100px; font: 700 14px 'Poppins', sans-serif; cursor: pointer; transition: all .2s; }
    .wa-page .btn-wa-danger:hover { opacity: .9; box-shadow: 0 4px 14px rgba(204,0,34,.3); }
    .wa-page .btn-wa-outline { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: #fff; color: var(--primary-500); border: 2px solid var(--primary-200); border-radius: 100px; font: 700 14px 'Poppins', sans-serif; cursor: pointer; transition: all .2s; }
    .wa-page .btn-wa-outline:hover { border-color: var(--primary-500); background: #f0f7f4; }
    .wa-page .pairing-code-box { background: var(--primary-100); border: 2px dashed var(--primary-500); border-radius: 12px; padding: 20px; text-align: center; margin-top: 16px; }
    .wa-page .pairing-code-box .code { font-size: 28px; font-weight: 800; color: var(--primary-600); letter-spacing: 6px; font-family: 'Courier New', monospace; margin: 12px 0 8px; }
    .wa-page .pairing-code-box p { font-size: 13px; color: var(--primary-400); }
    .wa-page .alert-success, .wa-page .alert-error { display: flex; align-items: center; gap: 10px; padding: 14px 24px; border-radius: 14px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
    .wa-page .alert-success { background: var(--success-100); border: 1px solid var(--success-200); color: var(--success-700); }
    .wa-page .alert-error { background: var(--danger-100); border: 1px solid var(--danger-200); color: var(--danger-700); }
    .wa-page .alert-success .material-icons, .wa-page .alert-error .material-icons { font-size: 20px; }
    .wa-page .wa-phone-display { display: flex; align-items: center; gap: 10px; font-size: 16px; font-weight: 600; color: var(--primary-600); }
    .wa-page .wa-phone-display .material-icons { font-size: 24px; color: #25D366; }
    .wa-page .qr-section { display: none; }
    .wa-page .qr-section.active { display: flex; flex-direction: column; align-items: center; gap: 16px; padding: 16px 0; }
    .wa-page .qr-section img { width: 220px; height: 220px; border-radius: 12px; border: 2px solid var(--primary-200); }
    .wa-page .qr-section .qr-hint { font-size: 13px; color: var(--primary-400); text-align: center; max-width: 260px; }
    .wa-page .method-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; color: var(--primary-300); font-size: 12px; font-weight: 600; }
    .wa-page .method-divider::before, .wa-page .method-divider::after { content: ''; flex: 1; height: 1px; background: var(--primary-200); }
    .wa-page .qr-spinner { width: 220px; height: 220px; border-radius: 12px; background: var(--primary-100); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; color: var(--primary-300); font-size: 14px; border: 2px dashed var(--primary-200); }
    .wa-page .qr-spinner .spin { width: 32px; height: 32px; border: 3px solid var(--primary-200); border-top-color: var(--primary-500); border-radius: 50%; animation: spin .8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    @media (max-width: 768px) { .wa-page .wa-grid { grid-template-columns: 1fr; } }
</style>

<div class="wa-page">
    <div class="page-index wa-header">
        <h1>WhatsApp Management</h1>
        <p>Kelola koneksi WhatsApp untuk mengirim notifikasi otomatis ke anggota</p>
    </div>

    @if(session('success'))
    <div class="alert-success"><span class="material-icons">check_circle</span> {{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div class="alert-error"><span class="material-icons">error</span> {{ session('error') }}</div>
    @endif

    @if(session('pairing_code'))
    <div class="wa-card full" style="margin-bottom:24px;">
        <h3><span class="material-icons">pin</span> Pairing Code</h3>
        <div class="pairing-code-box">
            <p>Buka WhatsApp &rarr; Perangkat Tertaut &rarr; Ketuk perangkat baru</p>
            <div class="code">{{ session('pairing_code') }}</div>
            <p>Masukkan kode di atas dalam waktu 5 menit</p>
        </div>
    </div>
    @endif

    <div class="wa-page" style="margin-bottom:20px;">
        <div id="qrLiveContainer" class="wa-card full" style="display:none;">
            <h3><span class="material-icons">qr_code_scanner</span> Scan QR Code</h3>
            <div id="qrBody" class="qr-section active">
                <div id="qrSpinner" class="qr-spinner">
                    <div class="spin"></div>
                    <span>Menghasilkan QR code...</span>
                </div>
                <img id="qrImage" style="display:none;" alt="QR Code WhatsApp">
                <p id="qrHint" class="qr-hint" style="display:none;">Buka WhatsApp &rarr; Perangkat Tertaut &rarr; Ketuk perangkat baru &rarr; Scan QR code ini</p>
            </div>
        </div>
    </div>

    <div class="wa-grid">
        <div class="wa-card">
            <h3><span class="material-icons">link</span> Status Koneksi</h3>
            @if(!$serviceUp)
                <div class="wa-status disconnected">
                    <span class="status-dot"></span> Service WhatsApp tidak berjalan
                </div>
                <p style="font-size:13px;color:var(--primary-400);margin-bottom:16px;">
                    Jalankan service dengan perintah:
                    <code style="background:var(--primary-100);padding:2px 8px;border-radius:4px;">php artisan whatsapp:start</code>
                </p>
            @else
                @php $statusText = $status['status'] ?? 'unknown'; @endphp
                <div class="wa-status {{ $statusText === 'connected' ? 'connected' : ($statusText === 'qr_ready' || $statusText === 'pairing' ? $statusText : 'disconnected') }}">
                    <span class="status-dot"></span>
                    @if($statusText === 'connected') Terhubung
                    @elseif($statusText === 'qr_ready') QR siap discan
                    @elseif($statusText === 'pairing') Menunggu pairing...
                    @elseif($statusText === 'connecting') Menghubungkan...
                    @else Belum terhubung
                    @endif
                </div>

                @if($statusText === 'connected' && isset($status['phone']))
                <div class="wa-phone-display">
                    <span class="material-icons">check_circle</span>
                    {{ $status['phone'] }}
                </div>
                @endif

                <div style="margin-top:16px;">
                    <div class="wa-info-item"><span class="label">Status</span><span class="value">{{ $statusText }}</span></div>
                    @if(isset($status['phone']))
                    <div class="wa-info-item"><span class="label">Nomor</span><span class="value">{{ $status['phone'] }}</span></div>
                    @endif
                    @if(isset($status['name']))
                    <div class="wa-info-item"><span class="label">Nama</span><span class="value">{{ $status['name'] }}</span></div>
                    @endif
                </div>

                @if($statusText === 'connected')
                <form method="POST" action="{{ route('adminweb.whatsapp.logout') }}" style="margin-top:20px;">
                    @csrf
                    <button type="submit" class="btn-wa-danger" onclick="return confirm('Putuskan koneksi WhatsApp?')">
                        <span class="material-icons">link_off</span> Putuskan Koneksi
                    </button>
                </form>
                @endif
            @endif
        </div>

        @if(!isset($status['status']) || $status['status'] !== 'connected')
        <div class="wa-card">
            <h3><span class="material-icons">qr_code</span> Hubungkan</h3>

            <p style="font-size:13px;color:var(--primary-400);margin-bottom:12px;font-weight:600;">Metode 1: Scan QR Code</p>
            <form id="qrForm" method="POST" action="{{ route('adminweb.whatsapp.connect-qr') }}">
                @csrf
                <button type="submit" class="btn-wa-primary" style="width:100%;justify-content:center;">
                    <span class="material-icons">qr_code_scanner</span> Tampilkan QR Code
                </button>
            </form>

            <div class="method-divider">ATAU</div>

            <p style="font-size:13px;color:var(--primary-400);margin-bottom:12px;font-weight:600;">Metode 2: Pairing Code</p>
            <form method="POST" action="{{ route('adminweb.whatsapp.connect') }}">
                @csrf
                <div class="form-group">
                    <label>Nomor WhatsApp (dengan kode negara)</label>
                    <input type="text" name="phone" placeholder="6281234567890" required>
                </div>
                <button type="submit" class="btn-wa-outline" style="width:100%;justify-content:center;">
                    <span class="material-icons">pin</span> Dapatkan Pairing Code
                </button>
            </form>
        </div>
        @endif

        <div class="wa-card full">
            <h3><span class="material-icons">send</span> Kirim Pesan Uji Coba</h3>
            <p style="font-size:13px;color:var(--primary-400);margin-bottom:16px;">
                Kirim pesan teks untuk menguji koneksi WhatsApp.
            </p>
            <form method="POST" action="{{ route('adminweb.whatsapp.send') }}">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
                    <div class="form-group">
                        <label>Nomor Tujuan</label>
                        <input type="text" name="to" placeholder="6281234567890" required>
                    </div>
                    <div class="form-group">
                        <label>Pesan</label>
                        <input type="text" name="text" placeholder="Tulis pesan..." required>
                    </div>
                </div>
                <button type="submit" class="btn-wa-primary">
                    <span class="material-icons">send</span> Kirim
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var refreshUrl = '{{ route("adminweb.whatsapp.refresh") }}';
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    var qrContainer = document.getElementById('qrLiveContainer');
    var qrSpinner = document.getElementById('qrSpinner');
    var qrImage = document.getElementById('qrImage');
    var qrHint = document.getElementById('qrHint');

    function startPolling() {
        qrContainer.style.display = '';
        pollStatus();
    }

    function pollStatus() {
        fetch(refreshUrl, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.status === 'connected') {
                window.location.reload();
                return;
            }
            if (data.qr) {
                qrSpinner.style.display = 'none';
                qrImage.src = data.qr;
                qrImage.style.display = '';
                qrHint.style.display = '';
                setTimeout(pollStatus, 5000);
            } else {
                setTimeout(pollStatus, 1500);
            }
        })
        .catch(function() {
            setTimeout(pollStatus, 3000);
        });
    }

    // If QR was triggered (from session), start polling immediately
    @if(session('qr_triggered'))
    startPolling();
    @endif

    // Also: intercept the QR form to avoid full page reload
    var qrForm = document.getElementById('qrForm');
    if (qrForm) {
        qrForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = qrForm.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="btn-spinner"></span> Memproses...';

            fetch(qrForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: new FormData(qrForm),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-icons">qr_code_scanner</span> Tampilkan QR Code';
                if (!data.error) {
                    startPolling();
                } else {
                    alert(data.error);
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<span class="material-icons">qr_code_scanner</span> Tampilkan QR Code';
            });
        });
    }
})();
</script>
@endpush
