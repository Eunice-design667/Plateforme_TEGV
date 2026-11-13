<x-layouts.app title="Supprimer mon compte">
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('profile.edit') }}" class="-my-2 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-muted hover:text-navy-800"><x-icon name="arrow-left" class="size-4" /> Mon profil</a>
        <h1 class="mt-3 font-serif text-3xl font-semibold tracking-tight text-navy-900">Supprimer mon compte</h1>

        <section class="card mt-5 grid grid-cols-1 gap-4 p-5 sm:p-6">
            <h2 class="font-bold text-navy-900">Ce qui va se passer</h2>
            <ol class="grid grid-cols-1 gap-3 text-sm text-ink/80">
                <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-navy-800 text-xs font-bold text-white">1</span><span><strong>Tout de suite</strong> : vous êtes déconnecté, votre fiche disparaît de l’annuaire et votre nom n’apparaît plus dans les anniversaires.</span></li>
                <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-navy-800 text-xs font-bold text-white">2</span><span><strong>Pendant {{ config('tegv.account_deletion_days') }} jours</strong>, jusqu’au {{ now()->addDays(config('tegv.account_deletion_days'))->translatedFormat('j F Y') }} : vous pouvez changer d’avis. Il suffit de vous reconnecter avec votre email et votre mot de passe, puis de toucher « Annuler la suppression ».</span></li>
                <li class="flex gap-3"><span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-crimson-600 text-xs font-bold text-white">3</span><span><strong>Au bout de {{ config('tegv.account_deletion_days') }} jours</strong> : votre compte, votre profil, vos photos, vos affiches et vos envois (absences, signalements, informations proposées) sont supprimés définitivement. Ils ne peuvent plus être récupérés.</span></li>
            </ol>
            <p class="rounded-2xl bg-gold-50 p-3 text-sm text-navy-900 ring-1 ring-gold-100">L’administrateur de la plateforme voit votre demande et sa date. Pour toute question, contactez l’église au {{ config('tegv.phone') }}.</p>
        </section>

        <form method="POST" action="{{ route('account-deletion.store') }}" class="card mt-5 grid grid-cols-1 gap-5 p-5 sm:p-6">
            @csrf
            <x-field label="Mot de passe" name="password" bag="deletion" hint="Pour confirmer que c’est bien vous.">
                <input id="password" name="password" type="password" required autocomplete="current-password" class="input">
            </x-field>
            <label class="flex items-start gap-3 text-sm text-navy-900">
                <input type="checkbox" name="confirmation" value="1" required class="mt-0.5 size-5 shrink-0 rounded accent-crimson-600">
                <span>J’ai compris que mon compte sera supprimé définitivement dans {{ config('tegv.account_deletion_days') }} jours si je n’annule pas.</span>
            </label>
            @error('confirmation', 'deletion')<p class="-mt-3 text-xs font-semibold text-crimson-600">{{ $message }}</p>@enderror
            <button class="btn w-full bg-crimson-600 py-3.5 text-white hover:bg-crimson-700">Demander la suppression de mon compte</button>
            <a href="{{ route('profile.edit') }}" class="btn-light w-full">Garder mon compte</a>
        </form>
    </div>
</x-layouts.app>
