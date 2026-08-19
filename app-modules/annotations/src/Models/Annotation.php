<?php

namespace Nucleus\Annotations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Annotation extends Model
{
    protected $fillable = [
        'user_id',
        'book',
        'chapter',
        'verse',
        'type',
        'data',
        'colour',
        'is_shared',
        'share_token',
        'deleted_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_shared' => 'boolean',
        'chapter' => 'integer',
        'verse' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function notebooks(): BelongsToMany
    {
        return $this->belongsToMany(Notebook::class, 'annotation_notebook')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeForChapter(Builder $query, string $book, int $chapter): Builder
    {
        return $query->where('book', strtoupper($book))->where('chapter', $chapter);
    }
}
