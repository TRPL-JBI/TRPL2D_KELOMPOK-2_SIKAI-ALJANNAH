<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Anggota Baru - RKM Al-Jannah</title>
    @include('components.favicon')
    
    <!-- Google Fonts: Space Grotesk & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">

    <!-- Navbar Component Styles -->
    @vite('resources/css/components/navbar.css')

    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            font-style: normal;
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Registration Page */
        .registration-page {
            flex: 1;
            padding: 4rem 2rem 6rem;
            position: relative;
            overflow: hidden;
        }

        /* Bottom Ornament */
        .registration-bottom-ornament {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: auto;
            object-fit: cover;
            z-index: 1;
            pointer-events: none;
        }

        /* Registration Container */
        .registration-container {
            position: relative;
            z-index: 10;
            max-width: 900px;
            margin: 0 auto;
        }

        /* Registration Box */
        .registration-box {
            background-color: #ffffff;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(22, 66, 60, 0.3);
        }

        /* Registration Header */
        .registration-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .registration-logo {
            width: 100px;
            height: auto;
            flex-shrink: 0;
        }

        .registration-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #16423C;
            line-height: 1.4;
        }

        /* Form Sections */
        .form-section {
            margin-bottom: 2.5rem;
        }

        .form-section-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #16423C;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #6A9C89;
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-family: "Poppins", sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: #dc2626;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            font-family: "Poppins", sans-serif;
            font-size: 1rem;
            color: #374151;
            background-color: #9ca3af;
            border: 2px solid #6b7280;
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input::placeholder,
        .form-textarea::placeholder {
            color: rgba(55, 65, 81, 0.7);
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            background-color: #6b7280;
            border-color: #16423C;
            box-shadow: 0 0 0 3px rgba(22, 66, 60, 0.2);
        }

        .form-input:hover,
        .form-textarea:hover,
        .form-select:hover {
            border-color: #16423C;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Form Row (for side-by-side fields) */
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        /* Radio Buttons */
        .radio-group {
            display: flex;
            gap: 2rem;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .radio-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #1E5A52;
            cursor: pointer;
        }

        .radio-option label {
            font-family: "Poppins", sans-serif;
            font-size: 1rem;
            color: #374151;
            cursor: pointer;
        }

        /* Dynamic Fields (Nama yang Ditanggung) */
        .dynamic-fields {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .dynamic-field-row {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .dynamic-field-row .form-input {
            flex: 1;
        }

        .btn-add-field {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #6A9C89;
            color: #ffffff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.5rem;
            line-height: 1;
        }

        .btn-add-field:hover {
            background-color: #1E5A52;
            transform: scale(1.1);
        }

        .btn-remove-field {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: #dc2626;
            color: #ffffff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.25rem;
            line-height: 1;
        }

        .btn-remove-field:hover {
            background-color: #991b1b;
            transform: scale(1.1);
        }

        /* Checkbox */
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-top: 2rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: #1E5A52;
            cursor: pointer;
            margin-top: 0.25rem;
        }

        .checkbox-group label {
            font-family: "Poppins", sans-serif;
            font-size: 0.95rem;
            color: #374151;
            cursor: pointer;
            line-height: 1.6;
        }

        .checkbox-group label a {
            color: #1E5A52;
            text-decoration: underline;
        }

        .checkbox-group label a:hover {
            color: #16423C;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            font-family: "Poppins", sans-serif;
            font-size: 1.125rem;
            font-weight: 600;
            color: #ffffff;
            background-color: #1E5A52;
            border: none;
            border-radius: 12px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(30, 90, 82, 0.3);
            margin-top: 2rem;
        }

        .btn-submit:hover {
            background-color: #16423C;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 90, 82, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Footer */
        .footer {
            background: linear-gradient(180deg, #16423C 0%, #0d2b26 100%);
            padding: 2rem;
            margin-top: auto;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .footer-brand-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 0.25rem;
        }

        .footer-brand-text {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-divider {
            width: 1px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.3);
        }

        .footer-developer-text {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-developer-name {
            font-weight: 600;
            color: #98CBBE;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .registration-page {
                padding: 3rem 1.5rem 4rem;
            }

            .registration-box {
                padding: 2rem 1.5rem;
            }

            .registration-header {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }

            .registration-logo {
                width: 80px;
            }

            .registration-title {
                font-size: 1.25rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .radio-group {
                flex-direction: column;
                gap: 1rem;
            }

            .dynamic-field-row {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-add-field,
            .btn-remove-field {
                width: 50px;
                height: 50px;
                font-size: 1.75rem;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-divider {
                width: 100%;
                height: 1px;
            }
        }

        @media (max-width: 480px) {
            .registration-box {
                padding: 1.5rem 1rem;
            }

            .registration-title {
                font-size: 1.125rem;
            }

            .form-section-title {
                font-size: 1.125rem;
            }

            .form-input,
            .form-textarea,
            .form-select {
                font-size: 0.95rem;
                padding: 0.75rem 1rem;
            }

            .btn-submit {
                font-size: 1rem;
                padding: 1rem;
            }
        }
    </style>
    <script>if('serviceWorker'in navigator){navigator.serviceWorker.register('/sw.js')}</script>
</head>
<body>
    @include('components.preloader')
    <!-- Navbar Component -->
    <x-navbar />

    <!-- Registration Page -->
    <div class="registration-page">
        <div class="registration-container">
            <!-- Registration Box -->
            <div class="registration-box">
                <!-- Header -->
                <div class="registration-header">
                    <img src="{{ asset('images/logo-al-jannah.png') }}" alt="RKM Al-Jannah Logo" class="registration-logo">
                    <h1 class="registration-title">FORM PENDAFTARAN ANGGOTA BARU RKM AL-JANNAH</h1>
                </div>

                @if(session('success'))
                    <div style="background-color: #d8efdd; border: 1px solid #35ab50; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-family: 'Poppins', sans-serif; font-size: 0.95rem; color: #154420;">
                        <span class="material-icons" style="color: #35ab50; font-size: 20px;">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div style="background-color: #fde8e8; border: 1px solid #e53e3e; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; font-family: 'Poppins', sans-serif; font-size: 0.95rem; color: #742a2a;">
                        <span class="material-icons" style="color: #e53e3e; font-size: 20px;">error</span>
                        {{ $errors->first() }}
                    </div>
                @endif

                <!-- Registration Form -->
                <form class="registration-form" action="/register-member" method="POST">
                    @csrf

                    <!-- Data Pribadi Section -->
                    <div class="form-section">
                        <h2 class="form-section-title">Data Pribadi</h2>

                        <!-- Nama Lengkap -->
                        <div class="form-group">
                            <label for="nama_lengkap" class="form-label">
                                Nama Lengkap/Nama Kepala Keluarga <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="nama_lengkap" 
                                name="nama_lengkap" 
                                class="form-input" 
                                placeholder="Masukkan nama lengkap"
                                required
                            >
                        </div>

                        <!-- Nama yang Ditanggung (Dynamic) -->
                        <div class="form-group">
                            <label class="form-label">
                                Nama yang Ditanggung
                            </label>
                            <div class="dynamic-fields" id="nama_ditanggung_fields">
                                <div class="dynamic-field-row">
                                    <input 
                                        type="text" 
                                        name="nama_ditanggung[]" 
                                        class="form-input" 
                                        placeholder="Masukkan nama yang ditanggung"
                                    >
                                    <button type="button" class="btn-add-field" onclick="addDitanggungField()" title="Tambah nama">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="form-group">
                            <label for="tanggal_lahir" class="form-label">
                                Tanggal Lahir <span class="required">*</span>
                            </label>
                            <input 
                                type="date" 
                                id="tanggal_lahir" 
                                name="tanggal_lahir" 
                                class="form-input" 
                                required
                            >
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="form-group">
                            <label class="form-label">
                                Jenis Kelamin <span class="required">*</span>
                            </label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input 
                                        type="radio" 
                                        id="laki_laki" 
                                        name="jenis_kelamin" 
                                        value="laki_laki"
                                        required
                                    >
                                    <label for="laki_laki">Laki-laki</label>
                                </div>
                                <div class="radio-option">
                                    <input 
                                        type="radio" 
                                        id="perempuan" 
                                        name="jenis_kelamin" 
                                        value="perempuan"
                                        required
                                    >
                                    <label for="perempuan">Perempuan</label>
                                </div>
                            </div>
                        </div>

                        <!-- Alamat Lengkap -->
                        <div class="form-group">
                            <label for="alamat" class="form-label">
                                Alamat Lengkap <span class="required">*</span>
                            </label>
                            <textarea 
                                id="alamat" 
                                name="alamat" 
                                class="form-textarea" 
                                placeholder="Masukkan alamat lengkap"
                                required
                            ></textarea>
                        </div>

                        <!-- RT/RW dan Kelurahan -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="rt_rw" class="form-label">
                                    RT/RW <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="rt_rw" 
                                    name="rt_rw" 
                                    class="form-input" 
                                    placeholder="Contoh: 001/002"
                                    required
                                >
                            </div>
                            <div class="form-group">
                                <label for="kelurahan" class="form-label">
                                    Kelurahan <span class="required">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    id="kelurahan" 
                                    name="kelurahan" 
                                    class="form-input" 
                                    placeholder="Masukkan kelurahan"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Nomor HP dan Email -->
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nomor_hp" class="form-label">
                                    Nomor HP <span class="required">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    id="nomor_hp" 
                                    name="nomor_hp" 
                                    class="form-input" 
                                    placeholder="Contoh: 081234567890"
                                    required
                                >
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    Email <span class="required">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="form-input" 
                                    placeholder="contoh@email.com"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="checkbox-group">
                        <input 
                            type="checkbox" 
                            id="terms" 
                            name="terms" 
                            required
                        >
                        <label for="terms">
                            Saya menyetujui <a href="/syarat-ketentuan" target="_blank">persyaratan & ketentuan</a> yang berlaku <span class="required">*</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">Daftar Sekarang</button>
                </form>
            </div>
        </div>

        <!-- Bottom Ornament -->
        <img src="{{ asset('images/bottom-ornament.png') }}" alt="Bottom ornament" class="registration-bottom-ornament">
    </div>

    <!-- Footer Component -->
    <x-footer />

    <!-- Script untuk Dynamic Fields -->
    <script>
        function addDitanggungField() {
            const container = document.getElementById('nama_ditanggung_fields');
            const newRow = document.createElement('div');
            newRow.className = 'dynamic-field-row';
            newRow.innerHTML = `
                <input 
                    type="text" 
                    name="nama_ditanggung[]" 
                    class="form-input" 
                    placeholder="Masukkan nama yang ditanggung"
                >
                <button type="button" class="btn-remove-field" onclick="removeDitanggungField(this)" title="Hapus nama">−</button>
            `;
            container.appendChild(newRow);
        }

        function removeDitanggungField(button) {
            const container = document.getElementById('nama_ditanggung_fields');
            // Don't remove if it's the last field
            if (container.children.length > 1) {
                button.parentElement.remove();
            } else {
                alert('Minimal harus ada 1 nama yang ditanggung');
            }
        }

        // Mobile Menu Script (from navbar component)
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const navMenuWrapper = document.getElementById('navMenuWrapper');

            if (mobileMenuToggle && navMenuWrapper) {
                mobileMenuToggle.addEventListener('click', function() {
                    navMenuWrapper.classList.toggle('active');
                    mobileMenuToggle.classList.toggle('active');
                });

                document.querySelectorAll('.nav-link').forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenuToggle.classList.remove('active');
                        navMenuWrapper.classList.remove('active');
                    });
                });
            }

        });
    </script>
</body>
</html>
