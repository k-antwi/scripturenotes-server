@props([
    'text' => 'Go Back',
    'href' => '/'
])

<div {{ $attributes }}>
    <a href="{{ $href }}" wire:navigate class="inline-flex items-center gap-2 text-sm text-stone-500 hover:text-stone-800 transition-colors group">
        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span>{{ $text }}</span>
    </a>
</div>
