<section class="py-24 lg:py-32 relative overflow-hidden" style="background: linear-gradient(135deg, #fdf8f0 0%, #f5f0e8 100%);">
    <div class="absolute inset-0 pointer-events-none">
        <svg class="absolute top-0 right-0 opacity-5 w-96" viewBox="0 0 200 200" fill="none">
            <circle cx="100" cy="100" r="80" stroke="#4B6741" stroke-width="2"/>
            <circle cx="100" cy="100" r="60" stroke="#4B6741" stroke-width="1"/>
            <circle cx="100" cy="100" r="40" stroke="#4B6741" stroke-width="1"/>
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative">
        <x-marketing.elements.heading
            title="Stories from the community"
            description="Thousands of believers use ScriptureNotes to capture what God is teaching them every day." />

        <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
            $testimonials = [
                [
                    'quote' => 'ScriptureNotes has transformed my quiet time. I used to lose all those insights I had during devotions — now they\'re safe, searchable, and growing into a real library of faith.',
                    'name' => 'Sarah M.',
                    'role' => 'Bible Study Leader',
                    'initials' => 'SM',
                    'color' => '#4B6741',
                ],
                [
                    'quote' => 'I\'ve tried journaling apps before, but nothing felt right for Scripture. This one just fits — the interface feels reverent, unhurried. It\'s become part of my morning routine.',
                    'name' => 'Pastor James K.',
                    'role' => 'Senior Pastor',
                    'initials' => 'JK',
                    'color' => '#C9922F',
                ],
                [
                    'quote' => 'Being able to tag notes by theme and search across three years of Bible study notes is incredible. It\'s like having a personal concordance built from my own spiritual journey.',
                    'name' => 'Rachel T.',
                    'role' => 'Seminary Student',
                    'initials' => 'RT',
                    'color' => '#6B5744',
                ],
            ];
            @endphp

            @foreach($testimonials as $testimonial)
            <div class="bg-white rounded-2xl p-7 border border-stone-200/60 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col">
                {{-- Stars --}}
                <div class="flex gap-1 mb-5">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4" style="color: #C9922F;" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endfor
                </div>
                <blockquote class="font-serif text-stone-700 italic leading-relaxed text-sm flex-1">
                    "{{ $testimonial['quote'] }}"
                </blockquote>
                <div class="mt-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                         style="background: {{ $testimonial['color'] }};">
                        {{ $testimonial['initials'] }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-stone-900">{{ $testimonial['name'] }}</p>
                        <p class="text-xs text-stone-400">{{ $testimonial['role'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Stats row --}}
        <div class="mt-16 grid grid-cols-2 md:grid-cols-4 gap-8">
            @foreach([['50K+', 'Active Believers'], ['2M+', 'Notes Created'], ['180+', 'Countries'], ['4.9★', 'Average Rating']] as $stat)
            <div class="text-center">
                <p class="font-serif text-3xl font-bold text-stone-900">{{ $stat[0] }}</p>
                <p class="text-sm text-stone-400 mt-1">{{ $stat[1] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
