<?php

use function Laravel\Folio\{middleware, name};

middleware('auth');
name('kyc.name-change');
?>

<x-layouts.kyc>
    @livewire('kyc.kyc-document-upload', ['type' => 'name_change'])
</x-layouts.kyc>