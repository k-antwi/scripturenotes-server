<div x-data x-cloak
     x-show="$store.toast.show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-6 right-6 z-[100] max-w-sm w-full">

    <div class="flex items-center gap-4 px-5 py-4 bg-white rounded-2xl shadow-2xl border border-stone-200/60"
         :class="{
             'border-l-4': true,
         }"
         :style="$store.toast.type === 'success' ? 'border-left-color: #4B6741' : ($store.toast.type === 'error' ? 'border-left-color: #dc2626' : 'border-left-color: #C9922F')">

        {{-- Icon --}}
        <div class="flex-shrink-0">
            <svg x-show="$store.toast.type === 'success'" class="w-5 h-5" style="color: #4B6741;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <svg x-show="$store.toast.type === 'error'" class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <svg x-show="$store.toast.type !== 'success' && $store.toast.type !== 'error'" class="w-5 h-5" style="color: #C9922F;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
        </div>

        <p class="flex-1 text-sm font-medium text-stone-800" x-text="$store.toast.message"></p>

        <button @click="$store.toast.close()" class="flex-shrink-0 text-stone-400 hover:text-stone-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
</div>
