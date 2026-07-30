<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'role' => 'guru',
            'name' => 'Budi Santoso',
            'username' => 'guru',
            'email' => 'guru@sipelajar.com',
            'password' => 'password',
        ]);

        Teacher::create([
            'user_id' => $user->id,
            'nip' => '19880001',
            'gender' => 'Laki-laki',
            'phone' => '08123456789',
            'address' => 'Demak',
            'photo' => null,
        ]);
    }
}
