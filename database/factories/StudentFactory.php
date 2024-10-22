<?php

namespace Database\Factories;

use App\Models\GradeSystem;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'school_id' =>  School::query()
                ->inRandomOrder()
                ->first()->id,
            'grade_system_id' => GradeSystem::query()
                ->inRandomOrder()
                ->first()->id,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Student $teacher) {
            $user = User::factory()->create([
                'name' => $this->faker->name,
                'username' => 'student_' . $this->faker->unique()->userName,
                'email' => $this->faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => bcrypt('password'), // 기본 비밀번호 설정
                'remember_token' => Str::random(10),
            ]);

            $teacher->user()->save($user);
        });
    }
}
