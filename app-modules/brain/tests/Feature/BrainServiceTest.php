<?php

use Nucleus\Brain\Ai\Agents\AdminBrainAgent;
use Nucleus\Brain\Models\AiModel;
use Nucleus\Brain\Services\BrainService;

it('returns no-model-configured message when no active model exists', function () {
    $service = new BrainService;

    $result = $service->promptAsAdmin('Hello');

    expect($result['content'])->toContain('No active AI model configured');
});

it('prompts admin agent and returns conversation id', function () {
    AiModel::factory()->create([
        'provider'  => 'anthropic',
        'model_id'  => 'claude-sonnet-4-6',
        'api_key'   => 'test-key',
        'is_active' => true,
    ]);

    AdminBrainAgent::fake(['You have 3 active users this week.']);

    $service = new BrainService;
    $result  = $service->promptAsAdmin('Summarise platform activity');

    AdminBrainAgent::assertPrompted('Summarise platform activity');

    expect($result['content'])->toBe('You have 3 active users this week.')
        ->and($result['artifacts'])->toBeArray();
});

it('throws when promptAs called with unregistered role', function () {
    AiModel::factory()->create(['is_active' => true]);

    $user = \App\Models\User::factory()->create();
    $service = new BrainService;

    expect(fn () => $service->promptAs($user, 'unknown', 'hi'))
        ->toThrow(InvalidArgumentException::class);
});

it('extracts code artifacts from ai response', function () {
    $service = new BrainService;

    $reflection = new ReflectionClass($service);
    $method     = $reflection->getMethod('extractArtifacts');
    $method->setAccessible(true);

    $content = "Here is some SQL:\n```sql\nSELECT 1;\n```\nAnd some PHP:\n```php\necho 'hello';\n```";

    $artifacts = $method->invoke($service, $content);

    expect($artifacts)->toHaveCount(2)
        ->and($artifacts[0]['type'])->toBe('sql')
        ->and($artifacts[1]['type'])->toBe('php');
});

it('ai model can be activated and deactivates others', function () {
    $modelA = AiModel::factory()->create(['is_active' => true]);
    $modelB = AiModel::factory()->create(['is_active' => false]);

    $modelB->activate();

    expect($modelB->fresh()->is_active)->toBeTrue()
        ->and($modelA->fresh()->is_active)->toBeFalse();
});

it('returns correct provider options', function () {
    $options = AiModel::providerOptions();

    expect($options)->toHaveKey('anthropic')
        ->and($options)->toHaveKey('openai')
        ->and($options)->toHaveKey('gemini');
});
