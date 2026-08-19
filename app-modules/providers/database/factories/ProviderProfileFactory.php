<?php

namespace Nucleus\Providers\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Nucleus\Providers\Models\ProviderProfile;

class ProviderProfileFactory extends Factory
{
    protected $model = ProviderProfile::class;

    public function definition(): array
    {
        return [
            'user_id'              => User::factory(),
            'credential_reference' => $this->faker->bothify('CR-#####'),
            'verification_status'  => ProviderProfile::STATUS_PENDING,
            'verification_message' => null,
            'verified_at'          => null,
            'bio'                  => $this->faker->paragraph(),
            'is_active'            => true,
            'is_primary'           => false,
            'metadata'             => [],
        ];
    }

    public function verified(): self
    {
        return $this->state(fn () => [
            'verification_status' => ProviderProfile::STATUS_VERIFIED,
            'verified_at'         => now(),
        ]);
    }

    public function primary(): self
    {
        return $this->state(fn () => ['is_primary' => true]);
    }
}
