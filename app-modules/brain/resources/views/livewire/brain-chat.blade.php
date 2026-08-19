<div
    class="flex flex-col h-[calc(100vh-10rem)] max-w-3xl mx-auto"
    x-data="{ pendingMessage: '' }"
    x-on:message-sent.window="pendingMessage = ''"
>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="currentColor" viewBox="0 0 256 256"><!-- phosphor brain icon placeholder --></svg>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ config('brain.assistant_title', 'Assistant') }}</h2>
            @if($activeModelLabel)
                <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-full px-2 py-0.5">{{ $activeModelLabel }}</span>
            @endif
        </div>
        <button
            wire:click="startNew"
            class="text-xs text-indigo-600 hover:text-indigo-700 font-medium"
        >New conversation</button>
    </div>

    @if(!$activeModelLabel)
        <div class="rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 text-sm mb-4">
            The AI assistant is not currently available. Please contact your administrator.
        </div>
    @endif

    {{-- Past conversations --}}
    @if(!empty($pastConversations) && empty($messages))
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Recent conversations</p>
            <div class="flex flex-wrap gap-2">
                @foreach(array_slice($pastConversations, 0, 5) as $conv)
                    <button
                        wire:click="loadConversation('{{ $conv['id'] }}')"
                        class="text-xs px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-full text-gray-600 dark:text-gray-300 truncate max-w-[200px]"
                    >{{ \Illuminate\Support\Str::limit($conv['title'] ?? 'Conversation', 35) }}</button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Messages --}}
    <div
        class="flex-1 overflow-y-auto space-y-4 mb-4 pr-1"
        id="brain-chat-messages"
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
                    <div class="flex-shrink-0 w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mt-1">
                        <span class="text-indigo-600 dark:text-indigo-300 text-xs font-bold">AI</span>
                    </div>
                    <div class="max-w-[80%] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-sm px-4 py-2.5 shadow-sm">
                        <div class="prose prose-sm dark:prose-invert max-w-none text-sm">
                            {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                        </div>

                        {{-- Artifacts --}}
                        @if(!empty($message['artifacts']))
                            <div class="mt-3 space-y-2 border-t border-gray-100 dark:border-gray-700 pt-2">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Generated artifacts</p>
                                @foreach($message['artifacts'] as $artifact)
                                    <div>
                                        <span class="text-xs text-gray-400 font-mono">{{ $artifact['type'] }}</span>
                                        <pre class="mt-1 bg-gray-900 text-green-300 rounded-lg p-3 overflow-x-auto text-xs leading-relaxed"><code>{{ $artifact['content'] }}</code></pre>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="flex flex-col items-center justify-center h-full py-16 text-center">
                <div class="w-12 h-12 rounded-full bg-indigo-50 dark:bg-indigo-900 flex items-center justify-center mb-3">
                    <span class="text-indigo-500 text-xl font-bold">AI</span>
                </div>
                <p class="text-gray-600 dark:text-gray-300 font-medium">How can I help you today?</p>
                <p class="text-sm text-gray-400 mt-1">Ask me anything about your account.</p>
                <div class="mt-4 flex flex-wrap gap-2 justify-center">
                    <button wire:click="$set('input', 'Show me my profile.')" class="text-xs px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-full hover:bg-indigo-100">Show me my profile</button>
                    <button wire:click="$set('input', 'What does my KYC status mean?')" class="text-xs px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 rounded-full hover:bg-indigo-100">What does my KYC status mean?</button>
                </div>
            </div>
        @endforelse

        {{-- Optimistic user message shown while AI is processing --}}
        <template x-if="pendingMessage">
            <div class="flex justify-end">
                <div class="max-w-[75%] bg-gray-900 text-white rounded-2xl rounded-br-sm px-4 py-2.5 text-sm shadow-sm" x-text="pendingMessage"></div>
            </div>
        </template>

        @if($loading)
            <div class="flex justify-start gap-2">
                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center flex-shrink-0">
                    <span class="text-indigo-600 dark:text-indigo-300 text-xs font-bold">AI</span>
                </div>
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm">
                    <div class="flex gap-1 items-center">
                        <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce [animation-delay:0ms]"></span>
                        <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce [animation-delay:150ms]"></span>
                        <span class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce [animation-delay:300ms]"></span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Input --}}
    <form wire:submit="send" class="flex gap-2 items-end" x-on:submit="pendingMessage = $wire.input">
        <div class="flex-1 relative">
            <textarea
                wire:model="input"
                rows="2"
                placeholder="Ask anything…"
                class="w-full rounded-2xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm px-4 py-3 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-12"
                @if($loading || !$activeModelLabel) disabled @endif
                x-on:keydown.ctrl.enter="pendingMessage = $wire.input; $wire.send()"
                x-on:keydown.meta.enter="pendingMessage = $wire.input; $wire.send()"
            ></textarea>
        </div>
        <button
            type="submit"
            wire:loading.attr="disabled"
            @if($loading || !$activeModelLabel) disabled @endif
            class="flex-shrink-0 px-4 py-3 bg-gray-900 hover:bg-gray-800 disabled:opacity-50 text-white text-sm font-medium rounded-2xl transition"
        >
            <span wire:loading.remove wire:target="send">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </span>
            <span wire:loading wire:target="send">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </span>
        </button>
    </form>
    <p class="text-xs text-gray-400 mt-1 text-center">Ctrl+Enter to send · AI responses may not always be accurate</p>
</div>
