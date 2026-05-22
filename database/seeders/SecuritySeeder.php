<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SecuritySeeder extends Seeder
{
    /**
     * Buat 1 user dengan role 'security' untuk testing fitur absensi NFC.
     * Login: security@purplebook.test / password123
     */
    public function run(): void
    {
        $exists = DB::table('users')->where('email', 'security@purplebook.test')->exists();
        if ($exists) {
            $this->command->info('User security@purplebook.test sudah ada, skip.');
            return;
        }

        DB::table('users')->insert([
            'name'       => 'Security',
            'email'      => 'security@purplebook.test',
            'password'   => Hash::make('password123'),
            'role'       => 'security',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ User Security berhasil dibuat: security@purplebook.test / password123');
    }
}
