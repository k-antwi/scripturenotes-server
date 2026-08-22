<?php
use function Laravel\Folio\name;

name('home');
?>
<x-layouts.marketing :seo="[
    'title'       => setting('site.title', 'ScriptureNotes') . ' — Deepen Your Scripture Journey',
    'description' => 'A sacred space for capturing your journey through Scripture. Take notes, track verses, and grow in faith.',
]">
    <x-marketing.sections.hero />
    <x-marketing.sections.features />
    <x-marketing.sections.testimonials />
    <x-marketing.sections.pricing />
</x-layouts.marketing>
