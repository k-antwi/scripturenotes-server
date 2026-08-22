<div x-data="{ open: false }" x-cloak>
    {{-- Trigger slot --}}
    <button @click="open = true" {{ $attributes->merge(['class' => '']) }}>
        {{ $slot }}
    </button>

    {{-- Modal overlay --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[300] flex items-center justify-center p-4"
         style="background: rgba(44,24,16,0.4); backdrop-filter: blur(4px);"
         @click.self="open = false">

        <div x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-stone-100 flex items-center justify-between">
                <h3 class="font-serif text-lg font-semibold text-stone-800">Cancel Subscription</h3>
                <button @click="open = false"
                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5">
                @include('theme::partials.cancel')
            </div>
        </div>
    </div>
</div>
