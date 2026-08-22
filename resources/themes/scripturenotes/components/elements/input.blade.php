@props([
    'disabled' => false,
    'type' => 'text',
])

<input
    type="{{ $type }}"
    @if($disabled) disabled @endif
    {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 text-sm text-stone-800 bg-white border border-stone-200 rounded-xl shadow-sm placeholder-stone-400 focus:outline-none focus:ring-2 focus:border-transparent transition-all disabled:bg-stone-50 disabled:cursor-not-allowed']) }}
    style="focus:ring-color: #4B6741;"
>
