<section class="relative min-h-screen flex flex-col justify-center items-center overflow-hidden -mt-20 pt-20">

    {{-- Background decorative elements --}}
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-1/4 right-0 w-[600px] h-[600px] rounded-full opacity-10" style="background: radial-gradient(circle, #4B6741, transparent);"></div>
        <div class="absolute bottom-1/4 left-0 w-[400px] h-[400px] rounded-full opacity-10" style="background: radial-gradient(circle, #C9922F, transparent);"></div>
        {{-- Subtle wave SVG background --}}
        <svg class="absolute bottom-0 left-0 w-full opacity-5" viewBox="0 0 1440 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 100 C360 200 720 0 1080 100 C1260 150 1350 75 1440 100 L1440 200 L0 200 Z" fill="#4B6741"/>
        </svg>
    </div>

    <div class="relative max-w-7xl mx-auto px-6 lg:px-8 py-20 lg:py-32 w-full">
        <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-20">

            {{-- Left: Text Content --}}
            <div class="w-full lg:w-1/2 space-y-8 text-center lg:text-left">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium text-stone-600 bg-white/80 border border-stone-200 shadow-sm backdrop-blur-sm">
                    <svg class="w-4 h-4" style="color: #C9922F;" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>Your Sacred Space for Bible Study</span>
                </div>

                {{-- Headline --}}
                <h1 class="font-serif text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-stone-900 leading-[1.05]">
                    Deepen Your<br>
                    <span class="relative">
                        <span style="color: #4B6741;">Scripture</span>
                        <span class="text-stone-900"> Journey</span>
                    </span>
                </h1>

                <p class="text-lg sm:text-xl leading-relaxed text-stone-500 max-w-xl mx-auto lg:mx-0">
                    A beautiful, distraction-free space to capture reflections, track Bible passages, and grow in your walk with God — one verse at a time.
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" wire:navigate
                       class="inline-flex items-center justify-center gap-2 px-7 py-4 text-base font-semibold text-white rounded-2xl shadow-lg hover:shadow-xl transition-all"
                       style="background: linear-gradient(135deg, #4B6741, #3A5432);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.966 8.966 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                        <span>Start Writing Notes</span>
                    </a>
                    <a href="#features" onclick="event.preventDefault(); document.getElementById('features').scrollIntoView({ behavior: 'smooth' });"
                       class="inline-flex items-center justify-center gap-2 px-7 py-4 text-base font-semibold text-stone-700 bg-white/80 border border-stone-200 rounded-2xl shadow-sm hover:shadow-md hover:bg-white transition-all">
                        <span>See Features</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>

                {{-- Social proof --}}
                <div class="flex items-center gap-6 justify-center lg:justify-start text-sm text-stone-500 pt-2">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>Free to start</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span>No credit card needed</span>
                    </div>
                </div>
            </div>

            {{-- Right: Illustration --}}
            <div class="w-full lg:w-1/2 flex justify-center lg:justify-end">
                <div class="relative w-full max-w-[520px]">
                    {{-- Glow background --}}
                    <div class="absolute inset-0 rounded-3xl blur-3xl opacity-20" style="background: linear-gradient(135deg, #4B6741, #C9922F);"></div>

                    {{-- Bible Notes App Mockup --}}
                    <div class="relative bg-white rounded-3xl shadow-2xl overflow-hidden border border-stone-200/60">
                        {{-- App Header --}}
                        <div class="px-6 py-4 flex items-center justify-between" style="background: linear-gradient(135deg, #4B6741, #3A5432);">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.966 8.966 0 00-6 2.292m0-14.25v14.25"/>
                                    </svg>
                                </div>
                                <span class="text-white font-semibold text-sm">ScriptureNotes</span>
                            </div>
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-white/30"></div>
                                <div class="w-3 h-3 rounded-full bg-white/30"></div>
                                <div class="w-3 h-3 rounded-full bg-white/30"></div>
                            </div>
                        </div>

                        {{-- Note Editor --}}
                        <div class="p-6 space-y-5 bg-amber-50/30">
                            {{-- Verse Reference --}}
                            <div class="flex items-center gap-3">
                                <div class="px-3 py-1 rounded-lg text-xs font-semibold text-white" style="background: #4B6741;">John 3:16</div>
                                <div class="flex-1 h-px bg-stone-200"></div>
                                <span class="text-xs text-stone-400">Today</span>
                            </div>

                            {{-- Verse Text --}}
                            <blockquote class="text-sm text-stone-600 italic leading-relaxed border-l-4 pl-4" style="border-color: #C9922F;">
                                "For God so loved the world that he gave his one and only Son, that whoever believes in him shall not perish but have eternal life."
                            </blockquote>

                            {{-- Note --}}
                            <div class="space-y-2">
                                <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">My Reflection</p>
                                <p class="text-sm text-stone-700 leading-relaxed">
                                    The depth of this love is incomprehensible — a Father giving his most precious treasure so that we might live. Today I'm reminded that my worth is not in what I do, but in whose I am...
                                </p>
                                <div class="h-0.5 w-8 rounded-full mt-2" style="background: #C9922F;"></div>
                            </div>

                            {{-- Tags --}}
                            <div class="flex gap-2 flex-wrap">
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">Love</span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">Salvation</span>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-stone-50 text-stone-600 border border-stone-200">Gospel of John</span>
                            </div>
                        </div>

                        {{-- Recent Notes List --}}
                        <div class="border-t border-stone-100">
                            <div class="px-6 py-3 flex items-center justify-between bg-white">
                                <span class="text-xs font-semibold text-stone-400 uppercase tracking-wide">Recent Notes</span>
                                <span class="text-xs text-green-600 font-medium">12 this week</span>
                            </div>
                            @foreach([['Psalm 23:1', 'The Lord is my shepherd...', 'Yesterday'], ['Romans 8:28', 'All things work together...', 'Monday'], ['Proverbs 3:5-6', 'Trust in the Lord...', 'Sunday']] as $note)
                            <div class="px-6 py-3 flex items-center gap-4 hover:bg-amber-50/50 transition-colors border-t border-stone-50">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background: #f0f4ef;">
                                    <svg class="w-4 h-4" style="color: #4B6741;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-stone-700">{{ $note[0] }}</p>
                                    <p class="text-xs text-stone-400 truncate">{{ $note[1] }}</p>
                                </div>
                                <span class="text-xs text-stone-300 flex-shrink-0">{{ $note[2] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Floating badge --}}
                    <div class="absolute -top-4 -right-4 bg-white rounded-2xl shadow-lg border border-stone-200 px-4 py-3 flex items-center gap-2">
                        <svg class="w-5 h-5" style="color: #C9922F;" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-bold text-stone-900">Daily Streak</p>
                            <p class="text-xs text-stone-500">30 days 🔥</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Wave separator --}}
    <div class="absolute bottom-0 left-0 right-0 w-full overflow-hidden leading-none">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
            <path d="M0 30 C240 60 480 0 720 30 C960 60 1200 0 1440 30 L1440 60 L0 60 Z" fill="white" fill-opacity="0.5"/>
        </svg>
    </div>
</section>

{{-- Trusted by section --}}
<div class="bg-white/50 py-10 border-y border-stone-100">
    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <p class="text-center text-xs font-semibold text-stone-400 uppercase tracking-widest mb-8">Trusted by believers worldwide</p>
        <div class="grid grid-cols-3 lg:grid-cols-6 gap-8 items-center opacity-40 grayscale">
            @foreach(['Bible Gateway', 'YouVersion', 'Crossway', 'Logos', 'Faithlife', 'Olive Tree'] as $brand)
            <div class="text-center">
                <span class="text-sm font-semibold text-stone-500">{{ $brand }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
