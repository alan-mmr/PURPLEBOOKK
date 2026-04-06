<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel detail_pesanan (item-item per order).
     */
    public function up(): void
    {
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->increments('iddetail');

            // FK ke tabel pesanan
            $table->unsignedInteger('idpesanan');
            $table->foreign('idpesanan')
                  ->references('idpesanan')
                  ->on('pesanan')
                  ->onDelete('cascade'); // hapus pesanan → hapus detailnya

            // FK ke tabel menu
            $table->unsignedInteger('idmenu');
            $table->foreign('idmenu')
                  ->references('idmenu')
                  ->on('menu')
                  ->onDelete('restrict'); // jangan hapus menu jika ada di detail

            // Jumlah item yang dipesan
            $table->integer('jumlah');

            // Subtotal = harga_menu × jumlah (snapshot harga saat transaksi)
            $table->integer('subtotal');
        });
    }

    /**
     * Hapus tabel detail_pesanan.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
