@props(['announcement'])

<article {{ $attributes->merge(['class' => 'card break-inside-avoid p-5']) }}>
    <p class="flex items-center gap-2 text-xs font-semibold text-gold-600">
        <x-icon name="megaphone" class="size-4" />
        Publiée le {{ $announcement->published_at?->translatedFormat('j F Y') }}
    </p>
    <h3 class="mt-2 text-lg leading-snug font-extrabold text-navy-900">{{ $announcement->title }}</h3>
    <p class="mt-2 text-sm leading-relaxed whitespace-pre-line text-ink/80">{{ $announcement->body }}</p>
    @if ($announcement->publisherLabel())
        <p class="mt-3 flex items-center gap-1.5 border-t border-sand pt-3 text-xs text-muted"><x-icon name="shield" class="size-3.5 text-gold-600" /> Validée et publiée {{ $announcement->publisherLabel() }}</p>
    @endif
</article>
