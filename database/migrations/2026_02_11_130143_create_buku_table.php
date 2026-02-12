<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            // Custom primary key with name 'idbuku'
            $table->id('idbuku');
            $table->string('kode');
            $table->string('judul');
            $table->string('pengarang');
            
            // Foreign key to kategori table (references custom PK 'idkategori')
            $table->foreignId('idkategori')
                  ->constrained('kategori', 'idkategori')
                  ->onDelete('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
