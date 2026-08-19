<?php

namespace Nucleus\Brain\Services;

use App\Models\User;
use InvalidArgumentException;
use Laravel\Ai\Contracts\Agent;
use Nucleus\Brain\Ai\Agents\AdminBrainAgent;
use Nucleus\Brain\Models\AiModel;

class BrainService
{
    /**
     * Execute an admin prompt via the AdminBrainAgent.
     *
     * @return array{conversation_id: string, content: string, artifacts: array}
     */
    public function promptAsAdmin(string $prompt, ?string $conversationId = null, ?User $admin = null): array
    {
        return $this->dispatch(new AdminBrainAgent, $prompt, $conversationId, $admin);
    }

    /**
     * Execute a prompt via an agent registered for the given role.
     *
     * Roles are mapped to agent classes in config('brain.agents'). The agent
     * receives the user as constructor arg if its constructor accepts one.
     *
     * @return array{conversation_id: string, content: string, artifacts: array}
     */
    public function promptAs(User $user, string $role, string $prompt, ?string $conversationId = null): array
    {
        $agentClass = config("brain.agents.$role");

        if (! $agentClass || ! class_exists($agentClass)) {
            throw new InvalidArgumentException("No agent registered for role [$role].");
        }

        $agent = $this->makeAgent($agentClass, $user);

        return $this->dispatch($agent, $prompt, $conversationId, $user);
    }

    /**
     * Execute a prompt as the given user, picking the agent from their roles.
     *
     * The first role in config('brain.agents') that the user actually holds
     * wins, so the mapping order in config is the precedence order. Users
     * with no mapped role fall back to config('brain.default_agent').
     *
     * @return array{conversation_id: string, content: string, artifacts: array}
     */
    public function promptAsUser(User $user, string $prompt, ?string $conversationId = null): array
    {
        $agentClass = $this->resolveAgentForUser($user);

        if (! $agentClass || ! class_exists($agentClass)) {
            throw new InvalidArgumentException('No agent is registered for this user.');
        }

        return $this->dispatch($this->makeAgent($agentClass, $user), $prompt, $conversationId, $user);
    }

    private function resolveAgentForUser(User $user): ?string
    {
        foreach (config('brain.agents', []) as $role => $agentClass) {
            if ($user->hasRole($role)) {
                return $agentClass;
            }
        }

        return config('brain.default_agent');
    }

    private function makeAgent(string $agentClass, User $user): Agent
    {
        return app()->make($agentClass, ['user' => $user]);
    }

    /**
     * @return array{conversation_id: string, content: string, artifacts: array}
     */
    private function dispatch(Agent $agent, string $prompt, ?string $conversationId, ?User $user): array
    {
        $model = AiModel::getActive();

        if (! $model) {
            return [
                'conversation_id' => $conversationId ?? '',
                'content'         => 'No active AI model configured. Please activate a model from the AI Models panel.',
                'artifacts'       => [],
            ];
        }

        try {
            $canConverse = method_exists($agent, 'forUser');

            if ($conversationId && $user && $canConverse) {
                $response = $agent
                    ->continue($conversationId, $user)
                    ->prompt($prompt, provider: $model->provider, model: $model->model_id);
            } elseif ($user && $canConverse) {
                $response = $agent
                    ->forUser($user)
                    ->prompt($prompt, provider: $model->provider, model: $model->model_id);
            } else {
                $response = $agent
                    ->prompt($prompt, provider: $model->provider, model: $model->model_id);
            }

            $content = $response->text;

            return [
                'conversation_id' => $response->conversationId ?? $conversationId ?? '',
                'content'         => $content,
                'artifacts'       => $this->extractArtifacts($content),
            ];
        } catch (\Throwable $e) {
            return [
                'conversation_id' => $conversationId ?? '',
                'content'         => 'An error occurred while contacting the AI model: ' . $e->getMessage(),
                'artifacts'       => [],
            ];
        }
    }

    /**
     * Extract fenced code blocks from a response as artifacts.
     */
    private function extractArtifacts(string $content): array
    {
        $artifacts = [];

        preg_match_all('/```(\w*)\n(.*?)```/s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $artifacts[] = [
                'type'    => $match[1] ?: 'text',
                'content' => trim($match[2]),
            ];
        }

        return $artifacts;
    }
}
