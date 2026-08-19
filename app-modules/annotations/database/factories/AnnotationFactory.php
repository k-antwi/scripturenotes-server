<?php

namespace Nucleus\Annotations\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Nucleus\Annotations\Models\Annotation;

class AnnotationFactory extends Factory
{
    protected $model = Annotation::class;

    public function definition(): array
    {
        $types = ['highlight', 'pen', 'note', 'underline', 'shape'];
        $books = ['GEN', 'PSA', 'PRO', 'MAT', 'JHN', 'ROM'];
        $colours = ['#FF0000', '#FFD700', '#00FF00', '#0000FF', '#800080'];

        return [
            'user_id' => User::factory(),
            'book' => $this->faker->randomElement($books),
            'chapter' => $this->faker->numberBetween(1, 30),
            'verse' => $this->faker->optional()->numberBetween(1, 31),
            'type' => $this->faker->randomElement($types),
            'data' => ['charStart' => 0, 'charEnd' => 20],
            'colour' => $this->faker->randomElement($colours),
            'is_shared' => false,
            'share_token' => null,
            'deleted_at' => null,
        ];
    }
}
