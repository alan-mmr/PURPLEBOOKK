<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BarangSeeder extends Seeder
{
    /**
     * Insert 12 data barang untuk Toko PurpleBook.
     * id_barang akan diisi otomatis oleh trigger PostgreSQL trigger_id_barang.
     * Kita hanya perlu mengisi kolom nama dan harga.
     */
    public function run(): void
    {
        $barangList = [
            ['nama' => 'Novel Laskar Pelangi',    'harga' => 85000],
            ['nama' => 'Novel Bumi Manusia',       'harga' => 90000],
            ['nama' => 'Buku Laravel 11',          'harga' => 120000],
            ['nama' => 'Buku Python Dasar',        'harga' => 95000],
            ['nama' => 'Pulpen Pilot G2',          'harga' => 8500],
            ['nama' => 'Stabilo Boss 4 Warna',     'harga' => 22000],
            ['nama' => 'Sticky Notes 3x3',         'harga' => 12000],
            ['nama' => 'Snack Chitato Original',   'harga' => 11000],
            ['nama' => 'Air Mineral Aqua 600ml',   'harga' => 5000],
            ['nama' => 'Teh Botol Sosro 450ml',    'harga' => 6500],
            ['nama' => 'Kopi Kapal Api Sachet',    'harga' => 3000],
            ['nama' => 'Map Plastik Bening',       'harga' => 7500],
        ];

        foreach ($barangList as $barang) {
            // Insert via DB::table agar trigger PostgreSQL terpanggil dengan benar
            // (Eloquent::create juga bisa, tapi DB::table lebih eksplisit untuk kasus trigger)
            DB::table('barang')->insert([
                'nama'  => $barang['nama'],
                'harga' => $barang['harga'],
                // id_barang & timestamp diisi otomatis oleh trigger + default DB
            ]);
        }
    }
}
