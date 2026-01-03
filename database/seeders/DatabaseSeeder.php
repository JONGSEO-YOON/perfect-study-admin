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

        $admin = User::factory()->create([
            'name' => '관리자',
            'username' => 'admin',
            'phone' => '010-1234-5678',
        ]);
        $adminTeacher = Teacher::factory()->create([
            'role' => 'root_admin',
        ]);
        $adminTeacher->user()->save($admin);

        $this->call([
            SchoolSeeder::class,
            GradeSystemSeeder::class,
            QuestionCategorySeeder::class,
            QuestionSeeder::class,
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
            'name' => '3학년 A레벨 평일반',
            'teacher_id' => Teacher::first()->id,
            'target_grades' => [
                GradeSystem::where('display_name', '고3')->first()->id,
            ],
            'target_level' => 'A',
        ]);
        Classroom::create([
            'name' => '3학년 M레벨 평일반',
            'teacher_id' => Teacher::first()->id,
            'target_grades' => [
                GradeSystem::where('display_name', '고3')->first()->id,
            ],
            'target_level' => 'M',
        ]);

        $classroom->students()->attach(Student::all()->random(5));
    }
}
