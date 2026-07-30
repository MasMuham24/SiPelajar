<?php

namespace Database\Seeders;

use App\Models\Major;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    public function run(): void
    {
        $majors = [
            ['code' => 'TJKT', 'name' => 'Teknik Jaringan Komputer dan Telekomunikasi'],
            ['code' => 'RPL',  'name' => 'Rekayasa Perangkat Lunak'],
            ['code' => 'AKL',  'name' => 'Akuntansi dan Keuangan Lembaga'],
            ['code' => 'MPLB', 'name' => 'Manajemen Perkantoran dan Layanan Bisnis'],
        ];

        foreach ($majors as $major) {
            Major::create($major);
        }
    }
}
