<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel penjualan (header transaksi POS).
     */
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            // Primary key auto-increment
            $table->increments('id_penjualan');

            // Waktu transaksi
            $table->timestamp('timestamp')->useCurrent();

            // Total keseluruhan transaksi (dalam Rupiah)
            $table->integer('total');
        });
    }

    /**
     * Hapus tabel penjualan.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
