<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel customer untuk modul manajemen customer + kamera.
     * foto_blob: menyimpan foto langsung di database (bytea di PostgreSQL).
     * foto_path: menyimpan path file jika user pilih simpan ke disk.
     */
    public function up(): void
    {
        Schema::create('customer', function (Blueprint $table) {
            $table->id('idcustomer');
            $table->string('nama', 255);
            $table->text('foto_blob')->nullable();     // base64 encoded foto
            $table->string('foto_path', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
