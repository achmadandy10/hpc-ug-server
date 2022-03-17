<?php

namespace Database\Seeders;

use App\Models\AdminProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'id' => 'ADM'. date('dmy') . '0001',
            'role' => 1,
            'email' => 'admincontent@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);

        AdminProfile::create([
            'user_id' => 'ADM'. date('dmy') . '0001',
            'first_name' => 'Admin',
            'last_name' => 'Konten',
        ]);
        
        User::create([
            'id' => 'ADM'. date('dmy') . '0002',
            'role' => 2,
            'email' => 'adminproposal@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);

        AdminProfile::create([
            'user_id' => 'ADM'. date('dmy') . '0002',
            'first_name' => 'Admin',
            'last_name' => 'Pengajuan Usulan',
        ]);
        
        User::create([
            'id' => 'ADM'. date('dmy') . '0003',
            'role' => 3,
            'email' => 'adminsuper@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);

        AdminProfile::create([
            'user_id' => 'ADM'. date('dmy') . '0003',
            'first_name' => 'Admin',
            'last_name' => 'Super',
        ]);
    }
}
