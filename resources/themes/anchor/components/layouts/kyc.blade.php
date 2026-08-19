<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('theme::partials.head', ['seo' => ($seo ?? null)])
</head>
<body x-data class="flex flex-col min-h-screen bg-zinc-100 dark:bg-zinc-950" x-cloak>

    {{-- Minimal top bar --}}
    <header class="flex items-center justify-between px-6 py-4 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-blue-600 rounded-md flex items-center justify-center">
                <x-phosphor-piggy-bank class="w-4 h-4 text-white" />
            </div>
            <span class="font-bold text-zinc-900 dark:text-zinc-100">PensionTrack</span>
        </div>
        <a href="{{ route('wave.logout') }}" class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 border border-zinc-200 dark:border-zinc-700 px-3 py-1.5 rounded-md">
            Log out
        </a>
    </header>

    <main class="flex flex-1 items-center justify-center p-6">
        {{ $slot }}
    </main>

    @livewire('notifications')
    @include('theme::partials.footer-scripts')

</body>
</html>
