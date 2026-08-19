<?php

namespace Nucleus\Brain\Ai\Agents;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Promptable;
use Nucleus\Brain\Ai\Middleware\LogBrainPrompts;
use Stringable;

class AdminBrainAgent implements Agent, Conversational, HasMiddleware
{
    use Promptable, RemembersConversations;

    /**
     * The instructions the agent must follow.
     */
    public function instructions(): Stringable|string
    {
        return 'You are an intelligent assistant for the admin panel. '
            . 'You can help administrators perform tasks, analyse data, and generate content. '
            . 'Be precise, professional, and helpful.';
    }

    /**
     * Middleware to apply to every prompt.
     */
    public function middleware(): array
    {
        return [
            new LogBrainPrompts('admin'),
        ];
    }
}
