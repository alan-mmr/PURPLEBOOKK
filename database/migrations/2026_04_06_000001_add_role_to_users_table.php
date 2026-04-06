<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom role ke tabel users.
     * Nilai: admin | vendor | guest
     * Default: admin (karena user yang sudah ada sebelumnya adalah admin)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('admin')->after('password');
        });
    }

    /**
     * Hapus kolom role dari tabel users.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
