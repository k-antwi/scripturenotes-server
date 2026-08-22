<div class="space-y-5">
    {{-- Card details heading --}}
    <div class="flex items-center gap-2 mb-1">
        <svg class="w-4 h-4 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
        </svg>
        <p class="text-sm font-medium text-stone-700">Payment details</p>
        <div class="ml-auto flex items-center gap-1.5 text-stone-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span class="text-xs">Secured by Stripe</span>
        </div>
    </div>

    {{-- Stripe card element --}}
    <div>
        <div id="card-element"
             class="w-full px-4 py-3 border border-stone-200 rounded-xl bg-white text-sm text-stone-800
                    focus-within:ring-2 focus-within:border-transparent transition-all"
             style="focus-within:ring-color: #4B6741;">
        </div>
        <div id="card-errors" class="mt-2 text-xs text-red-500" role="alert"></div>
    </div>

    {{-- Billing name --}}
    <div>
        <label for="billing_name" class="block text-sm font-medium text-stone-700 mb-1.5">Name on card</label>
        <input id="billing_name" name="billing_name" type="text"
               placeholder="John Doe"
               class="w-full px-4 py-2.5 border border-stone-200 rounded-xl bg-white text-sm text-stone-800
                      placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:border-transparent transition-all"
               style="--tw-ring-color: #4B6741;">
    </div>

    {{-- Secure notice --}}
    <p class="text-xs text-stone-400 text-center">
        🔒 Your payment info is encrypted and never stored on our servers.
    </p>
</div>
