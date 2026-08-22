@props(['title' => 'Settings', 'description' => ''])

<div class="space-y-8">
    <div class="pb-5 border-b border-stone-200/60">
        <h2 class="font-serif text-xl font-semibold text-stone-900">{{ $title }}</h2>
        @if($description)
        <p class="mt-1 text-sm text-stone-500">{{ $description }}</p>
        @endif
    </div>

    <div class="flex flex-col md:flex-row gap-8">
        <nav class="md:w-48 flex-shrink-0">
            {{ $nav ?? '' }}
        </nav>
        <div class="flex-1 min-w-0">
            {{ $slot }}
        </div>
    </div>
</div>
