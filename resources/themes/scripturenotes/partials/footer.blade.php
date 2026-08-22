<footer class="border-t border-stone-200/60" style="background: #faf7f2;">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            {{-- Brand --}}
            <div class="md:col-span-2 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #4B6741, #6B8F5E);">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.966 8.966 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </div>
                    <span class="font-serif text-lg font-semibold text-stone-800">{{ setting('site.title', 'ScriptureNotes') }}</span>
                </div>
                <p class="text-sm text-stone-500 leading-relaxed max-w-xs">
                    A sacred space for capturing your journey through Scripture. Deepen your faith, one note at a time.
                </p>
                <blockquote class="border-l-2 pl-3 text-sm italic text-stone-400" style="border-color: #C9922F;">
                    "Thy word have I hid in mine heart." — Psalm 119:11
                </blockquote>
            </div>

            {{-- Product --}}
            <div>
                <h4 class="font-semibold text-stone-800 mb-4 text-sm">Product</h4>
                <ul class="space-y-3">
                    @foreach([['Features', '#features'], ['Pricing', '#pricing'], ['Blog', '/blog'], ['Changelog', '/changelog']] as $link)
                    <li><a href="{{ $link[1] }}" class="text-sm text-stone-500 hover:text-stone-800 transition-colors">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Legal --}}
            <div>
                <h4 class="font-semibold text-stone-800 mb-4 text-sm">Company</h4>
                <ul class="space-y-3">
                    @foreach([['About', '/about'], ['Privacy', '/privacy'], ['Terms', '/terms'], ['Contact', '/contact']] as $link)
                    <li><a href="{{ $link[1] }}" class="text-sm text-stone-500 hover:text-stone-800 transition-colors">{{ $link[0] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-stone-200/60 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-xs text-stone-400">© {{ date('Y') }} {{ setting('site.title', 'ScriptureNotes') }}. All rights reserved.</p>
            <p class="text-xs text-stone-400">Built with ❤️ for the kingdom</p>
        </div>
    </div>
</footer>
