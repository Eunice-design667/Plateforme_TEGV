<x-layouts.app title="QR code visiteurs">
    <p class="eyebrow">Administration</p>
    <h1 class="mt-1 text-3xl font-serif font-semibold tracking-tight text-navy-900">QR code des visiteurs</h1>
    <p class="mt-1 text-sm text-muted">Les visiteurs le scannent avec l’appareil photo de leur téléphone : la fiche visiteur s’ouvre, sans application à installer.</p>

    <div class="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-[22rem_1fr]">
        <div class="card flex flex-col items-center p-6">
            <div class="w-full rounded-3xl bg-white p-3 ring-1 ring-sand [&_svg]:h-auto [&_svg]:w-full">{!! $svg !!}</div>
            <img src="/images/logo-rond.png" alt="" class="-mt-6 size-12 rounded-full ring-4 ring-white">
        </div>
        <div class="grid grid-cols-1 content-start gap-4">
            <div class="card p-5">
                <p class="text-xs font-semibold text-muted uppercase">Adresse de la page visiteur</p>
                <div class="mt-2 flex items-center gap-2" x-data="{ copied: false }">
                    <code class="min-w-0 flex-1 truncate rounded-2xl bg-cream px-4 py-3 text-sm text-navy-900 ring-1 ring-sand">{{ $visitorUrl }}</code>
                    <button type="button" class="btn-light px-4 py-3" @click="navigator.clipboard.writeText(@js($visitorUrl)); copied = true; setTimeout(() => copied = false, 1500)" x-text="copied ? 'Copié !' : 'Copier'">Copier</button>
                </div>
                @if (str_contains($visitorUrl, 'localhost') || str_contains($visitorUrl, '127.0.0.1'))
                    <p class="mt-3 rounded-2xl bg-crimson-50 p-3 text-xs text-crimson-700">Cette adresse est locale : réglez <code>APP_URL</code> sur l’adresse définitive du site avant d’imprimer le QR code.</p>
                @endif
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <a href="{{ route('admin.qr-code.download') }}" class="btn-primary py-4"><x-icon name="download" class="size-4" /> Télécharger le QR code</a>
                <a href="{{ route('admin.poster') }}" target="_blank" class="btn-gold py-4"><x-icon name="printer" class="size-4" /> Affiche A4 à imprimer</a>
            </div>
            <a href="{{ route('visitors.create') }}" target="_blank" class="inline-flex min-h-11 items-center text-sm font-semibold text-navy-700 underline underline-offset-4">Ouvrir la fiche visiteur</a>
        </div>
    </div>
</x-layouts.app>
