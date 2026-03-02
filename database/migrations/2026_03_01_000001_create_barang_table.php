<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel barang.
     * id_barang adalah VARCHAR(8) sebagai Primary Key,
     * nilainya akan diisi otomatis oleh trigger PostgreSQL trigger_id_barang.
     */
    public function up(): void
    {
        Schema::create('barang', function (Blueprint $table) {
            // Primary key custom: varchar(8), bukan auto-increment
            $table->string('id_barang', 8)->primary();

            // Nama barang
            $table->string('nama', 50);

            // Harga barang (integer, bukan decimal untuk kesederhanaan)
            $table->integer('harga');

            // Timestamp tunggal (bukan created_at/updated_at standar Laravel)
            $table->timestamp('timestamp')->useCurrent();
        });
    }

    /**
     * Hapus tabel barang.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
