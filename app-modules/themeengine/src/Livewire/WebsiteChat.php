<?php

namespace Nucleus\Themeengine\Livewire;

use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Nucleus\Brain\Models\AiModel;
use Nucleus\Brain\Services\BrainService;

class WebsiteChat extends Component
{
    use WithFileUploads;

    private const FRAMEWORK_LABELS = [
        'vue3'          => 'Vue 3 (SFC components)',
        'react'         => 'React (JSX components)',
        'livewire-volt' => 'Livewire Volt (Laravel Blade + Alpine.js)',
        'plain-html'    => 'Plain HTML (vanilla HTML/CSS/JS)',
    ];

    // 1 = framework, 2 = context, 0 = chat
    public int $wizardStep = 1;

    public string $framework = '';
    public string $customFramework = '';

    public string $websiteType = '';
    public string $businessType = '';
    public string $mainObjective = '';
    public string $inspirationUrl = '';
    public $logo = null;
    public bool $logoUploaded = false;

    public string $input = '';
    public array $messages = [];
    public ?string $conversationId = null;
    public bool $loading = false;
    public ?string $activeModelLabel = null;

    public function mount(): void
    {
        $this->activeModelLabel = AiModel::getActive()?->label;
    }

    public function selectFramework(string $key): void
    {
        $this->framework = $key;

        if ($key !== 'other') {
            $this->wizardStep = 2;
        }
    }

    public function confirmOtherFramework(): void
    {
        $custom = trim($this->customFramework);

        if ($custom !== '') {
            $this->framework  = $custom;
            $this->wizardStep = 2;
        }
    }

    public function backToStep1(): void
    {
        $this->wizardStep      = 1;
        $this->framework       = '';
        $this->customFramework = '';
    }

    public function updatedLogo(): void
    {
        $this->logoUploaded = $this->logo !== null;
    }

    public function submitWizard(): void
    {
        $agentPrompt    = $this->buildPrompt();
        $displayMessage = $this->buildDisplaySummary();

        $this->wizardStep = 0;

        $this->messages[] = [
            'role'    => 'user',
            'content' => $displayMessage,
        ];

        $this->loading = true;

        try {
            $this->messages[] = $this->askBrain($agentPrompt);
        } finally {
            $this->loading = false;
            $this->dispatch('message-sent');
        }
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
        $this->messages        = [];
        $this->input           = '';
        $this->loading         = false;
        $this->conversationId  = null;
        $this->wizardStep      = 1;
        $this->framework       = '';
        $this->customFramework = '';
        $this->websiteType     = '';
        $this->businessType    = '';
        $this->mainObjective   = '';
        $this->inspirationUrl  = '';
        $this->logo            = null;
        $this->logoUploaded    = false;
    }

    public function render()
    {
        return view('themeengine::livewire.website-chat');
    }

    private function frameworkLabel(): string
    {
        return self::FRAMEWORK_LABELS[$this->framework] ?? $this->framework;
    }

    private function buildDisplaySummary(): string
    {
        $parts   = [];
        $parts[] = 'Framework: ' . $this->frameworkLabel();

        if ($this->websiteType) {
            $parts[] = "Site: {$this->websiteType}";
        }
        if ($this->businessType) {
            $parts[] = "Business: {$this->businessType}";
        }
        if ($this->mainObjective) {
            $parts[] = "Goal: {$this->mainObjective}";
        }
        if ($this->logoUploaded) {
            $parts[] = 'Logo: uploaded';
        }
        if ($this->inspirationUrl) {
            $parts[] = "Inspiration: {$this->inspirationUrl}";
        }

        return implode(' · ', $parts);
    }

    private function buildPrompt(): string
    {
        $label = $this->frameworkLabel();
        $parts = [];

        $parts[] = "Generate a complete website theme using {$label}.";

        $context = [];
        if ($this->websiteType) {
            $context[] = "Website type: {$this->websiteType}";
        }
        if ($this->businessType) {
            $context[] = "Business: {$this->businessType}";
        }
        if ($this->mainObjective) {
            $context[] = "Primary objective: {$this->mainObjective}";
        }
        if ($context) {
            $parts[] = implode('. ', $context) . '.';
        }

        if ($this->logoUploaded) {
            $parts[] = 'The user has uploaded a logo — extract the primary, secondary, and accent brand colors from it and use them as the color palette. Mention the colors you extracted before generating.';
        } else {
            $parts[] = 'No logo was provided — choose a color palette appropriate to the business type and objective, and briefly state your color choices and reasoning before generating.';
        }

        if ($this->inspirationUrl) {
            $parts[] = "Take design inspiration from: {$this->inspirationUrl} — reference this site's visual style, layout, and aesthetic.";
        }

        return implode(' ', $parts);
    }

    private function askBrain(string $description): array
    {
        $user = auth()->user();

        if (! $user) {
            return $this->errorMessage('You need to be signed in to design a website.');
        }

        /** @var BrainService $brain */
        $brain = app(BrainService::class);

        try {
            $result = $brain->promptAs(
                $user,
                config('themeengine.website_agent_role', 'website'),
                $description,
                $this->conversationId,
            );
        } catch (InvalidArgumentException $e) {
            report($e);

            return $this->errorMessage(
                'The website designer has no AI agent configured. Register one under '
                . 'config/brain.php → agents to enable website design.'
            );
        }

        if ($result['conversation_id'] !== '') {
            $this->conversationId = $result['conversation_id'];
        }

        return [
            'role'    => 'assistant',
            'content' => $result['content'],
        ];
    }

    private function errorMessage(string $content): array
    {
        return [
            'role'    => 'assistant',
            'content' => $content,
        ];
    }
}
