<?php

namespace Nucleus\Annotations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notebook extends Model
{
    protected $fillable = ['user_id', 'title', 'description', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function annotations(): BelongsToMany
    {
        return $this->belongsToMany(Annotation::class, 'annotation_notebook')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'note_notebook')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** Returns (or creates) the Untitled Notebook for a user. */
    public static function defaultForUser(int $userId): static
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'is_default' => true],
            ['title' => 'Untitled Notebook', 'description' => null]
        );
    }
}
