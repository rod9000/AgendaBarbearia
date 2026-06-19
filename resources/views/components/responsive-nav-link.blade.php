@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block pl-3 pr-4 py-2 border-l-4 border-brand-400 text-base font-medium text-brand-700 bg-brand-50 focus:outline-none focus:text-brand-800 focus:bg-brand-100 focus:border-brand-600 transition duration-150 ease-in-out dark:border-brand-600 dark:text-brand-300 dark:bg-stone-800 dark:focus:text-brand-200 dark:focus:bg-stone-700'
            : 'block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:text-brand-600 hover:bg-brand-50 hover:border-brand-300 focus:outline-none focus:text-brand-600 focus:bg-brand-50 focus:border-brand-300 transition duration-150 ease-in-out dark:text-stone-400 dark:hover:text-brand-400 dark:hover:bg-stone-800 dark:hover:border-brand-600 dark:focus:text-brand-400 dark:focus:bg-stone-800';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
