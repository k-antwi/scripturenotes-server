<?php

namespace Nucleus\Brain\Ai\Tools;

use App\Models\User;
use Laravel\Ai\Attributes\Tool;

class GetUserProfile
{
    public function __construct(protected User $user) {}

    #[Tool('Get the profile information for the currently authenticated user')]
    public function __invoke(): array
    {
        return [
            'id'         => $this->user->id,
            'name'       => $this->user->name,
            'email'      => $this->user->email,
            'created_at' => $this->user->created_at?->toDateString(),
        ];
    }
}
