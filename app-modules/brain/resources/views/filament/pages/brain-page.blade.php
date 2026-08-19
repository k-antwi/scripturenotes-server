<x-filament-panels::page>
    <div class="flex gap-4 h-[calc(100vh-12rem)]">

        {{-- Sidebar: past conversations --}}
        <aside class="w-64 flex-shrink-0 flex flex-col gap-2 overflow-y-auto border-r border-gray-200 dark:border-gray-700 pr-3">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Conversations</span>
                <button
                    wire:click="newConversation"
                    class="text-xs text-primary-600 hover:text-primary-700 font-medium"
                >+ New</button>
            </div>

            @forelse($pastConversations as $conv)
                <button
                    wire:click="loadConversation('{{ $conv['id'] }}')"
                    class="text-left px-2 py-1.5 rounded text-sm truncate {{ $conversationId === ($conv['id'] ?? '') ? 'bg-primary-50 dark:bg-primary-900 text-primary-700 dark:text-primary-300 font-medium' : 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300' }}"
                >
                    {{ \Illuminate\Support\Str::limit($conv['title'] ?? 'New conversation', 40) }}
                </button>
            @empty
                <p class="text-xs text-gray-400">No conversations yet.</p>
            @endforelse
        </aside>

        {{-- Main panel --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Active model badge --}}
            <div class="flex items-center gap-2 mb-3">
                <x-phosphor-brain-duotone class="w-4 h-4 text-primary-500" />
                <span class="text-xs text-gray-500">Active model:</span>
                <span class="text-xs font-semibold text-primary-600">{{ $activeModelLabel }}</span>
            </div>

            {{-- Message history --}}
            <div class="flex-1 overflow-y-auto space-y-4 mb-4" id="brain-messages">
                @forelse($messages as $message)
                    @if($message['role'] === 'user')
                        <div class="flex justify-end">
                            <div class="max-w-[75%] bg-primary-600 text-white rounded-xl px-4 py-2 text-sm">
                                {{ $message['content'] }}
                            </div>
                        </div>
                    @else
                        <div class="flex justify-start">
                            <div class="max-w-[85%] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
                                {{-- Tabs --}}
                                <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs">
                                    <button
                                        wire:click="$set('activeTab', 'response')"
                                        class="px-3 py-1.5 {{ $activeTab === 'response' ? 'border-b-2 border-primary-500 text-primary-600 font-medium' : 'text-gray-500 hover:text-gray-700' }}"
                                    >Response</button>
                                    @if(!empty($message['reasoning']))
                                    <button
                                        wire:click="$set('activeTab', 'reasoning')"
                                        class="px-3 py-1.5 {{ $activeTab === 'reasoning' ? 'border-b-2 border-primary-500 text-primary-600 font-medium' : 'text-gray-500 hover:text-gray-700' }}"
                                    >Reasoning</button>
                                    @endif
                                    @if(!empty($message['artifacts']))
                                    <button
                                        wire:click="$set('activeTab', 'artifacts')"
                                        class="px-3 py-1.5 {{ $activeTab === 'artifacts' ? 'border-b-2 border-primary-500 text-primary-600 font-medium' : 'text-gray-500 hover:text-gray-700' }}"
                                    >Artifacts ({{ count($message['artifacts']) }})</button>
                                    @endif
                                </div>

                                <div class="p-4 text-sm text-gray-900 dark:text-gray-100">
                                    @if($activeTab === 'response')
                                        <div class="prose prose-sm dark:prose-invert max-w-none text-gray-900 dark:text-gray-100">
                                            {!! \Illuminate\Support\Str::markdown($message['content']) !!}
                                        </div>
                                    @elseif($activeTab === 'reasoning' && !empty($message['reasoning']))
                                        <div class="text-gray-500 dark:text-gray-400 font-mono text-xs whitespace-pre-wrap leading-relaxed">
                                            {{ $message['reasoning'] }}
                                        </div>
                                    @elseif($activeTab === 'artifacts' && !empty($message['artifacts']))
                                        <div class="space-y-3">
                                            @foreach($message['artifacts'] as $artifact)
                                                <div>
                                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1 block">{{ $artifact['type'] }}</span>
                                                    <pre class="bg-gray-900 text-green-300 rounded p-3 overflow-x-auto text-xs leading-relaxed"><code>{{ $artifact['content'] }}</code></pre>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 py-12">
                        <x-phosphor-brain-duotone class="w-12 h-12 mb-3 text-gray-300" />
                        <p class="text-sm">Start a conversation with the AI.</p>
                    </div>
                @endforelse

                @if($loading)
                    <div class="flex justify-start">
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3">
                            <div class="flex gap-1 items-center">
                                <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce [animation-delay:0ms]"></span>
                                <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce [animation-delay:150ms]"></span>
                                <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce [animation-delay:300ms]"></span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Prompt input --}}
            <form wire:submit="send" class="flex gap-2">
                <textarea
                    wire:model.live="prompt"
                    wire:keydown.ctrl.enter="send"
                    rows="3"
                    placeholder="Enter your prompt… (Ctrl+Enter to send)"
                    class="flex-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm px-3 py-2 resize-none focus:outline-none focus:ring-2 focus:ring-primary-500"
                    @if($loading) disabled @endif
                ></textarea>
                <button
                    type="submit"
                    class="self-end px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-xl transition disabled:opacity-50"
                    @if($loading || !trim($prompt)) disabled @endif
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove>Send</span>
                    <span wire:loading>...</span>
                </button>
            </form>

        </div>
    </div>

    <script>
        document.addEventListener('livewire:updated', () => {
            const el = document.getElementById('brain-messages');
            if (el) el.scrollTop = el.scrollHeight;
        });
    </script>
</x-filament-panels::page>
