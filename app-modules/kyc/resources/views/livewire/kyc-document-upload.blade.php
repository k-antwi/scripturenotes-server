<div class="w-full max-w-lg">
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">

        {{-- Step 3: Success --}}
        @if($step === 3)
        <div class="p-8">
            <div class="w-full px-4 py-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="font-semibold text-green-800 dark:text-green-300 text-sm">
                    You have successfully uploaded the {{ strtolower($label) }}. Click on Done to go
                    back to your dashboard.
                </p>
            </div>
            <div class="mt-6 flex justify-end">
                <a href="/dashboard" wire:navigate
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800 transition-colors">
                    Done
                </a>
            </div>
        </div>

        {{-- Step 2: Preview & Confirm --}}
        @elseif($step === 2 && $filePath)
        <div class="p-8 space-y-6">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $label }} upload</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Please review the document below and confirm it is correct.
                </p>
            </div>

            {{-- Tips (collapsible) --}}
            <div x-data="{ open: false }" class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                <button @click="open = !open" type="button"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <span>Tips</span>
                    <x-phosphor-caret-down class="w-4 h-4 transition-transform" ::class="open && 'rotate-180'" />
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4 space-y-3">
                    @if(!empty($tips['what']))
                    <div>
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">What we accept:</p>
                        <ul class="text-xs text-zinc-500 dark:text-zinc-400 space-y-0.5 list-disc list-inside">
                            @foreach($tips['what'] as $tip)
                            <li>{{ $tip }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if(!empty($tips['keep_in_mind']))
                    <div>
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Keep in mind:</p>
                        <ul class="text-xs text-zinc-500 dark:text-zinc-400 space-y-0.5 list-disc list-inside">
                            @foreach($tips['keep_in_mind'] as $tip)
                            <li>{{ $tip }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Preview --}}
            <div>
                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Current {{ strtolower($label) }}</p>
                <div class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                    @if($isImage)
                    <img src="{{ asset('storage/' . $filePath) }}" alt="{{ $label }}"
                        class="w-full max-h-64 object-contain bg-zinc-50 dark:bg-zinc-800" />
                    @else
                    <div class="flex items-center gap-3 p-4 bg-zinc-50 dark:bg-zinc-800">
                        <x-phosphor-file-pdf class="w-8 h-8 text-red-500 flex-shrink-0" />
                        <span class="text-sm text-zinc-600 dark:text-zinc-400 truncate">{{ basename($filePath) }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <button wire:click="removeDocument" type="button"
                    class="px-5 py-2.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 rounded-lg hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors">
                    Replace
                </button>
                <button wire:click="confirmDocument" type="button"
                    class="px-5 py-2.5 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800 transition-colors">
                    Confirm
                </button>
            </div>
        </div>

        {{-- Step 1: Upload --}}
        @else
        <div class="p-8 space-y-6">
            <div>
                <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ $label }} upload</h2>
                <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    @if($this->type === 'proof_of_address')
                    Providing proof of your address helps us confirm your identity. Upload a recent
                    utility bill or bank statement below.
                    @elseif($this->type === 'photo_id')
                    Take a photo of the front of your driver's licence or passport and upload it
                    below to verify your identity.
                    @else
                    If you have recently changed your name, upload a copy of your name change
                    certificate so we can update our records.
                    @endif
                </p>
            </div>

            {{-- Tips (collapsible) --}}
            <div x-data="{ open: false }" class="border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden">
                <button @click="open = !open" type="button"
                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                    <span>Tips</span>
                    <x-phosphor-caret-down class="w-4 h-4 transition-transform" ::class="open && 'rotate-180'" />
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4 space-y-3">
                    @if(!empty($tips['what']))
                    <div>
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">What we accept:</p>
                        <ul class="text-xs text-zinc-500 dark:text-zinc-400 space-y-0.5 list-disc list-inside">
                            @foreach($tips['what'] as $tip)
                            <li>{{ $tip }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if(!empty($tips['keep_in_mind']))
                    <div>
                        <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Keep in mind:</p>
                        <ul class="text-xs text-zinc-500 dark:text-zinc-400 space-y-0.5 list-disc list-inside">
                            @foreach($tips['keep_in_mind'] as $tip)
                            <li>{{ $tip }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Current status --}}
            <div>
                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Current {{ strtolower($label) }}</p>
                <div class="px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center gap-2">
                        <x-phosphor-info class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                        <span class="text-sm text-blue-700 dark:text-blue-300">You haven't uploaded any {{ strtolower($label) }} yet</span>
                    </div>
                </div>
            </div>

            {{-- File upload --}}
            <div>
                <label for="file-upload-{{ $this->type }}"
                    class="flex flex-col items-center justify-center w-full px-4 py-6 border-2 border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                    <x-phosphor-upload-simple class="w-6 h-6 text-zinc-400 mb-2" />
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Upload {{ strtolower($label) }}</span>
                    <span class="text-xs text-zinc-400 mt-1">JPG, PNG or PDF — max 5 MB</span>
                    <input wire:model="file" type="file" id="file-upload-{{ $this->type }}"
                        accept=".jpg,.jpeg,.png,.pdf" class="sr-only" />
                </label>
                @error('file')
                <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
                @enderror

                {{-- Upload progress --}}
                <div wire:loading wire:target="file" class="mt-3">
                    <div class="flex items-center gap-2 text-sm text-zinc-500">
                        <svg class="w-4 h-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Uploading...
                    </div>
                </div>

                {{-- Temporary preview --}}
                @if($file && !$errors->has('file'))
                <div class="mt-3 flex items-center gap-3 px-3 py-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <x-phosphor-file class="w-5 h-5 text-zinc-400 flex-shrink-0" />
                    <span class="text-sm text-zinc-600 dark:text-zinc-400 truncate">{{ $file->getClientOriginalName() }}</span>
                    <span class="text-xs text-zinc-400 ml-auto flex-shrink-0">{{ number_format($file->getSize() / 1024, 0) }} KB</span>
                </div>
                @endif
            </div>

            {{-- Next --}}
            <div class="flex justify-end">
                <button wire:click="uploadDocument" type="button"
                    @if(!$file || $errors->has('file')) disabled @endif
                    class="px-6 py-2.5 text-sm font-medium text-white bg-blue-900 rounded-lg hover:bg-blue-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    Next
                </button>
            </div>
        </div>
        @endif

    </div>
</div>