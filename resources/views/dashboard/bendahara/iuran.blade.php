@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/bendahara', 'active' => 'bendahara'],
        ['label' => 'Catat Transaksi', 'url' => '/bendahara/catat-transaksi', 'active' => 'bendahara/catat-transaksi'],
        ['label' => 'Iuran Anggota', 'url' => '/bendahara/iuran', 'active' => 'bendahara/iuran'],
        ['label' => 'Laporan Keuangan', 'url' => '/bendahara/laporan', 'active' => 'bendahara/laporan'],
        ['label' => 'Verifikasi Pendaftaran', 'url' => '/bendahara/verifikasi', 'active' => 'bendahara/verifikasi'],
    ]
])

@section('title', 'Iuran Anggota')

@section('content')
<div class="page-iuran">
    <div class="iuran-header">
        <div>
            <h1>Iuran Tahunan RKM Al-Jannah</h1>
            <p class="subtitle">Monitoring pembayaran iuran anggota</p>
        </div>
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <span id="waStatusBadge" style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:20px;font-size:12px;font-weight:600;{{ $waReady ? 'background:#d1fae5;color:#065f46;' : 'background:#fee2e2;color:#991b1b;' }}">
                <span class="material-icons" style="font-size:14px;">{{ $waReady ? 'check_circle' : 'error' }}</span>
                WhatsApp {{ $waReady ? 'Terhubung' : 'Belum terhubung' }}
            </span>
            <button class="btn-secondary" id="btnKirimTagihanSemua" onclick="kirimTagihanSemua()" data-loading data-loading-text="Mengirim..." {{ $waReady ? '' : 'disabled' }} style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;font-size:13px;">
                <span class="material-icons" style="font-size:18px;">campaign</span>
                Kirim Tagihan
            </button>
            <button class="btn-primary" onclick="openCariIuranModal()" data-loading data-loading-text="Membuka..." style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;font-size:13px;">
                <span class="material-icons" style="font-size:18px;">add</span>
                Tambah Iuran
            </button>
            <div class="year-selector">
                <label>Pilih Tahun</label>
                <select>
                    <option selected>2026</option>
                    <option>2025</option>
                    <option>2024</option>
                </select>
            </div>
        </div>
    </div>

    <div id="alertSuccess" class="alert-success" style="display:none;">
        <span class="material-icons">check_circle</span>
        <span id="alertMsg">Status pembayaran berhasil diperbarui</span>
    </div>

    <div id="toastNotif" class="toast-notif">
        <span class="material-icons">check_circle</span>
        <span id="toastMsg">Pembayaran berhasil dicatat</span>
    </div>

    <div class="table-header-card">
        <h3>Tabel Iuran Tahunan RKM Al-Jannah - Tahun 2026</h3>
    </div>

    <div class="table-card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Anggota</th>
                        <th>Nama Anggota</th>
                        @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $bln)
                        <th class="month-cell">{{ $bln }}</th>
                        @endforeach
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="iuranBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Cari Anggota + Tambah Iuran -->
<div id="modalCariIuran" class="modal-overlay">
    <div class="modal-bg" onclick="closeCariIuranModal()"></div>
    <div class="modal-box">
        <div class="modal-icon">
            <span class="material-icons">person_search</span>
        </div>
        <h3>Tambah Iuran Anggota</h3>

        <div class="form-group">
            <label>No Anggota</label>
            <input type="text" id="cariNoAnggota" list="daftarAnggota" placeholder="Ketik nomor atau nama anggota..." autocomplete="off" class="form-input">
            <datalist id="daftarAnggota"></datalist>
        </div>

        <div id="hasilCariAnggota" style="display:none;padding:12px 16px;background:#f0f7f4;border-radius:10px;margin-bottom:16px;">
            <div style="font-size:12px;color:#6b7280;">Nama Anggota</div>
            <div id="cariNamaAnggota" style="font-size:16px;font-weight:700;color:#1f2937;"></div>
        </div>

        <hr style="border:none;border-top:1px solid #e5e7eb;margin:16px 0;">

        <div class="form-group">
            <label>Nominal Pembayaran</label>
            <div class="nominal-row">
                <div class="nominal-input-wrap">
                    <span class="nominal-prefix">Rp</span>
                    <input type="text" id="cariNominal" value="10.000">
                </div>
                <div class="month-control">
                    <button type="button" class="month-btn" onclick="decrCariBulan()">−</button>
                    <span class="month-qty" id="cariMonthCount">1 Bulan</span>
                    <button type="button" class="month-btn" onclick="incrCariBulan()">+</button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan</label>
            <textarea id="cariKeterangan" placeholder="Tulis keterangan disini..."></textarea>
        </div>

        <div class="form-group">
            <label>Upload Bukti Pembayaran</label>
            <div class="file-upload-custom" onclick="document.getElementById('cariFileInput').click()">
                <span class="upload-placeholder" id="cariFileLabel">Upload here...</span>
                <input type="file" id="cariFileInput" required onchange="document.getElementById('cariFileLabel').textContent = this.files[0].name">
                <div class="upload-icon-box">
                    <span class="material-icons">cloud_upload</span>
                </div>
            </div>
        </div>

        <div class="modal-actions">
            <button class="btn-batal" onclick="closeCariIuranModal()">Batal</button>
            <button class="btn-minta" data-loading onclick="submitCariIuran()">Simpan</button>
        </div>
    </div>
