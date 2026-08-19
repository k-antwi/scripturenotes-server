<?php

use App\Models\User;
use Livewire\Livewire;
use Nucleus\Brain\Models\AiModel;
use Nucleus\Brain\Services\BrainService;
use Nucleus\Themeengine\Ai\Agents\ThemeAgent;
use Nucleus\Themeengine\Livewire\ThemeChat;

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function themeSpecJson(): string
{
    return json_encode([
        'name'        => 'Midnight Harbour',
        'description' => 'A deep navy dashboard theme.',
        'mode'        => 'dark',
        'colors'      => ['primary' => '#3B82F6', 'background' => '#0F172A', 'text' => '#F8FAFC'],
        'typography'  => ['heading_font' => 'Inter', 'body_font' => 'Inter'],
        'radius'      => ['md' => '8px'],
    ]);
}

/** Mock BrainService with a canned promptAs() reply. */
function mockThemeBrain(string $content = 'Here is your theme.', array $artifacts = [], string $convId = 'conv-theme'): BrainService
{
    $service = Mockery::mock(BrainService::class);
    $service->shouldReceive('promptAs')->byDefault()->andReturn([
        'conversation_id' => $convId,
        'content'         => $content,
        'artifacts'       => $artifacts,
    ]);

    app()->instance(BrainService::class, $service);

    return $service;
}

