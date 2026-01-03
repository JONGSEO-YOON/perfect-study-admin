<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::first()->id,
            'question_display_type' => 'image',
            'image_path' => 'question_1.jpg',
            'content' => null,
            'answer_type' => 'multiple_choice',
            'answer' => fake()->numberBetween(1, 5),
            'explanation_display_type' => 'content',
            'choices_display_type' => 'in_question',
            'explanation' => fake()->paragraph(),
            'tags' => fake()->randomElements(['테스트 태그 1', '테스트 태그 2', '테스트 태그 3', '테스트 태그 4'], rand(1, 2)),
            // 'is_wrong_note' => false,
            'explanation_image_path' => null,
            'explanation_video_url' => null,
        ];
    }
}
