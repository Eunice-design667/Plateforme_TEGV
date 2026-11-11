<x-layouts.guest title="Mot de passe oublié">
    <a href="{{ route('login') }}" class="inline-flex size-10 items-center justify-center rounded-full bg-white ring-1 ring-sand" aria-label="Retour"><x-icon name="arrow-left" class="size-4" /></a>

    <div class="mt-6 flex items-center gap-3">
        <span class="flex size-12 items-center justify-center rounded-full bg-gold-100 text-gold-600"><x-icon name="mail" /></span>
        <div>
            <p class="eyebrow">Connexion</p>
            <h1 class="text-2xl font-serif font-semibold tracking-tight text-navy-900">Mot de passe oublié</h1>
        </div>
    </div>
    <p class="mt-4 text-sm text-muted">Indiquez l’email de votre compte : nous vous envoyons un lien pour choisir un nouveau mot de passe.</p>

    @if (session('status'))
        <p class="mt-5 rounded-2xl bg-navy-800 px-4 py-3 text-sm text-white" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="card mt-5 grid grid-cols-1 gap-5 p-5 sm:p-6">
        @csrf
        <x-field label="Email" name="email">
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input">
        </x-field>
        <button class="btn-primary w-full py-3.5">Recevoir le lien</button>
    </form>

    <p class="mt-6 text-center text-sm text-muted"><a href="{{ route('login') }}" class="font-semibold text-navy-700 underline underline-offset-4">Revenir à la connexion</a></p>
</x-layouts.guest>
