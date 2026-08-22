<div class="space-y-4">
    {{-- Welcome back banner --}}
    <div class="flex items-start gap-3 p-4 rounded-xl border border-green-200"
         style="background: linear-gradient(135deg, #f0f7ee, #e8f3e4);">
        <span class="text-xl flex-shrink-0">🕊️</span>
        <div>
            <p class="text-sm font-semibold" style="color: #3A5432;">Welcome back!</p>
            <p class="text-xs text-stone-600 mt-0.5 leading-relaxed">
                Reactivate your subscription to continue your Scripture study journey with all premium features.
            </p>
        </div>
    </div>

    {{-- What you'll regain --}}
    <div class="p-4 rounded-xl border border-stone-200 bg-stone-50 space-y-2.5">
        <p class="text-sm font-semibold text-stone-700">You'll regain access to:</p>
        <ul class="space-y-1.5">
            @foreach(['Unlimited Scripture notes', 'Advanced verse cross-referencing', 'Reading plan tracking', 'Study group collaboration', 'Priority support'] as $feature)
            <li class="flex items-center gap-2 text-xs text-stone-600">
                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #4B6741;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ $feature }}
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Reactivate CTA --}}
    <button wire:click="reactivate"
            class="w-full py-3 text-sm font-semibold text-white rounded-xl transition-all
                   hover:opacity-90 active:scale-[0.98]"
            style="background: linear-gradient(135deg, #4B6741, #3A5432);">
        Reactivate Subscription
    </button>

    <p class="text-xs text-stone-400 text-center">
        Your billing resumes immediately upon reactivation.
    </p>
</div>
