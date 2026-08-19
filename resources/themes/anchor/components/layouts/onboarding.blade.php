<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('theme::partials.head', ['seo' => ($seo ?? null)])
</head>
<body x-data class="flex flex-col min-h-screen bg-slate-100 dark:bg-zinc-950" x-cloak>

    {{-- Header --}}
    <header class="bg-[#1a2a5c] px-6 py-4">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 bg-white/20 rounded-md flex items-center justify-center">
                <x-phosphor-piggy-bank class="w-4 h-4 text-white" />
            </div>
            <span class="font-bold text-white text-lg tracking-tight">PensionTrack</span>
        </div>
    </header>

    {{-- Main content --}}
    <main class="flex-1 py-4 px-4">
        {{ $slot }}
    </main>

    {{-- Risk disclaimer --}}
    <div class="px-6 py-4 text-xs text-gray-500 text-center border-t border-gray-200 bg-white">
        Investments can go down in value as well as up and you could get back less than you invest. Tax rules for pensions can change.
    </div>

    {{-- Footer --}}
    <footer class="bg-white border-t border-gray-100 px-6 py-6">
        <div class="max-w-3xl mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-[#1a2a5c] rounded flex items-center justify-center">
                    <x-phosphor-piggy-bank class="w-3.5 h-3.5 text-white" />
                </div>
                <span class="font-bold text-[#1a2a5c] text-sm">PensionTrack</span>
            </div>
            <p class="text-xs text-gray-500 max-w-xl">
                &copy; {{ date('Y') }} PensionTrack. All rights reserved. Issued by PensionTrack which is authorised and regulated in the UK by the Financial Conduct Authority. Information about us can be found on the Financial Services Register.
            </p>
        </div>
    </footer>

    @livewire('notifications')
    @include('theme::partials.footer-scripts')

</body>
</html>
