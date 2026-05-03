<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\KotaController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PemesananController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// ─────────────────────────────────────────────────────────────
// Authentication Routes (Login Normal)
// ─────────────────────────────────────────────────────────────
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// ─────────────────────────────────────────────────────────────
// Google SSO Routes
// - /auth/google          : Redirect user ke halaman login Google
// - /auth/google/callback : Google redirect kembali ke sini setelah login
// ─────────────────────────────────────────────────────────────
Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// ─────────────────────────────────────────────────────────────
// OTP Verification Routes (setelah login berhasil, sebelum masuk dashboard)
// - GET  /otp : Tampilkan halaman input OTP
// - POST /otp : Proses verifikasi kode OTP yang diinput user
// ─────────────────────────────────────────────────────────────
Route::get('otp', [AuthController::class, 'showOtp'])->name('otp.show');
Route::post('otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');

// ─────────────────────────────────────────────────────────────
// Modul Pemesanan — Public (tidak perlu login)
// Customer bisa pesan, bayar, dan cek status tanpa akun
// ─────────────────────────────────────────────────────────────
Route::get('pesan', [PemesananController::class, 'index'])->name('pesan.index');
Route::get('pesan/menu', [PemesananController::class, 'getMenu'])->name('pesan.getMenu');
Route::post('pesan', [PemesananController::class, 'store'])->name('pesan.store');
Route::get('pesan/{id}/bayar', [PaymentController::class, 'pay'])->name('pesan.bayar');
Route::get('pesan/{id}/status', [PemesananController::class, 'status'])->name('pesan.status');

// ─────────────────────────────────────────────────────────────
// Praktikum 1 — Barcode Scanner (Public, tidak perlu login)
// Siapapun bisa scan barcode barang (admin, vendor, publik)
// ─────────────────────────────────────────────────────────────
Route::get('barang/scan', [BarangController::class, 'scanBarcode'])->name('barang.scan');
Route::get('barang/lookup', [BarangController::class, 'lookupBarang'])->name('barang.lookup');

// ─────────────────────────────────────────────────────────────
// Midtrans Webhook — exclude CSRF (Midtrans tidak kirim CSRF token)
// Keamanan dijamin via signature key SHA512 di dalam handler
// ─────────────────────────────────────────────────────────────
Route::post('midtrans/webhook', [PaymentController::class, 'webhook'])
    ->name('midtrans.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ─────────────────────────────────────────────────────────────
// Protected Routes (hanya bisa diakses setelah login + OTP berhasil)
// ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Kategori CRUD
    Route::resource('kategori', KategoriController::class);

    // Buku CRUD
    Route::resource('buku', BukuController::class);

    // PDF Generation (Studi Kasus 2)
    Route::get('pdf', [PdfController::class, 'index'])->name('pdf.index');
    Route::get('pdf/sertifikat', [PdfController::class, 'cetakSertifikat'])->name('pdf.sertifikat');
    Route::get('pdf/undangan', [PdfController::class, 'cetakUndangan'])->name('pdf.undangan');

    // ─────────────────────────────────────────────────────────────
    // Barang CRUD + Cetak Label (Studi Kasus 3)
    // Route cetakLabel harus didaftar SEBELUM Route::resource
    // agar tidak konflik dengan {barang} slug
    // ─────────────────────────────────────────────────────────────
    Route::post('barang/cetak-label', [BarangController::class, 'cetakLabel'])->name('barang.cetakLabel');
    Route::resource('barang', BarangController::class);

    // SK2 & SK3 — Diskon Barang (2 halaman terpisah, controller sendiri)
    Route::get('diskon',            [DiskonController::class, 'html'])->name('diskon.html');
    Route::get('diskon-datatables', [DiskonController::class, 'datatables'])->name('diskon.datatables');

    // SK4 — Kota
    Route::get('kota', [KotaController::class, 'index'])->name('kota.index');

    // ─────────────────────────────────────────────────────────────
    // Studi Kasus Baru — Wilayah Administrasi Indonesia
    // Dua halaman: AJAX (jQuery) dan Axios
    // ─────────────────────────────────────────────────────────────
    Route::get('administrasi',       [WilayahController::class, 'ajax'])->name('administrasi.ajax');
    Route::get('administrasi-axios', [WilayahController::class, 'axios'])->name('administrasi.axios');

    // Endpoint AJAX: return JSON untuk cascading select
    Route::get('wilayah/kota',      [WilayahController::class, 'getKota'])->name('wilayah.kota');
    Route::get('wilayah/kecamatan', [WilayahController::class, 'getKecamatan'])->name('wilayah.kecamatan');
    Route::get('wilayah/kelurahan', [WilayahController::class, 'getKelurahan'])->name('wilayah.kelurahan');

    // ─────────────────────────────────────────────────────────────
    // Studi Kasus Baru — Point of Sales (POS)
    // Dua halaman: AJAX (jQuery) dan Axios
    // ─────────────────────────────────────────────────────────────
    Route::get('pos',        [PenjualanController::class, 'ajax'])->name('pos.ajax');
    Route::get('pos-axios',  [PenjualanController::class, 'axios'])->name('pos.axios');

    // Endpoint AJAX: lookup barang & simpan transaksi
    Route::get('pos/barang', [PenjualanController::class, 'getBarang'])->name('pos.getBarang');
    Route::post('pos/store', [PenjualanController::class, 'store'])->name('pos.store');

    // ─────────────────────────────────────────────────────────────
    // Vendor Dashboard — hanya role vendor
    // ─────────────────────────────────────────────────────────────
    Route::middleware('role:vendor')->group(function () {
        Route::get('vendor/dashboard', [VendorDashboardController::class, 'index'])
            ->name('vendor.dashboard');
        Route::resource('menu', MenuController::class);

        // ─────────────────────────────────────────────────────────────
        // Praktikum 2B — QR Code Scanner untuk Vendor
        // ─────────────────────────────────────────────────────────────
        Route::get('vendor/scan-qr', [VendorDashboardController::class, 'scanQr'])
            ->name('vendor.scanQr');
        Route::get('vendor/lookup-pesanan', [VendorDashboardController::class, 'lookupPesanan'])
            ->name('vendor.lookupPesanan');
    });

    // ─────────────────────────────────────────────────────────────
    // Admin — Kelola Vendor (CRUD)
    // Hanya role admin yang bisa akses
    // ─────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::resource('vendor', VendorController::class);
        Route::resource('user', UserController::class);

        // Customer CRUD + serve foto blob
        Route::get('customer/{id}/photo', [CustomerController::class, 'showPhoto'])->name('customer.photo');
        Route::resource('customer', CustomerController::class);
    });
});
