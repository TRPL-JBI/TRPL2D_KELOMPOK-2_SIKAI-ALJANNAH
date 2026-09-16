<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LogSuperadmin;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected WhatsAppService $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request, RateLimiter $limiter)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if ($limiter->tooManyAttempts($throttleKey, 5)) {
            $seconds = $limiter->availableIn($throttleKey);
            return back()->withErrors([
                'email' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam ' . $seconds . ' detik.',
            ])->onlyInput('email');
        }

        if (Auth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'status' => 'aktif',
        ])) {

            $request->session()->regenerate();
            $limiter->clear($throttleKey);

            LogSuperadmin::create([
                'user_id' => Auth::id(),
                'aksi' => 'login',
                'deskripsi' => 'Login ke sistem',
                'modul' => 'Auth',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $role = Auth::user()->role;
            $dashboard = match ($role) {
                'sekretaris' => '/sekretaris',
                'bendahara' => '/bendahara',
                'superadmin' => '/superadmin',
                'ketua' => '/ketua',
                'logistik' => '/logistik',
                'adminweb' => '/adminweb',
                default => '/login',
            };

            return redirect()->intended($dashboard);
        }

        $limiter->hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(64);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($email));

            // Kirim via WhatsApp jika nomor tersedia
            if (!empty($user->no_whatsapp)) {
                $this->kirimWaLupaPassword($user->no_whatsapp, $resetUrl, $user->nama);
            }
        }

        // Selalu tampilkan sukses untuk mencegah email enumeration
        return redirect('/forgot-password')->with('success', true);
    }

    private function kirimWaLupaPassword(string $noWhatsapp, string $resetUrl, string $nama)
    {
        try {
            $pesan = "Halo {$nama},\n\n"
                . "Kami menerima permintaan reset password untuk akun Anda di sistem RKM Al-Jannah.\n\n"
                . "Silakan klik link berikut untuk membuat password baru:\n{$resetUrl}\n\n"
                . "Link ini akan kadaluarsa dalam 60 menit.\n"
                . "Jika Anda tidak meminta reset password, abaikan pesan ini.";

            $this->whatsapp->send($noWhatsapp, $pesan);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal kirim WA lupa password: ' . $e->getMessage());
        }
    }

    public function showResetPasswordForm(Request $request, string $token)
    {
        $email = $request->query('email');

        if (!$email) {
            return redirect('/login')->withErrors(['email' => 'Link reset password tidak valid.']);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return redirect('/login')->withErrors(['email' => 'Link reset password tidak valid atau sudah kedaluwarsa.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect('/login')->withErrors(['email' => 'Link reset password sudah kedaluwarsa. Silakan ajukan ulang.']);
        }

        return view('auth.reset-password', ['email' => $email, 'token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'password_confirmation' => 'required',
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex' => 'Password harus mengandung setidaknya 1 huruf kapital (A-Z) dan 1 angka (0-9).',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->input('email'))
            ->first();

        if (!$record || !Hash::check($request->input('token'), $record->token)) {
            return back()->withErrors(['email' => 'Link reset password tidak valid.']);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();
            return back()->withErrors(['email' => 'Link reset password sudah kedaluwarsa.']);
        }

        $user = User::where('email', $request->input('email'))->first();
        if ($user) {
            $user->update(['password' => Hash::make($request->input('password'))]);
        }

        DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();

        return redirect('/login')->with('reset_success', true);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        LogSuperadmin::create([
            'user_id' => $user?->id,
            'aksi' => 'logout',
            'deskripsi' => 'Logout dari sistem',
            'modul' => 'Auth',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
