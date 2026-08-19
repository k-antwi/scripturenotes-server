<?php

use function Laravel\Folio\{middleware, name};

middleware('auth');
name('kyc.proof-of-address');
?>

<x-layouts.kyc>
    @livewire('kyc.kyc-document-upload', ['type' => 'proof_of_address'])
</x-layouts.kyc>