</div>

<div id="modalInfoIuran" class="modal-overlay">
    <div class="modal-bg" onclick="closeInfoIuranModal()"></div>
    <div class="modal-box" style="max-width:540px;text-align:left;">
        <div class="modal-icon">
            <span class="material-icons">account_balance</span>
        </div>
        <h3 style="text-align:center;margin-bottom:12px;">Detail Iuran Anggota</h3>
        <div id="infoIuranContent" style="display:flex;flex-direction:column;gap:12px;">
            <div class="info-row" style="display:flex;gap:12px;">
                <div class="info-field" style="flex:1;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">ID Anggota</label>
                    <span id="infoIuranId" style="font-size:15px;font-weight:600;"></span>
                </div>
                <div class="info-field" style="flex:2;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Nama</label>
                    <span id="infoIuranNama" style="font-size:15px;font-weight:600;"></span>
                </div>
                <div class="info-field" style="flex-shrink:0;">
                    <label style="display:block;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Tahun</label>
                    <select id="infoIuranTahun" style="padding:4px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;font-family:'Poppins',sans-serif;outline:none;background:#fff;">
                    </select>
                </div>
            </div>
            <div style="background:#f9fafb;border-radius:12px;padding:20px;display:flex;gap:24px;">
                <div style="flex:1;text-align:center;">
                    <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Tanggungan</div>
                    <div id="infoIuranTanggunganCount" style="font-size:22px;font-weight:700;color:#dc2626;"></div>
                    <div id="infoIuranTanggunganNominal" style="font-size:13px;color:#6b7280;margin-top:2px;"></div>
                </div>
                <div style="width:1px;background:#e5e7eb;"></div>
                <div style="flex:1;text-align:center;">
                    <div style="font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Sudah Dibayar</div>
                    <div id="infoIuranPaidCount" style="font-size:22px;font-weight:700;color:#065f46;"></div>
                    <div id="infoIuranPaidNominal" style="font-size:13px;color:#6b7280;margin-top:2px;"></div>
                </div>
            </div>
            <div id="infoIuranAccessCode" style="margin-top:12px;display:none;background:#f0fdf4;border:1px solid #86efac;border-radius:10px;padding:12px 16px;text-align:center;">
                <div style="font-size:11px;font-weight:600;color:#16a34a;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Kode Akses Anggota</div>
                <div id="infoIuranAccessCodeValue" style="font-size:22px;font-weight:700;color:#15803d;font-family:monospace;letter-spacing:2px;"></div>
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:6px;">
                    <button onclick="copyAccessCode()" style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;background:#15803d;color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;">Salin Kode</button>
                    <span id="copyAccessCodeMsg" style="font-size:11px;color:#6b7280;display:none;">Tersalin!</span>
                </div>
                <div style="font-size:11px;color:#6b7280;margin-top:2px;">Gunakan kode ini untuk login di halaman anggota</div>
            </div>
        </div>
        <div class="modal-actions" style="gap:8px;flex-wrap:wrap;">
            <button class="btn-batal" onclick="closeInfoIuranModal()" style="flex:1;">Batal</button>
            <button class="btn-batal" id="btnGenerateAccessCode" onclick="generateAccessCode()" data-loading style="flex:1;background:#f3f4f6;color:#374151;border:2px solid #d1d5db;">Generate Access Code</button>
            <button class="btn-batal" id="btnKirimUlangKwitansi" onclick="kirimUlangKwitansi()" data-loading style="flex:1;background:#f3f4f6;color:#374151;border:2px solid #d1d5db;">Kirim Ulang Kwitansi</button>
            <button id="btnInfoTambahTransaksi" class="btn-minta" style="flex:1;">Tambah Transaksi</button>
        </div>
    </div>
