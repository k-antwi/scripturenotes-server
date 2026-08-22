@props([
    'href' => '#',
    'target' => '_self',
    'title' => '',
    'description' => '',
    'link_text' => 'Learn More',
    'image' => null
])

<a href="{{ $href }}" target="{{ $target }}"
   class="group flex flex-col flex-1 p-6 rounded-2xl bg-white border border-stone-200/60 hover:border-stone-300 hover:shadow-md transition-all duration-200">
    @if($image)
    <img src="{{ $image }}" alt="{{ $title }}" class="w-12 h-12 mb-4 rounded-xl object-cover">
    @endif
    <h3 class="font-serif text-base font-semibold text-stone-900 mb-2">{{ $title }}</h3>
    <p class="text-sm text-stone-500 leading-relaxed flex-1">{{ $description }}</p>
    <div class="flex items-center gap-1.5 mt-4 text-sm font-medium transition-colors" style="color: #4B6741;">
        <span>{{ $link_text }}</span>
        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
        </svg>
    </div>
</a>
