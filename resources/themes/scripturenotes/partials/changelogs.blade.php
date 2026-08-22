{{-- Changelog notification modal --}}
@if(!auth()->guest() && auth()->user()->hasChangelogNotifications())
<div x-data="{ open: true }" x-show="open" x-cloak
     class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-4 sm:p-6"
     style="background: rgba(44,24,16,0.4); backdrop-filter: blur(4px);">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="px-6 py-5 border-b border-stone-100" style="background: linear-gradient(135deg, #4B6741, #3A5432);">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="text-xl">📖</span>
                    <h3 class="font-serif text-lg font-semibold text-white">What's New</h3>
                </div>
                <button @click="open=false; @this.markChangelogNotificationsAsRead()" class="p-1.5 rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <div class="px-6 py-5 max-h-80 overflow-y-auto space-y-4">
            @foreach(auth()->user()->unreadChangelogs() as $changelog)
            <div class="space-y-1">
                <p class="text-sm font-semibold text-stone-800">{{ $changelog->title }}</p>
                <p class="text-xs text-stone-500 leading-relaxed">{{ $changelog->body }}</p>
            </div>
            @endforeach
        </div>
        <div class="px-6 py-4 border-t border-stone-100">
            <button @click="open=false; @this.markChangelogNotificationsAsRead()"
                class="w-full py-2.5 text-sm font-semibold text-white rounded-xl transition-all"
                style="background: linear-gradient(135deg, #4B6741, #3A5432);">
                Got it, thanks!
            </button>
        </div>
    </div>
</div>
@endif
