<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth', 'onboarding']);
name('website.index');
?>

<x-layouts.app>
    <div class="py-8 px-4">
        <livewire:themeengine.website-chat />
    </div>
</x-layouts.app>
