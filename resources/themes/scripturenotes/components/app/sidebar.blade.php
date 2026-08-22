<div x-data="{ sidebarOpen: false }" @open-sidebar.window="sidebarOpen = true"
    x-init="$watch('sidebarOpen', v => { v ? document.body.classList.add('overflow-hidden') : document.body.classList.remove('overflow-hidden'); })"
    class="relative z-50 w-screen md:w-auto" x-cloak>

    {{-- Backdrop --}}
    <div x-show="sidebarOpen" @click="sidebarOpen=false"
        class="fixed top-0 right-0 z-50 w-screen h-screen bg-stone-900/20 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    {{-- Sidebar Panel --}}
    <div :class="{ '-translate-x-full': !sidebarOpen }"
        class="fixed top-0 left-0 flex items-stretch -translate-x-full overflow-hidden lg:translate-x-0 z-50 h-dvh transition-[width,transform] duration-200 ease-out w-64 @if(config('wave.dev_bar')){{ 'pb-10' }}@endif"
        style="background: linear-gradient(180deg, #f7f5f0 0%, #f0ece3 100%); border-right: 1px solid #e8ddd3;">

        <div class="flex flex-col justify-between w-full overflow-auto pt-5 pb-3">
            <div class="flex flex-col">
                {{-- Close button (mobile) --}}
                <button x-on:click="sidebarOpen=false"
                    class="flex items-center justify-center w-8 h-8 ml-4 mb-1 rounded-lg lg:hidden text-stone-400 hover:text-stone-700 hover:bg-stone-200/50">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                {{-- Logo --}}
                <div class="flex items-center px-5 py-2 mb-3">
                    <a href="/" class="flex items-center gap-2.5 font-semibold text-stone-800">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                             style="background: linear-gradient(135deg, #4B6741, #6B8F5E);">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.966 8.966 0 00-6 2.292m0-14.25v14.25"/>
                            </svg>
                        </div>
                        <span class="font-serif text-base">{{ setting('site.title', 'ScriptureNotes') }}</span>
                    </a>
                </div>

                {{-- Search --}}
                <div class="px-4 mb-4">
                    <div class="relative flex items-center">
                        <svg class="absolute left-3 w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text"
                            class="w-full py-2 pl-9 pr-3 text-sm rounded-xl border text-stone-700 placeholder-stone-400 focus:outline-none focus:ring-2 transition-all"
                            style="background: rgba(255,255,255,0.7); border-color: #e8ddd3; focus:ring-color: #4B6741;"
                            placeholder="Search notes…">
                    </div>
                </div>

                {{-- Nav Links --}}
                <div class="px-3 space-y-0.5">
                    <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-widest text-stone-400">Study</p>
                    <x-app.sidebar-link href="/dashboard" icon="phosphor-house-duotone"
                        :active="Request::is('*/dashboard*') || Request::is('dashboard*')"
                        :ajax="false">Dashboard</x-app.sidebar-link>
                    <x-app.sidebar-link href="/notes" icon="phosphor-note-pencil-duotone"
                        :active="Request::is('*/notes*')"
                        :ajax="false">My Notes</x-app.sidebar-link>
                    <x-app.sidebar-link href="/passages" icon="phosphor-book-open-duotone"
                        :active="Request::is('*/passages*')"
                        :ajax="false">Passages</x-app.sidebar-link>
                    <x-app.sidebar-link href="/tags" icon="phosphor-tag-duotone"
                        :active="Request::is('*/tags*')"
                        :ajax="false">Topics & Tags</x-app.sidebar-link>

                    <div class="pt-4">
                        <p class="px-3 pb-2 text-[10px] font-semibold uppercase tracking-widest text-stone-400">Plans</p>
                    </div>
                    <x-app.sidebar-link href="/reading-plans" icon="phosphor-calendar-duotone"
                        :active="Request::is('*/reading-plans*')"
                        :ajax="false">Reading Plans</x-app.sidebar-link>
                    <x-app.sidebar-link href="/insights" icon="phosphor-chart-bar-duotone"
                        :active="Request::is('*/insights*')"
                        :ajax="false">Insights</x-app.sidebar-link>
                </div>
            </div>

            {{-- Bottom section --}}
            <div class="px-3 space-y-1">
                <x-app.sidebar-link href="/help" target="_blank" icon="phosphor-question-duotone" active="false">Help & Support</x-app.sidebar-link>

                <div x-show="sidebarTip" x-data="{ sidebarTip: $persist(true) }" class="px-1 py-2" x-collapse x-cloak>
                    <div class="relative w-full px-4 py-3 space-y-1 rounded-xl border text-stone-700"
                         style="background: rgba(75,103,65,0.06); border-color: rgba(75,103,65,0.15);">
                        <button @click="sidebarTip=false"
                            class="absolute top-2 right-2 p-1 rounded-full text-stone-400 hover:text-stone-600 hover:bg-stone-100">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <p class="text-xs font-semibold text-stone-700">✨ Tip of the Day</p>
                        <p class="text-xs text-stone-500 leading-relaxed">Try tagging notes by book of the Bible to build a searchable commentary over time.</p>
                    </div>
                </div>

                <div class="h-px my-1" style="background: #e8ddd3;"></div>
                <x-app.user-menu />
            </div>
        </div>
    </div>
</div>
