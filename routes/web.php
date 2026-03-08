<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\KotaController;
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
});

