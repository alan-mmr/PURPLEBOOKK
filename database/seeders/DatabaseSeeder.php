<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Buku;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Data sesuai requirement modul (3 Kategori, 3 Buku)
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@purplebook.com',
            'password' => Hash::make('admin123'),
        ]);

        // Create categories (Modul Requirement: Novel, Biografi, Komik)
        $novel = Kategori::create(['nama_kategori' => 'Novel']);
        $biografi = Kategori::create(['nama_kategori' => 'Biografi']);
        $komik = Kategori::create(['nama_kategori' => 'Komik']);

        // Buku 1: Home Sweet Loan (Novel)
        Buku::create([
            'kode' => 'NV-01',
            'judul' => 'Home Sweet Loan',
            'pengarang' => 'Almira Bastari',
            'idkategori' => $novel->idkategori,
        ]);

        // Buku 2: Mohammad Hatta, Untuk Negeriku (Biografi)
        Buku::create([
            'kode' => 'BO-01',
            'judul' => 'Mohammad Hatta, Untuk Negeriku',
            'pengarang' => 'Taufik Abdullah',
            'idkategori' => $biografi->idkategori,
        ]);

        // Buku 3: Keajaiban Toko Kelontong Namiya (Novel)
        Buku::create([
            'kode' => 'NV-02',
            'judul' => 'Keajaiban Toko Kelontong Namiya',
            'pengarang' => 'Keigo Higashino',
            'idkategori' => $novel->idkategori,
        ]);

        // Seed data barang Toko PurpleBook (12 barang)
        $this->call(BarangSeeder::class);
    }
}
