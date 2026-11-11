<x-layouts.guest title="Accès réservé">
    <div class="flex flex-1 flex-col items-center justify-center text-center">
        <img src="/images/logo-rond.png" alt="" class="size-20 rounded-full ring-1 ring-sand">
        <p class="mt-6 text-sm font-bold tracking-[0.2em] text-gold-600">403</p>
        <h1 class="mt-2 text-3xl font-serif font-semibold tracking-tight text-navy-900">Accès réservé</h1>
        <p class="mt-3 max-w-sm text-muted">{{ $exception?->getMessage() ? $exception->getMessage() : 'Cette page est réservée à certains responsables de l’église.' }}</p>
        <a href="{{ url('/') }}" class="btn-primary mt-8">Revenir à l’accueil</a>
    </div>
</x-layouts.guest>
