@props(['position' => 'bottom'])

@php
$user = auth()->user();
@endphp

<div x-data="{ dropdownOpen: false }" class="relative flex-shrink-0 sm:p-0 sm:flex sm:w-auto sm:bg-transparent sm:items-center" x-cloak>
    <button @click="dropdownOpen = !dropdownOpen"
        class="flex p-2 w-full text-[13px] hover:bg-white/60 rounded-xl justify-between items-center text-stone-700 hover:text-stone-900 space-x-2 overflow-hidden transition-all">
        <span class="relative flex items-center space-x-2.5">
            <x-avatar src="{{ $user->avatar() }}" alt="{{ $user->name }}" size="2xs" />
            <span @class(['flex-shrink-0 text-[13px]', 'hidden' => ($position != 'bottom')])>{{ $user->name }}</span>
        </span>
        <svg :class="{ 'rotate-180': dropdownOpen }" class="w-4 h-4 text-stone-400 transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div wire:ignore x-show="dropdownOpen"
         @click.away="dropdownOpen=false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-1"
         @class(['z-50', 'absolute w-full bottom-full mb-2 left-0 origin-bottom' => ($position == 'bottom'), 'fixed top-16 right-4 w-64 origin-top-right' => ($position != 'bottom')])
         x-cloak>
        <div class="bg-white border rounded-2xl shadow-xl overflow-hidden" style="border-color: #e8ddd3;">
            <div class="px-4 py-3 border-b" style="border-color: #f0ece3; background: #faf7f2;">
                <p class="text-xs font-semibold text-stone-700">{{ $user->name }}</p>
                <p class="text-xs text-stone-400 truncate">{{ $user->email }}</p>
            </div>
            <div class="p-2 space-y-0.5">
                <x-app.light-dark-toggle />
                <div class="h-px my-1" style="background: #f0ece3;"></div>
                <x-app.sidebar-link :hideUntilGroupHover="false" href="{{ route('notifications') }}" icon="phosphor-bell-duotone" active="false">Notifications</x-app.sidebar-link>
                <x-app.sidebar-link href="/" icon="phosphor-house-duotone">View Site</x-app.sidebar-link>
                <x-app.sidebar-link :hideUntilGroupHover="false" href="{{ route('settings.profile') }}" icon="phosphor-gear-duotone" active="false">Settings</x-app.sidebar-link>
                @if(auth()->user()->isAdmin())
                <x-app.sidebar-link :hideUntilGroupHover="false" :ajax="false" href="/admin" icon="phosphor-crown-duotone" active="false">Admin Panel</x-app.sidebar-link>
                @endif
                <div class="h-px my-1" style="background: #f0ece3;"></div>
                <form method="POST" action="{{ filament()->getLogoutUrl() }}" class="w-full">
                    @csrf
                    <button onclick="event.preventDefault(); this.closest('form').submit();"
                        class="w-full flex items-center gap-2.5 px-3 py-2 text-[13px] text-stone-600 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all">
                        <x-phosphor-sign-out-duotone class="w-4.5 h-4.5" />
                        <span>Sign out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
