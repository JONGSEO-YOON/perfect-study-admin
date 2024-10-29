<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{


    public function definition()
    {
        return [];
    }

    public function configure()
    {
        return $this->afterCreating(function (Teacher $teacher) {
            $user = User::factory()->create([
                'name' => $this->faker->name,
                'username' => 'teacher_' . $this->faker->unique()->userName,
                'email' => $this->faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => bcrypt('password'), // 기본 비밀번호 설정
                'remember_token' => Str::random(10),
            ]);

            $teacher->user()->save($user);
        });
    }
}
