@props([
    'href' => '',
    'icon' => 'phosphor-house-duotone',
    'active' => false,
    'hideUntilGroupHover' => true,
    'target' => '_self',
    'ajax' => true
])

@php
    $isActive = filter_var($active, FILTER_VALIDATE_BOOLEAN);
@endphp

<a {{ $attributes }} href="{{ $href }}"
    @if((($href ?? false) && $target == '_self') && $ajax) wire:navigate @else @if($ajax) target="_blank" @endif @endif
    class="transition-all px-3 py-2 flex rounded-xl w-full h-auto text-sm justify-start items-center space-x-2.5 overflow-hidden
        @if($isActive)
            font-semibold shadow-sm
        @else
            text-stone-600 hover:text-stone-800 hover:bg-white/60
        @endif"
    style="@if($isActive) background: white; color: #4B6741; box-shadow: 0 1px 4px rgba(75,103,65,0.12); @endif">
    <x-dynamic-component :component="$icon" class="flex-shrink-0 w-4.5 h-4.5" />
    <span class="flex-shrink-0 text-[13px]">{{ $slot }}</span>
</a>
