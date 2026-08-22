@props([
    'level' => 'h2',
    'title' => 'Heading',
    'description' => '',
    'align' => 'center'
])

<div {{ $attributes->class([
    'relative w-full',
    'text-left' => $align == 'left',
    'text-right' => $align == 'right',
    'text-center' => $align != 'left' && $align != 'right'
]) }}>
    <{{ $level }} class="font-serif text-3xl sm:text-4xl lg:text-5xl font-semibold tracking-tight text-stone-900 leading-tight">{!! $title !!}</{{ $level }}>
    @if($description)
    <p class="mt-5 text-base sm:text-lg leading-relaxed text-stone-500 @if($align == 'left'){{ '' }}@elseif($align == 'right'){{ 'ml-auto' }}@else{{ 'mx-auto max-w-2xl' }}@endif">{!! $description !!}</p>
    @endif
</div>
