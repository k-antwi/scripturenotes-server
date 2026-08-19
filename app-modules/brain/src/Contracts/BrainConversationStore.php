<?php

namespace Nucleus\Brain\Contracts;

use Laravel\Ai\Contracts\ConversationStore;

/**
 * Extends the Laravel AI SDK ConversationStore with a method for listing
 * conversations — used by the Brain admin panel and user chat sidebar.
 */
interface BrainConversationStore extends ConversationStore
{
    /**
     * List recent conversations for the given user, newest first.
     *
     * @return array<int, array{id: string, title: string, updated_at: string}>
     */
    public function listConversations(string|int $userId, int $limit = 20): array;
}
