{{-- Mobile app header --}}
<header class="lg:hidden sticky top-0 z-50 flex items-center justify-between px-4 h-14 border-b border-stone-200/60"
        style="background: #f7f5f0;">
    {{-- Logo --}}
    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
             style="background: linear-gradient(135deg, #4B6741, #6B8F5E);">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.966 8.966 0 00-6 2.292m0-14.25v14.25"/>
            </svg>
        </div>
        <span class="font-serif text-base font-semibold text-stone-800">{{ setting('site.title', 'ScriptureNotes') }}</span>
    </a>

    {{-- Right controls --}}
    <div class="flex items-center gap-2">
        {{-- Sidebar toggle --}}
        <button @click="sidebarOpen = !sidebarOpen"
                class="p-2 rounded-lg text-stone-500 hover:text-stone-700 hover:bg-stone-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>
</header>
