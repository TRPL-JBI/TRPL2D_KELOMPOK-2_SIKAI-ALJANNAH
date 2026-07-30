<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | RKM Al-Jannah</title>
    @include('components.favicon')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- <link rel="stylesheet" href="{{ asset('css/bendahara.css') }}"> -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [data-loading].btn-is-loading { pointer-events: none; position: relative; }
        .btn-spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: btnSpin .6s linear infinite; vertical-align: middle; margin-right: 6px; }
        @keyframes btnSpin { to { transform: rotate(360deg); } }
    </style>
    @livewireStyles
    @stack('styles')
    <script>if('serviceWorker'in navigator){navigator.serviceWorker.register('/sw.js')}</script>
</head>
<body>
    @include('components.preloader')
    @include('components.dashboard.navbar')
    <div class="dashboard-container">
        @include('components.dashboard.sidebar', ['menuItems' => $menuItems ?? []])
        <main class="main-content">
            <div class="content-wrapper">
                @yield('content')
            </div>
        </main>
    </div>
    @stack('modals')
    <script>
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function today() { var d = new Date(); return pad(d.getDate()) + '/' + pad(d.getMonth() + 1) + '/' + d.getFullYear(); }
    function formatNominal(val) { return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }
    function showToast(el, msg, duration) {
        el.textContent = msg;
        el.style.display = 'flex';
        setTimeout(function() { el.style.display = 'none'; }, duration || 3500);
    }
    (function() {
        var modal = document.getElementById('modalEdit');
        if (!modal) return;
        window.openModal = function(btn) {
            modal.classList.add('active');
            var row = btn ? btn.closest('tr') : null;
            window._editRow = row;
            if (row) {
                var tableName = row.getAttribute('data-target-table') || window._targetTable || '';
                document.getElementById('modalTargetTable').value = tableName;
                var idEl = row.querySelector('[data-target-id]');
                document.getElementById('modalTargetId').value = idEl ? idEl.getAttribute('data-target-id') : '';
                document.getElementById('modalFieldName').value = '';
                document.getElementById('modalOldValue').value = '';
                document.getElementById('modalNewValue').value = '';
                document.getElementById('modalNewValue').style.display = '';
                document.getElementById('modalNewKategori').style.display = 'none';
                document.getElementById('modalNewKategori').value = '';
                document.getElementById('modalAlasan').value = '';
            }
        };
        window.closeModal = function() { modal.classList.remove('active'); window._editRow = null; };
        window.requestAccess = function() {
            var btn = window._loadingBtn;
            var table = document.getElementById('modalTargetTable').value;
            var id = document.getElementById('modalTargetId').value;
            var field = document.getElementById('modalFieldName').value;
            var oldVal = document.getElementById('modalOldValue').value;
            var newVal = field === 'kategori'
                ? document.getElementById('modalNewKategori').value
                : document.getElementById('modalNewValue').value;
            var alasan = document.getElementById('modalAlasan').value;
            if (!field || !alasan) { enableBtn(btn); alert('Harap isi kolom yang ingin diubah dan alasan.'); return; }
            if (!newVal) { enableBtn(btn); alert('Harap isi nilai baru.'); return; }
            if (!table || !id) { enableBtn(btn); alert('Data target tidak ditemukan.'); return; }
            var formData = new FormData();
            formData.append('target_table', table);
            formData.append('target_id', id);
            formData.append('field_name', field);
            formData.append('old_value', oldVal);
            formData.append('new_value', newVal);
            formData.append('alasan', alasan);
            fetchAPI('/bendahara/permintaan-izin', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            })
            .then(function(res) {
                closeModal();
                if (res.success) {
                    showToast(document.getElementById('toastSuccess'), res.message, 4000);
                    document.getElementById('modalFieldName').value = '';
                    document.getElementById('modalOldValue').value = '';
                    document.getElementById('modalNewValue').value = '';
                    document.getElementById('modalAlasan').value = '';
                } else {
                    alert(res.message || 'Gagal mengirim permintaan');
                }
                enableBtn(btn);
            })
            .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); enableBtn(btn); });
        };
        document.addEventListener('change', function(e) {
            if (e.target.id !== 'modalFieldName') return;
            var row = window._editRow;
            if (!row) { document.getElementById('modalOldValue').value = ''; return; }
            var val = '';
            switch (e.target.value) {
                case 'nominal': val = row.getAttribute('data-jumlah') || ''; break;
                case 'kategori': val = row.getAttribute('data-kategori') || ''; break;
                case 'keterangan': val = row.getAttribute('data-keterangan') || ''; break;
            }
            document.getElementById('modalOldValue').value = val;

            var newValInput = document.getElementById('modalNewValue');
            var newKatSelect = document.getElementById('modalNewKategori');
            if (e.target.value === 'kategori') {
                newValInput.style.display = 'none';
                newKatSelect.style.display = '';
                var rowTable = row.getAttribute('data-target-table') || window._targetTable || '';
                var opts = window._kategoriOptions || [];
                if (!opts.length && rowTable === 'pemasukan' && window._kategoriPemasukan) opts = window._kategoriPemasukan;
                if (!opts.length && (rowTable === 'pengeluaran' || rowTable === 'transaksi') && window._kategoriPengeluaran) opts = window._kategoriPengeluaran;
                newKatSelect.innerHTML = '<option value="">Pilih kategori</option>';
                for (var i = 0; i < opts.length; i++) {
                    var v = typeof opts[i] === 'object' ? opts[i].nama : opts[i];
                    newKatSelect.innerHTML += '<option value="' + v + '">' + v + '</option>';
                }
                newKatSelect.value = '';
            } else {
                newValInput.style.display = '';
                newKatSelect.style.display = 'none';
            }
        });
        modal.addEventListener('click', function(e) {
            if (e.target === this || (e.target.classList && e.target.classList.contains('modal-bg'))) closeModal();
        });
    })();
    function _catat(prefix, emptyRowId, tbodyId) {
        var tanggal = document.getElementById('tanggal' + prefix).value;
        var jenis = document.getElementById('jenis' + prefix).value;
        var nominal = document.getElementById('nominal' + prefix).value;
        var keterangan = document.getElementById('keterangan' + prefix).value;
        if (!tanggal || !jenis || !nominal) { alert('Harap isi tanggal, jenis, dan nominal.'); return; }
        var parts = tanggal.split('-');
        tanggal = parts[2] + '/' + parts[1] + '/' + parts[0];
        var nominalFormat = 'Rp ' + parseInt(nominal.replace(/[^0-9]/g, '') || 0).toLocaleString('id-ID');
        var emptyRow = document.getElementById(emptyRowId);
        if (emptyRow) emptyRow.remove();
        var tbody = document.getElementById(tbodyId);
        var row = document.createElement('tr');
        row.innerHTML = '<td>' + tanggal + '</td><td class="nominal-value">' + nominalFormat + '</td><td class="name-cell">' + jenis + '</td><td>' + (keterangan || '-') + '</td><td><button class="btn-edit-disabled" onclick="openModal(this)"><span class="material-icons">lock</span> Edit</button></td>';
        tbody.appendChild(row);
        document.getElementById('tanggal' + prefix).value = '';
        document.getElementById('jenis' + prefix).value = '';
        document.getElementById('nominal' + prefix).value = '';
        document.getElementById('keterangan' + prefix).value = '';
        document.querySelector('.file-upload .upload-placeholder').textContent = 'Upload here...';
    }
    // ── Loading button handler ──
    window._loadingBtn = null;
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-loading]');
        if (!btn || btn.disabled) return;
        window._loadingBtn = btn;
        var orig = btn.getAttribute('data-orig-html');
        if (!orig) btn.setAttribute('data-orig-html', btn.innerHTML);
        btn.disabled = true;
        btn.classList.add('btn-is-loading');
        btn.innerHTML = '<span class="btn-spinner"></span> ' + (btn.getAttribute('data-loading-text') || 'Memproses...');
        btn._loadingTimer = setTimeout(function() { window.enableBtn(); }, 30000);
    }, true);
    window.enableBtn = function(btn) {
        btn = btn || window._loadingBtn;
        if (!btn) return;
        clearTimeout(btn._loadingTimer);
        btn.disabled = false;
        btn.classList.remove('btn-is-loading');
        var orig = btn.getAttribute('data-orig-html');
        if (orig) btn.innerHTML = orig;
        window._loadingBtn = null;
    };
    window.fetchAPI = function(url, opts) {
        return fetch(url, opts).then(function(r) {
            if (!r.ok) {
                return r.text().then(function(text) {
                    try {
                        var body = JSON.parse(text);
                        throw new Error(body.message || 'Error server: ' + r.status);
                    } catch(e) {
                        if (e instanceof SyntaxError) {
                            console.error('fetchAPI: JSON parse failed. Status:', r.status, 'Body:', text.substring(0,500));
                            throw new Error('Error server: ' + r.status);
                        }
                        throw e;
                    }
                });
            }
            return r.json();
        });
    };
    window.addEventListener('pageshow', function() {
        document.querySelectorAll('[data-loading]').forEach(function(el) {
            el.disabled = false;
            el.classList.remove('btn-is-loading');
            var orig = el.getAttribute('data-orig-html');
            if (orig) { el.innerHTML = orig; el.removeAttribute('data-orig-html'); }
        });
        window._loadingBtn = null;
    });
    </script>
    @stack('scripts')
    @livewireScripts
</body>
</html>
