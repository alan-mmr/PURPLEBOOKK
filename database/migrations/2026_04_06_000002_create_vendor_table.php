<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel vendor.
     * Setiap vendor terhubung ke satu akun user (role = vendor).
     */
    public function up(): void
    {
        Schema::create('vendor', function (Blueprint $table) {
            $table->increments('idvendor');

            // Nama toko/vendor
            $table->string('nama_vendor', 255);

            // FK ke tabel users — akun login vendor
            // nullable karena admin bisa tambah vendor sebelum assign akun
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Hapus tabel vendor.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor');
    }
};
