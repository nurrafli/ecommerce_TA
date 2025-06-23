<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin 1
        User::create([
            'name' => 'Admin 1',
            'email' => 'admin1@example.com',
            'mobile' => '081111111111',
            'password' => Hash::make('admin123'),
            'utype' => 'ADM',
        ]);

        // Admin 2
        User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@example.com',
            'mobile' => '082222222222',
            'password' => Hash::make('admin123'),
            'utype' => 'ADM',
        ]);
    }
}
