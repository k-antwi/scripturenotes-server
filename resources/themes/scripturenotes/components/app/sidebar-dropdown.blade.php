@props([
    'text' => 'Menu',
    'icon' => 'phosphor-stack',
    'id' => 'dropdown',
    'active' => false,
    'open' => '0'
])

@php
    $isActive = filter_var($active, FILTER_VALIDATE_BOOLEAN);
    $isOpen = filter_var($open, FILTER_VALIDATE_BOOLEAN);
@endphp

<div x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }" class="w-full">
    <button @click="open = !open"
        class="transition-all px-3 py-2 flex rounded-xl w-full h-auto text-sm justify-between items-center space-x-2.5
            @if($isActive) font-semibold @else text-stone-600 hover:text-stone-800 hover:bg-white/60 @endif"
        style="@if($isActive) background: white; color: #4B6741; @endif">
        <div class="flex items-center space-x-2.5">
            <x-dynamic-component :component="$icon" class="flex-shrink-0 w-4.5 h-4.5" />
            <span class="text-[13px]">{{ $text }}</span>
        </div>
        <svg :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform duration-200 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="pl-4 pt-1 space-y-0.5">
        {{ $slot }}
    </div>
</div>
