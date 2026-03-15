<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Import seluruh data wilayah administrasi Indonesia.
     * Tabel: reg_provinces, reg_regencies, reg_districts, reg_villages
     * Data bersumber dari: https://github.com/guzfirdaus/Wilayah-Administrasi-Indonesia
     */
    public function up(): void
    {
        $sqlPath = database_path('sql/wilayah_indonesia_pg.sql');

        if (!file_exists($sqlPath)) {
            throw new \RuntimeException("File SQL tidak ditemukan di: {$sqlPath}");
        }

        // Ambil string SQL dari file lokal
        $sql = file_get_contents($sqlPath);
        
        if (empty($sql)) {
            throw new \RuntimeException("File SQL kosong.");
        }

        // Jalankan seluruh SQL (DROP + CREATE + INSERT) sekaligus
        DB::unprepared($sql);
    }

    /**
     * Drop semua tabel wilayah.
     */
    public function down(): void
    {
        Schema::dropIfExists('reg_villages');
        Schema::dropIfExists('reg_districts');
        Schema::dropIfExists('reg_regencies');
        Schema::dropIfExists('reg_provinces');
    }
};
