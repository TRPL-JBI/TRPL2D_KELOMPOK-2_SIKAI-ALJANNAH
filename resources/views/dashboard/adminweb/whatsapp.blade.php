@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'WhatsApp', 'url' => '/adminweb/whatsapp', 'active' => 'adminweb/whatsapp*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@section('title', 'WhatsApp Management')

@section('content')
<style>
    .wa-page *:not(.material-icons):not(.material-symbols-outlined) { font-family: 'Poppins', sans-serif; }
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
    .wa-status .status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .wa-status.connected .status-dot { background: var(--success-500); }
    .wa-status.disconnected .status-dot { background: var(--danger-500); }
    .wa-status.connecting .status-dot { background: var(--warning-500); animation: pulse 1s infinite; }
    .wa-status.qr_ready .status-dot { background: var(--info-500); }
    @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .4; } }
    .wa-info-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--primary-100); font-size: 14px; }
    .wa-info-item:last-child { border-bottom: none; }
    .wa-info-item .label { color: var(--primary-400); font-weight: 500; }
    .wa-info-item .value { font-weight: 600; color: #1f2937; }
    .wa-page .btn-wa-primary { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: var(--primary-500); color: #fff; border: none; border-radius: 100px; font: 700 14px 'Poppins', sans-serif; cursor: pointer; transition: all .2s; text-decoration: none; }
    .wa-page .btn-wa-primary:hover { opacity: .9; box-shadow: 0 4px 14px rgba(22,66,60,.3); }
    .wa-page .btn-wa-danger { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: var(--danger-500); color: #fff; border: none; border-radius: 100px; font: 700 14px 'Poppins', sans-serif; cursor: pointer; transition: all .2s; }
    .wa-page .btn-wa-danger:hover { opacity: .9; box-shadow: 0 4px 14px rgba(204,0,34,.3); }
    .wa-page .btn-wa-outline { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: #fff; color: var(--primary-500); border: 2px solid var(--primary-200); border-radius: 100px; font: 700 14px 'Poppins', sans-serif; cursor: pointer; transition: all .2s; }
    .wa-page .btn-wa-outline:hover { border-color: var(--primary-500); background: #f0f7f4; }
    .wa-page .btn-wa-primary:disabled, .wa-page .btn-wa-outline:disabled { opacity: .6; cursor: not-allowed; }
    .wa-page .form-group { margin-bottom: 16px; }
    .wa-page .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--primary-400); margin-bottom: 6px; }
    .wa-page .form-group input { width: 100%; padding: 11px 16px; border: 2px solid var(--primary-100); border-radius: 12px; font: 14px 'Poppins', sans-serif; color: #1f2937; outline: none; transition: border-color .2s; background: #fff; box-sizing: border-box; }
    .wa-page .form-group input:focus { border-color: var(--primary-600); }
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

    @php
        $statusText = $status['status'] ?? 'unknown';
        $isReady = $statusText === 'ready';
        $hasQr = !empty($status['qr']);
        $showQrOnLoad = ($statusText === 'qr' && $hasQr);
        $initialQr = $showQrOnLoad ? $status['qr'] : null;
        $initialPoll = $showQrOnLoad || session('qr_triggered');
        $qrSrc = $showQrOnLoad ? $status['qr'] : '';
    @endphp

    <div class="wa-page" style="margin-bottom:20px;">
        <div id="qrLiveContainer" class="wa-card full" style="display:none;" data-initial-poll="{{ $initialPoll ? '1' : '0' }}">
            <h3><span class="material-icons">qr_code_scanner</span> Scan QR Code</h3>
            <div id="qrBody" class="qr-section active">
                <div id="qrSpinner" class="qr-spinner">
                    <div class="spin"></div>
                    <span>Menghasilkan QR code...</span>
                </div>
                <img id="qrImage" src="{{ $qrSrc }}" style="display:none;" alt="QR Code WhatsApp">
                <p id="qrHint" class="qr-hint" style="display:none;">Buka WhatsApp &rarr; Perangkat Tertaut &rarr; Ketuk perangkat baru &rarr; Scan QR code ini</p>
            </div>
        </div>
    </div>

    <div class="wa-grid">
        <div class="wa-card full">
            <h3><span class="material-icons">dns</span> Server WhatsApp</h3>

            @if(!$server['installed'])
                <div class="wa-status disconnected">
                    <span class="status-dot"></span> Sidecar belum di-install
                </div>
                <p style="font-size:13px;color:var(--primary-400);margin-bottom:0;">
                    Jalankan perintah ini sekali di server (SSH/terminal):
                    <code style="background:var(--primary-100);padding:2px 8px;border-radius:4px;">php artisan whatsapp:sidecar:install</code>
                </p>
            @elseif($server['running'])
                <div style="display:flex;align-items:center;gap:24px;flex-wrap:wrap;justify-content:space-between;">
                    <div style="min-width:0;flex:1;">
                        <div class="wa-status connected" style="margin-bottom:12px;">
                            <span class="status-dot"></span> Server berjalan
                        </div>
                        <div class="wa-info-item" style="border:none;padding:4px 0;"><span class="label">PID</span><span class="value">{{ $server['pid'] }}</span></div>
                        <div class="wa-info-item" style="border:none;padding:4px 0;"><span class="label">Endpoint</span><span class="value">{{ $server['endpoint'] }}</span></div>
                        <div class="wa-info-item" style="border:none;padding:4px 0;">
                            <span class="label">Reachable</span>
                            <span class="value" style="color:{{ $serviceUp ? 'var(--success-600)' : 'var(--warning-700)' }};">
                                {{ $serviceUp ? 'Ya' : 'Belum (masih booting?)' }}
                            </span>
                        </div>
                        @if(!$serviceUp)
                        <div id="serverBooting" data-status-url="{{ route('adminweb.whatsapp.server-status') }}" style="margin-top:12px;">
                            <p style="font-size:13px;color:var(--primary-400);margin:0 0 8px;">
                                Halaman akan memuat ulang otomatis begitu server siap. Jika lama, cek log di bawah:
                            </p>
                            @if(!empty($server['log_tail']))
                            <pre id="serverLogTail" style="max-height:160px;overflow:auto;background:var(--primary-100);color:#1f2937;border-radius:8px;padding:10px 14px;font-size:12px;white-space:pre-wrap;word-break:break-all;margin:0;">{{ $server['log_tail'] }}</pre>
                            @endif
                        </div>
                        @endif
                    </div>
                    <form class="wa-server-form" method="POST" action="{{ route('adminweb.whatsapp.server-stop') }}" data-confirm="Hentikan server WhatsApp? Koneksi aktif akan terputus.">
                        @csrf
                        <button type="submit" class="btn-wa-outline" data-loading-text="Menghentikan server...">
                            <span class="material-icons">stop_circle</span> Stop Server
                        </button>
                    </form>
                </div>
            @else
                <div class="wa-status disconnected">
                    <span class="status-dot"></span> Server tidak berjalan
                </div>
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;justify-content:space-between;">
                    <p style="font-size:13px;color:var(--primary-400);margin:0;">Mulai server untuk mengaktifkan koneksi WhatsApp. Perlu beberapa detik untuk boot.</p>
                    <form class="wa-server-form" method="POST" action="{{ route('adminweb.whatsapp.server-start') }}">
                        @csrf
                        <button type="submit" class="btn-wa-primary" data-loading-text="Menjalankan server...">
                            <span class="material-icons">play_arrow</span> Start Server
                        </button>
                    </form>
                </div>
                @if(!empty($server['log_tail']))
                <details style="margin-top:12px;">
                    <summary style="font-size:13px;color:var(--primary-400);cursor:pointer;">Log error terakhir</summary>
                    <pre style="max-height:160px;overflow:auto;background:var(--primary-100);color:#1f2937;border-radius:8px;padding:10px 14px;font-size:12px;white-space:pre-wrap;word-break:break-all;margin:8px 0 0;">{{ $server['log_tail'] }}</pre>
                </details>
                @endif
            @endif
        </div>

        <div class="wa-card">
            <h3><span class="material-icons">link</span> Status Koneksi</h3>
            @if(!$serviceUp)
                @if(!$server['installed'])
                    <div class="wa-status disconnected">
                        <span class="status-dot"></span> Service WhatsApp tidak merespons
                    </div>
                    <p style="font-size:13px;color:var(--primary-400);margin-bottom:16px;">
                        Sidecar belum terpasang. Ikuti petunjuk pada kartu Server WhatsApp di atas.
                    </p>
                @elseif($server['running'])
                    <div class="wa-status connecting">
                        <span class="status-dot"></span> Server sedang booting...
                    </div>
                    <p style="font-size:13px;color:var(--primary-400);margin-bottom:16px;">
                        Server berjalan tapi belum merespons. Tunggu beberapa saat lalu muat ulang halaman.
                    </p>
                @else
                    <div class="wa-status disconnected">
                        <span class="status-dot"></span> Service WhatsApp tidak merespons
                    </div>
                    <p style="font-size:13px;color:var(--primary-400);margin-bottom:16px;">
                        Gunakan tombol <strong>Start Server</strong> di kartu Server WhatsApp di atas untuk menjalankan service.
                    </p>
                @endif
            @else
                @php
                    $statusClass = $isReady ? 'connected'
                        : ($statusText === 'qr' ? 'qr_ready'
                        : (in_array($statusText, ['initializing', 'authenticated']) ? 'connecting' : 'disconnected'));
                @endphp
                <div class="wa-status {{ $statusClass }}">
                    <span class="status-dot"></span>
                    @if($isReady) Terhubung
                    @elseif($statusText === 'qr') QR siap discan
                    @elseif($statusText === 'initializing') Menghubungkan...
                    @elseif($statusText === 'authenticated') Otentikasi berhasil...
                    @else Belum terhubung
                    @endif
                </div>

                @if($isReady && !empty($status['phone']))
                <div class="wa-phone-display">
                    <span class="material-icons">check_circle</span>
                    {{ $status['phone'] }}
                </div>
                @endif

                <div style="margin-top:16px;">
                    <div class="wa-info-item"><span class="label">Status</span><span class="value">{{ $statusText }}</span></div>
                    @if(!empty($status['phone']))
                    <div class="wa-info-item"><span class="label">Nomor</span><span class="value">{{ $status['phone'] }}</span></div>
                    @endif
                    @if(!empty($status['name']))
                    <div class="wa-info-item"><span class="label">Nama</span><span class="value">{{ $status['name'] }}</span></div>
                    @endif
                    @if(!empty($status['error']))
                    <div class="wa-info-item"><span class="label">Error</span><span class="value" style="color:var(--danger-500);">{{ $status['error'] }}</span></div>
                    @endif
                </div>

                @if($isReady)
                <form method="POST" action="{{ route('adminweb.whatsapp.logout') }}" style="margin-top:20px;">
                    @csrf
                    <button type="submit" class="btn-wa-danger" onclick="return confirm('Putuskan koneksi WhatsApp?')">
                        <span class="material-icons">link_off</span> Putuskan Koneksi
                    </button>
                </form>
                @endif
            @endif
        </div>

        @if($serviceUp && !$isReady)
        <div class="wa-card">
            <h3><span class="material-icons">qr_code</span> Hubungkan</h3>

            <p style="font-size:13px;color:var(--primary-400);margin-bottom:16px;">
                Buka WhatsApp di ponsel &rarr; menu <strong>Perangkat Tertaut</strong> &rarr; <strong>Ketuk perangkat baru</strong>, lalu scan QR code yang muncul.
            </p>

            <form id="qrForm" method="POST" action="{{ route('adminweb.whatsapp.connect-qr') }}">
                @csrf
                <button type="submit" class="btn-wa-primary" style="width:100%;justify-content:center;">
                    <span class="material-icons">qr_code_scanner</span> Tampilkan QR Code
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
                        <small style="display:block;font-size:12px;color:var(--primary-400);margin-top:6px;">Kode negara + nomor, tanpa 0 di depan. Contoh: 6281234567890 (bukan 0812...)</small>
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

    function showQr(qrDataUri) {
        qrSpinner.style.display = 'none';
        qrImage.src = qrDataUri;
        qrImage.style.display = '';
        qrHint.style.display = '';
    }

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
            if (data.status === 'ready') {
                window.location.reload();
                return;
            }
            if (data.status === 'error' && data.error) {
                qrSpinner.style.display = 'none';
                qrImage.style.display = 'none';
                qrHint.style.display = '';
                qrHint.textContent = 'Terjadi kesalahan: ' + data.error;
                return;
            }
            if (data.qr) {
                showQr(data.qr);
                setTimeout(pollStatus, 5000);
            } else {
                setTimeout(pollStatus, 1500);
            }
        })
        .catch(function() {
            setTimeout(pollStatus, 3000);
        });
    }

    var initialPoll = qrContainer.getAttribute('data-initial-poll') === '1';

    if (qrImage.getAttribute('src')) {
        showQr(qrImage.getAttribute('src'));
    }
    if (initialPoll) {
        startPolling();
    }

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

    document.querySelectorAll('form.wa-server-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var confirmMsg = form.getAttribute('data-confirm');
            if (confirmMsg && !confirm(confirmMsg)) {
                return;
            }
            var btn = form.querySelector('button[type="submit"]');
            var orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="btn-spinner"></span> ' + (btn.getAttribute('data-loading-text') || 'Memproses...');

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: new FormData(form),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.error) {
                    btn.disabled = false;
                    btn.innerHTML = orig;
                    alert(data.error);
                    return;
                }
                window.location.reload();
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = orig;
                alert('Gagal menghubungi server.');
            });
        });
    });

    var bootContainer = document.getElementById('serverBooting');
    if (bootContainer) {
        var serverStatusUrl = bootContainer.getAttribute('data-status-url');
        (function pollBoot() {
            fetch(serverStatusUrl, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.reachable || !data.running) {
                    window.location.reload();
                    return;
                }
                if (data.log_tail) {
                    var logEl = document.getElementById('serverLogTail');
                    if (logEl) { logEl.textContent = data.log_tail; }
                }
                setTimeout(pollBoot, 3000);
            })
            .catch(function() {
                setTimeout(pollBoot, 5000);
            });
        })();
    }
})();
</script>
@endpush