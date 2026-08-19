{{-- Generic three-step onboarding wizard. Forks restyle / restructure freely. --}}
<div class="max-w-2xl mx-auto p-6 space-y-6">
    @if ($submitted)
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6 text-center">
            <h2 class="text-xl font-semibold text-green-800 dark:text-green-200">Onboarding complete</h2>
            <p class="text-green-700 dark:text-green-300 mt-2">Redirecting…</p>
        </div>
    @else
        <div class="flex items-center gap-4 text-sm">
            <span @class(['font-semibold' => $step >= 1, 'text-gray-400' => $step < 1])>1. Personal</span>
            <span class="text-gray-300">→</span>
            <span @class(['font-semibold' => $step >= 2, 'text-gray-400' => $step < 2])>2. Address</span>
            <span class="text-gray-300">→</span>
            <span @class(['font-semibold' => $step >= 3, 'text-gray-400' => $step < 3])>3. Sign &amp; submit</span>
        </div>

        @if ($step === 1)
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Personal details</h2>

                <div class="grid grid-cols-2 gap-4">
                    <label>First name *
                        <input wire:model="firstName" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                        @error('firstName')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </label>
                    <label>Last name *
                        <input wire:model="lastName" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                        @error('lastName')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </label>
                </div>

                <label>Previous name
                    <input wire:model="previousName" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                </label>

                <div class="grid grid-cols-3 gap-4">
                    <label>Day *
                        <select wire:model="dobDay" class="w-full rounded border-gray-300 dark:bg-gray-800">
                            <option value="">—</option>
                            @foreach ($dobDays as $day)
                                <option value="{{ $day }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Month *
                        <select wire:model="dobMonth" class="w-full rounded border-gray-300 dark:bg-gray-800">
                            <option value="">—</option>
                            @foreach (range(1, 12) as $m)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Year *
                        <select wire:model="dobYear" class="w-full rounded border-gray-300 dark:bg-gray-800">
                            <option value="">—</option>
                            @foreach ($dobYears as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <label>Mobile
                    <input wire:model="mobile" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                </label>

                <label>National ID
                    <input wire:model="nationalId" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                </label>
            </div>
        @endif

        @if ($step === 2)
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Address</h2>

                @if (! $manualAddress)
                    <label>Postcode
                        <input wire:model="postcode" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                        @error('postcode')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </label>

                    <button type="button" wire:click="lookupAddress"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Search
                    </button>

                    @if (! empty($addressResults))
                        <label>Pick an address
                            <select wire:model="selectedAddressId" class="w-full rounded border-gray-300 dark:bg-gray-800">
                                <option value="">— select —</option>
                                @foreach ($addressResults as $addr)
                                    <option value="{{ $addr['id'] }}">{{ $addr['label'] ?? $addr['id'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="button" wire:click="selectAddress"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Use selected
                        </button>
                    @endif
                @else
                    <div class="grid grid-cols-2 gap-4">
                        <label>House number
                            <input wire:model="houseNumber" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                        </label>
                        <label>Postcode
                            <input wire:model="postcode" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                        </label>
                    </div>
                    <label>Address line 1 *
                        <input wire:model="addressLine1" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                        @error('addressLine1')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </label>
                    <label>Address line 2
                        <input wire:model="addressLine2" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                    </label>
                    <label>City *
                        <input wire:model="city" type="text" class="w-full rounded border-gray-300 dark:bg-gray-800">
                        @error('city')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                    </label>
                    <label>Proof of address (optional)
                        <input wire:model="proofFile" type="file" class="block">
                    </label>
                @endif
            </div>
        @endif

        @if ($step === 3)
            <div class="space-y-4">
                <h2 class="text-xl font-semibold">Sign &amp; submit</h2>

                @if ($requireDocumentApproval)
                    <details class="border rounded p-3">
                        <summary class="cursor-pointer">Review document</summary>
                        <textarea wire:model="documentApprovalContent" rows="6"
                                  class="w-full rounded border-gray-300 dark:bg-gray-800 mt-2"></textarea>
                    </details>
                @endif

                <label>Signature (base64 PNG data) *
                    <textarea wire:model="signatureData" rows="3"
                              placeholder="Wire up your preferred signature pad and feed the base64 result here"
                              class="w-full rounded border-gray-300 dark:bg-gray-800 font-mono text-xs"></textarea>
                    @error('signatureData')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                </label>
            </div>
        @endif

        <div class="flex justify-between pt-4 border-t">
            @if ($step > 1)
                <button type="button" wire:click="back"
                        class="px-4 py-2 border rounded hover:bg-gray-50 dark:hover:bg-gray-800">
                    Back
                </button>
            @else
                <span></span>
            @endif

            @if ($step < 3)
                <button type="button" wire:click="next"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Continue
                </button>
            @else
                <button type="button" wire:click="submit"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Submit
                </button>
            @endif
        </div>
    @endif
</div>
