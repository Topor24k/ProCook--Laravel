<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a demo user
        User::create([
            'name' => 'Demo Chef',
            'email' => 'demo@procook.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Gordon Ramsay',
            'email' => 'gordon@procook.com',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Jamie Oliver',
            'email' => 'jamie@procook.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
