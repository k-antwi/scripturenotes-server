<?php

namespace Nucleus\Annotations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudySession extends Model
{
    protected $fillable = ['user_id', 'started_at', 'last_active_at', 'passage_ref'];

    protected $casts = [
        'started_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }
}
