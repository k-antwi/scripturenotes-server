<?php

namespace Nucleus\Brain\Storage;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\ToolResultMessage;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\ToolCall;
use Laravel\Ai\Responses\Data\ToolResult;
use Nucleus\Brain\Contracts\BrainConversationStore;

/**
 * SQL fallback for the Brain conversation store.
 * Uses the agent_conversations / agent_conversation_messages tables
 * created by the Laravel AI SDK migration.
 */
class RelationalConversationStore implements BrainConversationStore
{
    public function latestConversationId(string|int $userId): ?string
    {
        return DB::table('agent_conversations')
            ->where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->first()?->id;
    }

    public function storeConversation(string|int|null $userId, string $title): string
    {
        $id = (string) Str::uuid7();

        DB::table('agent_conversations')->insert([
            'id'         => $id,
            'user_id'    => $userId,
            'title'      => $title,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function storeUserMessage(string $conversationId, string|int|null $userId, AgentPrompt $prompt): string
    {
        $id = (string) Str::uuid7();

        DB::table('agent_conversation_messages')->insert([
            'id'              => $id,
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
            'agent'           => $prompt->agent::class,
            'role'            => 'user',
            'content'         => $prompt->prompt,
            'attachments'     => $prompt->attachments->toJson(),
            'tool_calls'      => '[]',
            'tool_results'    => '[]',
            'usage'           => '[]',
            'meta'            => '[]',
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->update(['updated_at' => now()]);

        return $id;
    }

    public function storeAssistantMessage(string $conversationId, string|int|null $userId, AgentPrompt $prompt, AgentResponse $response): string
    {
        $id = (string) Str::uuid7();

        DB::table('agent_conversation_messages')->insert([
            'id'              => $id,
            'conversation_id' => $conversationId,
            'user_id'         => $userId,
            'agent'           => $prompt->agent::class,
            'role'            => 'assistant',
            'content'         => $response->text,
            'attachments'     => '[]',
            'tool_calls'      => json_encode($response->toolCalls),
            'tool_results'    => json_encode($response->toolResults),
            'usage'           => json_encode($response->usage),
            'meta'            => json_encode($response->meta),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->update(['updated_at' => now()]);

        return $id;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getLatestConversationMessages(string $conversationId, int $limit): Collection
    {
        return DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values()
            ->flatMap(function ($record): array {
                $toolCalls   = collect(json_decode($record->tool_calls, true));
                $toolResults = collect(json_decode($record->tool_results, true));

                if ($record->role === 'user') {
                    return [new Message('user', $record->content)];
                }

                if ($toolCalls->isNotEmpty()) {
                    $messages = [];

                    $messages[] = new AssistantMessage(
                        $record->content ?: '',
                        $toolCalls->map(fn ($tc) => new ToolCall(
                            id: $tc['id'],
                            name: $tc['name'],
                            arguments: $tc['arguments'],
                            resultId: $tc['result_id'] ?? null,
                        ))
                    );

                    if ($toolResults->isNotEmpty()) {
                        $messages[] = new ToolResultMessage(
                            $toolResults->map(fn ($tr) => new ToolResult(
                                id: $tr['id'],
                                name: $tr['name'],
                                arguments: $tr['arguments'],
                                result: $tr['result'],
                                resultId: $tr['result_id'] ?? null,
                            ))
                        );
                    }

                    return $messages;
                }

                return [new AssistantMessage($record->content)];
            });
    }

    /**
     * List recent conversations for a user, newest first.
     *
     * @return array<int, array{id: string, title: string, updated_at: string}>
     */
    public function listConversations(string|int $userId, int $limit = 20): array
    {
        try {
            return DB::table('agent_conversations')
                ->where('user_id', $userId)
                ->orderByDesc('updated_at')
                ->limit($limit)
                ->get(['id', 'title', 'updated_at'])
                ->map(fn ($row) => [
                    'id'         => $row->id,
                    'title'      => $row->title,
                    'updated_at' => (string) $row->updated_at,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
