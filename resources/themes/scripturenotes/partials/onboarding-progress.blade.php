@if(auth()->check() && !auth()->user()->onboarding_complete)
<div class="mb-6 rounded-2xl border border-amber-200/60 overflow-hidden"
     style="background: linear-gradient(135deg, #fffbf0, #fef9ec);">
    <div class="px-5 py-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2.5">
                <span class="text-lg">✨</span>
                <div>
                    <p class="text-sm font-semibold text-stone-800">Set up your Scripture journey</p>
                    <p class="text-xs text-stone-500">Complete these steps to get started</p>
                </div>
            </div>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full"
                  style="background: #fff8e6; color: #C9922F; border: 1px solid #f0d080;">
                {{ auth()->user()->onboarding_steps_completed }}/{{ auth()->user()->onboarding_steps_total }} done
            </span>
        </div>

        {{-- Progress bar --}}
        <div class="w-full h-1.5 bg-amber-100 rounded-full overflow-hidden">
            @php
                $total = auth()->user()->onboarding_steps_total ?: 1;
                $completed = auth()->user()->onboarding_steps_completed ?: 0;
                $percent = round(($completed / $total) * 100);
            @endphp
            <div class="h-full rounded-full transition-all duration-500"
                 style="width: {{ $percent }}%; background: linear-gradient(90deg, #C9922F, #e6a835);">
            </div>
        </div>

        {{-- Steps --}}
        <div class="mt-4 space-y-2">
            @foreach(auth()->user()->onboarding_steps ?? [] as $step)
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center
                            {{ $step['completed'] ? 'bg-green-100' : 'bg-stone-100' }}">
                    @if($step['completed'])
                    <svg class="w-3 h-3 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    @else
                    <span class="w-1.5 h-1.5 rounded-full bg-stone-300"></span>
                    @endif
                </div>
                <p class="text-xs {{ $step['completed'] ? 'line-through text-stone-400' : 'text-stone-600' }}">
                    {{ $step['label'] }}
                </p>
                @if(!$step['completed'] && isset($step['url']))
                <a href="{{ $step['url'] }}"
                   class="ml-auto text-xs font-medium transition-colors"
                   style="color: #C9922F;">
                    Start →
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
