<?php

namespace Nucleus\Brain\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Nucleus\Brain\Models\AiModel;

class AiModelFactory extends Factory
{
    protected $model = AiModel::class;

    public function definition(): array
    {
        return [
            'label'     => $this->faker->words(3, true),
            'provider'  => $this->faker->randomElement(['anthropic', 'openai', 'gemini']),
            'model_id'  => 'test-model-' . $this->faker->slug(2),
            'api_key'   => 'sk-test-' . $this->faker->sha256(),
            'is_active' => false,
            'config'    => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }
}
