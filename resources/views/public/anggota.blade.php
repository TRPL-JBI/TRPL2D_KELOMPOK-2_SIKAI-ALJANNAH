<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anggota - RKM Al-Jannah</title>
    @include('components.favicon')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    @vite('resources/css/components/navbar.css')

    <style>
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

        .anggota-page-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #f5f5f5 0%, #e8e8e8 100%);
        }

        .anggota-decor-left {
            position: absolute;
            top: -10%;
            left: -10%;
            width: auto;
            height: 70%;
            max-height: 700px;
            object-fit: contain;
            z-index: 1;
            pointer-events: none;
        }

        .anggota-decor-right {
            position: absolute;
            top: -10%;
            right: 0;
            width: auto;
            height: 60%;
            max-height: 550px;
            object-fit: contain;
            z-index: 1;
            pointer-events: none;
        }

        .anggota-bottom-ornament {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            height: auto;
            object-fit: contain;
            z-index: 1;
            pointer-events: none;
        }

        .anggota-box {
            position: relative;
            z-index: 10;
            background-color: #ffffff;
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(22, 66, 60, 0.3);
        }

        .anggota-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .anggota-logo img {
            width: 120px;
            height: auto;
        }

        .anggota-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #16423C;
            text-align: center;
            margin-bottom: 0.5rem;
        }

        .anggota-subtitle {
            font-family: "Poppins", sans-serif;
            font-size: 0.95rem;
            font-weight: 400;
            color: #6B7280;
            text-align: center;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .anggota-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-label {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 600;
            color: #16423C;
        }

        .form-input {
            font-family: "Poppins", sans-serif;
            font-size: 1rem;
            font-weight: 400;
            color: #ffffff;
            background-color: #1E5A52;
            border: 2px solid #16423C;
            border-radius: 12px;
            padding: 0.875rem 1.25rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .form-input:focus {
            border-color: #6A9C89;
            box-shadow: 0 0 0 3px rgba(106, 156, 137, 0.2);
        }

        .form-input:hover {
            border-color: #6A9C89;
        }

        .btn-submit {
            font-family: "Poppins", sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: #ffffff;
            background-color: #1E5A52;
            border: none;
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(30, 90, 82, 0.3);
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            background-color: #16423C;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 90, 82, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .anggota-note {
            margin-top: 1.5rem;
            padding: 1rem 1.25rem;
            background-color: #fefce8;
            border: 1px solid #eab308;
            border-radius: 12px;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .anggota-note-icon {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            color: #ca8a04;
            margin-top: 1px;
        }

        .anggota-note-text {
            font-family: "Poppins", sans-serif;
            font-size: 0.85rem;
            font-weight: 400;
            color: #713f12;
            line-height: 1.6;
        }

        .anggota-note-text strong {
            font-weight: 600;
            color: #a16207;
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

        @media (max-width: 768px) {
            .anggota-page-wrapper {
                padding: 3rem 1.5rem;
            }

            .anggota-box {
                padding: 2rem 1.5rem;
            }

            .anggota-decor-left {
                left: -20%;
                height: 50%;
                max-height: 400px;
            }

            .anggota-decor-right {
                right: -15%;
                height: 40%;
                max-height: 350px;
            }

            .anggota-title {
                font-size: 1.25rem;
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
            .anggota-box {
                padding: 1.5rem 1rem;
            }

            .anggota-logo img {
                width: 100px;
            }

            .anggota-title {
                font-size: 1.125rem;
            }

            .form-input {
                font-size: 0.95rem;
                padding: 0.75rem 1rem;
            }

            .btn-submit {
                font-size: 0.95rem;
                padding: 0.875rem;
            }
        }
    </style>
    <script>if('serviceWorker'in navigator){navigator.serviceWorker.register('/sw.js')}</script>
</head>
<body>
    @include('components.preloader')
    <x-navbar />

    <div class="anggota-page-wrapper">
        <img src="{{ asset('images/ranting.png') }}" alt="Decorative branch" class="anggota-decor-left">
        <img src="{{ asset('images/pohon.png') }}" alt="Decorative tree" class="anggota-decor-right">

        <div class="anggota-box">
            <div class="anggota-logo">
                <img src="{{ asset('images/logo-al-jannah.png') }}" alt="RKM Al-Jannah Logo">
            </div>

            <h1 class="anggota-title">Akses Anggota</h1>
            <p class="anggota-subtitle">Masukkan kode akses untuk masuk ke halaman anggota</p>

            @if ($errors->any())
            <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:12px;padding:0.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.75rem;">
                <svg style="width:20px;height:20px;color:#dc2626;flex-shrink:0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <span style="font-size:0.85rem;color:#991b1b;">{{ $errors->first('access_code') }}</span>
            </div>
            @endif

            <form class="anggota-form" method="POST" action="{{ route('anggota') }}">
                @csrf
                <div class="form-group">
                    <label for="access_code" class="form-label">Kode Akses</label>
                    <input
                        type="text"
                        id="access_code"
                        name="access_code"
                        class="form-input"
                        placeholder="Masukkan kode akses"
                        required
                        autocomplete="off"
                    >
                </div>

                <button type="submit" class="btn-submit">Masuk</button>
            </form>

            <div class="anggota-note">
                <svg class="anggota-note-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div class="anggota-note-text">
                    <strong>Penting:</strong> Kode akses dapat diperoleh dengan menghubungi pengurus <strong>Bendahara</strong> RKM Al-Jannah. Kode ini bersifat rahasia dan hanya diberikan kepada anggota yang telah terdaftar.
                </div>
            </div>
        </div>

        <img src="{{ asset('images/bottom-ornament-login.png') }}" alt="Bottom ornament" class="anggota-bottom-ornament">
    </div>

    <x-footer />

</body>
</html>
