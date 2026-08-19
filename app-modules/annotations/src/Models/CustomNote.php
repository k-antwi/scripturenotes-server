<?php

namespace Nucleus\Annotations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomNote extends Model
{
    protected $fillable = ['user_id', 'book', 'chapter', 'verse', 'title', 'body'];

    protected $casts = [
        'chapter' => 'integer',
        'verse' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
