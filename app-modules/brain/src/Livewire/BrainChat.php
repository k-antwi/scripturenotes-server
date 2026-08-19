<?php

namespace Nucleus\Brain\Livewire;

use Livewire\Component;
use Nucleus\Brain\Contracts\BrainConversationStore;
use Nucleus\Brain\Models\AiModel;
use Nucleus\Brain\Services\BrainService;

class BrainChat extends Component
{
    public string $input = '';

    public array $messages = [];

    public ?string $conversationId = null;

    public bool $loading = false;

    public ?string $activeModelLabel = null;

    public function mount(): void
    {
        $this->activeModelLabel = AiModel::getActive()?->label ?? null;
    }

    public function send(): void
    {
        $this->validate(['input' => 'required|string|max:5000']);

        $this->loading = true;

        $user = auth()->user();

        /** @var BrainService $brain */
        $brain = app(BrainService::class);

        $result = $brain->promptAsUser($user, $this->input, $this->conversationId);

        $this->conversationId = $result['conversation_id'];

        $this->messages[] = [
            'role'    => 'user',
            'content' => $this->input,
        ];

        $this->messages[] = [
            'role'      => 'assistant',
            'content'   => $result['content'],
            'artifacts' => $result['artifacts'],
        ];

        $this->input   = '';
        $this->loading = false;

        $this->dispatch('message-sent');
    }

    public function loadConversation(string $conversationId): void
    {
        /** @var BrainConversationStore $store */
        $store    = app(BrainConversationStore::class);
        $messages = $store->getLatestConversationMessages($conversationId, 200);

        if ($messages->isNotEmpty()) {
            $this->conversationId = $conversationId;
            $this->messages       = $messages->map(fn ($msg) => [
                'role'      => $msg instanceof \Laravel\Ai\Messages\AssistantMessage ? 'assistant' : 'user',
                'content'   => $msg->content ?? '',
                'artifacts' => [],
            ])->values()->all();
        }
    }

    public function startNew(): void
    {
        $this->conversationId = null;
        $this->messages       = [];
        $this->input          = '';
    }

    public function render()
    {
        $pastConversations = [];
        $user              = auth()->user();

        if ($user) {
            /** @var BrainConversationStore $store */
            $store             = app(BrainConversationStore::class);
            $pastConversations = $store->listConversations($user->id, 10);
        }

        return view('brain::livewire.brain-chat', compact('pastConversations'));
    }
}
