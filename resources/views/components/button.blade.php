@props(['variant' => 'primary', 'type' => 'submit'])

@php
$base = 'inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg focus:outline-none focus:ring-2 transition-all duration-150';
$variants = [
    'primary' => $base . ' bg-gradient-to-r from-brand-400 to-brand-600 text-white hover:from-brand-500 hover:to-brand-700 focus:ring-brand-300 shadow-sm',
    'secondary' => $base . ' bg-stone-100 text-stone-700 hover:bg-stone-200 focus:ring-stone-300 dark:bg-stone-700 dark:text-stone-300 dark:hover:bg-stone-600',
    'success' => $base . ' bg-gradient-to-r from-emerald-400 to-teal-400 text-white hover:from-emerald-500 hover:to-teal-500 focus:ring-emerald-300 shadow-sm',
    'danger' => $base . ' bg-gradient-to-r from-red-400 to-rose-400 text-white hover:from-red-500 hover:to-rose-500 focus:ring-red-300 shadow-sm',
];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $variants[$variant] ?? $variants['primary']]) }}>
    {{ $slot }}
</button>
