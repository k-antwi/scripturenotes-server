<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('theme::partials.head', ['seo' => ($seo ?? null) ])
    <script>
        if (typeof(Storage) !== "undefined") {
            if(localStorage.getItem('theme') && localStorage.getItem('theme') == 'dark'){
                document.documentElement.classList.add('dark');
            }
        }
        document.addEventListener("livewire:navigated", () => {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>
</head>
<body x-data class="flex flex-col lg:min-h-screen bg-gray-50 @if(config('wave.dev_bar')){{ 'pb-10' }}@endif">

    <x-app.sidebar />

    <div class="flex flex-col pl-0 min-h-screen justify-stretch lg:pl-64">
        {{-- Mobile Header --}}
        <header class="lg:hidden px-5 block flex justify-between sticky top-0 z-40 bg-slate-800 -mb-px border-b border-slate-700 h-[72px] items-center">
            <button x-on:click="window.dispatchEvent(new CustomEvent('open-sidebar'))" class="flex flex-shrink-0 justify-center items-center w-10 h-10 rounded-md text-slate-300 hover:text-white hover:bg-white/10">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" /></svg>
            </button>
            <x-app.user-menu position="top" />
        </header>
        {{-- End Mobile Header --}}
        <main class="flex flex-col flex-1 xl:px-0 lg:h-screen">
            <div class="flex-1 h-full bg-gray-50">
                <div class="px-5 w-full h-full sm:px-8 lg:overflow-y-scroll scrollbar-hidden lg:px-8 lg:py-6">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    @livewire('notifications')
    @if(!auth()->guest() && auth()->user()->hasChangelogNotifications())
        @include('theme::partials.changelogs')
    @endif
    @include('theme::partials.footer-scripts')
    {{ $javascript ?? '' }}

</body>
</html>
