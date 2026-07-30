<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Major;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Major::all() as $major) {

            for ($grade = 10; $grade <= 12; $grade++) {

                for ($i = 1; $i <= 2; $i++) {

                    Classroom::create([
                        'major_id' => $major->id,
                        'grade' => $grade,
                        'name' => "{$grade} {$major->code} {$i}",
                    ]);

                }

            }

        }
    }
}
