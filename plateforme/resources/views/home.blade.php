@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Bonjour' : ($hour < 18 ? 'Bon après-midi' : 'Bonsoir');
    $names = $birthdays->pluck('first_name');
    $birthdayNames = $names->count() > 1 ? $names->slice(0, -1)->implode(', ').' et '.$names->last() : $names->first();
    $tiles = [
        ['route' => route('directory'), 'icon' => 'users', 'title' => 'Annuaire', 'text' => 'Trouver un expert', 'tone' => 'bg-navy-700 text-white', 'iconTone' => 'bg-white/15 text-gold-300'],
        ['route' => route('profile.edit'), 'icon' => 'user', 'title' => 'Mon profil', 'text' => 'Ma fiche de membre', 'tone' => 'bg-white text-navy-900 ring-1 ring-sand/70', 'iconTone' => 'bg-gold-100 text-gold-600'],
        ['route' => route('notices.create'), 'icon' => 'calendar', 'title' => 'Absence', 'text' => 'Prévenir les pasteurs', 'tone' => 'bg-white text-navy-900 ring-1 ring-sand/70', 'iconTone' => 'bg-navy-50 text-navy-700'],
        ['route' => route('notices.create', ['onglet' => 'signaler']), 'icon' => 'alert', 'title' => 'Signaler', 'text' => 'Décès ou autre souci', 'tone' => 'bg-crimson-50 text-crimson-700 ring-1 ring-crimson-100', 'iconTone' => 'bg-white text-crimson-600'],
    ];
@endphp

