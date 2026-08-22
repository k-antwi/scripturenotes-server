<div class="space-y-4">
    {{-- Warning banner --}}
    <div class="flex items-start gap-3 p-4 rounded-xl border border-amber-200 bg-amber-50">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #C9922F;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold" style="color: #92620a;">Before you go…</p>
            <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                Cancelling will end your access to premium features at the end of your billing period.
                Your notes and data will remain safe.
            </p>
        </div>
    </div>

    {{-- What you'll lose --}}
    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50 space-y-2.5">
        <p class="text-sm font-semibold text-stone-700">You'll lose access to:</p>
        <ul class="space-y-1.5">
            @foreach(['Unlimited Scripture notes', 'Advanced verse cross-referencing', 'Reading plan tracking', 'Study group collaboration', 'Priority support'] as $feature)
            <li class="flex items-center gap-2 text-xs text-stone-600">
                <svg class="w-3.5 h-3.5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                {{ $feature }}
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Cancel button --}}
    <div class="flex gap-3 pt-1">
        <a href="{{ route('dashboard') }}"
           class="flex-1 py-2.5 text-sm font-semibold text-center text-white rounded-xl transition-all"
           style="background: linear-gradient(135deg, #4B6741, #3A5432);">
            Keep my subscription
        </a>
        <button wire:click="cancel"
                class="flex-1 py-2.5 text-sm font-semibold text-red-600 rounded-xl border border-red-200 hover:bg-red-50 transition-colors">
            Cancel anyway
        </button>
    </div>
</div>
