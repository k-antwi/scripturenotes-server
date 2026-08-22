@props(['level' => 'h2'])

<{{ $level }} {{ $attributes->merge(['class' => 'font-serif font-semibold text-stone-900']) }}>
    {{ $slot }}
</{{ $level }}>
