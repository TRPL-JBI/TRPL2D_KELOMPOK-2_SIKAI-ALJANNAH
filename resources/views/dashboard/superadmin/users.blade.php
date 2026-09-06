@extends('layouts.dashboard', [
    'menuItems' => [
        ['label' => 'Dashboard', 'url' => '/superadmin', 'active' => 'superadmin'],
        ['label' => 'Manajemen User', 'url' => '/superadmin/user', 'active' => 'superadmin/user'],
        ['label' => 'Storage File', 'url' => '/superadmin/file', 'active' => 'superadmin/file'],
        ['label' => 'Log Aktivitas', 'url' => '/superadmin/log', 'active' => 'superadmin/log'],
    ]
])

@section('title', 'Manajemen User')

@section('content')
    @if(session('success'))
        <div style="background-color: #d8efdd; border: 1px solid #35ab50; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-family: 'Segoe UI', sans-serif; font-size: 0.95rem; color: #154420;">
            <span class="material-icons" style="color: #35ab50; font-size: 20px;">check_circle</span>
            {{ session('success') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-400);">Manajemen Pengguna</span>
            <h1 style="font-size: 28px; font-weight: 800; color: var(--primary-500); margin: 4px 0 0 0;">Daftar Pengguna Sistem</h1>
            <p style="font-size: 14px; color: #6b7280; margin-top: 6px;">Kelola seluruh akun pengguna, role, dan status</p>
        </div>
        <button class="btn btn-sm btn-primary" onclick="openTambahModal()" style="display: inline-flex; align-items: center; gap: 6px;">
            <span class="material-icons" style="font-size: 16px;">person_add</span> Tambah Pengguna
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="card" style="padding: 16px; margin-bottom: 16px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <span class="material-icons" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 18px; pointer-events: none;">search</span>
            <input type="text" name="search" placeholder="Cari nama atau email..." class="form-input" style="padding-left: 40px; width: 100%;" value="{{ request('search') }}">
        </div>
        <select class="form-select" style="min-width: 140px; cursor: pointer;">
            <option value="">Semua Role</option>
            <option value="superadmin">Super Admin</option>
            <option value="ketua">Ketua</option>
            <option value="sekretaris">Sekretaris</option>
            <option value="bendahara">Bendahara</option>
            <option value="logistik">Logistik</option>
            <option value="adminweb">Admin Web</option>
        </select>
        <select class="form-select" style="min-width: 140px; cursor: pointer;">
            <option value="">Semua Status</option>
            <option value="aktif">Aktif</option>
            <option value="non_aktif">Non-aktif</option>
        </select>
        <button class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 4px;">
            <span class="material-icons" style="font-size: 14px;">refresh</span> Reset
        </button>
        <span style="font-size: 12px; color: #6b7280; margin-left: auto;">{{ $users->total() }} hasil</span>
    </div>

    {{-- User Table --}}
    <x-dashboard.data-table
        title="Daftar Pengguna"
        :headers="['Nomor', 'Nama User', 'Email', 'Role', 'Status', 'Dibuat', 'Aksi']"
        :paginator="$users"
        search-placeholder="Cari user..."
    >
        @foreach($users as $index => $user)
            <tr>
                <td>{{ $users->firstItem() + $index }}</td>
                <td style="font-weight: 600; color: #1f2937;">{{ $user->nama }}</td>
                <td style="color: #6b7280;">{{ $user->email }}</td>
                <td>
                    @php
                        $roleBadge = match($user->role) {
                            'superadmin' => 'badge-danger',
                            'ketua' => 'badge-info',
                            'sekretaris' => 'badge-warning',
                            'bendahara' => 'badge-success',
                            'logistik' => 'badge-info',
                            'adminweb' => 'badge-info',
                            default => 'badge-info',
                        };
                    @endphp
                    <span class="badge {{ $roleBadge }}">{{ ucfirst($user->role) }}</span>
                </td>
                <td>
                    <span class="badge {{ $user->status === 'aktif' ? 'badge-success' : 'badge-danger' }}">
                        {{ $user->status === 'aktif' ? 'Aktif' : 'Non-Aktif' }}
                    </span>
                </td>
                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                <td>
                    <div style="display: flex; gap: 6px;">
                        <button class="btn btn-sm btn-outline-primary"
                                onclick="openEditModal(this)"
                                data-id="{{ $user->id }}"
                                data-nama="{{ $user->nama }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                data-status="{{ $user->status }}">
                            <span class="material-icons" style="font-size: 16px;">edit</span>
                        </button>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-dashboard.data-table>

    {{-- Modal Tambah User --}}
    <div class="modal-overlay" id="tambahUserModal">
        <div class="modal" style="max-width: 520px;">
            <div class="modal-header">
                <h3 class="modal-title">Tambah Pengguna</h3>
                <button class="modal-close" onclick="closeTambahModal()">&times;</button>
            </div>
            <form id="tambahUserForm" method="POST" action="/superadmin/user">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="tambah_nama" class="form-label">Nama Lengkap</label>
                        <input type="text" id="tambah_nama" name="nama" class="form-input" required maxlength="255" placeholder="cth: Budi Santoso">
                    </div>
                    <div class="form-group">
                        <label for="tambah_email" class="form-label">Email</label>
                        <input type="email" id="tambah_email" name="email" class="form-input" required maxlength="255" placeholder="budi@company.com">
                    </div>
                    <div class="form-group">
                        <label for="tambah_password" class="form-label">Password</label>
                        <input type="password" id="tambah_password" name="password" class="form-input" required minlength="8" autocomplete="new-password">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="tambah_role" class="form-label">Role</label>
                            <select id="tambah_role" name="role" class="form-select" required>
                                <option value="ketua">Ketua</option>
                                <option value="sekretaris">Sekretaris</option>
                                <option value="bendahara">Bendahara</option>
                                <option value="logistik">Logistik</option>
                                <option value="superadmin">Super Admin</option>
                                <option value="adminweb">Admin Web</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="tambah_status" class="form-label">Status</label>
                            <select id="tambah_status" name="status" class="form-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="non_aktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" onclick="closeTambahModal()" style="font-weight: 600;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 600;">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit User --}}
    <div class="modal-overlay" id="editUserModal">
        <div class="modal" style="max-width: 520px;">
            <div class="modal-header">
                <h3 class="modal-title">Edit Pengguna</h3>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nama" class="form-label">Nama Lengkap</label>
                        <input type="text" id="edit_nama" name="nama" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" id="edit_email" name="email" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password" class="form-label">Password <span style="font-weight: 400; color: #828282; font-size: 0.8rem;">(kosongkan jika tidak diubah)</span></label>
                        <input type="password" id="edit_password" name="password" class="form-input" autocomplete="new-password">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="edit_role" class="form-label">Role</label>
                            <select id="edit_role" name="role" class="form-select" required>
                                <option value="ketua">Ketua</option>
                                <option value="sekretaris">Sekretaris</option>
                                <option value="bendahara">Bendahara</option>
                                <option value="logistik">Logistik</option>
                                <option value="superadmin">Super Admin</option>
                                <option value="adminweb">Admin Web</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_status" class="form-label">Status</label>
                            <select id="edit_status" name="status" class="form-select" required>
                                <option value="aktif">Aktif</option>
                                <option value="non_aktif">Non-Aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" onclick="closeEditModal()" style="font-weight: 600;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 600;">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(button) {
            document.getElementById('edit_nama').value = button.dataset.nama;
            document.getElementById('edit_email').value = button.dataset.email;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_role').value = button.dataset.role;
            document.getElementById('edit_status').value = button.dataset.status;
            document.getElementById('editUserForm').action = '/superadmin/user/' + button.dataset.id;
            document.getElementById('editUserModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editUserModal').classList.remove('active');
        }

        document.getElementById('editUserModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        function openTambahModal() {
            document.getElementById('tambahUserForm').reset();
            document.getElementById('tambahUserModal').classList.add('active');
        }

        function closeTambahModal() {
            document.getElementById('tambahUserModal').classList.remove('active');
        }

        document.getElementById('tambahUserModal').addEventListener('click', function(e) {
            if (e.target === this) closeTambahModal();
        });
    </script>
@endsection
