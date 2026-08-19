<?php

namespace Nucleus\Scripture\Models;

use Illuminate\Database\Eloquent\Model;

class Passage extends Model
{
    protected $fillable = [
        'book',
        'chapter',
        'translation',
        'content',
        'fetched_at',
    ];

    protected $casts = [
        'content' => 'array',
        'fetched_at' => 'datetime',
        'chapter' => 'integer',
    ];

    /**
     * Find a cached passage or return null.
     */
    public static function findCached(string $book, int $chapter, string $translation): ?self
    {
        return static::where('book', strtoupper($book))
            ->where('chapter', $chapter)
            ->where('translation', strtoupper($translation))
            ->first();
    }

    /**
     * Store or update a passage from the Bible API response.
     */
    public static function upsertFromApi(string $book, int $chapter, string $translation, array $content): self
    {
        return static::updateOrCreate(
            [
                'book' => strtoupper($book),
                'chapter' => $chapter,
                'translation' => strtoupper($translation),
            ],
            [
                'content' => $content,
                'fetched_at' => now(),
            ]
        );
    }
}
