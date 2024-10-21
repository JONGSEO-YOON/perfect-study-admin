<?php

namespace Database\Seeders;

use App\Models\Teacher;
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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => '관리자',
            'username' => 'admin',
        ]);

        Teacher::factory(12)->create();
    }
}
