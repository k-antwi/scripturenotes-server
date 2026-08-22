<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('theme::partials.head', ['seo' => ($seo ?? null)])
</head>
<body x-data class="flex flex-col min-h-screen bg-amber-50/30" style="font-family: 'Inter', system-ui, sans-serif;" x-cloak>
    {{ $slot }}
    @livewire('notifications')
    @include('theme::partials.toast')
    @include('theme::partials.footer-scripts')
    {{ $javascript ?? '' }}
</body>
</html>
