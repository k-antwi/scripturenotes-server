{{-- Renders a generated theme specification: palette swatches, type, radii. --}}
<div
    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl px-4 py-3.5 shadow-sm"
    x-data="{ showJson: false }"
>
    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $spec['name'] }}</h3>
            @if($spec['description'] !== '')
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $spec['description'] }}</p>
            @endif
        </div>
        <span class="flex-shrink-0 text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
            {{ $spec['mode'] }}
        </span>
    </div>

    <div class="grid grid-cols-4 sm:grid-cols-6 gap-2 mt-3">
        @foreach($spec['colors'] as $name => $hex)
            <div class="text-center">
                <div
                    class="w-full h-10 rounded-lg border border-gray-200 dark:border-gray-600"
                    style="background-color: {{ $hex }}"
                    title="{{ $name }} · {{ $hex }}"
                ></div>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 truncate">{{ str_replace('_', ' ', $name) }}</p>
                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-mono uppercase">{{ $hex }}</p>
            </div>
        @endforeach
    </div>

    @if(! empty($spec['typography']) || ! empty($spec['radius']))
        <dl class="flex flex-wrap gap-x-4 gap-y-1 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-[11px] text-gray-500 dark:text-gray-400">
            @foreach($spec['typography'] as $key => $value)
                <div class="flex gap-1">
                    <dt class="text-gray-400 dark:text-gray-500">{{ str_replace('_', ' ', $key) }}:</dt>
                    <dd class="text-gray-600 dark:text-gray-300">{{ $value }}</dd>
                </div>
            @endforeach
            @if(! empty($spec['radius']))
                <div class="flex gap-1">
                    <dt class="text-gray-400 dark:text-gray-500">radius:</dt>
                    <dd class="text-gray-600 dark:text-gray-300">{{ implode(' · ', $spec['radius']) }}</dd>
                </div>
            @endif
        </dl>
    @endif

    @if($spec['json'] !== '')
        <button
            type="button"
            x-on:click="showJson = ! showJson"
            class="mt-3 text-[11px] text-violet-600 hover:text-violet-700 font-medium"
            x-text="showJson ? 'Hide specification' : 'View specification'"
        >View specification</button>

        <pre
            x-show="showJson"
            x-cloak
            class="mt-2 p-3 rounded-lg bg-gray-900 text-gray-100 text-[11px] overflow-x-auto"
        >{{ $spec['json'] }}</pre>
    @endif
</div>
