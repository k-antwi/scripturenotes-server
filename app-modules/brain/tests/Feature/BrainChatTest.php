<?php

use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Nucleus\Brain\Contracts\BrainConversationStore;
use Nucleus\Brain\Livewire\BrainChat;
use Nucleus\Brain\Models\AiModel;
use Nucleus\Brain\Services\BrainService;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Build a mock ConversationStore that returns an empty past-conversation list
 * by default. Tests that need different behaviour can override via Mockery.
 */
function mockStore(array $conversations = []): BrainConversationStore
{
    $store = Mockery::mock(BrainConversationStore::class);
    $store->shouldReceive('listConversations')->byDefault()->andReturn($conversations);

    app()->instance(BrainConversationStore::class, $store);

    return $store;
}

/** Return a mock BrainService that immediately resolves with a canned response. */
function mockBrainService(string $content = 'AI response.', string $convId = 'conv-test'): BrainService
{
    $service = Mockery::mock(BrainService::class);
    $service->shouldReceive('promptAsUser')->andReturn([
        'conversation_id' => $convId,
        'content'         => $content,
        'artifacts'       => [],
    ]);

    app()->instance(BrainService::class, $service);

    return $service;
}

// ---------------------------------------------------------------------------
// Shared setup
// ---------------------------------------------------------------------------

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ---------------------------------------------------------------------------
// Rendering
// ---------------------------------------------------------------------------

describe('rendering', function () {
    it('renders successfully for an authenticated user', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->assertOk()
            ->assertSee(config('brain.assistant_title', 'Assistant'));
    });

    it('shows the empty-state prompt when no messages exist', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->assertSee('How can I help you today?');
    });

    it('shows a warning banner when no AI model is configured', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->assertSee('AI assistant is not currently available');
    });

    it('shows the active model label when one is configured', function () {
        mockStore();
        AiModel::factory()->create(['label' => 'Claude Pro', 'is_active' => true]);

        Livewire::test(BrainChat::class)
            ->assertSee('Claude Pro');
    });

    it('renders past conversations as clickable buttons', function () {
        mockStore([
            ['id' => 'conv-1', 'title' => 'Earlier conversation', 'updated_at' => now()],
        ]);

        Livewire::test(BrainChat::class)
            ->assertSee('Earlier conversation');
    });
});

// ---------------------------------------------------------------------------
// User-message bubble visibility (the colour fix)
// ---------------------------------------------------------------------------

describe('user message bubble colours', function () {
    it('renders user messages with a dark background class', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->set('messages', [['role' => 'user', 'content' => 'Hello']])
            ->assertSeeHtml('bg-gray-900')
            ->assertSeeHtml('text-white')
            ->assertSee('Hello');
    });

    it('does not use the undefined bg-primary-600 class on user message bubbles', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->set('messages', [['role' => 'user', 'content' => 'Test message']])
            ->assertDontSeeHtml('bg-primary-600');
    });

    it('contains no undefined primary-colour utility classes anywhere in the rendered HTML', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->set('messages', [
                ['role' => 'user',      'content' => 'User message', 'artifacts' => []],
                ['role' => 'assistant', 'content' => 'AI response',  'artifacts' => []],
            ])
            ->assertDontSeeHtml('bg-primary-')
            ->assertDontSeeHtml('text-primary-');
    });

    it('uses indigo-coloured classes for AI avatar elements', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->set('messages', [['role' => 'assistant', 'content' => 'Hi', 'artifacts' => []]])
            ->assertSeeHtml('bg-indigo-100');
    });
});

// ---------------------------------------------------------------------------
// send()
// ---------------------------------------------------------------------------

describe('send()', function () {
    it('appends the user message and the AI response to the messages array', function () {
        mockStore();
        mockBrainService('All systems nominal.', 'conv-abc');

        Livewire::test(BrainChat::class)
            ->set('input', 'How are things?')
            ->call('send')
            ->assertSet('messages', [
                ['role' => 'user',      'content' => 'How are things?'],
                ['role' => 'assistant', 'content' => 'All systems nominal.', 'artifacts' => []],
            ])
            ->assertSet('input', '')
            ->assertSet('conversationId', 'conv-abc')
            ->assertSet('loading', false);
    });

    it('dispatches the message-sent event after a successful send', function () {
        mockStore();
        mockBrainService();

        Livewire::test(BrainChat::class)
            ->set('input', 'Hello')
            ->call('send')
            ->assertDispatched('message-sent');
    });

    it('passes the existing conversation id to the brain service on follow-up messages', function () {
        mockStore();

        $service = Mockery::mock(BrainService::class);
        $service->shouldReceive('promptAsUser')
            ->with(Mockery::any(), 'Follow-up question', 'existing-conv')
            ->once()
            ->andReturn([
                'conversation_id' => 'existing-conv',
                'content'         => 'Continued response.',
                'artifacts'       => [],
            ]);
        app()->instance(BrainService::class, $service);

        Livewire::test(BrainChat::class)
            ->set('conversationId', 'existing-conv')
            ->set('input', 'Follow-up question')
            ->call('send')
            ->assertSet('conversationId', 'existing-conv');
    });
});

