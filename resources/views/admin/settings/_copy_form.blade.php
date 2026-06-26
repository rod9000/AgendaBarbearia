<form method="POST" action="{{ route('admin.settings.working-hours.copy') }}" class="copy-form">
    @csrf
    <input type="hidden" name="user_id" value="{{ $user->id }}">
    <input type="hidden" name="source_day" value="{{ $day }}">
    <p class="text-xs text-stone-500 mb-2">Copiar para:</p>
    @foreach(range(0, 6) as $td)
    @if($td !== $day)
    <label class="flex items-center gap-2 py-1 text-sm text-stone-700 cursor-pointer hover:text-brand-700">
        <input type="checkbox" name="target_days[]" value="{{ $td }}" class="rounded border-brand-300 text-brand-600">
        {{ $days[$td] }}
    </label>
    @endif
    @endforeach
    <button type="submit" class="mt-2 w-full text-sm btn-pastel-primary py-1.5">Copiar</button>
</form>
