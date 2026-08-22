<?php
use function Laravel\Folio\{middleware, name};
use function Livewire\Volt\{state, mount};

middleware(['web', 'auth']);
name('dashboard');

state(['user' => null, 'recentNotes' => [], 'verseOfDay' => null]);

mount(function () {
    $this->user = auth()->user();

    // Recent notes — assumes a Note model exists on the user
    $this->recentNotes = method_exists($this->user, 'notes')
        ? $this->user->notes()->latest()->limit(5)->get()
        : collect();

    // Verse of the day — static fallback; real apps would use an API or model
    $this->verseOfDay = [
        'reference' => 'Philippians 4:13',
        'text'      => 'I can do all things through Christ who strengthens me.',
        'version'   => 'NKJV',
    ];
});
?>
<x-layouts.app>
    @volt
    <x-app.container x-data x-cloak class="space-y-6 lg:space-y-8">

        {{-- Onboarding progress (hidden when complete) --}}
        @include('theme::partials.onboarding-progress')

        {{-- Greeting --}}
        <div class="flex items-start justify-between">
            <div>
                <h1 class="font-serif text-2xl md:text-3xl font-bold text-stone-800">
                    Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
                    {{ $user->name ?? 'friend' }} 👋
                </h1>
                <p class="mt-1 text-sm text-stone-500">{{ now()->format('l, F j, Y') }}</p>
            </div>
            <a href="{{ route('notes.create') }}"
               class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl transition-all hover:opacity-90"
               style="background: linear-gradient(135deg, #4B6741, #3A5432);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Note
            </a>
        </div>

        {{-- Verse of the day --}}
        @if($verseOfDay)
        <div class="rounded-2xl p-5 relative overflow-hidden"
             style="background: linear-gradient(135deg, #4B6741 0%, #3A5432 50%, #2d4027 100%);">
            <div class="relative z-10">
                <p class="text-xs font-semibold uppercase tracking-widest text-white/60 mb-3">✨ Verse of the Day</p>
                <blockquote class="font-serif text-lg leading-relaxed text-white/95 italic">
                    "{{ $verseOfDay['text'] }}"
                </blockquote>
                <div class="mt-3 flex items-center justify-between">
                    <p class="text-sm font-semibold text-white/80">{{ $verseOfDay['reference'] }}</p>
                    <span class="text-xs text-white/50">{{ $verseOfDay['version'] }}</span>
                </div>
            </div>
            {{-- Decorative cross motif --}}
            <div class="absolute right-4 top-1/2 -translate-y-1/2 opacity-5 text-white">
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 100 100">
                    <rect x="40" y="5" width="20" height="90" rx="4"/>
                    <rect x="10" y="30" width="80" height="20" rx="4"/>
                </svg>
            </div>
        </div>
        @endif

        {{-- Quick stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['label' => 'Total Notes', 'value' => $user->notes_count ?? 0, 'icon' => '📝'],
                ['label' => 'Books Covered', 'value' => $user->books_count ?? 0, 'icon' => '📖'],
                ['label' => 'Day Streak', 'value' => ($user->streak ?? 0) . 'd', 'icon' => '🔥'],
                ['label' => 'Reading Plans', 'value' => $user->active_plans_count ?? 0, 'icon' => '📅'],
            ] as $stat)
            <div class="bg-white rounded-2xl p-4 border border-stone-100 shadow-sm">
                <p class="text-xl mb-1">{{ $stat['icon'] }}</p>
                <p class="text-2xl font-bold text-stone-800">{{ $stat['value'] }}</p>
                <p class="text-xs text-stone-500 mt-0.5">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Recent notes --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-serif text-lg font-semibold text-stone-800">Recent Notes</h2>
                <a href="{{ route('notes.index') }}"
                   class="text-xs font-medium transition-colors"
                   style="color: #4B6741;">
                    View all →
                </a>
            </div>

            @if($recentNotes->isEmpty())
            <div class="text-center py-12 rounded-2xl border-2 border-dashed border-stone-200 bg-stone-50/50">
                <p class="text-3xl mb-3">📖</p>
                <p class="text-sm font-semibold text-stone-700">Begin your Scripture journey</p>
                <p class="text-xs text-stone-400 mt-1">Your first note is just a verse away.</p>
                <a href="{{ route('notes.create') }}"
                   class="inline-flex mt-4 px-4 py-2 text-sm font-semibold text-white rounded-xl transition-all hover:opacity-90"
                   style="background: linear-gradient(135deg, #4B6741, #3A5432);">
                    Write your first note
                </a>
            </div>
            @else
            <div class="space-y-2.5">
                @foreach($recentNotes as $note)
                <a href="{{ route('notes.show', $note) }}"
                   class="group flex items-center gap-4 p-4 bg-white rounded-2xl border border-stone-100
                          hover:border-stone-200 hover:shadow-sm transition-all">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-sm"
                         style="background: #f0f7ee;">
                        📝
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-stone-800 truncate group-hover:text-stone-900">
                            {{ $note->title ?? 'Untitled Note' }}
                        </p>
                        <p class="text-xs text-stone-400 mt-0.5">
                            {{ $note->verse_reference ?? '' }}
                            @if($note->verse_reference && $note->updated_at) · @endif
                            {{ $note->updated_at?->diffForHumans() }}
                        </p>
                    </div>
                    <svg class="w-4 h-4 text-stone-300 group-hover:text-stone-500 transition-colors flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Admin notice --}}
        @admin
        <x-app.message-for-admin />
        @endadmin

        {{-- Subscriber-only tip --}}
        @subscriber
        <x-app.message-for-subscriber />
        @endsubscriber

    </x-app.container>
    @endvolt
</x-layouts.app>
