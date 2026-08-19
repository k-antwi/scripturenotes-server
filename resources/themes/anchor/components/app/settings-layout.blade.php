<div class="w-full max-w-6xl mx-auto py-2">
    {{-- Breadcrumb --}}
    <nav class="text-sm font-medium mb-6">
        <a href="{{ route('dashboard') }}" wire:navigate class="text-gray-400 hover:text-gray-600">Dashboard</a>
        <span class="text-gray-300 mx-1">&rsaquo;</span>
        <span class="text-gray-800">Settings</span>
    </nav>

    <div class="flex flex-wrap items-center justify-between pb-4 border-b border-gray-200">
        <div class="space-y-0.5">
            <h1 class="text-3xl font-bold text-gray-900">{{ $title ?? '' }}</h1>
            <p class="text-sm text-gray-500">{{ $description ?? '' }}</p>
        </div>
    </div>

    <div class="flex flex-col pt-6 lg:flex-row lg:space-x-8">
        <aside class="flex-shrink-0 pb-8 lg:pt-0 lg:pb-0 lg:w-52">
            <nav class="flex items-start justify-start lg:flex-col lg:space-y-1">
                <div class="px-2.5 pb-1.5 text-xs lg:block hidden font-semibold leading-6 text-gray-400 uppercase tracking-wider">Settings</div>
                <div class="flex items-center w-auto space-x-2 lg:items-stretch lg:flex-col lg:w-full lg:space-y-0.5 lg:space-x-0">
                    <x-settings-sidebar-link :href="route('settings.profile')" icon="phosphor-user-circle-duotone">Profile</x-settings-sidebar-link>
                    <x-settings-sidebar-link :href="route('settings.security')" icon="phosphor-lock-duotone">Security</x-settings-sidebar-link>
                    <x-settings-sidebar-link :href="route('settings.notifications')" icon="phosphor-bell-duotone">Notifications</x-settings-sidebar-link>
                    <x-settings-sidebar-link :href="route('settings.social')" icon="phosphor-share-network-duotone">Social Media</x-settings-sidebar-link>
                    <x-settings-sidebar-link :href="route('settings.api')" icon="phosphor-code-duotone">API Keys</x-settings-sidebar-link>
                    <x-settings-sidebar-link :href="route('settings.activity')" icon="phosphor-clock-counter-clockwise-duotone">Activity Log</x-settings-sidebar-link>
                </div>
                <div class="px-2.5 pt-4 pb-1.5 text-xs lg:block hidden font-semibold leading-6 text-gray-400 uppercase tracking-wider">Billing</div>
                <div class="flex items-center w-full ml-2 space-x-2 lg:items-stretch lg:flex-col lg:ml-0 lg:space-y-0.5 lg:space-x-0">
                    <x-settings-sidebar-link :href="route('settings.subscription')" icon="phosphor-credit-card-duotone">Subscription</x-settings-sidebar-link>
                    <x-settings-sidebar-link :href="route('settings.invoices')" icon="phosphor-invoice-duotone">Invoices</x-settings-sidebar-link>
                </div>
                <div class="px-2.5 pt-4 pb-1.5 text-xs lg:block hidden font-semibold leading-6 text-gray-400 uppercase tracking-wider">Privacy</div>
                <div class="flex items-center w-full ml-2 space-x-2 lg:items-stretch lg:flex-col lg:ml-0 lg:space-y-0.5 lg:space-x-0">
                    <x-settings-sidebar-link :href="route('settings.privacy')" icon="phosphor-shield-check-duotone">Privacy Settings</x-settings-sidebar-link>
                    <x-settings-sidebar-link :href="route('settings.export')" icon="phosphor-download-duotone">Export Data</x-settings-sidebar-link>
                    <x-settings-sidebar-link :href="route('settings.deletion')" icon="phosphor-trash-duotone">Account Deletion</x-settings-sidebar-link>
                </div>
            </nav>
        </aside>

        <div class="py-1 lg:px-6 lg:w-full">
            {{ $slot }}
        </div>
    </div>
</div>
