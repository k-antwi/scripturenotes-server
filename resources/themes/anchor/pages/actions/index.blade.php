<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth', 'onboarding']);
name('actions.index');
?>

<x-layouts.app>
    <div class="py-8 px-4">
        <livewire:themeengine.theme-chat />
    </div>
</x-layouts.app>
