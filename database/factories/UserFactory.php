<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        static $password;

        return [
            'name' => 'Test User',
            'username' => 'testuser_' . Str::random(5),
            'email' => 'test_' . Str::random(5) . '@example.com',
            'password' => $password ?: $password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // 'password'
            'avatar' => 'demo/default.png',
            'remember_token' => Str::random(10),
        ];
    }
}
