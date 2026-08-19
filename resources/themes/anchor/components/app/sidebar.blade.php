<div x-data="{ sidebarOpen: false }" @open-sidebar.window="sidebarOpen = true"
    x-init="
        $watch('sidebarOpen', function(value){
            if(value){ document.body.classList.add('overflow-hidden'); } else { document.body.classList.remove('overflow-hidden'); }
        });
    "
    class="relative z-50 w-screen md:w-auto" x-cloak>
    {{-- Backdrop --}}
    <div x-show="sidebarOpen" @click="sidebarOpen=false" class="fixed top-0 right-0 z-50 w-screen h-screen duration-300 ease-out bg-black/30"></div>

    {{-- Sidebar --}}
    <div :class="{ '-translate-x-full': !sidebarOpen }"
        class="fixed top-0 left-0 flex items-stretch -translate-x-full overflow-hidden lg:translate-x-0 z-50 h-dvh md:h-screen transition-[width,transform] duration-150 ease-out bg-slate-800 w-64 group @if(config('wave.dev_bar')){{ 'pb-10' }}@endif">
        <div class="flex flex-col justify-between w-full overflow-auto md:h-full h-svh pt-4 pb-2.5">

            <div class="relative flex flex-col flex-1">
                {{-- Mobile close button --}}
                <button x-on:click="sidebarOpen=false" class="flex items-center justify-center flex-shrink-0 w-10 h-10 ml-4 rounded-md lg:hidden text-slate-400 hover:text-white hover:bg-white/10">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>

                {{-- Logo + User Area --}}
                <div class="flex items-center justify-between px-5 py-3">
                    <a href="/" wire:navigate class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center flex-shrink-0">
                            <x-phosphor-piggy-bank class="w-5 h-5 text-white" />
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm font-bold text-white truncate">{{ setting('site.title') }}</span>
                            <span class="block text-xs text-slate-400 truncate">{{ auth()->check() ? auth()->user()->name : '' }}</span>
                        </div>
                    </a>
                </div>

                <div class="px-4 mt-1 mb-3">
                    <div class="h-px bg-white/10"></div>
                </div>

                {{-- Main Navigation --}}
                <div class="flex flex-col justify-start items-center px-4 space-y-0.5 w-full">
                    <x-app.sidebar-link href="/dashboard" icon="phosphor-house" :active="Request::is('dashboard')">Dashboard</x-app.sidebar-link>

                    <x-app.sidebar-link href="/actions" icon="phosphor-paint-brush" :active="Request::is('actions*')">Theme Engine</x-app.sidebar-link>
                    <x-app.sidebar-link href="/website" icon="phosphor-globe" :active="Request::is('website*')">Create Website</x-app.sidebar-link>
                </div>

                {{-- Pensions Group --}}
                <!-- <div class="px-4 mt-5">
                    <p class="px-2.5 mb-2 text-xs font-semibold tracking-wider uppercase text-slate-500">Pensions</p>
                    <div class="space-y-0.5">
                        <x-app.sidebar-link href="/pensions" icon="phosphor-list-bullets" :active="Request::is('pensions') && !Request::is('pensions/*')">All Pensions</x-app.sidebar-link>
                        <x-app.sidebar-link href="/pensions/find" icon="phosphor-magnifying-glass" :active="Request::is('pensions/find')">Find a Pension</x-app.sidebar-link>
                        <x-app.sidebar-link href="/pensions/create" icon="phosphor-plus-circle" :active="Request::is('pensions/create')">Add a Pension</x-app.sidebar-link>
                    </div>
                </div> -->

                {{-- Intel Group --}}
                <div class="px-4 mt-5">
                    <p class="px-2.5 mb-2 text-xs font-semibold tracking-wider uppercase text-slate-500">Intel</p>
                    <div class="space-y-0.5">
                        <x-app.sidebar-link :href="route('brain.chat')" icon="phosphor-brain" :active="Request::is('brain')">AI Assistant</x-app.sidebar-link>
                    </div>
                </div>
            </div>

            {{-- Bottom Section --}}
            <div class="relative px-4 space-y-0.5">
                <div class="h-px bg-white/10 mb-2"></div>
                @auth
                    @if(auth()->user()->hasRole('financial_advisor'))
                        <x-app.sidebar-link href="/financial-advisor" icon="phosphor-briefcase" :active="false">Advisor Panel</x-app.sidebar-link>
                    @elseif(auth()->user()->hasRole('admin'))
                        <x-app.sidebar-link href="/admin" icon="phosphor-shield-check" :active="false">Admin Panel</x-app.sidebar-link>
                    @endif
                @endauth
                <x-app.sidebar-link :href="route('settings.profile')" wire:navigate icon="phosphor-gear" :active="Request::is('settings*')">Settings</x-app.sidebar-link>
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button onclick="event.preventDefault(); this.closest('form').submit();"
                        class="text-slate-300 hover:bg-white/10 hover:text-white transition-colors px-2.5 py-2 flex rounded-lg w-full h-auto text-sm justify-start items-center space-x-2 overflow-hidden">
                        <x-phosphor-sign-out class="flex-shrink-0 w-5 h-5" />
                        <span class="flex-shrink-0">Logout</span>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
