<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    /**
     * Buat 1 user dengan role 'petugas antrian' untuk testing fitur antrian.
     * Login: petugas@purplebook.test / password123
     */
    public function run(): void
    {
        // Cek apakah sudah ada
        $exists = DB::table('users')->where('email', 'petugas@purplebook.test')->exists();
        if ($exists) {
            $this->command->info('User petugas@purplebook.test sudah ada, skip.');
            return;
        }

        DB::table('users')->insert([
            'name'       => 'Petugas Antrian',
            'email'      => 'petugas@purplebook.test',
            'password'   => Hash::make('password123'),
            'role'       => 'petugas antrian',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ User Petugas Antrian berhasil dibuat: petugas@purplebook.test / password123');
    }
}
