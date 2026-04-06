<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel menu.
     * Setiap menu milik satu vendor.
     */
    public function up(): void
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->increments('idmenu');

            // Nama item menu
            $table->string('nama_menu', 255);

            // Harga dalam Rupiah (integer)
            $table->integer('harga');

            // Path gambar relatif (nullable — boleh tidak ada gambar)
            $table->string('path_gambar', 255)->nullable();

            // FK ke tabel vendor
            $table->unsignedInteger('idvendor');
            $table->foreign('idvendor')
                  ->references('idvendor')
                  ->on('vendor')
                  ->onDelete('cascade'); // hapus vendor → hapus semua menunya

            $table->timestamps();
        });
    }

    /**
     * Hapus tabel menu.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
