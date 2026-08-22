@props([
    'title' => '',
    'description' => '',
    'border' => true
])

<div {{ $attributes->class(['pb-5', 'border-b border-stone-200/60' => $border]) }}>
    <h1 class="font-serif text-2xl font-semibold text-stone-900">{{ $title }}</h1>
    @if($description)
    <p class="mt-1 text-sm text-stone-500 leading-relaxed">{{ $description }}</p>
    @endif
</div>
