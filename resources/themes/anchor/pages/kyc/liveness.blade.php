<?php

use function Laravel\Folio\{middleware, name};

middleware('auth');
name('kyc.liveness');
?>

<x-layouts.kyc>
    @livewire('kyc.kyc-liveness-check')
</x-layouts.kyc>
