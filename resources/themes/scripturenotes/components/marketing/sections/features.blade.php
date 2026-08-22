<section id="features" class="relative py-24 lg:py-32 overflow-hidden">
    {{-- Background wave --}}
    <div class="absolute inset-0 pointer-events-none">
        <svg class="absolute top-0 left-0 w-full opacity-3" viewBox="0 0 1440 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 100 C360 0 720 200 1080 100 C1260 50 1350 125 1440 100 L1440 0 L0 0 Z" fill="#4B6741"/>
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative">
        <x-marketing.elements.heading
            title="Everything you need to<br><em>study Scripture deeply</em>"
            description="From capturing first thoughts to building a lifetime of spiritual insight — ScriptureNotes grows with your faith journey." />

        <div class="mt-16 lg:mt-20 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @php
            $featuresList = [
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>',
                    'color' => '#4B6741',
                    'bg' => '#f0f4ef',
                    'title' => 'Rich Note Editor',
                    'desc' => 'Write verse-by-verse reflections, prayers, and insights with a beautiful distraction-free editor designed for deep thought.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.966 8.966 0 00-6 2.292m0-14.25v14.25"/>',
                    'color' => '#C9922F',
                    'bg' => '#fdf6ec',
                    'title' => 'Verse Linking',
                    'desc' => 'Link any note directly to a Bible verse. Cross-reference Scripture effortlessly and see how passages connect across the whole Bible.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6h.008v.008H6V6z"/>',
                    'color' => '#6B5744',
                    'bg' => '#f7f4f1',
                    'title' => 'Tags & Topics',
                    'desc' => 'Organise notes by theme, book, sermon series, or any custom tag. Find exactly what you wrote, no matter how long ago.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/>',
                    'color' => '#4B6741',
                    'bg' => '#f0f4ef',
                    'title' => 'Reading Plans',
                    'desc' => 'Follow Bible reading plans and attach notes to each day\'s passages. Stay consistent with gentle daily reminders.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0015.803 15.803z"/>',
                    'color' => '#C9922F',
                    'bg' => '#fdf6ec',
                    'title' => 'Powerful Search',
                    'desc' => 'Instantly search across all your notes, verses, and tags. Your years of study become a personal searchable library of wisdom.',
                ],
                [
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>',
                    'color' => '#6B5744',
                    'bg' => '#f7f4f1',
                    'title' => 'Study Groups',
                    'desc' => 'Share notes with your small group or Bible study team. Collaborate on passages and build community through shared insight.',
                ],
            ];
            @endphp

            @foreach($featuresList as $feature)
            <div class="group p-7 rounded-2xl bg-white border border-stone-200/80 hover:border-stone-300 hover:shadow-lg transition-all duration-300 cursor-default">
                <div class="w-12 h-12 rounded-xl mb-5 flex items-center justify-center flex-shrink-0 transition-all duration-300 group-hover:scale-110"
                     style="background: {{ $feature['bg'] }};">
                    <svg class="w-6 h-6" style="color: {{ $feature['color'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $feature['icon'] !!}
                    </svg>
                </div>
                <h3 class="font-serif text-lg font-semibold text-stone-900 mb-2">{{ $feature['title'] }}</h3>
                <p class="text-sm leading-relaxed text-stone-500">{{ $feature['desc'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Bottom quote --}}
        <div class="mt-20 text-center">
            <blockquote class="font-serif text-2xl italic text-stone-600 max-w-2xl mx-auto leading-relaxed">
                "Your word is a lamp to my feet and a light to my path."
            </blockquote>
            <cite class="block mt-3 text-sm text-stone-400 not-italic">Psalm 119:105</cite>
        </div>
    </div>
</section>
