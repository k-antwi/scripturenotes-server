<x-layouts.marketing :seo="['title' => $page['title'], 'description' => $page['body'] ?? '']">

    {{-- Page hero --}}
    <section class="pt-20 pb-12 border-b border-stone-200/60" style="background: linear-gradient(180deg, #faf7f2 0%, #f5f0e8 100%);">
        <div class="max-w-3xl mx-auto px-6 lg:px-8 text-center">
            @if(isset($page['image']) && $page['image'])
            <div class="w-16 h-16 mx-auto mb-6 rounded-2xl overflow-hidden shadow-md">
                <img src="{{ $page['image'] }}" alt="{{ $page['title'] }}" class="w-full h-full object-cover">
            </div>
            @endif
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-stone-800 leading-tight">
                {{ $page['title'] }}
            </h1>
            @if(isset($page['subtitle']) && $page['subtitle'])
            <p class="mt-4 text-lg text-stone-500 leading-relaxed">{{ $page['subtitle'] }}</p>
            @endif
        </div>
    </section>

    {{-- Page content --}}
    <article class="max-w-3xl mx-auto px-6 lg:px-8 py-14">
        <div class="prose prose-stone prose-lg max-w-none
                    prose-headings:font-serif prose-headings:text-stone-800
                    prose-a:text-green-700 hover:prose-a:text-green-900
                    prose-blockquote:border-l-amber-400 prose-blockquote:text-stone-600 prose-blockquote:not-italic
                    prose-strong:text-stone-800 prose-code:text-stone-700
                    prose-img:rounded-2xl prose-img:shadow-md">
            {!! $page['body'] !!}
        </div>
    </article>

    {{-- Bottom verse decoration --}}
    <div class="py-12 border-t border-stone-200/60 text-center" style="background: #faf7f2;">
        <blockquote class="text-sm italic text-stone-400">
            "Thy word is a lamp unto my feet, and a light unto my path." — Psalm 119:105
        </blockquote>
    </div>

</x-layouts.marketing>
