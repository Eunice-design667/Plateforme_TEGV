@props(['label', 'name', 'hint' => null, 'bag' => 'default'])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endif
    @error($name, $bag)
        <p class="mt-1.5 text-xs font-semibold text-crimson-600">{{ $message }}</p>
    @enderror
</div>
