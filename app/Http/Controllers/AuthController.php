<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * AuthController
 *
 * Menangani semua proses autentikasi di PURPLEBOOK:
 * 1. Login normal (email + password) + OTP verifikasi
 * 2. Login via Google SSO (OAuth) + OTP verifikasi
 * 3. Logout
 */
class AuthController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // LOGIN NORMAL (Email + Password)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Tampilkan halaman form login.
     * Jika user sudah login (ada session auth), langsung redirect ke dashboard.
     */
    public function showLogin()
    {
        // Jika sudah login, tidak perlu ke halaman login lagi
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses form login (email + password).
     *
     * Alur:
     * 1. Validasi input
     * 2. Coba autentikasi dengan Auth::attempt()
     * 3. Jika berhasil → generate OTP → kirim email → redirect ke halaman OTP
     * 4. Jika gagal → kembali ke form login dengan pesan error
     */
    public function login(Request $request)
    {
        // Validasi input: email wajib format email, password wajib diisi
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Coba cocokkan email + password dengan data di tabel users
        if (Auth::attempt($credentials)) {
            // Regenerasi session untuk keamanan (mencegah session fixation attack)
            $request->session()->regenerate();

            // Ambil data user yang baru saja login
            $user = Auth::user();

            // Logout dulu (jangan buat session login sampai OTP diverifikasi)
            Auth::logout();
            $request->session()->regenerate();

            // Generate dan kirim OTP, lalu redirect ke halaman OTP
            return $this->sendOtpAndRedirect($request, $user);
        }

        // Autentikasi gagal → kembali ke form login dengan pesan error
        return back()->withErrors([
            'email' => 'Email atau password yang kamu masukkan salah.',
        ])->onlyInput('email');
    }

    // ═══════════════════════════════════════════════════════════════
    // GOOGLE SSO (OAuth 2.0 via Laravel Socialite)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Redirect user ke halaman login Google.
     *
     * User akan diarahkan ke halaman pilih akun Google (milik Google, bukan app ini).
     * Setelah login di Google, Google akan redirect ke handleGoogleCallback().
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Menangani callback dari Google setelah user berhasil login di Google.
     *
     * Alur:
     * 1. Ambil data user dari Google (nama, email, id_google)
     * 2. Cari user di database berdasarkan id_google atau email
     * 3. Jika belum ada → buat user baru otomatis
     * 4. Generate OTP → kirim ke email Google user → redirect ke halaman OTP
     */
    public function handleGoogleCallback(Request $request)
    {
        // Ambil data user dari Google (nama, email, avatar, dll)
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Cari user di DB: pertama cari by id_google, lalu coba by email
        $user = User::where('id_google', $googleUser->getId())->first()
             ?? User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // User sudah ada → update id_google jika belum diset (misal user sebelumnya login biasa)
            $user->update(['id_google' => $googleUser->getId()]);
        } else {
            // User belum ada → buat akun baru dari data Google
            $user = User::create([
                'name'      => $googleUser->getName(),
                'email'     => $googleUser->getEmail(),
                'id_google' => $googleUser->getId(),
                'password'  => null, // User Google tidak punya password di app ini
            ]);
        }

        // Generate OTP dan redirect ke halaman verifikasi
        return $this->sendOtpAndRedirect($request, $user);
    }

    // ═══════════════════════════════════════════════════════════════
    // OTP VERIFICATION
    // ═══════════════════════════════════════════════════════════════

    /**
     * Helper method: Generate OTP, simpan ke DB, kirim email, redirect ke halaman OTP.
     *
     * Digunakan oleh login normal dan Google SSO agar kode tidak duplikat (DRY principle).
     *
     * @param Request $request  Request Laravel
     * @param User    $user     Data user yang akan menerima OTP
     */
    private function sendOtpAndRedirect(Request $request, User $user)
    {
        // Generate kode OTP: 6 karakter alphanumeric acak, ubah ke uppercase
        $otpCode = strtoupper(Str::random(6));

        // Simpan kode OTP ke kolom 'otp' di tabel users
        $user->update(['otp' => $otpCode]);

        // Kirim email berisi kode OTP ke email user
        Mail::to($user->email)->send(new OtpMail($otpCode, $user->name));

        // Simpan ID user ke session (sementara, untuk proses verifikasi OTP)
        // Kita tidak login dulu, session auth baru dibuat SETELAH OTP diverifikasi
        $request->session()->put('otp_user_id', $user->id);
        $request->session()->put('otp_email', $user->email);

        // Redirect ke halaman input OTP
        return redirect()->route('otp.show');
    }

    /**
     * Tampilkan halaman verifikasi OTP.
     *
     * Jika tidak ada session otp_user_id (user belum login dulu),
     * redirect kembali ke halaman login.
     */
    public function showOtp(Request $request)
    {
        // Cek apakah ada session OTP yang valid (user sudah lewat proses login/SSO)
        if (!$request->session()->has('otp_user_id')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Silakan login terlebih dahulu.']);
        }

        return view('auth.otp');
    }

    /**
     * Verifikasi kode OTP yang diinput user.
     *
     * Alur:
     * 1. Ambil user_id dari session
     * 2. Cocokkan kode OTP input dengan yang tersimpan di DB
     * 3. Jika cocok → hapus OTP dari DB → buat session login → redirect ke dashboard
     * 4. Jika salah → kembali ke halaman OTP dengan pesan error
     */
    public function verifyOtp(Request $request)
    {
        // Validasi: OTP wajib diisi, tepat 6 karakter
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        // Ambil user_id dari session yang disimpan saat login/SSO berhasil
        $userId = $request->session()->get('otp_user_id');

        if (!$userId) {
            // Session expired atau tidak valid → kembali ke login
            return redirect()->route('login')
                ->withErrors(['email' => 'Session habis. Silakan login kembali.']);
        }

        // Ambil data user dari database
        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['email' => 'User tidak ditemukan.']);
        }

        // Cocokkan OTP yang diinput dengan OTP yang tersimpan di DB (case-insensitive)
        if (strtoupper($request->otp) !== strtoupper($user->otp)) {
            // OTP salah → kembali ke halaman OTP dengan pesan error
            return back()->withErrors([
                'otp' => 'Kode OTP yang kamu masukkan salah. Silakan coba lagi.',
            ]);
        }

        // OTP cocok! → Hapus kode OTP dari DB (sudah tidak diperlukan lagi)
        $user->update(['otp' => null]);

        // Hapus data OTP dari session
        $request->session()->forget(['otp_user_id', 'otp_email']);

        // Buat session login resmi → user sekarang sudah login
        Auth::login($user);
        $request->session()->regenerate();

        // Redirect ke dashboard dengan pesan sukses
        return redirect()->route('dashboard')
            ->with('success', 'Login berhasil! Selamat datang, ' . $user->name . '.');
    }

    // ═══════════════════════════════════════════════════════════════
    // LOGOUT
    // ═══════════════════════════════════════════════════════════════

    /**
     * Proses logout user.
     *
     * Menghapus session autentikasi dan semua data session,
     * kemudian redirect ke halaman login.
     */
    public function logout(Request $request)
    {
        Auth::logout();                          // Hapus autentikasi
        $request->session()->invalidate();       // Invalidasi semua data session
        $request->session()->regenerateToken();  // Generate CSRF token baru

        return redirect()->route('login');
    }
}
