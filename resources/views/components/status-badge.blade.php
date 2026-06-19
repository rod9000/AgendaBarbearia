@props(['status'])

@php
$statusMap = [
    'scheduled'   => ['label' => 'Agendado',    'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
    'confirmed'   => ['label' => 'Confirmado',   'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'],
    'in_progress' => ['label' => 'Em Andamento', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'],
    'completed'   => ['label' => 'Concluído',    'class' => 'bg-stone-100 text-stone-700 dark:bg-stone-700 dark:text-stone-300'],
    'cancelled'   => ['label' => 'Cancelado',    'class' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'],
    'no_show'     => ['label' => 'Não Compareceu', 'class' => 'bg-gray-200 text-gray-600 dark:bg-stone-600 dark:text-stone-400'],
];
$info = $statusMap[$status] ?? ['label' => $status, 'class' => 'bg-stone-100 text-stone-600 dark:bg-stone-700 dark:text-stone-400'];
@endphp

<span {{ $attributes->merge(['class' => 'badge-pastel ' . $info['class']]) }}>
    {{ $info['label'] }}
</span>
