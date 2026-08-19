<?php

namespace Nucleus\Themeengine\Livewire;

use InvalidArgumentException;
use Livewire\Component;
use Nucleus\Brain\Models\AiModel;
use Nucleus\Brain\Services\BrainService;
use Nucleus\Themeengine\Support\ThemeSpec;

class ThemeChat extends Component
{
    public string $input = '';

    public array $messages = [];

    public ?string $conversationId = null;

    public bool $loading = false;

    public ?string $activeModelLabel = null;

    public function mount(): void
    {
        $this->activeModelLabel = AiModel::getActive()?->label;
    }

    public function generate(): void
    {
        $this->validate([
            'input' => 'required|string|max:' . config('themeengine.max_prompt_length', 2000),
        ]);

        $description = trim($this->input);

        $this->messages[] = [
            'role'    => 'user',
            'content' => $description,
        ];

        $this->input   = '';
        $this->loading = true;

        try {
            $this->messages[] = $this->askBrain($description);
        } finally {
            $this->loading = false;
            $this->dispatch('message-sent');
        }
    }

    public function startNew(): void
    {
        $this->messages       = [];
        $this->input          = '';
        $this->loading        = false;
        $this->conversationId = null;
    }

    public function render()
    {
        return view('themeengine::livewire.theme-chat');
    }

    /**
     * Hand the description to the Brain module and shape the reply into a
     * chat message plus, when the agent returned one, a theme spec.
     */
    private function askBrain(string $description): array
    {
        $user = auth()->user();

        if (! $user) {
            return $this->errorMessage('You need to be signed in to generate a theme.');
        }

        /** @var BrainService $brain */
        $brain = app(BrainService::class);

        try {
            $result = $brain->promptAs(
                $user,
                config('themeengine.agent_role', 'theme'),
                $description,
                $this->conversationId,
            );
        } catch (InvalidArgumentException $e) {
            // No agent is mapped for the configured role — a setup problem,
            // not a model failure, so say so rather than surfacing a stack trace.
            report($e);

            return $this->errorMessage(
                'The theme engine has no AI agent configured. Register one under '
                . 'config/brain.php → agents to enable theme generation.'
            );
        }

        // BrainService swallows model errors and returns them as content, so a
        // blank conversation id means nothing was persisted — don't store it.
        if ($result['conversation_id'] !== '') {
            $this->conversationId = $result['conversation_id'];
        }

        $spec = ThemeSpec::fromArtifacts($result['artifacts']);

        return [
            'role'      => 'assistant',
            'content'   => $spec === [] ? $result['content'] : ThemeSpec::stripCodeBlocks($result['content']),
            'artifacts' => $result['artifacts'],
            'spec'      => $spec,
        ];
    }

    private function errorMessage(string $content): array
    {
        return [
            'role'      => 'assistant',
            'content'   => $content,
            'artifacts' => [],
            'spec'      => [],
        ];
    }
}
