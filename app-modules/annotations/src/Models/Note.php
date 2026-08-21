<?php

namespace Nucleus\Annotations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'book',
        'chapter',
        'verse',
        'char_start',
        'char_end',
        'title',
        'body',
    ];

    protected $casts = [
        'chapter'    => 'integer',
        'verse'      => 'integer',
        'char_start' => 'integer',
        'char_end'   => 'integer',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function notebooks(): BelongsToMany
    {
        return $this->belongsToMany(Notebook::class, 'note_notebook')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    /** All notes for a specific passage (excludes standalone notes). */
    public function scopeForPassage(Builder $query, string $book, int $chapter): Builder
    {
        return $query
            ->whereNotNull('book')
            ->whereNotNull('chapter')
            ->where('book', strtoupper($book))
            ->where('chapter', $chapter);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /** Returns 'verse', 'phrase', or 'standalone'. */
    public function getAnchorTypeAttribute(): string
    {
        if ($this->book === null) {
            return 'standalone';
        }
        if ($this->char_start !== null && $this->char_end !== null) {
            return 'phrase';
        }
        return 'verse';
    }
}
