<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - RKM Al-Jannah</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300..700&family=Poppins:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    @vite('resources/css/components/navbar.css')
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: "Poppins", sans-serif; background-color: #f5f5f5; min-height: 100vh; display: flex; flex-direction: column; }

        .login-page-wrapper { flex: 1; display: flex; align-items: center; justify-content: center; padding: 4rem 2rem; position: relative; overflow: hidden; background: linear-gradient(180deg, #f5f5f5 0%, #e8e8e8 100%); }
        .login-decor-left { position: absolute; top: -10%; left: -10%; width: auto; height: 70%; max-height: 700px; object-fit: contain; z-index: 1; pointer-events: none; }
        .login-decor-right { position: absolute; top: -10%; right: 0; width: auto; height: 60%; max-height: 550px; object-fit: contain; z-index: 1; pointer-events: none; }
        .login-bottom-ornament { position: absolute; bottom: 0; left: 0; right: 0; width: 100%; height: auto; object-fit: contain; z-index: 1; pointer-events: none; }

        .login-box { position: relative; z-index: 10; background-color: #ffffff; border-radius: 24px; padding: 3rem; width: 100%; max-width: 450px; box-shadow: 0 20px 60px rgba(22, 66, 60, 0.3); }
        .login-logo { display: flex; justify-content: center; margin-bottom: 2rem; }
        .login-logo img { width: 120px; height: auto; }
        .login-title { font-family: "Poppins", sans-serif; font-size: 1.5rem; font-weight: 700; color: #16423C; text-align: center; margin-bottom: 0.5rem; }
        .login-subtitle { font-family: "Poppins", sans-serif; font-size: 0.875rem; font-weight: 400; color: #6b7280; text-align: center; margin-bottom: 2rem; }

        .login-form { display: flex; flex-direction: column; gap: 1.5rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-label { font-family: "Poppins", sans-serif; font-size: 0.875rem; font-weight: 600; color: #16423C; }
        .form-input { font-family: "Poppins", sans-serif; font-size: 1rem; font-weight: 400; color: #ffffff; background-color: #1E5A52; border: 2px solid #16423C; border-radius: 12px; padding: 0.875rem 1.25rem; transition: all 0.3s ease; outline: none; }
        .form-input::placeholder { color: rgba(255,255,255,0.6); }
        .form-input:focus { border-color: #6A9C89; box-shadow: 0 0 0 3px rgba(106,156,137,0.2); }
        .form-input:hover { border-color: #6A9C89; }

        .btn-login-submit { font-family: "Poppins", sans-serif; font-size: 1rem; font-weight: 600; color: #ffffff; background-color: #1E5A52; border: none; border-radius: 12px; padding: 1rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.4,0,0.2,1); box-shadow: 0 4px 15px rgba(30,90,82,0.3); margin-top: 0.5rem; }
        .btn-login-submit:hover { background-color: #16423C; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(30,90,82,0.5); }
        .btn-login-submit:active { transform: translateY(0); }

        .back-link { font-family: "Poppins", sans-serif; font-size: 0.875rem; font-weight: 500; color: #6b7280; text-align: center; margin-top: 1rem; }
        .back-link a { color: #1E5A52; font-weight: 600; }
        .back-link a:hover { text-decoration: underline; }

        .error-box { background-color: #fef2f2; border: 1px solid #dc2626; border-radius: 12px; padding: 1rem; color: #dc2626; font-size: 0.9rem; }
        .password-hint { font-family: "Poppins", sans-serif; font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem; }

        /* Footer */
        .footer { background: linear-gradient(180deg, #16423C 0%, #0d2b26 100%); padding: 2rem; margin-top: auto; }
        .footer-container { max-width: 1200px; margin: 0 auto; }
        .footer-content { display: flex; justify-content: space-between; align-items: center; gap: 2rem; }
        .footer-brand-title { font-family: "Poppins", sans-serif; font-size: 1.5rem; font-weight: 700; color: #ffffff; margin-bottom: 0.25rem; }
        .footer-brand-text { font-family: "Poppins", sans-serif; font-size: 0.875rem; font-weight: 400; color: rgba(255,255,255,0.7); }
        .footer-divider { width: 1px; height: 40px; background-color: rgba(255,255,255,0.3); }
        .footer-developer-text { font-family: "Poppins", sans-serif; font-size: 0.875rem; font-weight: 400; color: rgba(255,255,255,0.7); }
        .footer-developer-name { font-weight: 600; color: #98CBBE; }

        @media (max-width: 768px) {
            .login-page-wrapper { padding: 3rem 1.5rem; }
            .login-box { padding: 2rem 1.5rem; }
            .login-decor-left { left: -20%; height: 50%; max-height: 400px; }
            .login-decor-right { right: -15%; height: 40%; max-height: 350px; }
            .login-title { font-size: 1.25rem; }
        }
        @media (max-width: 480px) {
            .login-box { padding: 1.5rem 1rem; }
            .login-logo img { width: 100px; }
            .login-title { font-size: 1.125rem; }
        }
    </style>
</head>
<body>
    <x-navbar />

    <div class="login-page-wrapper">
        <img src="{{ asset('images/ranting.png') }}" alt="" class="login-decor-left">
        <img src="{{ asset('images/pohon.png') }}" alt="" class="login-decor-right">

        <div class="login-box">
            <div class="login-logo">
                <img src="{{ asset('images/logo-al-jannah.png') }}" alt="RKM Al-Jannah Logo">
            </div>

            <h1 class="login-title">Reset Password</h1>
            <p class="login-subtitle">Masukkan password baru Anda di bawah ini.</p>

            <form class="login-form" action="{{ route('reset-password.do') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                @if($errors->any())
                    <div class="error-box">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="form-group">
                    <label for="password" class="form-label">Password Baru</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Masukkan password baru"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        pattern="^(?=.*[A-Z])(?=.*\d).{8,}$"
                        title="Minimal 8 karakter, 1 huruf kapital, dan 1 angka"
                    >
                    <p class="password-hint">Minimal 8 karakter, 1 huruf kapital (A-Z), dan 1 angka (0-9)</p>
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-input"
                        placeholder="Ulangi password baru"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        pattern="^(?=.*[A-Z])(?=.*\d).{8,}$"
                    >
                </div>

                <button type="submit" class="btn-login-submit">Simpan Password Baru</button>
            </form>

            <div class="back-link">
                <a href="{{ route('login') }}">Kembali ke Login</a>
            </div>
        </div>

        <img src="{{ asset('images/bottom-ornament-login.png') }}" alt="" class="login-bottom-ornament">
    </div>

    <x-footer />
</body>
</html>
