<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Counselor;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Counselor>
 */
class CounselorFactory extends Factory
{


    public function definition()
    {
        return [];
    }

    public function configure()
    {
        return $this->afterCreating(function (Counselor $counselor) {
            $user = User::factory()->create([
                'name' => $this->faker->name,
                'username' => 'counselor_' . $this->faker->unique()->userName,
                'email' => $this->faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => bcrypt('password'), // 기본 비밀번호 설정
                'remember_token' => Str::random(10),
            ]);

            $counselor->user()->save($user);
        });
    }
}
