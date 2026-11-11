<x-layouts.guest title="Nouveau mot de passe">
    <div class="mt-2 flex items-center gap-3">
        <img src="/images/logo-rond.png" alt="" class="size-12 rounded-full ring-1 ring-sand">
        <div>
            <p class="eyebrow">Connexion</p>
            <h1 class="text-2xl font-serif font-semibold tracking-tight text-navy-900">Nouveau mot de passe</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('password.update') }}" class="card mt-6 grid grid-cols-1 gap-5 p-5 sm:p-6">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-field label="Email" name="email">
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autocomplete="email" class="input">
        </x-field>
        <x-field label="Nouveau mot de passe" name="password" hint="Au moins 8 caractères, avec des lettres et des chiffres.">
            <input id="password" name="password" type="password" required autofocus autocomplete="new-password" class="input">
        </x-field>
        <x-field label="Confirmer le mot de passe" name="password_confirmation">
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
        </x-field>
        <button class="btn-primary w-full py-3.5">Enregistrer mon nouveau mot de passe</button>
    </form>
</x-layouts.guest>
