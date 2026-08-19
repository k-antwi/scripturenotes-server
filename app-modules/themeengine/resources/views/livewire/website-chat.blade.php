<div
    class="flex flex-col h-[calc(100vh-10rem)] max-w-3xl mx-auto"
    x-data="{ pendingMessage: '' }"
    x-on:message-sent.window="pendingMessage = ''"
>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-violet-500" fill="currentColor" viewBox="0 0 256 256">
                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm88,104a87.56,87.56,0,0,1-4.92,29.05L189.65,144H207.8A88.31,88.31,0,0,1,216,128Zm-96,88a87.62,87.62,0,0,1-40.18-9.78L107,188.65a8,8,0,0,0,7.53,5.35h27a8,8,0,0,0,7.53-5.35l10.15-28.65A88,88,0,0,1,132,216Zm-20.29-56,8.8-24.84a87.34,87.34,0,0,0,14.94,0L144.29,160Zm55.88,0-7.7-21.77A88.63,88.63,0,0,0,168,128a88,88,0,0,0-3.54-24.65l14.28,14.28A8,8,0,0,0,190,112h27.53A87.43,87.43,0,0,1,207.8,144ZM40,128a87.43,87.43,0,0,1,19.73-55H87.88a8,8,0,0,0,5.66-2.34L108.12,56H120V40.2A88,88,0,0,0,40,128Zm16.2,16H75.34L64.16,173.53A87.68,87.68,0,0,1,56.2,144ZM83.71,160l7.7,21.77A88.63,88.63,0,0,0,88,196a88,88,0,0,0,3.54,24.65L77.26,206.37A8,8,0,0,0,66,212H48.47A87.43,87.43,0,0,1,40,157V144h27.8A87.56,87.56,0,0,1,83.71,160Z"/>
            </svg>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Create Website</h2>
            @if($activeModelLabel)
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300">{{ $activeModelLabel }}</span>
            @endif
        </div>
        <button
            wire:click="startNew"
            class="text-xs text-violet-600 hover:text-violet-700 font-medium"
        >New design</button>
    </div>

    @unless($activeModelLabel)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/30 px-4 py-3 text-sm text-amber-800 dark:text-amber-200">
            Website design is not currently available — no AI model is active. Activate one under Settings → AI Models in the admin panel.
        </div>
    @endunless

    {{-- Wizard --}}
    @if($wizardStep >= 1)

        {{-- Progress indicator --}}
        <div class="flex items-center justify-center gap-2 mb-6">
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full {{ $wizardStep == 1 ? 'bg-violet-500' : 'bg-violet-300' }}"></div>
                <span class="text-xs {{ $wizardStep == 1 ? 'text-violet-600 dark:text-violet-400 font-medium' : 'text-gray-400' }}">Framework</span>
            </div>
            <div class="w-12 h-px {{ $wizardStep >= 2 ? 'bg-violet-300' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full {{ $wizardStep == 2 ? 'bg-violet-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
                <span class="text-xs {{ $wizardStep == 2 ? 'text-violet-600 dark:text-violet-400 font-medium' : 'text-gray-400' }}">Details</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto pr-1">

            {{-- Step 1: Framework --}}
            @if($wizardStep == 1)
                <div class="max-w-lg mx-auto">
                    <p class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-1">Which framework would you like your theme in?</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">This determines how the components and styles are generated.</p>

                    <div class="grid grid-cols-2 gap-3">
                        <button
                            wire:click="selectFramework('vue3')"
                            class="group flex flex-col items-start gap-1.5 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 dark:hover:border-violet-500 transition-all text-left"
                        >
                            <span class="text-xl">⚡</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Vue 3</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">SFC Components</span>
                        </button>

                        <button
                            wire:click="selectFramework('react')"
                            class="group flex flex-col items-start gap-1.5 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 dark:hover:border-violet-500 transition-all text-left"
                        >
                            <span class="text-xl">⚛️</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">React</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">JSX Components</span>
                        </button>

                        <button
                            wire:click="selectFramework('livewire-volt')"
                            class="group flex flex-col items-start gap-1.5 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 dark:hover:border-violet-500 transition-all text-left"
                        >
                            <span class="text-xl">🔴</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Livewire Volt</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">Blade + Alpine.js</span>
                        </button>

                        <button
                            wire:click="selectFramework('plain-html')"
                            class="group flex flex-col items-start gap-1.5 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 dark:hover:border-violet-500 transition-all text-left"
                        >
                            <span class="text-xl">🌐</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Plain HTML</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">Vanilla HTML / CSS / JS</span>
                        </button>

                        <button
                            wire:click="selectFramework('other')"
                            class="group flex flex-col items-start gap-1.5 p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/20 dark:hover:border-violet-500 transition-all text-left col-span-2"
                        >
                            <span class="text-xl">✏️</span>
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Other</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">Specify your own framework</span>
                        </button>
                    </div>

                    {{-- Other: custom input --}}
                    @if($framework == 'other')
                        <div class="mt-4 flex gap-2 items-center">
                            <input
                                wire:model="customFramework"
                                type="text"
                                placeholder="e.g. Svelte, Angular, Astro…"
                                class="flex-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500"
                                x-on:keydown.enter="$wire.confirmOtherFramework()"
                                autofocus
                            />
                            <button
                                wire:click="confirmOtherFramework"
                                class="px-4 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-violet-600 dark:hover:bg-violet-700 text-white text-sm font-medium rounded-xl transition"
                            >Continue →</button>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Step 2: Context --}}
            @if($wizardStep == 2)
                <div class="max-w-lg mx-auto">
                    <div class="mb-5">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="text-base font-semibold text-gray-800 dark:text-gray-100">A few quick questions</p>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">All optional</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Answer as many or as few as you like — the more you share, the better the result.</p>
                    </div>

                    <div class="space-y-4">
                        {{-- Website type --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                Website type
                            </label>
                            <input
                                wire:model="websiteType"
                                type="text"
                                placeholder="e.g. business website, online store, blog, portfolio…"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 placeholder-gray-400"
                            />
                        </div>

                        {{-- Business type --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                Business type
                            </label>
                            <input
                                wire:model="businessType"
                                type="text"
                                placeholder="e.g. restaurant, law firm, clothing store, hair salon…"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 placeholder-gray-400"
                            />
                        </div>

                        {{-- Main objective --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                Main objective
                            </label>
                            <input
                                wire:model="mainObjective"
                                type="text"
                                placeholder="e.g. generate leads, sell products, book appointments…"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 placeholder-gray-400"
                            />
                        </div>

                        {{-- Logo upload --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                Logo
                            </label>
                            <div class="relative">
                                <label class="flex items-center gap-3 w-full rounded-xl border border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 cursor-pointer hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-900/10 transition-colors">
                                    <input
                                        type="file"
                                        wire:model="logo"
                                        accept="image/*"
                                        class="sr-only"
                                    />
                                    @if($logoUploaded)
                                        <svg class="w-4 h-4 text-violet-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-sm text-violet-600 dark:text-violet-400">Logo uploaded — brand colors will be extracted</span>
                                    @else
                                        <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span class="text-sm text-gray-400">Upload your logo to extract brand colors</span>
                                    @endif
                                </label>
                                <div wire:loading wire:target="logo" class="absolute inset-0 flex items-center justify-center bg-white/70 dark:bg-gray-800/70 rounded-xl">
                                    <svg class="w-4 h-4 animate-spin text-violet-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </div>
                            </div>
                        </div>

                        {{-- Inspiration URL --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1.5">
                                Inspiration
                            </label>
                            <input
                                wire:model="inspirationUrl"
                                type="url"
                                placeholder="https://example.com — a site or theme you'd like to draw from"
                                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-500 placeholder-gray-400"
                            />
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-3 mt-6 pb-4">
                        <button
                            wire:click="backToStep1"
                            class="px-4 py-2.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 border border-gray-200 dark:border-gray-700 rounded-xl hover:border-gray-300 dark:hover:border-gray-600 transition"
                        >← Back</button>
                        <button
                            wire:click="submitWizard"
                            wire:loading.attr="disabled"
                            wire:target="submitWizard"
                            class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-gray-900 hover:bg-gray-800 dark:bg-violet-600 dark:hover:bg-violet-700 disabled:opacity-50 text-white text-sm font-medium rounded-xl transition"
                        >
                            <span wire:loading.remove wire:target="submitWizard">Generate website →</span>
                            <span wire:loading wire:target="submitWizard" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Generating…
                            </span>
                        </button>
                    </div>
                </div>
            @endif

        </div>

    {{-- Chat view (after wizard) --}}
    @else

        {{-- Messages --}}
        <div
            class="flex-1 overflow-y-auto space-y-4 mb-4 pr-1"
            id="website-messages"
            x-on:message-sent.window="$el.scrollTop = $el.scrollHeight"
        >
            @foreach($messages as $message)
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
                                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm88,104a87.56,87.56,0,0,1-4.92,29.05L189.65,144H207.8A88.31,88.31,0,0,1,216,128Zm-96,88a87.62,87.62,0,0,1-40.18-9.78L107,188.65a8,8,0,0,0,7.53,5.35h27a8,8,0,0,0,7.53-5.35l10.15-28.65A88,88,0,0,1,132,216Zm-20.29-56,8.8-24.84a87.34,87.34,0,0,0,14.94,0L144.29,160Zm55.88,0-7.7-21.77A88.63,88.63,0,0,0,168,128a88,88,0,0,0-3.54-24.65l14.28,14.28A8,8,0,0,0,190,112h27.53A87.43,87.43,0,0,1,207.8,144ZM40,128a87.43,87.43,0,0,1,19.73-55H87.88a8,8,0,0,0,5.66-2.34L108.12,56H120V40.2A88,88,0,0,0,40,128Zm16.2,16H75.34L64.16,173.53A87.68,87.68,0,0,1,56.2,144ZM83.71,160l7.7,21.77A88.63,88.63,0,0,0,88,196a88,88,0,0,0,3.54,24.65L77.26,206.37A8,8,0,0,0,66,212H48.47A87.43,87.43,0,0,1,40,157V144h27.8A87.56,87.56,0,0,1,83.71,160Z"/>
                            </svg>
                        </div>
                        <div class="max-w-[80%]">
                            @if(trim($message['content']) !== '')
                                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-sm px-4 py-2.5 shadow-sm">
                                    <div class="prose prose-sm dark:prose-invert max-w-none text-sm">
                                        {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

            @if($loading)
                <div class="flex justify-start gap-2">
                    <div class="w-7 h-7 rounded-full bg-violet-100 dark:bg-violet-900 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-violet-600 dark:text-violet-300" fill="currentColor" viewBox="0 0 256 256">
                            <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm88,104a87.56,87.56,0,0,1-4.92,29.05L189.65,144H207.8A88.31,88.31,0,0,1,216,128Zm-96,88a87.62,87.62,0,0,1-40.18-9.78L107,188.65a8,8,0,0,0,7.53,5.35h27a8,8,0,0,0,7.53-5.35l10.15-28.65A88,88,0,0,1,132,216Zm-20.29-56,8.8-24.84a87.34,87.34,0,0,0,14.94,0L144.29,160Zm55.88,0-7.7-21.77A88.63,88.63,0,0,0,168,128a88,88,0,0,0-3.54-24.65l14.28,14.28A8,8,0,0,0,190,112h27.53A87.43,87.43,0,0,1,207.8,144ZM40,128a87.43,87.43,0,0,1,19.73-55H87.88a8,8,0,0,0,5.66-2.34L108.12,56H120V40.2A88,88,0,0,0,40,128Zm16.2,16H75.34L64.16,173.53A87.68,87.68,0,0,1,56.2,144ZM83.71,160l7.7,21.77A88.63,88.63,0,0,0,88,196a88,88,0,0,0,3.54,24.65L77.26,206.37A8,8,0,0,0,66,212H48.47A87.43,87.43,0,0,1,40,157V144h27.8A87.56,87.56,0,0,1,83.71,160Z"/>
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

        {{-- Follow-up input --}}
        <form wire:submit="generate" class="flex gap-2 items-end" x-on:submit="pendingMessage = $wire.input">
            <div class="flex-1 relative">
                <textarea
                    wire:model="input"
                    rows="2"
                    placeholder="Ask a follow-up or request changes…"
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
        <p class="text-xs text-gray-400 mt-1 text-center">Ctrl+Enter to send</p>

    @endif
</div>
