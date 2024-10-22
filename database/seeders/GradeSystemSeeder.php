<?php

namespace Database\Seeders;

use App\Models\GradeSystem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSystemSeeder extends Seeder
{
    public function run()
    {
        $gradeData = [];

        // 초등학교 (1-6학년)
        for ($grade = 1; $grade <= 6; $grade++) {
            $gradeData[] = [
                'school_type' => '초등학교',
                'grade' => $grade,
                'display_name' => "초{$grade}",
                'sequential_order' => $grade,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 중학교 (1-3학년)
        for ($grade = 1; $grade <= 3; $grade++) {
            $gradeData[] = [
                'school_type' => '중학교',
                'grade' => $grade,
                'display_name' => "중{$grade}",
                'sequential_order' => $grade + 6,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 고등학교 (1-3학년)
        for ($grade = 1; $grade <= 3; $grade++) {
            $gradeData[] = [
                'school_type' => '고등학교',
                'grade' => $grade,
                'display_name' => "고{$grade}",
                'sequential_order' => $grade + 9,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $gradeData[] = [
            'school_type' => '재수',
            'grade' => 1,
            'display_name' => "재수",
            'sequential_order' => 13,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        GradeSystem::insert($gradeData);
    }
}