</div>

<div id="iuranModal">
    <div class="modal-bg-iuran"></div>
    <div class="modal-box">
        <div class="modal-title">
            Pendataan Iuran tahun 2026
            <button type="button" class="modal-close-iuran" onclick="closeIuranModal()">
                <span class="material-icons">close</span>
            </button>
        </div>

        <div class="form-group">
            <label>Atas Nama Anggota</label>
            <input type="text" id="modalNama" class="input-readonly" readonly>
        </div>

        <div class="form-group">
            <label>Nominal Pembayaran</label>
            <div class="nominal-row">
                <div class="nominal-input-wrap">
                    <span class="nominal-prefix">Rp</span>
                    <input type="text" id="modalNominal" value="10.000">
                </div>
                <div class="month-control">
                    <button type="button" class="month-btn" onclick="decrMonth()">−</button>
                    <span class="month-qty" id="monthCount">1 Bulan</span>
                    <button type="button" class="month-btn" onclick="incrMonth()">+</button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Keterangan</label>
            <textarea id="modalKeterangan" placeholder="Tulis keterangan disini..."></textarea>
        </div>

        <div class="form-group">
            <label>Upload Bukti Pembayaran</label>
            <div class="file-upload-custom" onclick="document.getElementById('fileInput').click()">
                <span class="upload-placeholder" id="fileLabel">Upload here...</span>
                <input type="file" id="fileInput" required onchange="document.getElementById('fileLabel').textContent = this.files[0].name">
                <div class="upload-icon-box">
                    <span class="material-icons">cloud_upload</span>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-batal" onclick="closeIuranModal()">Batal</button>
            <button type="button" class="btn-catat" data-loading onclick="submitIuran()">Catat &amp; Cetak Kwitansi</button>
        </div>
    </div>
</div>

