@props(['height' => '4'])

<div {{ $attributes->merge(['class' => "animate-pulse bg-stone-200 rounded-xl h-{$height} w-full"]) }}></div>
