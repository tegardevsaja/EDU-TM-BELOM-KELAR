@props([
    'variant' => 'primary', // primary, secondary, danger, success, gray
    'size' => 'md', // sm, md, lg
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $base = 'inline-flex items-center justify-center rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-0';

    $sizes = [
        'sm' => 'text-xs px-3 py-1.5',
        'md' => 'text-sm px-4 py-2',
        'lg' => 'text-base px-5 py-2.5',
    ][$size] ?? 'text-sm px-4 py-2';

    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-400',
        'secondary' => 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600 focus:ring-gray-300',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-400',
        'success' => 'bg-green-600 text-white hover:bg-green-700 focus:ring-green-400',
        'gray' => 'border border-gray-300 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-800',
    ][$variant] ?? 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-400';

    $classes = trim("$base $sizes $variants");
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-dynamic-component :component="$icon" class="w-4 h-4 mr-2" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-dynamic-component :component="$icon" class="w-4 h-4 mr-2" />
        @endif
        {{ $slot }}
    </button>
@endif
