<?php

namespace Nucleus\Brain\Ai\Agents;

use App\Models\User;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Promptable;
use Nucleus\Brain\Ai\Middleware\LogBrainPrompts;
use Nucleus\Brain\Ai\Tools\GetProviderProfile;
use Nucleus\Brain\Ai\Tools\GetUserProfile;
use Stringable;

/**
 * Reference agent demonstrating the tool-registration pattern.
 *
 * Fork-implementers should replace this with a domain-specific agent
 * (e.g. HealthcareAgent, LegalAgent) and register their own tools here.
 * Register the agent class in config/brain.php → agents.{role}.
 */
class ExampleAgent implements Agent, Conversational, HasMiddleware
{
    use Promptable, RemembersConversations;

    public function __construct(protected User $user) {}

    public function instructions(): Stringable|string
    {
        return 'You are a helpful assistant. '
            . 'You can look up the user\'s profile and any linked provider information. '
            . 'Answer clearly and concisely.';
    }

    public function tools(): array
    {
        return [
            new GetUserProfile($this->user),
            new GetProviderProfile($this->user),
        ];
    }

    public function middleware(): array
    {
        return [
            new LogBrainPrompts('consumer'),
        ];
    }
}
