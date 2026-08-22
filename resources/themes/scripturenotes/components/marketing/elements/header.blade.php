<header x-data="{
        mobileMenuOpen: false,
        scrolled: false,
        evaluateScrollPosition() {
            this.scrolled = window.pageYOffset > 10;
        }
    }"
    x-init="
        window.addEventListener('resize', () => { if(window.innerWidth > 768) mobileMenuOpen = false; });
        $watch('mobileMenuOpen', v => { v ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'); });
        evaluateScrollPosition();
        window.addEventListener('scroll', () => evaluateScrollPosition());
    "
    :class="{ 'bg-white/95 border-stone-200/60 border-b shadow-sm backdrop-blur-lg': scrolled, 'bg-transparent border-transparent border-b': !scrolled }"
    class="box-content sticky top-0 z-50 w-full h-20 transition-all duration-300">

    <div class="max-w-7xl mx-auto px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 gap-8">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-3 font-semibold text-stone-900 group">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #4B6741, #6B8F5E);">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.966 8.966 0 00-6 2.292m0-14.25v14.25"/>
                    </svg>
                </div>
                <span class="text-lg font-serif text-stone-800">{{ setting('site.title', 'ScriptureNotes') }}</span>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1">
                <a href="#features" onclick="event.preventDefault(); document.getElementById('features').scrollIntoView({ behavior: 'smooth' });"
                   class="px-4 py-2 text-sm font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100/70 rounded-lg transition-colors">Features</a>
                <a href="#pricing" onclick="event.preventDefault(); document.getElementById('pricing').scrollIntoView({ behavior: 'smooth' });"
                   class="px-4 py-2 text-sm font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100/70 rounded-lg transition-colors">Pricing</a>
                @if(setting('site.blog', false))
                <a href="/blog" wire:navigate class="px-4 py-2 text-sm font-medium text-stone-600 hover:text-stone-900 hover:bg-stone-100/70 rounded-lg transition-colors">Blog</a>
                @endif
            </nav>

            {{-- CTA Buttons --}}
            <div class="hidden md:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="px-4 py-2 text-sm font-medium text-stone-700 hover:text-stone-900 transition-colors">
                        My Notes
                    </a>
                @else
                    <a href="{{ route('login') }}" wire:navigate
                       class="px-4 py-2 text-sm font-medium text-stone-700 hover:text-stone-900 transition-colors">
                        Sign in
                    </a>
                    <a href="{{ route('register') }}" wire:navigate
                       class="px-5 py-2.5 text-sm font-medium text-white rounded-xl shadow-sm hover:shadow-md transition-all"
                       style="background: linear-gradient(135deg, #4B6741, #3A5432);">
                        Start Free
                    </a>
                @endauth
            </div>

            {{-- Mobile Menu Toggle --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-2 rounded-lg text-stone-600 hover:text-stone-900 hover:bg-stone-100 transition-colors">
                <svg x-show="!mobileMenuOpen" class="w-5 h-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                </svg>
                <svg x-show="mobileMenuOpen" class="w-5 h-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden absolute top-full left-0 right-0 bg-white/98 backdrop-blur-xl border-b border-stone-200 shadow-xl"
         x-cloak>
        <div class="max-w-7xl mx-auto px-6 py-6 flex flex-col gap-2">
            <a href="#features" @click="mobileMenuOpen=false; event.preventDefault(); document.getElementById('features').scrollIntoView({ behavior: 'smooth' });"
               class="px-4 py-3 text-sm font-medium text-stone-700 hover:bg-stone-50 rounded-xl transition-colors">Features</a>
            <a href="#pricing" @click="mobileMenuOpen=false; event.preventDefault(); document.getElementById('pricing').scrollIntoView({ behavior: 'smooth' });"
               class="px-4 py-3 text-sm font-medium text-stone-700 hover:bg-stone-50 rounded-xl transition-colors">Pricing</a>
            <div class="mt-2 pt-4 border-t border-stone-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="px-4 py-3 text-sm font-medium text-center text-stone-700 bg-stone-50 rounded-xl">My Notes</a>
                @else
                    <a href="{{ route('login') }}" wire:navigate
                       class="px-4 py-3 text-sm font-medium text-center text-stone-700 bg-stone-50 rounded-xl">Sign in</a>
                    <a href="{{ route('register') }}" wire:navigate
                       class="px-4 py-3 text-sm font-medium text-center text-white rounded-xl"
                       style="background: linear-gradient(135deg, #4B6741, #3A5432);">Start Free</a>
                @endauth
            </div>
        </div>
    </div>
</header>
