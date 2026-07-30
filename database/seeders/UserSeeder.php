<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'role' => 'admin',
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@sipelajar.com',
            'password' => 'password',
        ]);
    }
}