<script>
    var bulanListIuran = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    var members = [];
    var selectedMi = -1, selectedBi = -1, monthQty = 1, nominalPerMonth = 10000;
    var currentYear = {{ date('Y') }};
    var waReady = {{ $waReady ? 'true' : 'false' }};

    function showIuranToast(msg) {
        var el = document.getElementById('toastMsg');
        var box = document.getElementById('toastNotif');
        el.textContent = msg;
        box.style.display = 'flex';
        clearTimeout(box._t);
        box._t = setTimeout(function() { box.style.display = 'none'; }, 4000);
    }

    function fetchIuranData(tahun) {
        fetch('/bendahara/iuran/data?tahun=' + (tahun || currentYear))
            .then(function(r) { return r.json(); })
            .then(function(res) {
                members = res.anggota.map(function(a, i) {
                    return {
                        no: i + 1,
                        id: a.id,
                        noAnggota: a.nomor_anggota,
                        nama: a.nama,
                        telpon: a.telepon,
                        accessCode: a.access_code || '',
                        bulan: a.bulan.map(function(b) {
                            return { status: b.status === 'lunas' ? 'paid' : 'unpaid', date: b.tanggal_bayar || '' };
                        })
                    };
                });
                renderIuranTable();
            });
    }

    function renderIuranTable() {
        var tbody = document.getElementById('iuranBody'), html = '';
        for (var i = 0; i < members.length; i++) {
            var m = members[i];
            html += '<tr><td>' + m.no + '</td><td>' + m.noAnggota + '</td><td class="name-cell">' + m.nama + '</td>';
            for (var b = 0; b < 12; b++) {
                var bln = m.bulan[b], isPaid = bln.status === 'paid';
                html += '<td class="month-cell"><span class="status-dot" data-mi="' + i + '" data-bi="' + b + '" data-paid="' + (isPaid ? '1' : '0') + '"><span class="dot ' + (isPaid ? 'paid' : 'unpaid') + '"></span>' + (bln.date ? '<span class="date-label">' + bln.date + '</span>' : '') + '</span></td>';
            }
            html += '<td><div style="display:flex;gap:6px;align-items:center;justify-content:center;"><button class="btn-icon btn-wa" onclick="kirimTagihan(' + i + ')" title="Kirim Tagihan Iuran via WhatsApp" data-loading data-loading-text="Mengirim..." ' + (waReady ? '' : 'disabled') + '><span class="material-icons" style="font-size:16px;">campaign</span></button><a href="https://wa.me/' + m.telpon.replace(/[^0-9]/g, '') + '" target="_blank" class="btn-icon btn-wa" title="Telepon WA"><span class="material-icons" style="font-size:16px;">phone</span></a><button class="btn-icon btn-info-iuran" onclick="openInfoIuranModal(' + i + ')" title="Info Iuran"><span class="material-icons" style="font-size:16px;">info</span></button></div></td></tr>';
        }
        if (!members.length) {
            html = '<tr><td colspan="15" style="padding:60px 32px;text-align:center;color:#9ca3af;">Belum ada data anggota</td></tr>';
        }
        tbody.innerHTML = html;
    }

    function openIuranModal(mi, bi) {
        selectedMi = mi; selectedBi = bi; monthQty = 1;
        document.getElementById('modalNama').value = members[mi].nama;
        document.getElementById('modalNominal').value = '10.000';
        document.getElementById('monthCount').textContent = '1 Bulan';
        document.getElementById('modalKeterangan').value = '';
        document.getElementById('fileLabel').textContent = 'Upload here...';
        document.getElementById('iuranModal').classList.add('active');
    }
    function closeIuranModal() { document.getElementById('iuranModal').classList.remove('active'); }
    function decrMonth() { if (monthQty > 1) { monthQty--; updateNominal(); } }
    function incrMonth() { var maxQty = 12 - selectedBi; if (monthQty < maxQty) { monthQty++; updateNominal(); } }
    function updateNominal() { document.getElementById('modalNominal').value = formatNominal(nominalPerMonth * monthQty); document.getElementById('monthCount').textContent = monthQty + ' Bulan'; }

    function submitIuran() {
        var btn = window._loadingBtn;
        var member = members[selectedMi];
        var bulanAkhir = Math.min(selectedBi + monthQty - 1, 11);
        var rangeLabel = bulanListIuran[selectedBi] + (monthQty > 1 ? '-' + bulanListIuran[bulanAkhir] : '');

        var formData = new FormData();
        formData.append('anggota_id', member.id);
        formData.append('tahun', currentYear);
        formData.append('bulan_mulai', selectedBi + 1);
        formData.append('jumlah_bulan', monthQty);
        formData.append('nominal', nominalPerMonth);
        formData.append('keterangan', document.getElementById('modalKeterangan').value);
        var fi = document.getElementById('fileInput');
        if (!fi || !fi.files[0]) { enableBtn(btn); alert('Harap upload bukti pembayaran.'); return; }
        formData.append('file_bukti', fi.files[0]);

        fetchAPI('/bendahara/iuran', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(function(res) {
            if (!res.success) { enableBtn(btn); return alert(res.message || 'Gagal menyimpan iuran'); }
            for (var b = selectedBi; b <= bulanAkhir; b++) {
                member.bulan[b].status = 'paid';
                if (!member.bulan[b].date) member.bulan[b].date = today();
            }
            closeIuranModal();
            renderIuranTable();
            var waMsg = res.wa === 'terkirim' ? ' & kwitansi terkirim via WA' : (res.wa === 'gagal' ? ' (kwitansi WA gagal)' : '');
            showIuranToast('Pembayaran ' + member.nama + ' untuk ' + rangeLabel + ' (' + monthQty + ' bln) berhasil dicatat' + waMsg);
            enableBtn(btn);
        })
        .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); enableBtn(btn); });
    }

    /* Modal Cari Anggota + Tambah Iuran */
    var cariMemberIdx = -1;
    var cariMonthQty = 1;
    var cariMaxQty = 12;
    var cariNominalPerMonth = 10000;

    function buildDaftarAnggota() {
        var dl = document.getElementById('daftarAnggota');
        dl.innerHTML = '';
        for (var i = 0; i < members.length; i++) {
            var opt = document.createElement('option');
            opt.value = members[i].noAnggota + ' — ' + members[i].nama;
            dl.appendChild(opt);
        }
    }

    function openCariIuranModal() {
        enableBtn();
        cariMemberIdx = -1;
        cariMonthQty = 1;
        cariMaxQty = 12;
        buildDaftarAnggota();
        document.getElementById('cariNoAnggota').value = '';
        document.getElementById('hasilCariAnggota').style.display = 'none';
        document.getElementById('cariNamaAnggota').textContent = '';
        document.getElementById('cariNominal').value = '10.000';
        document.getElementById('cariMonthCount').textContent = '1 Bulan';
        document.getElementById('cariKeterangan').value = '';
        document.getElementById('cariFileLabel').textContent = 'Upload here...';
        document.getElementById('modalCariIuran').classList.add('active');
        setTimeout(function() { document.getElementById('cariNoAnggota').focus(); }, 100);
    }
    function closeCariIuranModal() { document.getElementById('modalCariIuran').classList.remove('active'); }

    document.getElementById('cariNoAnggota').addEventListener('input', function() {
        var val = this.value.trim();
        if (!val) { document.getElementById('hasilCariAnggota').style.display = 'none'; cariMemberIdx = -1; return; }
        var noAnggota = val.split(' — ')[0];
        var idx = -1;
        for (var i = 0; i < members.length; i++) {
            if (members[i].noAnggota === noAnggota) { idx = i; break; }
        }
        if (idx >= 0) {
            cariMemberIdx = idx;
            cariMaxQty = 0;
            for (var b = 0; b < 12; b++) {
                if (members[idx].bulan[b].status !== 'paid') { cariMaxQty = 12 - b; break; }
            }
            if (cariMaxQty < 1) cariMaxQty = 1;
            cariMonthQty = 1;
            updateCariNominal();
            document.getElementById('cariNamaAnggota').textContent = members[idx].nama;
            document.getElementById('hasilCariAnggota').style.display = 'block';
        } else {
            document.getElementById('hasilCariAnggota').style.display = 'none';
            cariMemberIdx = -1;
        }
    });

    function decrCariBulan() { if (cariMonthQty > 1) { cariMonthQty--; updateCariNominal(); } }
    function incrCariBulan() { if (cariMonthQty < cariMaxQty) { cariMonthQty++; updateCariNominal(); } }
    function updateCariNominal() {
        document.getElementById('cariNominal').value = formatNominal(cariNominalPerMonth * cariMonthQty);
        document.getElementById('cariMonthCount').textContent = cariMonthQty + ' Bulan';
    }

    function submitCariIuran() {
        var btn = window._loadingBtn;
        if (cariMemberIdx < 0) { enableBtn(btn); alert('Cari anggota terlebih dahulu.'); return; }
        var member = members[cariMemberIdx];
        var bulanMulai = -1;
        for (var b = 0; b < 12; b++) {
            if (member.bulan[b].status !== 'paid') { bulanMulai = b; break; }
        }
        if (bulanMulai < 0) { enableBtn(btn); alert('Semua bulan sudah lunas.'); return; }
        var bulanAkhir = Math.min(bulanMulai + cariMonthQty - 1, 11);
        var rangeLabel = bulanListIuran[bulanMulai] + (cariMonthQty > 1 ? '-' + bulanListIuran[bulanAkhir] : '');

        var formData = new FormData();
        formData.append('anggota_id', member.id);
        formData.append('tahun', currentYear);
        formData.append('bulan_mulai', bulanMulai + 1);
        formData.append('jumlah_bulan', cariMonthQty);
        formData.append('nominal', cariNominalPerMonth);
        formData.append('keterangan', document.getElementById('cariKeterangan').value);
        var fi = document.getElementById('cariFileInput');
        if (!fi || !fi.files[0]) { enableBtn(btn); alert('Harap upload bukti pembayaran.'); return; }
        formData.append('file_bukti', fi.files[0]);

        fetchAPI('/bendahara/iuran', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: formData
        })
        .then(function(res) {
            if (!res.success) { enableBtn(btn); return alert(res.message || 'Gagal menyimpan iuran'); }
            for (var b = bulanMulai; b <= bulanAkhir; b++) {
                member.bulan[b].status = 'paid';
                if (!member.bulan[b].date) member.bulan[b].date = today();
            }
            closeCariIuranModal();
            renderIuranTable();
            var waMsg = res.wa === 'terkirim' ? ' & kwitansi terkirim via WA' : (res.wa === 'gagal' ? ' (kwitansi WA gagal)' : '');
            showIuranToast('Pembayaran ' + member.nama + ' untuk ' + rangeLabel + ' (' + cariMonthQty + ' bln) berhasil dicatat' + waMsg);
            enableBtn(btn);
        })
        .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); enableBtn(btn); });
    }

    function buildInfoTahunDropdown() {
        var sel = document.getElementById('infoIuranTahun');
        sel.innerHTML = '';
        var thisYear = {{ date('Y') }};
        for (var y = thisYear; y >= 2020; y--) {
            var opt = document.createElement('option');
            opt.value = y; opt.textContent = y;
            sel.appendChild(opt);
        }
    }
    buildInfoTahunDropdown();

    var _infoMemberId = null;
    var _infoMemberIdx = -1;

    function openInfoIuranModal(mi) {
        var m = members[mi];
        if (!m) return;
        _infoMemberId = m.id;
        _infoMemberIdx = mi;
        document.getElementById('infoIuranId').textContent = m.noAnggota;
        document.getElementById('infoIuranNama').textContent = m.nama;
        document.getElementById('infoIuranTahun').value = currentYear;

        var el = document.getElementById('infoIuranAccessCode');
        var valEl = document.getElementById('infoIuranAccessCodeValue');
        var btnGen = document.getElementById('btnGenerateAccessCode');
        if (m.accessCode) {
            valEl.textContent = m.accessCode;
            el.style.display = 'block';
            btnGen.textContent = 'Regenerate Access Code';
        } else {
            el.style.display = 'none';
            btnGen.textContent = 'Generate Access Code';
        }

        updateInfoIuranData(currentYear, m.id, mi);
        document.getElementById('modalInfoIuran').classList.add('active');
    }

    function closeInfoIuranModal() {
        document.getElementById('modalInfoIuran').classList.remove('active');
    }

    function updateInfoIuranData(tahun, memberId, mi) {
        var m = mi >= 0 ? members[mi] : null;
        if (m && tahun === currentYear) {
            renderInfoIuranFromMember(m, mi);
        } else {
            fetch('/bendahara/iuran/data?tahun=' + tahun + '&member_id=' + memberId)
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.anggota && res.anggota.length) {
                        var a = res.anggota[0];
                        var paid = 0, unpaid = 0;
                        for (var b = 0; b < a.bulan.length; b++) {
                            if (a.bulan[b].status === 'lunas') paid++; else unpaid++;
                        }
                        document.getElementById('infoIuranTanggunganCount').textContent = unpaid + ' Bulan';
                        document.getElementById('infoIuranTanggunganNominal').textContent = 'Rp ' + (unpaid * nominalPerMonth).toLocaleString('id-ID');
                        document.getElementById('infoIuranPaidCount').textContent = paid + ' Bulan';
                        document.getElementById('infoIuranPaidNominal').textContent = 'Rp ' + (paid * nominalPerMonth).toLocaleString('id-ID');
                    }
                    var cur = mi >= 0 ? members[mi] : null;
                    if (cur) {
                        var firstUnpaid = -1;
                        for (var b = 0; b < 12; b++) {
                            if (cur.bulan[b].status !== 'paid') { firstUnpaid = b; break; }
                        }
                        var btnTambah = document.getElementById('btnInfoTambahTransaksi');
                        if (firstUnpaid >= 0) {
                            btnTambah.onclick = (function(idx, bln) {
                                return function() { closeInfoIuranModal(); openIuranModal(idx, bln); };
                            })(mi, firstUnpaid);
                        } else {
                            btnTambah.onclick = function() { alert('Semua bulan sudah lunas.'); };
                        }
                    }
                });
        }
    }

    function renderInfoIuranFromMember(m, mi) {
        var paid = 0, unpaid = 0;
        for (var b = 0; b < 12; b++) {
            if (m.bulan[b].status === 'paid') paid++; else unpaid++;
        }
        document.getElementById('infoIuranTanggunganCount').textContent = unpaid + ' Bulan';
        document.getElementById('infoIuranTanggunganNominal').textContent = 'Rp ' + (unpaid * nominalPerMonth).toLocaleString('id-ID');
        document.getElementById('infoIuranPaidCount').textContent = paid + ' Bulan';
        document.getElementById('infoIuranPaidNominal').textContent = 'Rp ' + (paid * nominalPerMonth).toLocaleString('id-ID');

        var btnTambah = document.getElementById('btnInfoTambahTransaksi');
        var firstUnpaid = -1;
        for (var b = 0; b < 12; b++) {
            if (m.bulan[b].status !== 'paid') { firstUnpaid = b; break; }
        }
        if (firstUnpaid >= 0) {
            btnTambah.onclick = (function(idx, bln) {
                return function() { closeInfoIuranModal(); openIuranModal(idx, bln); };
            })(mi, firstUnpaid);
        } else {
            btnTambah.onclick = function() { alert('Semua bulan sudah lunas.'); };
        }
    }

    document.getElementById('infoIuranTahun').addEventListener('change', function() {
        var tahun = parseInt(this.value);
        if (tahun === currentYear && _infoMemberIdx >= 0) {
            renderInfoIuranFromMember(members[_infoMemberIdx], _infoMemberIdx);
        } else {
            updateInfoIuranData(tahun, _infoMemberId, -1);
        }
    });

    document.querySelector('.year-selector select').addEventListener('change', function() {
        currentYear = parseInt(this.value);
        document.querySelector('.table-header-card h3').textContent = 'Tabel Iuran Tahunan RKM Al-Jannah - Tahun ' + currentYear;
        fetchIuranData(currentYear);
    });

    document.querySelector('.modal-bg-iuran').addEventListener('click', function() { closeIuranModal(); });
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closeIuranModal(); closeCariIuranModal(); } });

    function generateAccessCode() {
        var member = members[_infoMemberIdx];
        if (!member) { enableBtn(document.getElementById('btnGenerateAccessCode')); return; }
        fetchAPI('/bendahara/iuran/generate-access-code', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ anggota_id: member.id })
        })
        .then(function(res) {
            if (!res.success) { alert(res.message || 'Gagal generate access code'); return; }
            member.accessCode = res.access_code;
            document.getElementById('infoIuranAccessCodeValue').textContent = res.access_code;
            document.getElementById('infoIuranAccessCode').style.display = 'block';
            var btn = document.getElementById('btnGenerateAccessCode');
            if (btn) { btn.setAttribute('data-orig-html', 'Regenerate Access Code'); btn.innerHTML = 'Regenerate Access Code'; }
        })
        .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); })
        .finally(function() { enableBtn(document.getElementById('btnGenerateAccessCode')); });
    }

    function kirimTagihan(idx) {
        var member = members[idx];
        if (!member) { enableBtn(); return; }
        if (!waReady) { enableBtn(); alert('WhatsApp belum terhubung. Silakan hubungkan di menu adminweb.'); return; }
        fetchAPI('/bendahara/iuran/kirim-tagihan', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
            body: JSON.stringify({ anggota_id: member.id })
        })
        .then(function(res) {
            enableBtn();
            if (!res.success) { return alert(res.message || 'Gagal mengirim tagihan'); }
            showIuranToast(res.message);
        })
        .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); enableBtn(); });
    }

    function kirimTagihanSemua() {
        if (!waReady) { enableBtn(); alert('WhatsApp belum terhubung. Silakan hubungkan di menu adminweb.'); return; }
        if (!confirm('Kirim tagihan iuran ke semua anggota yang belum lunas?')) { enableBtn(); return; }
        fetchAPI('/bendahara/iuran/kirim-tagihan', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        })
        .then(function(res) {
            enableBtn();
            if (!res.success) { return alert(res.message || 'Gagal mengirim tagihan'); }
            showIuranToast(res.message);
        })
        .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); enableBtn(); });
    }

    function kirimUlangKwitansi() {
        var btn = document.getElementById('btnKirimUlangKwitansi');
        var member = members[_infoMemberIdx];
        if (!member) { enableBtn(btn); return; }
        if (!waReady) { enableBtn(btn); alert('WhatsApp belum terhubung. Silakan hubungkan di menu adminweb.'); return; }
        fetchAPI('/bendahara/iuran/kirim-kwitansi', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
            body: JSON.stringify({ anggota_id: member.id })
        })
        .then(function(res) {
            enableBtn(btn);
            if (!res.success) { return alert(res.message || 'Gagal mengirim kwitansi'); }
            var msg = res.wa === 'terkirim' ? 'Kwitansi terkirim via WhatsApp' : (res.wa === 'gagal' ? 'Kwitansi gagal terkirim' : 'Kwitansi tidak terkirim (WA tidak aktif)');
            showIuranToast(msg);
        })
        .catch(function(e) { console.error(e); alert('Gagal: ' + e.message); enableBtn(btn); });
    }

    fetchIuranData(currentYear);

    window.decrMonth = decrMonth;
    window.incrMonth = incrMonth;
    window.submitIuran = submitIuran;
    window.decrCariBulan = decrCariBulan;
    window.incrCariBulan = incrCariBulan;
    window.submitCariIuran = submitCariIuran;
    window.generateAccessCode = generateAccessCode;
    window.kirimTagihan = kirimTagihan;
    window.kirimTagihanSemua = kirimTagihanSemua;
    window.kirimUlangKwitansi = kirimUlangKwitansi;
    window.openInfoIuranModal = openInfoIuranModal;
    window.closeInfoIuranModal = closeInfoIuranModal;

    function copyAccessCode() {
        var code = document.getElementById('infoIuranAccessCodeValue').textContent;
        if (!code) return;
        navigator.clipboard.writeText(code).then(function() {
            var msg = document.getElementById('copyAccessCodeMsg');
            msg.style.display = 'inline';
            setTimeout(function() { msg.style.display = 'none'; }, 2000);
        });
    }
</script>
@endsection
