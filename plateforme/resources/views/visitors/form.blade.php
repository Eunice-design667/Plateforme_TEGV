<x-layouts.guest title="Bienvenue">
    <div class="relative -mx-4 -mt-6 overflow-hidden sm:mx-0 sm:mt-0 sm:rounded-(--radius-card)">
        <img src="/images/eglise/culte.jpg" alt="" class="h-80 w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-navy-900 via-navy-900/75 to-navy-900/10"></div>
        <div class="absolute inset-x-0 bottom-0 p-5 text-white">
            <img src="/images/logo-rond.png" alt="Logo TEGV" class="size-14 rounded-full ring-4 ring-white/30">
            <h1 class="mt-3 text-[1.6rem] leading-tight font-serif font-semibold">Bienvenue au Temple Évangélique Grâce et Victoire !</h1>
            <p class="mt-1 text-sm text-white/80">Nous sommes heureux de vous accueillir. Laissez-nous vos coordonnées.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('visitors.store') }}" data-draft="visiteur" class="card relative mt-5 grid grid-cols-1 gap-5 p-5 sm:p-6" x-data="{ noChurch: @js((bool) old('has_no_church')) }">
        @csrf
        <div class="hidden" aria-hidden="true"><label>Site web <input name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="grid grid-cols-2 gap-3">
            <x-field label="Prénom" name="first_name">
                <input id="first_name" name="first_name" value="{{ old('first_name') }}" required class="input" autocomplete="given-name">
            </x-field>
            <x-field label="Nom" name="last_name">
                <input id="last_name" name="last_name" value="{{ old('last_name') }}" required class="input" autocomplete="family-name">
            </x-field>
        </div>
        <x-field label="Église d’origine" name="origin_church">
            <input id="origin_church" name="origin_church" value="{{ old('origin_church') }}" :disabled="noChurch" :required="!noChurch" class="input disabled:opacity-50" placeholder="Nom de votre église">
        </x-field>
        <label class="-mt-2 flex items-center gap-3 text-sm font-semibold text-navy-900">
            <input type="checkbox" name="has_no_church" value="1" x-model="noChurch" class="size-5 rounded accent-navy-700"> Je n’ai pas d’église
        </label>
        <x-field label="Numéro WhatsApp" name="whatsapp">
            <input id="whatsapp" name="whatsapp" type="tel" inputmode="tel" value="{{ old('whatsapp') }}" required class="input" placeholder="+226 70 00 00 00" autocomplete="tel">
        </x-field>
        <label class="flex items-start gap-3 rounded-2xl bg-gold-50 p-4 text-sm text-navy-900 ring-1 ring-gold-100">
            <input type="checkbox" name="consent" value="1" @checked(old('consent')) required class="mt-0.5 size-5 shrink-0 rounded accent-navy-700">
            <span>J’accepte de recevoir un message de remerciement du TEGV sur WhatsApp. Mes coordonnées ne servent qu’à l’église et peuvent être supprimées sur simple demande.</span>
        </label>
        @error('consent')<p class="-mt-3 text-xs font-semibold text-crimson-600">{{ $message }}</p>@enderror
        @error('website')<p class="-mt-3 text-xs font-semibold text-crimson-600">Envoi refusé.</p>@enderror
        <button class="btn-primary w-full py-4 text-base">Valider</button>
        <p class="-mt-2 text-center text-xs text-muted">La date de votre visite est enregistrée automatiquement.</p>
    </form>

    <p class="mt-6 text-center text-xs text-muted">{{ config('tegv.full_name') }} · {{ config('tegv.address') }}</p>
</x-layouts.guest>
