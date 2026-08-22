<section id="pricing" class="py-24 lg:py-32 relative overflow-hidden bg-white">
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse at 50% 0%, #f0f4ef, transparent 70%);"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative">
        <x-marketing.elements.heading
            level="h2"
            title="Start free, grow deeper"
            description="Every plan includes the core tools you need to study Scripture. Upgrade when you're ready for more." />

        <div x-data="{ on: false, billing: '{{ get_default_billing_cycle() }}',
                toggleRepositionMarker(toggleButton) {
                    if(this.$refs.marker && toggleButton){
                        this.$refs.marker.style.width  = toggleButton.offsetWidth + 'px';
                        this.$refs.marker.style.height = toggleButton.offsetHeight + 'px';
                        this.$refs.marker.style.left   = toggleButton.offsetLeft + 'px';
                    }
                }
             }"
            x-init="
                setTimeout(function(){
                    toggleRepositionMarker($refs.monthly);
                    if($refs.marker){
                        $refs.marker.classList.remove('opacity-0');
                        setTimeout(function(){ $refs.marker.classList.add('duration-300', 'ease-out'); }, 10);
                    }
                }, 1);
            "
            class="mx-auto mt-12 mb-2 w-full max-w-6xl md:my-12" x-cloak>

            @if(has_monthly_yearly_toggle())
            <div class="flex relative justify-start items-center pb-5 -translate-y-2 md:justify-center">
                <div class="inline-flex relative justify-center items-center p-1 w-auto text-center rounded-full border-2 -translate-y-3 md:mx-auto" style="border-color: #4B6741;">
                    <div x-ref="monthly" x-on:click="billing='Monthly'; toggleRepositionMarker($el)"
                         :class="{ 'text-white': billing == 'Monthly', 'text-stone-700': billing != 'Monthly' }"
                         class="relative z-20 px-4 py-1.5 text-sm font-medium leading-6 rounded-full duration-300 ease-out cursor-pointer">
                        Monthly
                    </div>
                    <div x-ref="yearly" x-on:click="billing='Yearly'; toggleRepositionMarker($el)"
                         :class="{ 'text-white': billing == 'Yearly', 'text-stone-700': billing != 'Yearly' }"
                         class="relative z-20 px-4 py-1.5 text-sm font-medium leading-6 rounded-full duration-300 ease-out cursor-pointer">
                        Yearly <span class="text-xs ml-1 opacity-80">Save 20%</span>
                    </div>
                    <div x-ref="marker" class="absolute left-0 z-10 w-1/2 h-full opacity-0" x-cloak>
                        <div class="w-full h-full rounded-full shadow-sm" style="background: #4B6741;"></div>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex flex-col flex-wrap lg:flex-row gap-6 lg:justify-center">
                @foreach(Wave\Plan::where('active', true)->get() as $plan)
                @php $features = explode(',', $plan->features); @endphp
                <div x-show="
                    @if(has_monthly_yearly_toggle())
                        (billing == 'Monthly' && '{{ $plan->billing_period }}' == 'monthly') ||
                        (billing == 'Yearly' && '{{ $plan->billing_period }}' == 'yearly') ||
                        ('{{ $plan->billing_period }}' == '' || '{{ $plan->billing_period }}' == null)
                    @else
                        true
                    @endif"
                    class="flex flex-col flex-1 w-full rounded-2xl overflow-hidden border transition-all duration-300 hover:shadow-xl
                        @if($plan->default) border-2 shadow-lg @else border border-stone-200 @endif"
                    style="@if($plan->default) border-color: #4B6741; @endif">

                    {{-- Plan Header --}}
                    <div class="px-8 pt-8 pb-6 @if($plan->default) text-white @else bg-white @endif"
                         style="@if($plan->default) background: linear-gradient(135deg, #4B6741, #3A5432); @endif">
                        @if($plan->default)
                        <div class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-white/20 text-white mb-4">
                            Most Popular
                        </div>
                        @endif
                        <h3 class="font-serif text-xl font-semibold @if(!$plan->default) text-stone-900 @endif">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm @if($plan->default) text-white/80 @else text-stone-500 @endif">{{ $plan->description }}</p>
                        <div class="mt-6 flex items-baseline gap-1">
                            <span class="font-serif text-5xl font-bold @if(!$plan->default) text-stone-900 @endif">${{ $plan->price }}</span>
                            <span class="text-sm @if($plan->default) text-white/70 @else text-stone-400 @endif">/{{ $plan->billing_period ?: 'month' }}</span>
                        </div>
                    </div>

                    {{-- Features List --}}
                    <div class="flex-1 px-8 py-6 bg-white space-y-3">
                        @foreach($features as $feature)
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 mt-0.5 flex-shrink-0" style="color: #4B6741;" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-stone-700">{{ trim($feature) }}</span>
                        </div>
                        @endforeach
                    </div>

                    {{-- CTA --}}
                    <div class="px-8 pb-8 bg-white">
                        @auth
                        <a href="{{ route('settings.subscription') }}" wire:navigate
                           class="block w-full text-center py-3.5 rounded-xl text-sm font-semibold transition-all duration-200
                               @if($plan->default) text-white shadow-md hover:shadow-lg @else text-stone-700 bg-stone-100 hover:bg-stone-200 @endif"
                           style="@if($plan->default) background: linear-gradient(135deg, #4B6741, #3A5432); @endif">
                            Get Started
                        </a>
                        @else
                        <a href="{{ route('register') }}" wire:navigate
                           class="block w-full text-center py-3.5 rounded-xl text-sm font-semibold transition-all duration-200
                               @if($plan->default) text-white shadow-md hover:shadow-lg @else text-stone-700 bg-stone-100 hover:bg-stone-200 @endif"
                           style="@if($plan->default) background: linear-gradient(135deg, #4B6741, #3A5432); @endif">
                            Get Started Free
                        </a>
                        @endauth
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Guarantee --}}
            <div class="mt-12 text-center">
                <p class="text-sm text-stone-400 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    30-day money-back guarantee · Cancel anytime · No hidden fees
                </p>
            </div>
        </div>
    </div>
</section>
