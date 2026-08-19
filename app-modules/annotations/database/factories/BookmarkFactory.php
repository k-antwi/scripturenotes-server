<?php

namespace Nucleus\Annotations\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Nucleus\Annotations\Models\Bookmark;

class BookmarkFactory extends Factory
{
    protected $model = Bookmark::class;

    public function definition(): array
    {
        $books = ['GEN', 'PSA', 'PRO', 'MAT', 'JHN', 'ROM'];

        return [
            'user_id' => User::factory(),
            'book' => $this->faker->randomElement($books),
            'chapter' => $this->faker->numberBetween(1, 30),
            'verse' => $this->faker->optional()->numberBetween(1, 31),
            'label' => $this->faker->optional()->sentence(3),
        ];
    }
}
