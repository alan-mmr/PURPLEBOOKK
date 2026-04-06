<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel pesanan (header order).
     * Terpisah dari tabel penjualan yang sudah ada.
     */
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->increments('idpesanan');

            // Nama pemesan dalam format Guest_0000001 (auto-generate, tidak ke tabel users)
            $table->string('nama_pemesan');

            // FK ke tabel vendor — pesanan ditujukan ke vendor ini
            $table->unsignedInteger('idvendor');
            $table->foreign('idvendor')
                  ->references('idvendor')
                  ->on('vendor')
                  ->onDelete('restrict'); // jangan hapus vendor jika ada pesanan

            // Total keseluruhan dalam Rupiah
            $table->integer('total_harga');

            // Status order: pending | confirmed | cancelled
            $table->string('status', 20)->default('pending');

            // Status pembayaran: pending | paid | failed | expired
            $table->string('status_bayar', 20)->default('pending');

            // Midtrans — Snap token untuk tampilkan halaman bayar
            $table->string('snap_token')->nullable();

            // Midtrans — tipe pembayaran (gopay, bca_va, qris, dll)
            $table->string('payment_type')->nullable();

            // Midtrans — order_id yang dikirim ke gateway (untuk double verify)
            $table->string('transaction_id')->nullable()->unique();

            // Waktu pembayaran dikonfirmasi (diisi saat webhook + verify API sukses)
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Hapus tabel pesanan.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
