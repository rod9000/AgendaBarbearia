@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-brand-400 text-sm font-medium leading-5 text-brand-900 focus:outline-none focus:border-brand-600 transition duration-150 ease-in-out dark:border-brand-600 dark:text-brand-300 dark:focus:border-brand-400'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-brand-600 hover:border-brand-300 focus:outline-none focus:text-brand-600 focus:border-brand-300 transition duration-150 ease-in-out dark:text-stone-400 dark:hover:text-brand-400 dark:hover:border-brand-600 dark:focus:text-brand-400 dark:focus:border-brand-600';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
