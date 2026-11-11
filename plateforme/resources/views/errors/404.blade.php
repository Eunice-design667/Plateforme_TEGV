<x-layouts.guest title="Page introuvable">
    <div class="flex flex-1 flex-col items-center justify-center text-center">
        <img src="/images/logo-rond.png" alt="" class="size-20 rounded-full ring-1 ring-sand">
        <p class="mt-6 text-sm font-bold tracking-[0.2em] text-gold-600">404</p>
        <h1 class="mt-2 text-3xl font-serif font-semibold tracking-tight text-navy-900">Page introuvable</h1>
        <p class="mt-3 max-w-sm text-muted">Cette page n’existe pas ou a été déplacée.</p>
        <a href="{{ url('/') }}" class="btn-primary mt-8">Revenir à l’accueil</a>
    </div>
</x-layouts.guest>
