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
        $adminContent = User::create([
            'role' => 1,
            'email' => 'admincontent@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);

        AdminProfile::create([
            'user_id' => $adminContent->id,
            'first_name' => 'Admin',
            'last_name' => 'Konten',
        ]);
        
        $adminProposal = User::create([
            'role' => 2,
            'email' => 'adminproposal@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);

        AdminProfile::create([
            'user_id' => $adminProposal->id,
            'first_name' => 'Admin',
            'last_name' => 'Pengajuan Usulan',
        ]);
        
        $adminSuper = User::create([
            'role' => 3,
            'email' => 'adminsuper@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);

        AdminProfile::create([
            'user_id' => $adminSuper->id,
            'first_name' => 'Admin',
            'last_name' => 'Super',
        ]);
    }
}