// ---------------------------------------------------------------------------
// Input validation
// ---------------------------------------------------------------------------

describe('input validation', function () {
    it('requires a non-empty prompt before sending', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->set('input', '')
            ->call('send')
            ->assertHasErrors(['input' => 'required']);
    });

    it('rejects a prompt longer than 5 000 characters', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->set('input', str_repeat('a', 5001))
            ->call('send')
            ->assertHasErrors(['input' => 'max']);
    });

    it('accepts a prompt at exactly 5 000 characters', function () {
        mockStore();
        mockBrainService();

        Livewire::test(BrainChat::class)
            ->set('input', str_repeat('a', 5000))
            ->call('send')
            ->assertHasNoErrors();
    });
});

// ---------------------------------------------------------------------------
// startNew()
// ---------------------------------------------------------------------------

describe('startNew()', function () {
    it('clears messages, input, and conversation id', function () {
        mockStore();

        Livewire::test(BrainChat::class)
            ->set('messages', [['role' => 'user', 'content' => 'Old message']])
            ->set('conversationId', 'conv-old')
            ->set('input', 'Some draft text')
            ->call('startNew')
            ->assertSet('messages', [])
            ->assertSet('conversationId', null)
            ->assertSet('input', '');
    });
});

// ---------------------------------------------------------------------------
// loadConversation()
// ---------------------------------------------------------------------------

describe('loadConversation()', function () {
    it('loads messages from the store and sets the conversation id', function () {
        $store = mockStore();

        // Plain objects with a `content` property (UserMessage-like)
        $userMsg          = new \Laravel\Ai\Messages\UserMessage('Saved question');
        $aiMsg            = new \Laravel\Ai\Messages\AssistantMessage('Saved answer');

        $store->shouldReceive('getLatestConversationMessages')
            ->with('conv-saved', 200)
            ->andReturn(Collection::make([$userMsg, $aiMsg]));

        Livewire::test(BrainChat::class)
            ->call('loadConversation', 'conv-saved')
            ->assertSet('conversationId', 'conv-saved')
            ->assertCount('messages', 2);
    });

    it('reads message text via the content property, not __toString', function () {
        $store = mockStore();

        $aiMsg = new \Laravel\Ai\Messages\AssistantMessage('Hello from AI');

        $store->shouldReceive('getLatestConversationMessages')
            ->andReturn(Collection::make([$aiMsg]));

        $component = Livewire::test(BrainChat::class)
            ->call('loadConversation', 'conv-content');

        expect($component->get('messages')[0]['content'])->toBe('Hello from AI');
    });

    it('does not update state when the conversation has no messages', function () {
        $store = mockStore();
        $store->shouldReceive('getLatestConversationMessages')
            ->with('conv-empty', 200)
            ->andReturn(Collection::make([]));

        Livewire::test(BrainChat::class)
            ->call('loadConversation', 'conv-empty')
            ->assertSet('conversationId', null)
            ->assertSet('messages', []);
    });

    it('maps AssistantMessage objects to the "assistant" role', function () {
        $store = mockStore();

        $aiMsg = new \Laravel\Ai\Messages\AssistantMessage('AI content');

        $store->shouldReceive('getLatestConversationMessages')
            ->andReturn(Collection::make([$aiMsg]));

        $component = Livewire::test(BrainChat::class)
            ->call('loadConversation', 'conv-ai');

        expect($component->get('messages')[0]['role'])->toBe('assistant');
    });

    it('maps non-AssistantMessage objects to the "user" role', function () {
        $store = mockStore();

        $userMsg = new \Laravel\Ai\Messages\UserMessage('User content');

        $store->shouldReceive('getLatestConversationMessages')
            ->andReturn(Collection::make([$userMsg]));

        $component = Livewire::test(BrainChat::class)
            ->call('loadConversation', 'conv-user');

        expect($component->get('messages')[0]['role'])->toBe('user');
    });

    it('returns an empty string for messages with null content', function () {
        $store = mockStore();

        // Simulate a message whose content is null (edge case guard)
        $msg          = new \Laravel\Ai\Messages\AssistantMessage('');
        $msg->content = null;

        $store->shouldReceive('getLatestConversationMessages')
            ->andReturn(Collection::make([$msg]));

        $component = Livewire::test(BrainChat::class)
            ->call('loadConversation', 'conv-null-content');

        expect($component->get('messages')[0]['content'])->toBe('');
    });
});
