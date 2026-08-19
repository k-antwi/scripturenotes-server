<?php
    use function Laravel\Folio\{middleware, name};
    middleware('auth');
    name('kyc.verify');
?>

<x-layouts.kyc>
    @livewire('kyc.kyc-status')
</x-layouts.kyc>
