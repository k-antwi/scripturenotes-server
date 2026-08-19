<?php

namespace Nucleus\Brain\Ai\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class LogBrainPrompts
{
    public function __construct(private readonly string $context = 'user') {}

    public function handle(AgentPrompt $prompt, Closure $next): mixed
    {
        Log::info('Brain prompt', [
            'context' => $this->context,
            'agent'   => $prompt->agent::class,
            'user_id' => auth()->id(),
            'length'  => strlen($prompt->prompt),
        ]);

        return $next($prompt)->then(function (AgentResponse $response) {
            Log::info('Brain response', [
                'context'         => $this->context,
                'conversation_id' => $response->conversationId,
                'length'          => strlen($response->text),
            ]);
        });
    }
}
