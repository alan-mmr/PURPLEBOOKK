<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel penjualan_detail (detail item per transaksi POS).
     */
    public function up(): void
    {
        Schema::create('penjualan_detail', function (Blueprint $table) {
            // Primary key auto-increment
            $table->increments('idpenjualan_detail');

            // FK ke tabel penjualan
            $table->unsignedInteger('id_penjualan');
            $table->foreign('id_penjualan')
                  ->references('id_penjualan')
                  ->on('penjualan')
                  ->onDelete('cascade');

            // FK ke tabel barang (id_barang adalah VARCHAR(8))
            $table->string('id_barang', 8);
            $table->foreign('id_barang')
                  ->references('id_barang')
                  ->on('barang')
                  ->onDelete('restrict');

            // Jumlah barang yang dibeli
            $table->smallInteger('jumlah');

            // Subtotal = harga * jumlah (pada saat transaksi)
            $table->integer('subtotal');
        });
    }

    /**
     * Hapus tabel penjualan_detail.
     */
    public function down(): void
    {
        Schema::dropIfExists('penjualan_detail');
    }
};
