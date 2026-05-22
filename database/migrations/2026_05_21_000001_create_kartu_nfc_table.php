<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kartu_nfc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('serial_number', 100)->unique(); // ID unik kartu NFC atau token QR
            $table->enum('tipe', ['nfc', 'qr'])->default('nfc'); // jenis kartu
            $table->string('label', 100)->nullable();             // nama kartu: "KTM", "Kartu biru"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kartu_nfc');
    }
};
