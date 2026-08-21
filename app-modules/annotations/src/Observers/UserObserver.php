<?php

namespace Nucleus\Annotations\Observers;

use Nucleus\Annotations\Models\Notebook;

class UserObserver
{
    /** Auto-create the Untitled Notebook when a new user registers. */
    public function created(mixed $user): void
    {
        Notebook::defaultForUser($user->id);
    }
}
