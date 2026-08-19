<?php

namespace Nucleus\Brain\Storage;

use Couchbase\Cluster;
use Couchbase\ClusterOptions;
use Couchbase\Collection as CouchbaseCollection;
use Couchbase\Exception\DocumentNotFoundException;
use Couchbase\QueryOptions;
use Illuminate\Support\Collection;
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
 * Couchbase-backed conversation store for the Laravel AI SDK.
 *
 * Each conversation is stored as a single Couchbase document keyed by UUID:
 *
 *   {
 *     "user_id":   1,
 *     "title":     "Conversation title",
 *     "messages":  [ { "id", "role", "agent", "content", ... }, ... ],
 *     "created_at": "ISO8601",
 *     "updated_at": "ISO8601"
 *   }
 *
 * If Couchbase is unreachable at construction time, or if any operation fails,
 * all methods automatically fall back to the relational database store.
 */
class CouchbaseConversationStore implements BrainConversationStore
{
    private ?Cluster $cluster;

    private ?CouchbaseCollection $collection;

    private readonly RelationalConversationStore $fallback;

    public function __construct()
    {
        $this->fallback = new RelationalConversationStore;
        [$this->cluster, $this->collection] = $this->makeClient();
    }

    // -------------------------------------------------------------------------
    // ConversationStore interface
    // -------------------------------------------------------------------------

    /**
     * Get the most recent conversation ID for a given user.
     */
    public function latestConversationId(string|int $userId): ?string
    {
        if (! $this->cluster) {
            return $this->fallback->latestConversationId($userId);
        }

        try {
            $bucket  = config('brain.couchbase.bucket');
            $options = new QueryOptions;
            $options->namedParameters(['userId' => (int) $userId]);

            $result = $this->cluster->query(
                "SELECT META().id FROM `{$bucket}` WHERE user_id = \$userId ORDER BY updated_at DESC LIMIT 1",
                $options
            );

            $rows = $result->rows();

            return $rows[0]['id'] ?? null;
        } catch (\Throwable) {
            return $this->fallback->latestConversationId($userId);
        }
    }

    /**
     * Store a new conversation document and return its ID.
     */
    public function storeConversation(string|int|null $userId, string $title): string
    {
        $id = (string) Str::uuid7();

        if (! $this->collection) {
            return $this->fallback->storeConversation($userId, $title);
        }

        try {
            $this->collection->upsert($id, [
                'user_id'    => $userId !== null ? (int) $userId : null,
                'title'      => $title,
                'messages'   => [],
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ]);

            return $id;
        } catch (\Throwable) {
            return $this->fallback->storeConversation($userId, $title);
        }
    }

    /**
     * Append a user message to the conversation document.
     */
    public function storeUserMessage(string $conversationId, string|int|null $userId, AgentPrompt $prompt): string
    {
        $messageId = (string) Str::uuid7();

        if (! $this->collection) {
            return $this->fallback->storeUserMessage($conversationId, $userId, $prompt);
        }

        try {
            $doc = $this->collection->get($conversationId)->content();

            $doc['messages'][] = [
                'id'           => $messageId,
                'role'         => 'user',
                'agent'        => $prompt->agent::class,
                'content'      => $prompt->prompt,
                'attachments'  => $prompt->attachments->toArray(),
                'tool_calls'   => [],
                'tool_results' => [],
                'usage'        => [],
                'meta'         => [],
                'created_at'   => now()->toIso8601String(),
            ];

            $doc['updated_at'] = now()->toIso8601String();

            $this->collection->upsert($conversationId, $doc);

            return $messageId;
        } catch (\Throwable) {
            return $this->fallback->storeUserMessage($conversationId, $userId, $prompt);
        }
    }

