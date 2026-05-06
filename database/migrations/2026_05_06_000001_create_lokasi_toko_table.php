<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel lokasi_toko.
     * barcode adalah VARCHAR(8) sebagai Primary Key,
     * nilainya akan diisi otomatis oleh trigger PostgreSQL trigger_barcode_toko.
     */
    public function up(): void
    {
        Schema::create('lokasi_toko', function (Blueprint $table) {
            // Primary key custom: varchar(8), bukan auto-increment
            $table->string('barcode', 8)->primary();

            // Nama toko
            $table->string('nama_toko', 50);

            // Koordinat GPS toko (default 0 karena diisi belakangan)
            $table->double('latitude')->default(0);
            $table->double('longitude')->default(0);
            $table->double('accuracy')->default(0);

            // Timestamp
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Hapus tabel lokasi_toko.
     */
    public function down(): void
    {
        Schema::dropIfExists('lokasi_toko');
    }
};
