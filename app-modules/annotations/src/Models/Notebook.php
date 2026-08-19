<?php

namespace Nucleus\Annotations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notebook extends Model
{
    protected $fillable = ['user_id', 'title', 'description'];

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
}
