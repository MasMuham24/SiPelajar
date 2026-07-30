<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'role' => 'siswa',
            'name' => 'Andi Saputra',
            'username' => 'siswa',
            'email' => 'siswa@sipelajar.com',
            'password' => 'password',
        ]);

        Student::create([
            'user_id' => $user->id,
            'classroom_id' => Classroom::query()->first()->id,
            'nis' => '240001',
            'nisn' => '1234567890',
            'gender' => 'Laki-laki',
            'phone' => '081111111111',
            'address' => 'Demak',
            'photo' => null,
        ]);
    }
}
