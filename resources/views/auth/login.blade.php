<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - RKM Al-Jannah</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts: Space Grotesk & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

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

        /* Login Page Specific Styles */
        .login-page-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #f5f5f5 0%, #e8e8e8 100%);
        }

        /* Decorative Images - Larger than hero */
        .login-decor-left {
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

        .login-decor-right {
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

        .login-bottom-ornament {
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

        /* Login Box */
        .login-box {
            position: relative;
            z-index: 10;
            background-color: #ffffff;
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 60px rgba(22, 66, 60, 0.3);
        }

        /* Login Logo */
        .login-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .login-logo img {
            width: 120px;
            height: auto;
        }

        /* Login Title */
        .login-title {
            font-family: "Poppins", sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #16423C;
            text-align: center;
            margin-bottom: 2rem;
        }

        /* Form Styles */
        .login-form {
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

        /* Forgot Password Link */
        .forgot-password {
            font-family: "Poppins", sans-serif;
            font-size: 0.875rem;
            font-weight: 500;
            color: #16423C;
            text-align: right;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #6A9C89;
            text-decoration: underline;
        }

        .password-hint {
            font-family: "Poppins", sans-serif;
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        /* Login Button */
        .btn-login-submit {
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

        .btn-login-submit:hover {
            background-color: #16423C;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 90, 82, 0.5);
        }

        .btn-login-submit:active {
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
            .login-page-wrapper {
                padding: 3rem 1.5rem;
            }

            .login-box {
                padding: 2rem 1.5rem;
            }

            .login-decor-left {
                left: -20%;
                height: 50%;
                max-height: 400px;
            }

            .login-decor-right {
                right: -15%;
                height: 40%;
                max-height: 350px;
            }

            .login-bottom-ornament {
                /* Original size */
            }

            .login-title {
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
            .login-box {
                padding: 1.5rem 1rem;
            }

            .login-logo img {
                width: 100px;
            }

            .login-title {
                font-size: 1.125rem;
            }

            .form-input {
                font-size: 0.95rem;
                padding: 0.75rem 1rem;
            }

            .btn-login-submit {
                font-size: 0.95rem;
                padding: 0.875rem;
            }
        }
    </style>
    <script>if('serviceWorker'in navigator){navigator.serviceWorker.register('/sw.js')}</script>
</head>
<body>
    <!-- Navbar Component -->
    <x-navbar />

    <!-- Login Page Wrapper -->
    <div class="login-page-wrapper">
        <!-- Decorative Images -->
        <img src="{{ asset('images/ranting.png') }}" alt="Decorative branch" class="login-decor-left">
        <img src="{{ asset('images/pohon.png') }}" alt="Decorative tree" class="login-decor-right">
        
        <!-- Login Box -->
        <div class="login-box">
            <!-- Logo -->
            <div class="login-logo">
                <img src="{{ asset('images/logo-al-jannah.png') }}" alt="RKM Al-Jannah Logo">
            </div>

            <!-- Title -->
            <h1 class="login-title">Login Pengurus</h1>

            <!-- Login Form -->
            <form class="login-form" action="{{ route('login') }}" method="POST">
                @csrf
                
                @if($errors->any())
                    <div style="background-color: #fef2f2; border: 1px solid #dc2626; border-radius: 12px; padding: 1rem; color: #dc2626; font-size: 0.9rem;">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Email Input -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="Masukkan email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        inputmode="email"
                    >
                </div>

                <!-- Password Input -->
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                        minlength="8"
                    >
                    <p class="password-hint">Minimal 8 karakter, 1 huruf kapital (A-Z), dan 1 angka (0-9)</p>
                </div>

                <!-- Forgot Password Link -->
                <div style="text-align: right;">
                    <a href="{{ route('forgot-password') }}" class="forgot-password">Lupa Password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login-submit">Login</button>
            </form>
        </div>

        <!-- Bottom Ornament -->
        <img src="{{ asset('images/bottom-ornament-login.png') }}" alt="Bottom ornament" class="login-bottom-ornament">
    </div>

    <!-- Footer Component -->
    <x-footer />

    <script>
        @if(session('reset_success'))
            alert('Password Anda sudah berhasil diperbarui. Silakan login dengan password baru Anda.');
        @endif
    </script>
</body>
</html>
