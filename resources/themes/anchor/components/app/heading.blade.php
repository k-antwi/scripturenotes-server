@props([
    'title' => '',
    'description' => '',
    'border' => true
])

<div class="@if($border){{ 'pb-5 border-b border-gray-200' }}@endif space-y-1">
    <h3 class="text-xl font-bold text-gray-900">{{ $title ?? '' }}</h3>
    <p class="text-sm text-gray-500">{{ $description ?? '' }}</p>
</div>