function specArtifacts(): array
{
    return [['type' => 'json', 'content' => themeSpecJson()]];
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// ---------------------------------------------------------------------------
// Agent registration
// ---------------------------------------------------------------------------

describe('agent registration', function () {
    it('registers the theme agent against the brain role map', function () {
        expect(config('brain.agents.' . config('themeengine.agent_role')))->toBe(ThemeAgent::class);
    });

    it('defaults the agent role to "theme"', function () {
        expect(config('themeengine.agent_role'))->toBe('theme');
    });
});

// ---------------------------------------------------------------------------
// Rendering
// ---------------------------------------------------------------------------

describe('rendering', function () {
    it('renders successfully', function () {
        Livewire::test(ThemeChat::class)
            ->assertOk()
            ->assertSee('Theme Engine');
    });

    it('warns when no AI model is active', function () {
        Livewire::test(ThemeChat::class)
            ->assertSee('Theme generation is not currently available');
    });

    it('shows the active model label instead of the warning', function () {
        AiModel::factory()->create(['label' => 'Claude Pro', 'is_active' => true]);

        Livewire::test(ThemeChat::class)
            ->assertSee('Claude Pro')
            ->assertDontSee('Theme generation is not currently available');
    });

    it('no longer advertises the unwired placeholder copy', function () {
        mockThemeBrain();

        Livewire::test(ThemeChat::class)
            ->set('input', 'A calm pastel theme')
            ->call('generate')
            ->assertDontSee('Theme generation is ready to be wired up');
    });
});

// ---------------------------------------------------------------------------
// generate()
// ---------------------------------------------------------------------------

describe('generate()', function () {
    it('sends the description to the brain under the configured role', function () {
        $service = Mockery::mock(BrainService::class);
        $service->shouldReceive('promptAs')
            ->with(Mockery::type(User::class), 'theme', 'A calm pastel theme', null)
            ->once()
            ->andReturn(['conversation_id' => 'conv-1', 'content' => 'Done.', 'artifacts' => []]);
        app()->instance(BrainService::class, $service);

        Livewire::test(ThemeChat::class)
            ->set('input', 'A calm pastel theme')
            ->call('generate')
            ->assertSet('conversationId', 'conv-1');
    });

    it('appends the user prompt and the assistant reply', function () {
        mockThemeBrain('Here is your theme.');

        $component = Livewire::test(ThemeChat::class)
            ->set('input', 'A calm pastel theme')
            ->call('generate')
            ->assertSet('input', '')
            ->assertSet('loading', false);

        $messages = $component->get('messages');

        expect($messages)->toHaveCount(2)
            ->and($messages[0])->toBe(['role' => 'user', 'content' => 'A calm pastel theme'])
            ->and($messages[1]['role'])->toBe('assistant')
            ->and($messages[1]['content'])->toBe('Here is your theme.');
    });

    it('trims whitespace from the description before sending it', function () {
        $service = Mockery::mock(BrainService::class);
        $service->shouldReceive('promptAs')
            ->with(Mockery::any(), 'theme', 'Bold coral theme', null)
            ->once()
            ->andReturn(['conversation_id' => '', 'content' => 'Done.', 'artifacts' => []]);
        app()->instance(BrainService::class, $service);

        Livewire::test(ThemeChat::class)
            ->set('input', "  Bold coral theme\n")
            ->call('generate');
    });

    it('passes the conversation id on follow-up prompts so refinements build on the theme', function () {
        $service = Mockery::mock(BrainService::class);
        $service->shouldReceive('promptAs')
            ->with(Mockery::any(), 'theme', 'Make it darker', 'conv-existing')
            ->once()
            ->andReturn(['conversation_id' => 'conv-existing', 'content' => 'Darker.', 'artifacts' => []]);
        app()->instance(BrainService::class, $service);

        Livewire::test(ThemeChat::class)
            ->set('conversationId', 'conv-existing')
            ->set('input', 'Make it darker')
            ->call('generate')
            ->assertSet('conversationId', 'conv-existing');
    });

    it('keeps the previous conversation id when the brain returns a blank one', function () {
        mockThemeBrain(convId: '');

        Livewire::test(ThemeChat::class)
            ->set('conversationId', 'conv-keep')
            ->set('input', 'Another theme')
            ->call('generate')
            ->assertSet('conversationId', 'conv-keep');
    });

    it('dispatches message-sent after generating', function () {
        mockThemeBrain();

        Livewire::test(ThemeChat::class)
            ->set('input', 'A calm pastel theme')
            ->call('generate')
            ->assertDispatched('message-sent');
    });

    it('does not swallow unexpected brain failures', function () {
        $service = Mockery::mock(BrainService::class);
        $service->shouldReceive('promptAs')->andThrow(new RuntimeException('boom'));
        app()->instance(BrainService::class, $service);

        expect(fn () => Livewire::test(ThemeChat::class)
            ->set('input', 'A calm pastel theme')
            ->call('generate'))
            ->toThrow(RuntimeException::class);
    });

    it('explains the problem instead of erroring when no agent is registered', function () {
        config(['themeengine.agent_role' => 'unmapped-role']);

        $service = Mockery::mock(BrainService::class);
        $service->shouldReceive('promptAs')->andThrow(new InvalidArgumentException('No agent registered for role [unmapped-role].'));
        app()->instance(BrainService::class, $service);

        Livewire::test(ThemeChat::class)
            ->set('input', 'A calm pastel theme')
            ->call('generate')
            ->assertSee('no AI agent configured');
    });
});

// ---------------------------------------------------------------------------
// Theme spec preview
// ---------------------------------------------------------------------------

describe('theme spec preview', function () {
    it('parses the returned spec onto the assistant message', function () {
        mockThemeBrain('Here is your theme.', specArtifacts());

        $component = Livewire::test(ThemeChat::class)
            ->set('input', 'A deep navy dashboard')
            ->call('generate');

        $spec = $component->get('messages')[1]['spec'];

        expect($spec['name'])->toBe('Midnight Harbour')
            ->and($spec['mode'])->toBe('dark')
            ->and($spec['colors']['primary'])->toBe('#3b82f6');
    });

    it('renders the palette swatches and hex values', function () {
        mockThemeBrain('Here is your theme.', specArtifacts());

        Livewire::test(ThemeChat::class)
            ->set('input', 'A deep navy dashboard')
            ->call('generate')
            ->assertSee('Midnight Harbour')
            ->assertSeeHtml('background-color: #3b82f6')
            ->assertSee('#0f172a');
    });

    it('strips the json block from the prose when a spec was parsed', function () {
        mockThemeBrain("Here is your theme.\n\n```json\n" . themeSpecJson() . "\n```", specArtifacts());

        $component = Livewire::test(ThemeChat::class)
            ->set('input', 'A deep navy dashboard')
            ->call('generate');

        expect($component->get('messages')[1]['content'])->toBe('Here is your theme.');
    });

    it('keeps the raw content when no spec could be parsed', function () {
        $content = "I need more detail.\n\n```text\nnope\n```";
        mockThemeBrain($content, [['type' => 'text', 'content' => 'nope']]);

        $component = Livewire::test(ThemeChat::class)
            ->set('input', 'Something vague')
            ->call('generate');

        expect($component->get('messages')[1]['content'])->toBe($content)
            ->and($component->get('messages')[1]['spec'])->toBe([]);
    });
});

// ---------------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------------

describe('input validation', function () {
    it('requires a description', function () {
        Livewire::test(ThemeChat::class)
            ->set('input', '')
            ->call('generate')
            ->assertHasErrors(['input' => 'required']);
    });

    it('rejects a description longer than the configured maximum', function () {
        Livewire::test(ThemeChat::class)
            ->set('input', str_repeat('a', config('themeengine.max_prompt_length') + 1))
            ->call('generate')
            ->assertHasErrors(['input' => 'max']);
    });

    it('accepts a description at exactly the configured maximum', function () {
        mockThemeBrain();

        Livewire::test(ThemeChat::class)
            ->set('input', str_repeat('a', config('themeengine.max_prompt_length')))
            ->call('generate')
            ->assertHasNoErrors();
    });

    it('does not call the brain when validation fails', function () {
        $service = Mockery::mock(BrainService::class);
        $service->shouldReceive('promptAs')->never();
        app()->instance(BrainService::class, $service);

        Livewire::test(ThemeChat::class)
            ->set('input', '')
            ->call('generate');
    });
});

// ---------------------------------------------------------------------------
// startNew()
// ---------------------------------------------------------------------------

describe('startNew()', function () {
    it('clears the transcript and the conversation id', function () {
        Livewire::test(ThemeChat::class)
            ->set('messages', [['role' => 'user', 'content' => 'Old theme']])
            ->set('conversationId', 'conv-old')
            ->set('input', 'draft')
            ->call('startNew')
            ->assertSet('messages', [])
            ->assertSet('conversationId', null)
            ->assertSet('input', '')
            ->assertSet('loading', false);
    });
});
