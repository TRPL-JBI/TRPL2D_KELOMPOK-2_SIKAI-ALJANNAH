@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Beranda', 'url' => '/adminweb', 'active' => 'adminweb'],
        ['label' => 'Posts', 'url' => '/adminweb/posts', 'active' => 'adminweb/posts*'],
        ['label' => 'Pages', 'url' => '/adminweb/pages', 'active' => 'adminweb/pages*'],
        ['label' => 'Menus', 'url' => '/adminweb/menus', 'active' => 'adminweb/menus*'],
        ['label' => 'Files & Images', 'url' => '/adminweb/files', 'active' => 'adminweb/files*'],
    ]
])

@section('title', 'Edit Halaman Beranda')

@section('content')
<style>
    .page-edit-wrap {
        max-width: 900px;
        margin: 0 auto;
    }

    .page-edit-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .page-edit-title {
        font-size: 24px;
        font-weight: 700;
        color: #16423c;
    }

    .page-edit-card {
        background: #fff;
        border: 1px solid #d4d4d4;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .page-edit-card-header {
        padding: 16px 24px;
        background: #f8faf9;
        border-bottom: 1px solid #e8e8e8;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .page-edit-card-header .material-icons {
        color: #16423c;
        font-size: 20px;
    }

    .page-edit-card-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #16423c;
        margin: 0;
    }

    .page-edit-card-body {
        padding: 20px 24px;
    }

    .page-edit-field {
        margin-bottom: 16px;
    }

    .page-edit-field:last-child {
        margin-bottom: 0;
    }

    .page-edit-field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .page-edit-field input[type="text"],
    .page-edit-field textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        color: #1a1a1a;
        background: #f8f9fa;
        outline: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .page-edit-field input[type="text"]:focus,
    .page-edit-field textarea:focus {
        border-color: #16423c;
    }

    .page-edit-field textarea {
        min-height: 80px;
        resize: vertical;
    }

    .page-edit-subgrid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .page-edit-subgrid-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 12px;
    }

    .page-edit-subgrid input[type="text"],
    .page-edit-subgrid textarea {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e0e0e0;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        color: #1a1a1a;
        background: #f8f9fa;
        outline: none;
        transition: border-color 0.2s;
        box-sizing: border-box;
    }

    .page-edit-subgrid input[type="text"]:focus,
    .page-edit-subgrid textarea:focus {
        border-color: #16423c;
    }

    .page-edit-subgrid textarea {
        min-height: 60px;
        resize: vertical;
    }

    .dynamic-item-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-remove-item {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border: none;
        border-radius: 50%;
        background: #fee2e2;
        color: #9a0000;
        cursor: pointer;
        flex-shrink: 0;
        transition: background 0.2s;
    }

    .btn-remove-item:hover {
        background: #fecaca;
    }

    .btn-remove-item .material-icons {
        font-size: 16px;
    }

    .btn-add-item {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 6px 14px;
        border: 1.5px dashed #16423c;
        border-radius: 8px;
        background: transparent;
        color: #16423c;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        transition: background 0.2s;
    }

    .btn-add-item:hover {
        background: #f0f7f5;
    }

    .btn-add-item .material-icons {
        font-size: 16px;
    }

    @media (max-width: 640px) {
        .page-edit-subgrid,
        .page-edit-subgrid-3 {
            grid-template-columns: 1fr;
        }
    }

    .page-edit-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 24px;
    }

    .btn-page-edit {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 28px;
        border: none;
        border-radius: 999px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: opacity 0.2s;
    }

    .btn-page-edit:hover {
        opacity: 0.85;
    }

    .btn-page-edit-primary {
        background-color: #16423c;
        color: #fff;
    }

    .btn-page-edit-secondary {
        background-color: #d9d9d9;
        color: #2b2b2b;
    }
</style>