    /**
     * Append an assistant message to the conversation document.
     */
    public function storeAssistantMessage(string $conversationId, string|int|null $userId, AgentPrompt $prompt, AgentResponse $response): string
    {
        $messageId = (string) Str::uuid7();

        if (! $this->collection) {
            return $this->fallback->storeAssistantMessage($conversationId, $userId, $prompt, $response);
        }

        try {
            $doc = $this->collection->get($conversationId)->content();

            $doc['messages'][] = [
                'id'           => $messageId,
                'role'         => 'assistant',
                'agent'        => $prompt->agent::class,
                'content'      => $response->text,
                'attachments'  => [],
                'tool_calls'   => json_decode(json_encode($response->toolCalls), true),
                'tool_results' => json_decode(json_encode($response->toolResults), true),
                'usage'        => json_decode(json_encode($response->usage), true),
                'meta'         => json_decode(json_encode($response->meta), true),
                'created_at'   => now()->toIso8601String(),
            ];

            $doc['updated_at'] = now()->toIso8601String();

            $this->collection->upsert($conversationId, $doc);

            return $messageId;
        } catch (\Throwable) {
            return $this->fallback->storeAssistantMessage($conversationId, $userId, $prompt, $response);
        }
    }

    /**
     * Get the latest N messages for a conversation, mapped to AI SDK Message objects.
     *
     * @return Collection<int, Message>
     */
    public function getLatestConversationMessages(string $conversationId, int $limit): Collection
    {
        if (! $this->collection) {
            return $this->fallback->getLatestConversationMessages($conversationId, $limit);
        }

        try {
            $doc  = $this->collection->get($conversationId)->content();
            $msgs = $doc['messages'] ?? [];
            $slice = array_slice($msgs, -$limit);

            return collect($slice)->flatMap(function (array $m): array {
                $toolCalls   = collect($m['tool_calls'] ?? []);
                $toolResults = collect($m['tool_results'] ?? []);

                if ($m['role'] === 'user') {
                    return [new Message('user', $m['content'])];
                }

                if ($toolCalls->isNotEmpty()) {
                    $result = [];

                    $result[] = new AssistantMessage(
                        $m['content'] ?: '',
                        $toolCalls->map(fn ($tc) => new ToolCall(
                            id: $tc['id'],
                            name: $tc['name'],
                            arguments: $tc['arguments'],
                            resultId: $tc['result_id'] ?? null,
                        ))
                    );

                    if ($toolResults->isNotEmpty()) {
                        $result[] = new ToolResultMessage(
                            $toolResults->map(fn ($tr) => new ToolResult(
                                id: $tr['id'],
                                name: $tr['name'],
                                arguments: $tr['arguments'],
                                result: $tr['result'],
                                resultId: $tr['result_id'] ?? null,
                            ))
                        );
                    }

                    return $result;
                }

                return [new AssistantMessage($m['content'])];
            });
        } catch (\Throwable) {
            return $this->fallback->getLatestConversationMessages($conversationId, $limit);
        }
    }

    // -------------------------------------------------------------------------
    // BrainConversationStore extension
    // -------------------------------------------------------------------------

    /**
     * List recent conversations for a user, newest first.
     *
     * @return array<int, array{id: string, title: string, updated_at: string}>
     */
    public function listConversations(string|int $userId, int $limit = 20): array
    {
        if (! $this->cluster) {
            return $this->fallback->listConversations($userId, $limit);
        }

        try {
            $bucket  = config('brain.couchbase.bucket');
            $options = new QueryOptions;
            $options->namedParameters(['userId' => (int) $userId, 'convLimit' => $limit]);

            $result = $this->cluster->query(
                "SELECT META().id, title, updated_at FROM `{$bucket}` WHERE user_id = \$userId ORDER BY updated_at DESC LIMIT \$convLimit",
                $options
            );

            return array_map(fn ($row) => [
                'id'         => $row['id'],
                'title'      => $row['title'] ?? 'Conversation',
                'updated_at' => $row['updated_at'] ?? '',
            ], $result->rows());
        } catch (\Throwable) {
            return $this->fallback->listConversations($userId, $limit);
        }
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    private function makeClient(): array
    {
        try {
            $cfg = config('brain.couchbase');

            if (empty($cfg['connection_string']) || empty($cfg['bucket'])) {
                return [null, null];
            }

            $options = new ClusterOptions;
            $options->credentials($cfg['username'], $cfg['password']);

            $cluster    = new Cluster($cfg['connection_string'], $options);
            $collection = $cluster->bucket($cfg['bucket'])->defaultCollection();

            return [$cluster, $collection];
        } catch (\Throwable) {
            return [null, null];
        }
    }
}
