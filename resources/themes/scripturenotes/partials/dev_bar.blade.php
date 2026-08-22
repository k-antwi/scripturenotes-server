{{-- Development debug bar --}}
<div x-data="{ open: false }" class="fixed bottom-0 left-0 right-0 z-[999] font-mono text-xs">
    {{-- Toggle bar --}}
    <div class="flex items-center justify-between px-4 py-1.5 border-t border-stone-300"
         style="background: #2c1810; color: #C9922F;">
        <div class="flex items-center gap-4">
            <span class="font-semibold" style="color: #C9922F;">📖 ScriptureNotes Dev</span>
            <span class="text-stone-400">{{ config('app.env') }}</span>
            @auth
            <span class="text-stone-400">User: {{ auth()->user()->email }}</span>
            @endauth
        </div>
        <button @click="open = !open"
                class="flex items-center gap-1 px-2 py-0.5 rounded text-stone-400 hover:text-white transition-colors">
            <span x-text="open ? 'Hide' : 'Show'">Show</span>
            <svg class="w-3 h-3 transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
        </button>
    </div>

    {{-- Expanded panel --}}
    <div x-show="open" x-cloak
         class="grid grid-cols-3 gap-4 px-4 py-3 border-t border-stone-700"
         style="background: #1e0f08; color: #e8ddd3;">

        {{-- Environment --}}
        <div>
            <p class="font-semibold mb-1" style="color: #C9922F;">Environment</p>
            <p>App: <span class="text-white">{{ config('app.name') }}</span></p>
            <p>Env: <span class="text-white">{{ config('app.env') }}</span></p>
            <p>Debug: <span class="{{ config('app.debug') ? 'text-red-400' : 'text-green-400' }}">{{ config('app.debug') ? 'ON' : 'OFF' }}</span></p>
            <p>PHP: <span class="text-white">{{ PHP_VERSION }}</span></p>
            <p>Laravel: <span class="text-white">{{ app()->version() }}</span></p>
        </div>

        {{-- Auth --}}
        <div>
            <p class="font-semibold mb-1" style="color: #C9922F;">Auth</p>
            @auth
            <p>ID: <span class="text-white">{{ auth()->id() }}</span></p>
            <p>Email: <span class="text-white">{{ auth()->user()->email }}</span></p>
            <p>Plan: <span class="text-white">{{ auth()->user()->plan?->name ?? 'None' }}</span></p>
            @else
            <p class="text-stone-500">Guest</p>
            @endauth
        </div>

        {{-- Request --}}
        <div>
            <p class="font-semibold mb-1" style="color: #C9922F;">Request</p>
            <p>Route: <span class="text-white">{{ request()->route()?->getName() ?? '-' }}</span></p>
            <p>URL: <span class="text-white truncate block max-w-xs">{{ request()->url() }}</span></p>
            <p>Method: <span class="text-white">{{ request()->method() }}</span></p>
        </div>
    </div>
</div>
