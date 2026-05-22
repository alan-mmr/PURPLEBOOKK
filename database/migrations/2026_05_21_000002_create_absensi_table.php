<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kartu_nfc_id')->nullable()->constrained('kartu_nfc')->onDelete('set null');
            $table->enum('metode', ['nfc', 'qr'])->default('nfc');
            $table->enum('status', ['hadir', 'terlambat'])->default('hadir');
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('scanned_by')->constrained('users')->onDelete('cascade');
            $table->timestamps(); // created_at = waktu absensi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
