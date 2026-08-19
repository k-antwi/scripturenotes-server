<?php

namespace Nucleus\Brain\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Nucleus\Brain\Contracts\BrainConversationStore;
use Nucleus\Brain\Models\AiModel;
use Nucleus\Brain\Services\BrainService;

class BrainPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'phosphor-brain-duotone';

    protected string $view = 'brain::filament.pages.brain-page';

    protected static ?string $navigationLabel = 'Brain';

    protected static ?string $title = 'Brain — AI Prompt';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Intel';
    }

    public string $prompt = '';

    public ?string $conversationId = null;
    public array $messages = [];

    public ?string $activeModelLabel = null;

    public bool $loading = false;

    public string $activeTab = 'response';

    public array $pastConversations = [];

    public function mount(): void
    {
        $this->activeModelLabel = AiModel::getActive()?->label ?? 'No model configured';
        $this->loadPastConversations();
    }

    public function send(): void
    {
        $this->validate(['prompt' => 'required|string|max:10000']);

        $this->loading = true;

        /** @var BrainService $brain */
        $brain = app(BrainService::class);

        $result = $brain->promptAsAdmin($this->prompt, $this->conversationId, auth()->user());

        $this->conversationId = $result['conversation_id'];

        $this->messages[] = [
            'role'      => 'user',
            'content'   => $this->prompt,
            'artifacts' => [],
        ];

        $this->messages[] = [
            'role'      => 'assistant',
            'content'   => $result['content'],
            'artifacts' => $result['artifacts'],
        ];

        $this->prompt  = '';
        $this->loading = false;

        $this->loadPastConversations();
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->messages       = [];
        $this->prompt         = '';
    }

    public function loadConversation(string $conversationId): void
    {
        $messages = $this->getConversationMessages($conversationId);

        if (! empty($messages)) {
            $this->conversationId = $conversationId;
            $this->messages       = $messages;
        }
    }

    private function loadPastConversations(): void
    {
        /** @var BrainConversationStore $store */
        $store = app(BrainConversationStore::class);

        $this->pastConversations = $store->listConversations(auth()->id());
    }

    private function getConversationMessages(string $conversationId): array
    {
        /** @var BrainConversationStore $store */
        $store    = app(BrainConversationStore::class);
        $messages = $store->getLatestConversationMessages($conversationId, 200);

        return $messages->map(fn ($msg) => [
            'role'      => $msg instanceof \Laravel\Ai\Messages\AssistantMessage ? 'assistant' : 'user',
            'content'   => (string) $msg,
            'artifacts' => [],
        ])->values()->all();
    }
}
