<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel antrian untuk menyimpan data antrian per vendor per hari.
     * - nomor_antrian di-reset per vendor per hari
     * - status: menunggu | dipanggil | selesai | terlewat
     * - idvendor: FK ke tabel vendor (konsep "ruangan" dari modul)
     */
    public function up(): void
    {
        Schema::create('antrian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idvendor');
            $table->integer('nomor_antrian');
            $table->string('nama', 100);
            $table->enum('status', ['menunggu', 'dipanggil', 'selesai', 'terlewat'])->default('menunggu');
            $table->timestamp('called_at')->nullable();
            $table->timestamps();

            $table->foreign('idvendor')->references('idvendor')->on('vendor')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrian');
    }
};
