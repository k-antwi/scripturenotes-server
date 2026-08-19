<div class="p-6 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl space-y-4">
    <div class="flex items-center gap-2">
        <x-phosphor-buildings class="w-5 h-5 text-blue-600 dark:text-blue-400" />
        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100 uppercase tracking-wide">Companies House Search</h3>
    </div>
    <p class="text-xs text-blue-700 dark:text-blue-300">
        Search the UK Companies House register to auto-fill organisation details. You can still edit the fields after selection.
    </p>

    {{-- Search Input --}}
    <div class="flex gap-2">
        <div class="flex-1">
            <input
                wire:model="searchQuery"
                wire:keydown.enter.prevent="search"
                type="text"
                placeholder="Enter company name or number..."
                class="w-full px-4 py-2.5 text-sm border border-blue-200 rounded-lg bg-white dark:bg-zinc-900 dark:border-blue-700 dark:text-zinc-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            @error('searchQuery')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <button
            wire:click="search"
            wire:loading.attr="disabled"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <span wire:loading.remove wire:target="search">
                <x-phosphor-magnifying-glass class="w-4 h-4" />
            </span>
            <span wire:loading wire:target="search">
                <svg class="w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </span>
            <span wire:loading.remove wire:target="search">Search</span>
            <span wire:loading wire:target="search">Searching...</span>
        </button>
    </div>

    {{-- Error --}}
    @if($searchError)
        <div class="flex items-start gap-2 p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-lg">
            <x-phosphor-warning-circle class="w-4 h-4 text-red-500 shrink-0 mt-0.5" />
            <p class="text-xs text-red-700 dark:text-red-300">{{ $searchError }}</p>
        </div>
    @endif

    {{-- No Results --}}
    @if($hasSearched && empty($searchResults) && !$searchError)
        <div class="flex items-center gap-2 p-3 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg">
            <x-phosphor-info class="w-4 h-4 text-zinc-400 shrink-0" />
            <p class="text-xs text-zinc-500 dark:text-zinc-400">No companies found for "{{ $searchQuery }}". Please check the name or enter details manually.</p>
        </div>
    @endif

    {{-- Results List --}}
    @if(!empty($searchResults))
        <div class="space-y-2 max-h-72 overflow-y-auto">
            @foreach($searchResults as $company)
                <button
                    wire:click="selectCompany('{{ $company['company_number'] }}')"
                    wire:loading.attr="disabled"
                    wire:key="company-{{ $company['company_number'] }}"
                    type="button"
                    class="w-full text-left px-4 py-3 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg hover:border-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/30 focus:ring-2 focus:ring-blue-500 transition-colors"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                {{ $company['title'] ?? $company['company_name'] ?? 'Unknown' }}
                            </p>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                {{ $company['company_number'] }}
                                @if(!empty($company['address']['locality']))
                                    &middot; {{ $company['address']['locality'] }}
                                @endif
                                @if(!empty($company['address']['postal_code']))
                                    &middot; {{ $company['address']['postal_code'] }}
                                @endif
                            </p>
                        </div>
                        <span @class([
                            'shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                            'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' => ($company['company_status'] ?? '') === 'active',
                            'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' => ($company['company_status'] ?? '') === 'dissolved',
                            'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300' => !in_array($company['company_status'] ?? '', ['active', 'dissolved']),
                        ])>
                            {{ ucfirst($company['company_status'] ?? 'unknown') }}
                        </span>
                    </div>
                </button>
            @endforeach
        </div>

        <button wire:click="clearSearch" type="button" class="text-xs text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 underline">
            Clear results
        </button>
    @endif
</div>
