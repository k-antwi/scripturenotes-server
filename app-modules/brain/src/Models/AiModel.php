<?php

namespace Nucleus\Brain\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Enums\Lab;
use Nucleus\Brain\Database\Factories\AiModelFactory;

/**
 * @property int         $id
 * @property string      $label
 * @property string      $provider
 * @property string      $model_id
 * @property string      $api_key
 * @property bool        $is_active
 * @property array|null  $config
 */
class AiModel extends Model
{
    use HasFactory;

    protected $table = 'ai_models';

    protected static function newFactory(): AiModelFactory
    {
        return AiModelFactory::new();
    }

    protected $fillable = [
        'label',
        'provider',
        'model_id',
        'api_key',
        'is_active',
        'config',
    ];

    protected $casts = [
        'api_key'   => 'encrypted',
        'is_active' => 'boolean',
        'config'    => 'array',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public static function getActive(): ?self
    {
        return static::active()->latest('updated_at')->first();
    }

    public function activate(): void
    {
        DB::transaction(function () {
            static::query()->update(['is_active' => false]);
            $this->update(['is_active' => true]);
        });

        cache()->forget('brain.active_model');
    }

    /**
     * Build the provider options list for forms.
     *
     * Built-in providers come from the Laravel AI SDK's Lab enum. Any provider
     * key already stored in ai_models that isn't in the enum is appended so
     * existing records remain selectable after a downgrade or manual entry.
     */
    public static function providerOptions(): array
    {
        $labels = [
            Lab::Azure->value      => 'Azure OpenAI',
            Lab::Bedrock->value    => 'AWS Bedrock',
            Lab::ElevenLabs->value => 'ElevenLabs',
            Lab::Gemini->value     => 'Google Gemini',
            Lab::OpenAI->value     => 'OpenAI',
            Lab::OpenRouter->value => 'OpenRouter',
            Lab::VoyageAI->value   => 'Voyage AI',
            Lab::xAI->value        => 'xAI (Grok)',
        ];

        $builtin = collect(Lab::cases())
            ->mapWithKeys(fn (Lab $lab) => [
                $lab->value => $labels[$lab->value] ?? $lab->name,
            ])
            ->all();

        $labValues = array_keys($builtin);

        $custom = static::query()
            ->whereNotIn('provider', $labValues)
            ->distinct()
            ->pluck('provider')
            ->mapWithKeys(fn (string $key) => [$key => ucwords(str_replace(['-', '_'], ' ', $key))])
            ->all();

        return array_merge($builtin, $custom);
    }
}
