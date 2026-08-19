<div
    class="flex flex-col h-[calc(100vh-10rem)] max-w-3xl mx-auto"
    x-data="{ pendingMessage: '' }"
    x-on:message-sent.window="pendingMessage = ''"
>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-violet-500" fill="currentColor" viewBox="0 0 256 256">
                <path d="M200.77,53.89A103.27,103.27,0,0,0,128,24h-1.07A104,104,0,0,0,24,128c0,43,26.58,81.19,67.89,98.77A8,8,0,0,0,104,220.13V196a12,12,0,0,1,24,0v24.13a8,8,0,0,0,12.11,6.64C181.42,209.19,208,171,208,128A103.3,103.3,0,0,0,200.77,53.89ZM96,120a12,12,0,1,1,12,12A12,12,0,0,1,96,120Zm52,12a12,12,0,1,1,12-12A12,12,0,0,1,148,132Z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Theme Engine</h2>
            @if($activeModelLabel)
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300">{{ $activeModelLabel }}</span>
            @endif
        </div>
        <button
            wire:click="startNew"
            class="text-xs text-violet-600 hover:text-violet-700 font-medium"
        >New theme</button>
    </div>

    @unless($activeModelLabel)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
            Theme generation is not currently available — no AI model is active. Activate one under Settings → AI Models in the admin panel.
        </div>
    @endunless

    {{-- Messages --}}
    <div
        class="flex-1 overflow-y-auto space-y-4 mb-4 pr-1"
        id="themeengine-messages"
        x-on:message-sent.window="$el.scrollTop = $el.scrollHeight"
    >
        @forelse($messages as $message)
            @if($message['role'] === 'user')
                <div class="flex justify-end">
                    <div class="max-w-[75%] bg-gray-900 text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm shadow-sm">
                        {{ $message['content'] }}
                    </div>
                </div>
            @else
                <div class="flex justify-start gap-2">
                    <div class="flex-shrink-0 w-7 h-7 rounded-full bg-violet-100 dark:bg-violet-900 flex items-center justify-center mt-1">
                        <svg class="w-3.5 h-3.5 text-violet-600 dark:text-violet-300" fill="currentColor" viewBox="0 0 256 256">
                            <path d="M200.77,53.89A103.27,103.27,0,0,0,128,24h-1.07A104,104,0,0,0,24,128c0,43,26.58,81.19,67.89,98.77A8,8,0,0,0,104,220.13V196a12,12,0,0,1,24,0v24.13a8,8,0,0,0,12.11,6.64C181.42,209.19,208,171,208,128A103.3,103.3,0,0,0,200.77,53.89ZM96,120a12,12,0,1,1,12,12A12,12,0,0,1,96,120Zm52,12a12,12,0,1,1,12-12A12,12,0,0,1,148,132Z"/>
                        </svg>
                    </div>
                    <div class="max-w-[80%] space-y-3">
                        @if(trim($message['content']) !== '')
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-sm px-4 py-2.5 shadow-sm">
                                <div class="prose prose-sm dark:prose-invert max-w-none text-sm">
                                    {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                </div>
                            </div>
                        @endif

                        @if(! empty($message['spec']))
                            @include('themeengine::partials.theme-preview', ['spec' => $message['spec']])
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-violet-50 dark:bg-violet-900 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-violet-500" fill="currentColor" viewBox="0 0 256 256">
                        <path d="M200.77,53.89A103.27,103.27,0,0,0,128,24h-1.07A104,104,0,0,0,24,128c0,43,26.58,81.19,67.89,98.77A8,8,0,0,0,104,220.13V196a12,12,0,0,1,24,0v24.13a8,8,0,0,0,12.11,6.64C181.42,209.19,208,171,208,128A103.3,103.3,0,0,0,200.77,53.89ZM96,120a12,12,0,1,1,12,12A12,12,0,0,1,96,120Zm52,12a12,12,0,1,1,12-12A12,12,0,0,1,148,132Z"/>
                    </svg>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-medium">Describe the theme you want to generate</p>
                <p class="text-sm text-gray-400 mt-1">Tell us the style, colours, and mood you have in mind.</p>
                <div class="mt-4 flex flex-wrap gap-2 justify-center">
                    <button wire:click="$set('input', 'Modern dark dashboard with deep navy and electric blue accents')" class="text-xs px-3 py-1.5 bg-violet-50 dark:bg-violet-900 text-violet-600 dark:text-violet-300 rounded-full hover:bg-violet-100 dark:hover:bg-violet-800 transition-colors">Modern dark dashboard</button>
                    <button wire:click="$set('input', 'Clean minimal light theme with warm grey tones and subtle green')" class="text-xs px-3 py-1.5 bg-violet-50 dark:bg-violet-900 text-violet-600 dark:text-violet-300 rounded-full hover:bg-violet-100 dark:hover:bg-violet-800 transition-colors">Clean minimal light</button>
                    <button wire:click="$set('input', 'Vibrant e-commerce theme with bold coral and fresh white')" class="text-xs px-3 py-1.5 bg-violet-50 dark:bg-violet-900 text-violet-600 dark:text-violet-300 rounded-full hover:bg-violet-100 dark:hover:bg-violet-800 transition-colors">Vibrant e-commerce</button>
                </div>
            </div>
        @endforelse

        {{-- Optimistic user bubble while generating --}}
        <template x-if="pendingMessage">
            <div class="flex justify-end">
                <div class="max-w-[75%] bg-gray-900 text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm shadow-sm" x-text="pendingMessage"></div>
            </div>
        </template>

        @if($loading)
            <div class="flex justify-start gap-2">
                <div class="w-7 h-7 rounded-full bg-violet-100 dark:bg-violet-900 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3.5 h-3.5 text-violet-600 dark:text-violet-300" fill="currentColor" viewBox="0 0 256 256">
                        <path d="M200.77,53.89A103.27,103.27,0,0,0,128,24h-1.07A104,104,0,0,0,24,128c0,43,26.58,81.19,67.89,98.77A8,8,0,0,0,104,220.13V196a12,12,0,0,1,24,0v24.13a8,8,0,0,0,12.11,6.64C181.42,209.19,208,171,208,128A103.3,103.3,0,0,0,200.77,53.89ZM96,120a12,12,0,1,1,12,12A12,12,0,0,1,96,120Zm52,12a12,12,0,1,1,12-12A12,12,0,0,1,148,132Z"/>
                    </svg>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm">
                    <div class="flex gap-1 items-center">
                        <span class="w-2 h-2 bg-violet-400 rounded-full animate-bounce [animation-delay:0ms]"></span>
                        <span class="w-2 h-2 bg-violet-400 rounded-full animate-bounce [animation-delay:150ms]"></span>
                        <span class="w-2 h-2 bg-violet-400 rounded-full animate-bounce [animation-delay:300ms]"></span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Input --}}
    <form wire:submit="generate" class="flex gap-2 items-end" x-on:submit="pendingMessage = $wire.input">
        <div class="flex-1 relative">
            <textarea
                wire:model="input"
                rows="2"
                placeholder="Describe the type of theme you want to generate…"
                class="w-full rounded-2xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-4 py-3 resize-none focus:outline-none focus:ring-2 focus:ring-violet-500"
                @if($loading) disabled @endif
                x-on:keydown.ctrl.enter="pendingMessage = $wire.input; $wire.generate()"
                x-on:keydown.meta.enter="pendingMessage = $wire.input; $wire.generate()"
            ></textarea>
        </div>
        <button
            type="submit"
            wire:loading.attr="disabled"
            @if($loading) disabled @endif
            class="flex-shrink-0 px-4 py-3 bg-gray-900 hover:bg-gray-800 disabled:opacity-50 text-white text-sm font-medium rounded-2xl transition"
        >
            <span wire:loading.remove wire:target="generate">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </span>
            <span wire:loading wire:target="generate">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </span>
        </button>
    </form>
    <p class="text-xs text-gray-400 mt-1 text-center">Ctrl+Enter to generate · Themes are generated based on your description</p>
</div>
