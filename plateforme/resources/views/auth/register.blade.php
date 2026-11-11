<x-layouts.guest title="Créer mon compte">
    <a href="{{ $account ? route('profile.create') : route('welcome') }}" class="inline-flex size-10 items-center justify-center rounded-full bg-white ring-1 ring-sand" aria-label="Retour"><x-icon name="arrow-left" class="size-4" /></a>

    <div class="mt-6 flex items-center gap-3">
        <img src="/images/logo-rond.png" alt="" class="size-12 rounded-full ring-1 ring-sand">
        <div>
            <p class="eyebrow">Étape 1 sur 2</p>
            <h1 class="text-2xl font-serif font-semibold tracking-tight text-navy-900">Créer mon compte</h1>
        </div>
    </div>
    <div class="mt-5 flex gap-2" aria-hidden="true"><span class="h-1.5 flex-1 rounded-full bg-gold-400"></span><span class="h-1.5 flex-1 rounded-full bg-sand"></span></div>

    <form method="POST" action="{{ route('register') }}" data-draft="inscription" class="card mt-6 grid grid-cols-1 gap-5 p-5 sm:p-6">
        @csrf
        @if ($account)
            <p class="rounded-2xl bg-navy-50 p-3 text-sm text-navy-900"><strong>Votre compte est déjà créé.</strong> Vous pouvez corriger votre email ; laissez le mot de passe vide pour le garder.</p>
        @endif
        <x-field label="Email" name="email">
            <input id="email" name="email" type="email" value="{{ old('email', $account?->email) }}" required autocomplete="email" class="input" placeholder="vous@exemple.com">
        </x-field>
        <x-field label="{{ $account ? 'Nouveau mot de passe (facultatif)' : 'Mot de passe' }}" name="password" hint="Au moins 8 caractères, avec des lettres et des chiffres.">
            <input id="password" name="password" type="password" @required(! $account) autocomplete="new-password" class="input">
        </x-field>
        <x-field label="Confirmer le mot de passe" name="password_confirmation">
            <input id="password_confirmation" name="password_confirmation" type="password" @required(! $account) autocomplete="new-password" class="input">
        </x-field>
        <button class="btn-primary w-full py-3.5">Continuer vers mon profil <x-icon name="arrow-right" class="size-4" /></button>
    </form>

    @unless ($account)
    <p class="mt-6 text-center text-sm text-muted">Déjà inscrit ? <a href="{{ route('login') }}" class="font-semibold text-navy-700 underline underline-offset-4">Se connecter</a></p>
    @endunless
</x-layouts.guest>