<div class="page-edit-wrap">
    <div class="page-edit-header">
        <h1 class="page-edit-title">Edit Halaman Beranda</h1>
        <a href="{{ route('adminweb.pages') }}" class="btn-page-edit btn-page-edit-secondary">
            <span class="material-icons" style="font-size:18px;">arrow_back</span>
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('adminweb.pages.update', $page->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Hero Section -->
        <div class="page-edit-card">
            <div class="page-edit-card-header">
                <span class="material-icons">home</span>
                <h3>Hero Section</h3>
            </div>
            <div class="page-edit-card-body">
                <div class="page-edit-field">
                    <label>Judul Hero</label>
                    <input type="text" name="hero[title]" value="{{ $data['hero']['title'] ?? '' }}">
                </div>
                <div class="page-edit-field">
                    <label>Subtitle Hero</label>
                    <input type="text" name="hero[subtitle]" value="{{ $data['hero']['subtitle'] ?? '' }}">
                </div>
            </div>
        </div>

        <!-- About Section -->
        <div class="page-edit-card">
            <div class="page-edit-card-header">
                <span class="material-icons">info</span>
                <h3>Tentang (Apa itu RKM Al-Jannah)</h3>
            </div>
            <div class="page-edit-card-body">
                <div class="page-edit-field">
                    <label>Judul</label>
                    <input type="text" name="about[title]" value="{{ $data['about']['title'] ?? '' }}">
                </div>
                <div class="page-edit-field">
                    <label>Konten</label>
                    <textarea name="about[content]" rows="3">{{ $data['about']['content'] ?? '' }}</textarea>
                </div>
                <div class="page-edit-field">
                    <label>Footer</label>
                    <input type="text" name="about[footer]" value="{{ $data['about']['footer'] ?? '' }}">
                </div>
            </div>
        </div>

        <!-- Vision & Mission -->
        <div class="page-edit-card">
            <div class="page-edit-card-header">
                <span class="material-icons">visibility</span>
                <h3>Visi &amp; Misi</h3>
            </div>
            <div class="page-edit-card-body">
                <div class="page-edit-field">
                    <label>Visi</label>
                    <textarea name="vision" rows="2">{{ $data['vision'] ?? '' }}</textarea>
                </div>
                <hr style="border:none;border-top:1px solid #e8e8e8;margin:16px 0;">
                <div style="font-size:13px;font-weight:700;color:#16423c;margin-bottom:12px;">Poin-Poin Misi</div>
                <div id="mission-items-container">
                    @php $missionItems = $data['mission']['items'] ?? []; @endphp
                    @foreach($missionItems as $i => $point)
                    <div class="mission-point" style="background:#f8faf9;border:1px solid #e8e8e8;border-radius:10px;padding:16px;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                            <label style="font-size:12px;font-weight:700;color:#16423c;">Poin Utama {{ $loop->iteration }}</label>
                            <button type="button" class="btn-remove-item" onclick="removeMissionPoint(this)">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div style="margin-bottom:8px;">
                            <input type="text" name="mission[items][{{ $i }}][text]" value="{{ $point['text'] ?? '' }}" placeholder="Teks poin utama" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:'Poppins',sans-serif;font-size:14px;color:#1a1a1a;background:#f8f9fa;outline:none;box-sizing:border-box;">
                        </div>
                        <div style="margin-bottom:8px;">
                            <input type="text" name="mission[items][{{ $i }}][subheading]" value="{{ $point['subheading'] ?? '' }}" placeholder="Subjudul (opsional)" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:'Poppins',sans-serif;font-size:14px;color:#1a1a1a;background:#f8f9fa;outline:none;box-sizing:border-box;">
                        </div>
                        <div style="font-size:11px;font-weight:600;color:#6b7280;margin-bottom:6px;">Subpoin (opsional):</div>
                        <div class="mission-sub-items">
                            @foreach(($point['sub_items'] ?? []) as $sub)
                            <div class="dynamic-item-row" style="margin-bottom:6px;">
                                <input type="text" name="mission[items][{{ $i }}][sub_items][]" value="{{ $sub }}" placeholder="Subpoin" style="width:100%;padding:8px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:'Poppins',sans-serif;font-size:13px;color:#1a1a1a;background:#fff;outline:none;box-sizing:border-box;">
                                <button type="button" class="btn-remove-item" onclick="this.parentElement.remove()">
                                    <span class="material-icons">close</span>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn-add-item" onclick="addSubItem(this)">
                            <span class="material-icons">add</span> Tambah Subpoin
                        </button>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn-add-item" onclick="addMissionPoint()" style="margin-top:4px;">
                    <span class="material-icons">add</span> Tambah Poin Utama
                </button>
                <template id="mission-point-template">
                    <div class="mission-point" style="background:#f8faf9;border:1px solid #e8e8e8;border-radius:10px;padding:16px;margin-bottom:12px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                            <label style="font-size:12px;font-weight:700;color:#16423c;">Poin Utama Baru</label>
                            <button type="button" class="btn-remove-item" onclick="removeMissionPoint(this)">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div style="margin-bottom:8px;">
                            <input type="text" name="mission[items][__INDEX__][text]" placeholder="Teks poin utama" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:'Poppins',sans-serif;font-size:14px;color:#1a1a1a;background:#f8f9fa;outline:none;box-sizing:border-box;">
                        </div>
                        <div style="margin-bottom:8px;">
                            <input type="text" name="mission[items][__INDEX__][subheading]" placeholder="Subjudul (opsional)" style="width:100%;padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:'Poppins',sans-serif;font-size:14px;color:#1a1a1a;background:#f8f9fa;outline:none;box-sizing:border-box;">
                        </div>
                        <div style="font-size:11px;font-weight:600;color:#6b7280;margin-bottom:6px;">Subpoin (opsional):</div>
                        <div class="mission-sub-items"></div>
                        <button type="button" class="btn-add-item" onclick="addSubItem(this)">
                            <span class="material-icons">add</span> Tambah Subpoin
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Services -->
        <div class="page-edit-card">
            <div class="page-edit-card-header">
                <span class="material-icons">star</span>
                <h3>Layanan Kami</h3>
            </div>
            <div class="page-edit-card-body">
                <div class="page-edit-field">
                    <label>Judul Section</label>
                    <input type="text" name="services[title]" value="{{ $data['services']['title'] ?? '' }}">
                </div>
                <div id="services-container">
                    @php $svcItems = $data['services']['items'] ?? []; @endphp
                    @foreach($svcItems as $i => $svc)
                    <div class="dynamic-item-row" style="flex-direction:column;gap:8px;padding:12px 0;{{ $loop->first ? '' : 'border-top:1px solid #e8e8e8;' }}">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <label style="font-size:12px;font-weight:700;color:#16423c;">Layanan {{ $loop->iteration }}</label>
                            <button type="button" class="btn-remove-item" onclick="this.closest('.dynamic-item-row').remove()">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="page-edit-subgrid">
                            <input type="text" name="services[items][{{ $i }}][title]" value="{{ $svc['title'] ?? '' }}" placeholder="Judul">
                            <textarea name="services[items][{{ $i }}][description]" rows="2" placeholder="Deskripsi">{{ $svc['description'] ?? '' }}</textarea>
                        </div>
                        <div style="margin-top:8px;">
                            <input type="hidden" name="services[items][{{ $i }}][existing_image]" value="{{ $svc['image'] ?? '' }}">
                            <input type="file" name="services[items][{{ $i }}][image_file]" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp" style="width:100%;padding:8px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box;">
                            @if(!empty($svc['image']))
                            <div style="margin-top:6px;display:flex;align-items:center;gap:8px;">
                                @if(is_numeric($svc['image']))
                                <img src="{{ route('media.download', (int)$svc['image']) }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #e0e0e0;">
                                @elseif(str_starts_with($svc['image'], '<svg'))
                                <div style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;border:1px solid #e0e0e0;border-radius:6px;">{!! $svc['image'] !!}</div>
                                @else
                                <img src="{{ asset($svc['image']) }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #e0e0e0;">
                                @endif
                                <span style="font-size:11px;color:#6b7280;">Gambar saat ini</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn-add-item" onclick="addServiceItem()" style="margin-top:8px;">
                    <span class="material-icons">add</span> Tambah Layanan
                </button>
                <template id="service-template">
                    <div class="dynamic-item-row" style="flex-direction:column;gap:8px;padding:12px 0;border-top:1px solid #e8e8e8;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <label style="font-size:12px;font-weight:700;color:#16423c;">Layanan Baru</label>
                            <button type="button" class="btn-remove-item" onclick="this.closest('.dynamic-item-row').remove()">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="page-edit-subgrid">
                            <input type="text" name="services[items][__INDEX__][title]" placeholder="Judul">
                            <textarea name="services[items][__INDEX__][description]" rows="2" placeholder="Deskripsi"></textarea>
                        </div>
                        <div style="margin-top:8px;">
                            <input type="hidden" name="services[items][__INDEX__][existing_image]" value="">
                            <input type="file" name="services[items][__INDEX__][image_file]" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp" style="width:100%;padding:8px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box;">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Member Benefits -->
        <div class="page-edit-card">
            <div class="page-edit-card-header">
                <span class="material-icons">card_giftcard</span>
                <h3>Keuntungan Anggota</h3>
            </div>
            <div class="page-edit-card-body">
                <div class="page-edit-field">
                    <label>Judul Section</label>
                    <input type="text" name="member_benefits[title]" value="{{ $data['member_benefits']['title'] ?? '' }}">
                </div>
                <div id="member-benefits-container">
                    @php $mbItems = $data['member_benefits']['items'] ?? []; @endphp
                    @foreach($mbItems as $i => $mb)
                    <div class="dynamic-item-row" style="flex-direction:column;gap:8px;padding:12px 0;{{ $loop->first ? '' : 'border-top:1px solid #e8e8e8;' }}">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <label style="font-size:12px;font-weight:700;color:#16423c;">Keuntungan {{ $loop->iteration }}</label>
                            <button type="button" class="btn-remove-item" onclick="this.closest('.dynamic-item-row').remove()">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="page-edit-subgrid">
                            <input type="text" name="member_benefits[items][{{ $i }}][title]" value="{{ $mb['title'] ?? '' }}" placeholder="Judul">
                            <div>
                                <input type="hidden" name="member_benefits[items][{{ $i }}][existing_image]" value="{{ $mb['image'] ?? '' }}">
                                <input type="file" name="member_benefits[items][{{ $i }}][image_file]" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp" style="width:100%;padding:8px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box;">
                                @if(!empty($mb['image']))
                                <div style="margin-top:6px;display:flex;align-items:center;gap:8px;">
                                    @if(is_numeric($mb['image']))
                                    <img src="{{ route('media.download', (int)$mb['image']) }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #e0e0e0;">
                                    @elseif(str_starts_with($mb['image'], '<svg'))
                                    <div style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;border:1px solid #e0e0e0;border-radius:6px;">{!! $mb['image'] !!}</div>
                                    @else
                                    <img src="{{ asset($mb['image']) }}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #e0e0e0;">
                                    @endif
                                    <span style="font-size:11px;color:#6b7280;">Gambar saat ini</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="btn-add-item" onclick="addMemberBenefitItem()" style="margin-top:8px;">
                    <span class="material-icons">add</span> Tambah Keuntungan
                </button>
                <template id="member-benefit-template">
                    <div class="dynamic-item-row" style="flex-direction:column;gap:8px;padding:12px 0;border-top:1px solid #e8e8e8;">
                        <div style="display:flex;align-items:center;justify-content:space-between;">
                            <label style="font-size:12px;font-weight:700;color:#16423c;">Keuntungan Baru</label>
                            <button type="button" class="btn-remove-item" onclick="this.closest('.dynamic-item-row').remove()">
                                <span class="material-icons">close</span>
                            </button>
                        </div>
                        <div class="page-edit-subgrid">
                            <input type="text" name="member_benefits[items][__INDEX__][title]" placeholder="Judul">
                            <div>
                                <input type="hidden" name="member_benefits[items][__INDEX__][existing_image]" value="">
                                <input type="file" name="member_benefits[items][__INDEX__][image_file]" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp" style="width:100%;padding:8px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box;">
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Contact -->
        <div class="page-edit-card">
            <div class="page-edit-card-header">
                <span class="material-icons">contact_phone</span>
                <h3>Kontak</h3>
            </div>
            <div class="page-edit-card-body">
                <div class="page-edit-field">
                    <label>Judul Section</label>
                    <input type="text" name="contact[title]" value="{{ $data['contact']['title'] ?? '' }}">
                </div>
                <div class="page-edit-field">
                    <label>Alamat</label>
                    <textarea name="contact[address]" rows="3">{{ $data['contact']['address'] ?? '' }}</textarea>
                </div>
                <div class="page-edit-subgrid">
                    <div class="page-edit-field">
                        <label>Email</label>
                        <input type="text" name="contact[email]" value="{{ $data['contact']['email'] ?? '' }}">
                    </div>
                    <div class="page-edit-field">
                        <label>Telepon</label>
                        <input type="text" name="contact[phone]" value="{{ $data['contact']['phone'] ?? '' }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="page-edit-actions">
            <a href="{{ route('adminweb.pages') }}" class="btn-page-edit btn-page-edit-secondary">Batal</a>
            <button type="submit" class="btn-page-edit btn-page-edit-primary">
                <span class="material-icons" style="font-size:18px;">save</span>
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@if(session('success'))
<div style="position:fixed;top:20px;right:20px;z-index:9999;background-color:#d8efdd;border:1px solid #35ab50;border-radius:12px;padding:1rem 1.25rem;display:flex;align-items:center;gap:0.75rem;font-family:'Segoe UI',sans-serif;font-size:0.95rem;color:#154420;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
    <span class="material-icons" style="color:#35ab50;font-size:20px;">check_circle</span>
    {{ session('success') }}
</div>
<script>
    setTimeout(function(){ document.querySelector('[style*="position:fixed"][style*="top:20px"]')?.remove(); }, 4000);
</script>
@endif

<script>
function addMissionPoint() {
    var container = document.getElementById('mission-items-container');
    var template = document.getElementById('mission-point-template');
    var index = container.querySelectorAll('.mission-point').length;
    var html = template.innerHTML.replace(/__INDEX__/g, index);
    var div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    updateMissionLabels();
}

function addSubItem(btn) {
    var container = btn.previousElementSibling;
    var row = document.createElement('div');
    row.className = 'dynamic-item-row';
    row.style.marginBottom = '6px';
    row.innerHTML = '<input type="text" name="' + getSubItemsName(btn) + '" placeholder="Subpoin" style="width:100%;padding:8px 12px;border:1.5px solid #e0e0e0;border-radius:8px;font-family:\'Poppins\',sans-serif;font-size:13px;color:#1a1a1a;background:#fff;outline:none;box-sizing:border-box;">' +
        '<button type="button" class="btn-remove-item" onclick="this.parentElement.remove()"><span class="material-icons">close</span></button>';
    container.appendChild(row);
}

function getSubItemsName(btn) {
    var point = btn.closest('.mission-point');
    var index = Array.from(document.getElementById('mission-items-container').children).indexOf(point);
    return 'mission[items][' + index + '][sub_items][]';
}

function addServiceItem() {
    var container = document.getElementById('services-container');
    var template = document.getElementById('service-template');
    var index = container.querySelectorAll('.dynamic-item-row').length;
    var html = template.innerHTML.replace(/__INDEX__/g, index);
    var div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    updateServiceLabels();
}

function addMemberBenefitItem() {
    var container = document.getElementById('member-benefits-container');
    var template = document.getElementById('member-benefit-template');
    var index = container.querySelectorAll('.dynamic-item-row').length;
    var html = template.innerHTML.replace(/__INDEX__/g, index);
    var div = document.createElement('div');
    div.innerHTML = html;
    container.appendChild(div.firstElementChild);
    updateBenefitLabels();
}

function removeMissionPoint(btn) {
    btn.closest('.mission-point').remove();
    updateMissionLabels();
}

function updateMissionLabels() {
    var container = document.getElementById('mission-items-container');
    var points = container.querySelectorAll('.mission-point');
    points.forEach(function(point, i) {
        var label = point.querySelector('label');
        if (label) label.textContent = 'Poin Utama ' + (i + 1);
        var allInputs = point.querySelectorAll('input[name^="mission[items]"]');
        allInputs.forEach(function(input) {
            input.name = input.name.replace(/mission\[items\]\[\d+\]/, 'mission[items][' + i + ']');
        });
    });
}

function updateServiceLabels() {
    var container = document.getElementById('services-container');
    var labels = container.querySelectorAll('.dynamic-item-row > div:first-child label');
    labels.forEach(function(label, i) {
        label.textContent = 'Layanan ' + (i + 1);
    });
}

function updateBenefitLabels() {
    var container = document.getElementById('member-benefits-container');
    var labels = container.querySelectorAll('.dynamic-item-row > div:first-child label');
    labels.forEach(function(label, i) {
        label.textContent = 'Keuntungan ' + (i + 1);
    });
}
</script>
@endsection
