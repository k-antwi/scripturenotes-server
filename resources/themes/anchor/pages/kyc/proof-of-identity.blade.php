<?php

use function Laravel\Folio\{middleware, name};

middleware('auth');
name('kyc.proof-of-identity');
?>

<x-layouts.kyc>
    @livewire('kyc.kyc-document-upload', ['type' => 'photo_id'])
</x-layouts.kyc>