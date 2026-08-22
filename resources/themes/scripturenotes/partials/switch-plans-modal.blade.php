<div x-data="{ open: false }" x-cloak>
    {{-- Trigger --}}
    <button @click="open = true" {{ $attributes->merge(['class' => '']) }}>
        {{ $slot }}
    </button>

    {{-- Overlay --}}
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
             class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-stone-100 flex items-center justify-between"
                 style="background: linear-gradient(135deg, #f7f5f0, #f0ece3);">
                <div>
                    <h3 class="font-serif text-lg font-semibold text-stone-800">Switch Plan</h3>
                    <p class="text-xs text-stone-500 mt-0.5">Choose the plan that fits your study journey</p>
                </div>
                <button @click="open = false"
                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-600 hover:bg-stone-200/60 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Plans --}}
            <div class="px-6 py-5 space-y-3 max-h-96 overflow-y-auto">
                @php $plans = Wave\Plan::where('active', true)->get(); @endphp
                @foreach($plans as $plan)
                <div class="relative p-4 rounded-2xl border-2 transition-all cursor-pointer
                            {{ auth()->user()->plan_id === $plan->id
                               ? 'border-stone-400 bg-stone-50'
                               : 'border-stone-200 hover:border-stone-300 bg-white' }}"
                     wire:click="switchPlan({{ $plan->id }})">

                    @if($plan->default)
                    <span class="absolute top-3 right-3 text-xs font-semibold px-2 py-0.5 rounded-full text-white"
                          style="background: linear-gradient(135deg, #4B6741, #3A5432);">
                        Popular
                    </span>
                    @endif

                    @if(auth()->user()->plan_id === $plan->id)
                    <span class="absolute top-3 right-3 text-xs font-semibold px-2 py-0.5 rounded-full bg-stone-200 text-stone-600">
                        Current
                    </span>
                    @endif

                    <p class="font-semibold text-stone-800">{{ $plan->name }}</p>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-2xl font-bold text-stone-800">${{ number_format($plan->price / 100, 0) }}</span>
                        <span class="text-sm text-stone-500">/{{ $plan->billing_cycle }}</span>
                    </div>
                    @if($plan->description)
                    <p class="text-xs text-stone-500 mt-1.5 leading-relaxed">{{ $plan->description }}</p>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-stone-100 bg-stone-50/50">
                <p class="text-xs text-stone-400 text-center">
                    Changes take effect immediately. Prorated amounts will be applied.
                </p>
            </div>
        </div>
    </div>
</div>