<x-layouts.app title="Accueil">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-serif font-semibold tracking-tight text-navy-900 sm:text-3xl">{{ $greeting }}, {{ $user->first_name }}</h1>
            <p class="mt-1 text-sm text-muted first-letter:uppercase">{{ now()->translatedFormat('l j F Y') }} · Bienvenue au TEGV</p>
        </div>
        @if ($user->isLeader())
            <a href="{{ route('leader.dashboard') }}" class="btn-light hidden sm:inline-flex"><x-icon name="shield" class="size-4 text-gold-600" /> Espace responsables</a>
        @endif
    </div>

    <x-stories :stories="$stories" />

    @if ($birthdayMessage)
        <div class="relative mt-5 overflow-hidden rounded-(--radius-card) bg-gradient-to-br from-gold-300 via-gold-400 to-gold-500 p-5 text-navy-900 shadow-(--shadow-card) sm:p-6">
            <x-icon name="sparkles" class="absolute -top-2 -right-2 size-28 text-white/30" />
            <p class="eyebrow text-navy-800">Joyeux anniversaire 🎉</p>
            <p class="relative mt-2 max-w-2xl text-[15px] leading-relaxed font-medium">{{ $birthdayMessage->message }}</p>
            <form method="POST" action="{{ route('birthday-messages.update', $birthdayMessage) }}" class="relative mt-4">
                @csrf @method('PATCH')
                <button class="btn bg-navy-900 px-4 py-2 text-white hover:bg-navy-800">Merci !</button>
            </form>
        </div>
    @endif

    {{-- Grande carte : prochain rendez-vous --}}
    <section class="relative mt-5 overflow-hidden rounded-(--radius-card) bg-navy-900 text-white shadow-(--shadow-card)">
        <img src="/images/eglise/culte.jpg" alt="" class="absolute inset-0 size-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-r from-navy-900 via-navy-900/80 to-navy-900/10"></div>
        <div class="relative flex min-h-56 flex-col justify-end p-5 sm:min-h-64 sm:p-8">
            <span class="chip w-fit bg-crimson-500 text-white">
                <span class="size-1.5 rounded-full bg-white"></span>
                {{ $nextService['isToday'] ? "Aujourd'hui" : 'Prochain rendez-vous' }}
            </span>
            <h2 class="mt-3 text-2xl font-serif font-semibold tracking-tight sm:text-4xl">{{ $nextService['title'] }}</h2>
            <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-white/85">
                <span class="inline-flex items-center gap-1.5"><x-icon name="clock" class="size-4 text-gold-300" /> {{ $nextService['day'] }} · {{ $nextService['time'] }}</span>
                <span class="inline-flex items-center gap-1.5"><x-icon name="map-pin" class="size-4 text-gold-300" /> {{ config('tegv.address') }}</span>
            </p>
        </div>
    </section>

    {{-- Accès rapides --}}
    <section class="mt-5 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4" aria-label="Accès rapides">
        @foreach ($tiles as $tile)
            <a href="{{ $tile['route'] }}" class="{{ $tile['tone'] }} group flex min-h-36 flex-col justify-between rounded-(--radius-card) p-4 shadow-(--shadow-card) transition hover:-translate-y-0.5 sm:p-5">
                <span class="{{ $tile['iconTone'] }} flex size-11 items-center justify-center rounded-2xl"><x-icon :name="$tile['icon']" class="size-5.5" /></span>
                <span>
                    <span class="block text-lg font-extrabold">{{ $tile['title'] }}</span>
                    <span class="block text-xs opacity-75">{{ $tile['text'] }}</span>
                </span>
            </a>
        @endforeach
    </section>

    <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Anniversaires --}}
        <section class="card p-5">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="cake" class="text-gold-600" /> Anniversaires aujourd’hui</h2>
            </div>
            @if ($birthdays->isEmpty())
                <p class="mt-4 text-sm text-muted">Pas d’anniversaire aujourd’hui. Pensez à indiquer le vôtre dans votre profil !</p>
            @else
                <div class="mt-4 flex -space-x-3">
                    @foreach ($birthdays->take(6) as $member)
                        <a href="{{ route('members.show', $member) }}"><x-avatar :member="$member" /></a>
                    @endforeach
                </div>
                <p class="mt-3 text-[15px] text-navy-900">🎉 <strong>{{ $birthdayNames }}</strong> {{ $birthdays->count() > 1 ? 'fêtent' : 'fête' }} {{ $birthdays->count() > 1 ? 'leur' : 'son' }} anniversaire. Bénissons-{{ $birthdays->count() > 1 ? 'les' : 'le' }} !</p>
            @endif
        </section>

        {{-- Programme --}}
        <section class="card p-5">
            <h2 class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="calendar" class="text-gold-600" /> Programme de la semaine</h2>
            <ul class="mt-4 grid grid-cols-1 gap-3">
                @foreach (config('tegv.programme') as $item)
                    <li class="flex items-center gap-3">
                        <span class="flex w-14 shrink-0 flex-col items-center rounded-2xl bg-cream py-1.5 ring-1 ring-sand">
                            <span class="text-[10px] font-bold text-gold-600 uppercase">{{ mb_substr($item['day'], 0, 3) }}</span>
                            <x-icon :name="$item['weekday'] === 0 ? 'cross' : ($item['weekday'] === 2 ? 'book' : 'heart')" class="size-4 text-navy-700" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold text-navy-900">{{ $item['title'] }}</span>
                            <span class="block text-xs text-muted">{{ $item['day'] }} · {{ $item['time'] }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Devise --}}
        <section class="relative overflow-hidden rounded-(--radius-card) bg-gold-50 p-5 ring-1 ring-gold-100">
            <img src="/images/eglise/louange2.jpg" alt="" class="absolute right-0 bottom-0 h-full w-1/3 object-cover opacity-70 [mask-image:linear-gradient(to_left,black,transparent)]">
            <div class="relative max-w-[78%]">
                <p class="eyebrow">Notre devise</p>
                <p class="mt-3 font-serif text-lg leading-snug text-navy-900 italic">« Nous ne faisons pas autre chose, nous faisons autrement les choses. »</p>
                <p class="mt-3 text-sm font-semibold text-crimson-600">Solo Deo Gloria</p>
            </div>
        </section>
    </div>

    {{-- Informations --}}
    <section class="mt-8">
        <div class="flex items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Vie de l’église</p>
                <h2 class="mt-1 text-xl font-serif font-semibold tracking-tight text-navy-900">Informations</h2>
            </div>
            <div class="flex gap-2">
                @if ($announcements->isNotEmpty())
                    <a href="{{ route('announcements.index') }}" class="btn-light px-4 py-2">Tout voir</a>
                @endif
                <a href="{{ route('announcements.create') }}" class="btn-gold px-4 py-2"><x-icon name="plus" class="size-4" /> Proposer</a>
            </div>
        </div>

        @if (! $user->canSeeAnnouncements())
            <p class="card mt-4 p-5 text-sm text-muted">Les informations publiées sont réservées aux chrétiens de TEGV. Si vous l’êtes, cochez la case dans <a href="{{ route('profile.edit') }}" class="font-semibold text-navy-700 underline">votre profil</a>.</p>
        @elseif ($announcements->isEmpty())
            <p class="card mt-4 p-5 text-sm text-muted">Aucune information publiée pour le moment.</p>
        @else
            <div class="mt-4 columns-1 gap-4 sm:columns-2 lg:columns-3">
                @foreach ($announcements as $announcement)
                    <x-announcement-card :announcement="$announcement" class="mb-4" />
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.app>
