<?php

namespace Database\Seeders;

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
            'id' => 'AC'. date('dmy') . '0001',
            'first_name' => 'Admin',
            'last_name' => 'Content',
            'role' => 1,
            'email' => 'admincontent@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);
        
        User::create([
            'id' => 'AP'. date('dmy') . '0001',
            'first_name' => 'Admin',
            'last_name' => 'Proposal',
            'role' => 2,
            'email' => 'adminproposal@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);
        
        User::create([
            'id' => 'AS'. date('dmy') . '0001',
            'first_name' => 'Admin',
            'last_name' => 'Super',
            'role' => 3,
            'email' => 'adminsuper@mail.test',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
        ]);
    }
}
