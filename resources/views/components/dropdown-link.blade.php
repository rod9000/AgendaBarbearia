@props(['active' => false])

<a {{ $attributes->merge(['class' => 'block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out dark:text-stone-300 dark:hover:bg-stone-700 dark:focus:bg-stone-700' . ($active ? ' bg-brand-50 text-brand-700 dark:bg-stone-700 dark:text-brand-300' : '')]) }}>{{ $slot }}</a>
