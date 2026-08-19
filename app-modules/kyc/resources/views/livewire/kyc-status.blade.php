<div wire:poll.5000ms="poll">

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- VERIFIED                                         --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @if($verification->status === 'verified')
        <div class="w-full max-w-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-8 flex flex-col items-center justify-center text-center">
                <div class="w-full px-4 py-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="font-semibold text-green-800 dark:text-green-300 text-sm">We've verified your information</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">Please wait while we redirect you to your dashboard</p>
                </div>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- FAILED                                           --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @elseif($verification->status === 'failed')
        <div class="w-full max-w-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">We couldn't verify you at this time</h2>
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-red-100 flex items-center justify-center">
                        <x-phosphor-x class="w-3.5 h-3.5 text-red-600" />
                    </div>
                </div>
                @if($verification->overall_review_note)
                    <div class="px-4 py-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <p class="text-xs text-red-700 dark:text-red-300 leading-relaxed">{{ $verification->overall_review_note }}</p>
                    </div>
                @endif
                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                    If you believe this is an error, please contact our support team.
                </p>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- MORE INFORMATION NEEDED                          --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @elseif($verification->status === 'more_info_needed')
        <div class="w-full max-w-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Additional information required</h2>
                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center">
                        <x-phosphor-warning class="w-3.5 h-3.5 text-amber-600" />
                    </div>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed">
                    Our compliance team has reviewed your submission and needs further information for the following check(s):
                </p>

                <div class="space-y-3">
                    @foreach($verification->checksNeedingAttention() as $type => $check)
                        <div class="px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg space-y-2">
                            <p class="text-xs font-semibold text-amber-800 dark:text-amber-300">{{ $check['label'] }}</p>
                            @if($check['note'])
                                <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">{{ $check['note'] }}</p>
                            @endif
                            <a href="{{ $check['route'] }}"
                               class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:underline">
                                Re-upload document
                                <x-phosphor-arrow-right class="w-3 h-3" />
                            </a>
                        </div>
                    @endforeach
                </div>

                @if($verification->overall_review_note)
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 pt-2">
                        <strong>Note from reviewer:</strong> {{ $verification->overall_review_note }}
                    </p>
                @endif

                @if($verification->canBeSubmitted())
                    <button wire:click="submitForReview"
                            class="w-full py-2 text-sm font-medium text-white bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 rounded-lg hover:bg-zinc-800 dark:hover:bg-white transition-colors">
                        Re-submit for review
                    </button>
                @endif
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- UNDER REVIEW                                     --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @elseif($verification->status === 'under_review')
        <div class="w-full max-w-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-8 space-y-6">
                <div class="text-center space-y-1">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Your documents are under review</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Our compliance team is checking your submission. This typically takes 1–2 business days.</p>
                </div>
                <div class="flex items-center justify-center">
                    <svg class="w-6 h-6 animate-spin text-zinc-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- READY TO SUBMIT                                  --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @elseif($verification->isReadyForVerification() && $verification->canBeSubmitted())
        <div class="w-full max-w-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-8 space-y-4">
                <div class="text-center space-y-1">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Ready for review</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">All your documents and liveness check are complete. Submit your application for our compliance team to review.</p>
                </div>
                <button wire:click="submitForReview"
                        wire:loading.attr="disabled"
                        class="w-full py-2.5 text-sm font-medium text-white bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 rounded-lg hover:bg-zinc-800 dark:hover:bg-white transition-colors disabled:opacity-60">
                    <span wire:loading.remove>Submit for review</span>
                    <span wire:loading>Submitting…</span>
                </button>
            </div>
        </div>

    {{-- ═══════════════════════════════════════════════ --}}
    {{-- DOCUMENT UPLOAD IN PROGRESS                      --}}
    {{-- ═══════════════════════════════════════════════ --}}
    @else
        <div class="w-full max-w-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-8 space-y-6">
                <div class="text-center space-y-1">
                    <h2 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Complete your verification</h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">Upload all required documents and complete a liveness check to continue.</p>
                </div>

                <div class="space-y-3">
                    @foreach(\Nucleus\Kyc\Models\KycVerification::DOCUMENT_TYPES as $type => $config)
                        <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                            <a href="{{ $config['route'] }}" class="text-sm text-zinc-700 dark:text-zinc-300 hover:underline">
                                {{ $config['label'] }}
                            </a>
                            @if($verification->hasDocument($type))
                                <div class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                                    <x-phosphor-check-bold class="w-3 h-3 text-white" />
                                </div>
                            @else
                                <div class="w-5 h-5 rounded-full border-2 border-zinc-200 dark:border-zinc-700"></div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Liveness check row --}}
                    <div class="flex items-center justify-between py-2">
                        <a href="/kyc/liveness" class="text-sm text-zinc-700 dark:text-zinc-300 hover:underline">
                            Liveness check
                        </a>
                        @if($verification->hasLivenessCheck())
                            <div class="flex-shrink-0 w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                                <x-phosphor-check-bold class="w-3 h-3 text-white" />
                            </div>
                        @else
                            <div class="w-5 h-5 rounded-full border-2 border-zinc-200 dark:border-zinc-700"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
