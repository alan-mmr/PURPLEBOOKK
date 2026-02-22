<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Menambahkan kolom Google SSO dan OTP ke tabel users
 *
 * Kolom yang ditambahkan:
 * - id_google : Menyimpan ID unik dari akun Google user (untuk SSO)
 * - otp       : Menyimpan kode OTP 6 karakter sementara untuk verifikasi login
 */
return new class extends Migration
{
    /**
     * Jalankan migration: tambah kolom ke tabel users
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kolom untuk menyimpan Google User ID (dari OAuth Google)
            // nullable: karena user yang login biasa (email/password) tidak punya id_google
            $table->string('id_google', 256)->nullable()->after('remember_token');

            // Kolom untuk menyimpan kode OTP sementara (6 karakter)
            // nullable: kosong ketika user belum/sudah melakukan verifikasi OTP
            $table->string('otp', 6)->nullable()->after('id_google');
        });
    }

    /**
     * Rollback migration: hapus kolom yang sudah ditambahkan
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['id_google', 'otp']);
        });
    }
};
