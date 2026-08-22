@props([
    'color' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

$sizeClasses = match($size) {
    'xs'  => 'px-3 py-1.5 text-xs',
    'sm'  => 'px-4 py-2 text-sm',
    'lg'  => 'px-7 py-3.5 text-base',
    'xl'  => 'px-8 py-4 text-lg',
    default => 'px-5 py-2.5 text-sm',
};

$colorClasses = match($color) {
    'secondary' => 'bg-white text-stone-700 border border-stone-200 hover:bg-stone-50 hover:border-stone-300 shadow-sm focus:ring-stone-300',
    'danger'    => 'bg-red-600 text-white hover:bg-red-700 shadow-sm focus:ring-red-500',
    'ghost'     => 'text-stone-600 hover:text-stone-900 hover:bg-stone-100 focus:ring-stone-300',
    default     => 'text-white shadow-sm hover:shadow-md focus:ring-green-500',
};

$primaryStyle = $color === 'primary' ? 'background: linear-gradient(135deg, #4B6741, #3A5432);' : '';
@endphp

@if($href)
<a href="{{ $href }}" style="{{ $primaryStyle }}" {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $colorClasses"]) }}>
    {{ $slot }}
</a>
@else
<button style="{{ $primaryStyle }}" {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $colorClasses", 'type' => 'button']) }}>
    {{ $slot }}
</button>
@endif
