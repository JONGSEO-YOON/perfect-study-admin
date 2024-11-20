<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Counselor;
use App\Models\GradeSystem;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\ResourceSubCategory;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TestSheet;
use App\Models\User;
use Database\Factories\StudentFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            SchoolSeeder::class,
            GradeSystemSeeder::class,
        ]);

        User::factory()->create([
            'name' => '관리자',
            'username' => 'admin',
        ]);

        Teacher::factory(12)->create();
        Counselor::factory(2)->create();
        Student::factory(20)->create();

        // 내신관
        $category = ResourceCategory::create([
            'name' => '내신관',
            'order' => 1,
        ]);

        ResourceSubCategory::create([
            'name' => '내신 대비 교과서',
            'order' => 1,
            'resource_category_id' => $category->id,
        ]);

        ResourceSubCategory::create([
            'name' => '내신 대비 추천',
            'order' => 2,
            'resource_category_id' => $category->id,
        ]);

        ResourceSubCategory::create([
            'name' => '학교별 기출',
            'order' => 3,
            'resource_category_id' => $category->id,
        ]);

        // 수능·경시관
        $category = ResourceCategory::create([
            'name' => '수능·경시관',
            'order' => 2,
        ]);

        ResourceSubCategory::create([
            'name' => '수능·모의고사',
            'order' => 1,
            'resource_category_id' => $category->id,
        ]);

        ResourceSubCategory::create([
            'name' => 'MAAT 수학경시',
            'order' => 2,
            'resource_category_id' => $category->id,
        ]);

        // 클리닉관
        $category = ResourceCategory::create([
            'name' => '클리닉관',
            'order' => 3,
        ]);

        ResourceSubCategory::create([
            'name' => '연산',
            'order' => 1,
            'resource_category_id' => $category->id,
        ]);

        // 평가관
        $category = ResourceCategory::create([
            'name' => '평가관',
            'order' => 4,
        ]);

        ResourceSubCategory::create([
            'name' => '학력평가',
            'order' => 1,
            'resource_category_id' => $category->id,
        ]);

        ResourceSubCategory::create([
            'name' => '입학 TEST',
            'order' => 2,
            'resource_category_id' => $category->id,
        ]);

        ResourceSubCategory::create([
            'name' => '주간 TEST',
            'order' => 3,
            'resource_category_id' => $category->id,
        ]);

        ResourceSubCategory::create([
            'name' => '단원 TEST',
            'order' => 4,
            'resource_category_id' => $category->id,
        ]);

        $classroom = Classroom::create([
            'name' => '3학년 A레벨 평일 특강반',
            'teacher_id' => Teacher::first()->id,
            'target_grades' => [
                GradeSystem::where('display_name', '고3')->first()->id,
            ],
            'target_level' => 'A',
        ]);
        Classroom::create([
            'name' => '3학년 A레벨 주말 특강반',
            'teacher_id' => Teacher::first()->id,
            'target_grades' => [
                GradeSystem::where('display_name', '고3')->first()->id,
            ],
            'target_level' => 'A',
        ]);

        $classroom->students()->attach(Student::all()->random(5));

        TestSheet::create([
            'tag' => '중간고사',
            'name' => '2024년 1학기 중간고사',
            'target_group' => 'grade',
            'target_grades' => ['고3'],
            'user_id' => User::first()->id,
            'status' => 'progress',
            'scopes' => [
                '두 점 사이의 거리',
                '부등식의 증명'
            ]
        ]);

        TestSheet::create([
            'tag' => '주간 TEST',
            'name' => '2024년 24주차 레벨별 주간 TEST',
            'target_group' => 'level',
            'target_grades' => ['고3'],
            'target_levels' => ['A'],
            'user_id' => User::first()->id,
            'status' => 'pending',
            'scopes' => [
                '수열의 귀납적 정의'
            ]
        ]);

        TestSheet::create([
            'tag' => '일일 TEST',
            'name' => '2024년 11월 1일 일일 TEST',
            'target_group' => 'classroom',
            'target_grades' => ['고3'],
            'target_classrooms' => ['고3 - A레벨 - 평일 특강반'],
            'status' => 'completed',
            'user_id' => User::first()->id,
            'scopes' => [
                '속도와 가속도',
                '부정적분'
            ]
        ]);

        TestSheet::create([
            'tag' => '숙제',
            'name' => '2024년 11월 1일자 숙제',
            'target_group' => 'classroom',
            'target_grades' => ['고3'],
            'target_classrooms' => ['고3 - A레벨 - 평일 특강반', '고3 - A레벨 - 주말 특강반'],
            'status' => 'completed',
            'user_id' => User::first()->id,
            'scopes' => [
                '이등변삼각형의 성질'
            ]
        ]);
    }
}
