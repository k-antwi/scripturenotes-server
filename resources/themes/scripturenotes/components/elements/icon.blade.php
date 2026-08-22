@props(['name' => 'star'])
<x-dynamic-component :component="'phosphor-' . $name" {{ $attributes }} />
