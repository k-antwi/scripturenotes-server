<?php
    use function Laravel\Folio\{middleware, name};
    middleware(['auth']);
    name('onboarding.index');
?>

<x-layouts.onboarding>
    @livewire('onboarding.onboarding-wizard')
</x-layouts.onboarding>
