@props(['active' => false, 'href' => '#'])

@php
$isActive = filter_var($active, FILTER_VALIDATE_BOOLEAN);
@endphp

<a href="{{ $href }}" wire:navigate
   class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm font-medium transition-all
       @if($isActive) text-white @else text-stone-600 hover:text-stone-800 hover:bg-stone-100/70 @endif"
   style="@if($isActive) background: linear-gradient(135deg, #4B6741, #3A5432); @endif">
    {{ $slot }}
</a>
