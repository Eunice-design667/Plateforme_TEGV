<x-layouts.guest title="Connexion">
    <a href="{{ route('welcome') }}" class="inline-flex size-10 items-center justify-center rounded-full bg-white ring-1 ring-sand" aria-label="Retour"><x-icon name="arrow-left" class="size-4" /></a>

    <div class="relative mt-6 overflow-hidden rounded-3xl">
        <img src="/images/eglise/culte.jpg" alt="" class="h-40 w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-navy-900/85 via-navy-900/30 to-transparent"></div>
        <div class="absolute bottom-4 left-4 flex items-center gap-3 text-white">
            <img src="/images/logo-rond.png" alt="" class="size-11 rounded-full ring-2 ring-white/60">
            <div>
                <p class="text-xl font-extrabold">Bon retour !</p>
                <p class="font-serif text-sm text-gold-300 italic">Solo Deo Gloria</p>
            </div>
        </div>
    </div>

    @if (session('status'))
        <p class="mt-5 rounded-2xl bg-navy-800 px-4 py-3 text-sm text-white" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="card mt-5 grid grid-cols-1 gap-5 p-5 sm:p-6">
        @csrf
        <x-field label="Email" name="email">
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="input">
        </x-field>
        <x-field label="Mot de passe" name="password">
            <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
        </x-field>
        <a href="{{ route('password.request') }}" class="-mt-3 justify-self-end text-sm font-semibold text-navy-700 underline underline-offset-4">Mot de passe oublié ?</a>
        <label class="flex items-center gap-2.5 text-sm text-muted">
            <input type="checkbox" name="remember" value="1" class="size-4 rounded accent-navy-700" checked> Rester connecté sur ce téléphone
        </label>
        <button class="btn-primary w-full py-3.5">Se connecter</button>
    </form>

    <p class="mt-6 text-center text-sm text-muted">Pas encore de compte ? <a href="{{ route('register') }}" class="font-semibold text-navy-700 underline underline-offset-4">Créer mon compte</a></p>
</x-layouts.guest>
