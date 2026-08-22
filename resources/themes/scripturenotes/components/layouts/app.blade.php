<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('theme::partials.head', ['seo' => ($seo ?? null)])
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
<body x-data class="flex flex-col lg:min-h-screen bg-amber-50/20 dark:bg-stone-950 @if(config('wave.dev_bar')){{ 'pb-10' }}@endif" style="font-family: 'Inter', system-ui, sans-serif;">

    <x-app.sidebar />

    <div class="flex flex-col pl-0 min-h-screen justify-stretch lg:pl-64">
        {{-- Mobile Header --}}
        <header class="lg:hidden px-5 block flex justify-between sticky top-0 z-40 bg-amber-50/80 dark:bg-stone-900 backdrop-blur-md -mb-px border-b border-stone-200/60 dark:border-stone-700 h-[72px] items-center">
            <button x-on:click="window.dispatchEvent(new CustomEvent('open-sidebar'))" class="flex flex-shrink-0 justify-center items-center w-10 h-10 rounded-lg text-stone-600 dark:text-stone-300 hover:bg-stone-200/50 dark:hover:bg-stone-700/50">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9h16.5m-16.5 6.75h16.5" />
                </svg>
            </button>
            <x-app.user-menu position="top" />
        </header>
        {{-- End Mobile Header --}}
        <main class="flex flex-col flex-1 xl:px-0 lg:pt-4 lg:h-screen">
            <div class="overflow-hidden flex-1 h-full bg-white/70 border-t border-l-0 lg:border-l dark:bg-stone-800/60 lg:rounded-tl-2xl border-stone-200/50 dark:border-stone-700/50 backdrop-blur-sm">
                <div class="px-5 w-full h-full sm:px-8 lg:overflow-y-scroll scrollbar-hidden lg:pt-6 lg:px-6">
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
