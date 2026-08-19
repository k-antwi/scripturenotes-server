<?php

use function Laravel\Folio\{middleware, name};

middleware(['auth', 'onboarding']);
name('dashboard');
?>

<x-layouts.app>
    <div x-data class="space-y-6" x-cloak>

        {{-- Breadcrumb --}}
        <nav class="text-sm text-gray-400 font-medium">
            <span class="text-gray-800">Overview</span>
        </nav>

        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-400 mt-1">Welcome back, {{ auth()->user()->name }}</p>
            </div>
        </div>

        @php
        $user = auth()->user();
        $kycVerification = \Nucleus\Kyc\Models\KycVerification::where('user_id', $user->id)->first();
        $pendingDocs = $kycVerification ? $kycVerification->pendingDocumentTypes() : [];
        $needsLiveness = $kycVerification && $kycVerification->needsLivenessCheck();
        $kycStatus = $kycVerification?->status ?? 'not_started';
        $onboarding = \Nucleus\Onboarding\Models\OnboardingSubmission::where('user_id', $user->id)->first();
        $onboardingComplete = $onboarding && $onboarding->isSubmitted();
        @endphp

        {{-- Action Required --}}
        @if(!empty($pendingDocs) || $needsLiveness)
        <div class="space-y-3">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Action Required</h2>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($pendingDocs as $docType)
                @php $docConfig = \Nucleus\Kyc\Models\KycVerification::DOCUMENT_TYPES[$docType]; @endphp
                <a href="{{ $docConfig['route'] }}" wire:navigate
                    class="flex items-center gap-4 p-4 bg-white rounded-xl border border-amber-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all group">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                        <x-phosphor-warning class="w-5 h-5 text-amber-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800">{{ $docConfig['label'] }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Upload required</p>
                    </div>
                    <x-phosphor-arrow-right class="w-4 h-4 text-gray-300 group-hover:text-amber-500 transition-colors flex-shrink-0" />
                </a>
                @endforeach

                @if($needsLiveness)
                <a href="/kyc/liveness" wire:navigate
                    class="flex items-center gap-4 p-4 bg-white rounded-xl border border-amber-200 shadow-sm hover:shadow-md hover:border-amber-300 transition-all group">
                    <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center group-hover:bg-amber-100 transition-colors">
                        <x-phosphor-warning class="w-5 h-5 text-amber-500" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800">Liveness verification</p>
                        <p class="text-xs text-gray-400 mt-0.5">Video check required</p>
                    </div>
                    <x-phosphor-arrow-right class="w-4 h-4 text-gray-300 group-hover:text-amber-500 transition-colors flex-shrink-0" />
                </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Stats Row --}}
        @php
        $kycLabel = match($kycStatus) {
            'verified'         => 'Verified',
            'under_review'     => 'Under Review',
            'failed'           => 'Failed',
            'more_info_needed' => 'More Info Needed',
            'pending'          => 'Pending',
            default            => 'Not Started',
        };
        $kycColor = match($kycStatus) {
            'verified'         => 'text-green-600',
            'under_review'     => 'text-blue-600',
            'failed'           => 'text-red-600',
            'more_info_needed' => 'text-amber-600',
            default            => 'text-gray-400',
        };
        @endphp
        <div class="grid grid-cols-2 gap-5 lg:grid-cols-4">
            <div class="bg-white rounded-xl border border-gray-200/80 p-5 shadow-sm">
                <p class="text-sm text-gray-500 font-medium mb-1">Identity Check</p>
                <p class="text-2xl font-bold {{ $kycColor }}">{{ $kycLabel }}</p>
                <a href="/kyc" wire:navigate class="inline-block text-sm font-medium text-orange-500 hover:text-orange-600 mt-2">Manage</a>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 p-5 shadow-sm">
                <p class="text-sm text-gray-500 font-medium mb-1">Onboarding</p>
                <p class="text-2xl font-bold {{ $onboardingComplete ? 'text-green-600' : 'text-amber-500' }}">
                    {{ $onboardingComplete ? 'Complete' : 'In Progress' }}
                </p>
                <a href="/onboarding" wire:navigate class="inline-block text-sm font-medium text-orange-500 hover:text-orange-600 mt-2">View</a>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 p-5 shadow-sm">
                <p class="text-sm text-gray-500 font-medium mb-1">Member Since</p>
                <p class="text-2xl font-bold text-gray-900">{{ $user->created_at->format('M Y') }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ $user->created_at->diffForHumans() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200/80 p-5 shadow-sm">
                <p class="text-sm text-gray-500 font-medium mb-1">Account Plan</p>
                <p class="text-2xl font-bold text-gray-900 capitalize">{{ $user->role?->name ?? 'Free' }}</p>
                <a href="/settings/billing" wire:navigate class="inline-block text-sm font-medium text-orange-500 hover:text-orange-600 mt-2">Upgrade</a>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            {{-- Account Overview --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200/80 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-base font-bold text-gray-900">Account Overview</h2>
                </div>

                {{-- Profile summary --}}
                <div class="px-6 py-5 flex items-center gap-4 border-b border-gray-50">
                    <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center text-xl font-bold text-orange-600 flex-shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-base font-semibold text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-400 truncate">{{ $user->email }}</p>
                    </div>
                    <a href="/settings/profile" wire:navigate
                        class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        <x-phosphor-pencil-simple class="w-3.5 h-3.5" />
                        Edit
                    </a>
                </div>

                {{-- Setup checklist --}}
                <div class="divide-y divide-gray-50">
                    @php
                    $steps = [
                        [
                            'label'    => 'Complete onboarding',
                            'desc'     => 'Finish the initial setup wizard',
                            'done'     => $onboardingComplete,
                            'href'     => '/onboarding',
                            'icon'     => 'phosphor-list-checks',
                        ],
                        [
                            'label'    => 'Verify your identity',
                            'desc'     => 'Upload documents for KYC approval',
                            'done'     => $kycStatus === 'verified',
                            'href'     => '/kyc',
                            'icon'     => 'phosphor-shield-check',
                        ],
                        [
                            'label'    => 'Complete your profile',
                            'desc'     => 'Add a name, avatar, and contact info',
                            'done'     => (bool) $user->avatar,
                            'href'     => '/settings/profile',
                            'icon'     => 'phosphor-user-circle',
                        ],
                    ];
                    @endphp

                    @foreach($steps as $step)
                    <a href="{{ $step['href'] }}" wire:navigate
                        class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors group">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center
                                    {{ $step['done'] ? 'bg-green-100' : 'bg-gray-100' }}">
                            @if($step['done'])
                                <x-phosphor-check class="w-4 h-4 text-green-600" />
                            @else
                                <x-dynamic-component :component="$step['icon']" class="w-4 h-4 text-gray-400" />
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold {{ $step['done'] ? 'text-gray-400 line-through' : 'text-gray-800' }}">
                                {{ $step['label'] }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $step['desc'] }}</p>
                        </div>
                        @if(!$step['done'])
                        <x-phosphor-arrow-right class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transition-colors flex-shrink-0" />
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="flex flex-col gap-5">
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-base font-bold text-gray-900">Quick Actions</h2>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <a href="/settings/profile" wire:navigate
                            class="flex items-center gap-3 px-6 py-4 hover:bg-gray-50 transition-colors group">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                <x-phosphor-user class="w-4 h-4 text-orange-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">Edit Profile</p>
                                <p class="text-xs text-gray-400 mt-0.5">Update your personal details</p>
                            </div>
                            <x-phosphor-arrow-right class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transition-colors" />
                        </a>
                        <a href="/kyc" wire:navigate
                            class="flex items-center gap-3 px-6 py-4 hover:bg-gray-50 transition-colors group">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                <x-phosphor-identification-card class="w-4 h-4 text-orange-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">Identity Verification</p>
                                <p class="text-xs text-gray-400 mt-0.5">Upload and manage documents</p>
                            </div>
                            <x-phosphor-arrow-right class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transition-colors" />
                        </a>
                        <a href="/settings/billing" wire:navigate
                            class="flex items-center gap-3 px-6 py-4 hover:bg-gray-50 transition-colors group">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                <x-phosphor-credit-card class="w-4 h-4 text-orange-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">Billing & Plan</p>
                                <p class="text-xs text-gray-400 mt-0.5">Manage your subscription</p>
                            </div>
                            <x-phosphor-arrow-right class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transition-colors" />
                        </a>
                        <a href="/settings/security" wire:navigate
                            class="flex items-center gap-3 px-6 py-4 hover:bg-gray-50 transition-colors group">
                            <div class="flex-shrink-0 w-9 h-9 rounded-lg bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                                <x-phosphor-lock-key class="w-4 h-4 text-orange-500" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800">Security</p>
                                <p class="text-xs text-gray-400 mt-0.5">Password and 2FA settings</p>
                            </div>
                            <x-phosphor-arrow-right class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transition-colors" />
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-layouts.app>
