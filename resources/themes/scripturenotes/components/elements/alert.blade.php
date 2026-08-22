@props(['type' => 'info'])

@php
$styles = match($type) {
    'success' => ['background: rgba(75,103,65,0.06); border-color: rgba(75,103,65,0.2);', '#4B6741'],
    'warning' => ['background: rgba(201,146,47,0.06); border-color: rgba(201,146,47,0.2);', '#C9922F'],
    'error'   => ['background: rgba(220,38,38,0.06); border-color: rgba(220,38,38,0.2);', '#dc2626'],
    default   => ['background: rgba(75,103,65,0.04); border-color: rgba(75,103,65,0.15);', '#4B6741'],
};
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 px-4 py-3.5 rounded-xl border text-sm text-stone-700']) }}
     style="{{ $styles[0] }}">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: {{ $styles[1] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
    </svg>
    <div>{{ $slot }}</div>
</div>
