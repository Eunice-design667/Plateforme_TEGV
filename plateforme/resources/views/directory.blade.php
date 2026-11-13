@php use App\Enums\ChurchRole; @endphp

<x-layouts.app title="Annuaire">
    <div>
        <p class="eyebrow">Annuaire des membres</p>
        <h1 class="mt-1 text-3xl font-serif font-semibold tracking-tight text-navy-900">Qui peut m’aider ?</h1>
        <p class="mt-1 text-sm text-muted">Cherchez un métier, une entreprise, une compétence ou un rôle.</p>
    </div>

    <form method="GET" action="{{ route('directory') }}" class="mt-5 grid grid-cols-1 gap-3">
        @if ($activeRole)<input type="hidden" name="role" value="{{ $activeRole->value }}">@endif
        <label for="q" class="sr-only">Rechercher un métier, une entreprise, un nom</label>
        <div class="relative">
            <x-icon name="search" class="pointer-events-none absolute top-1/2 left-5 -translate-y-1/2 text-muted" />
            <input id="q" name="q" type="search" list="trades" value="{{ $term }}" placeholder="Métier, entreprise, nom…" autocomplete="off" class="input rounded-full bg-white py-4 pr-28 pl-13 shadow-(--shadow-card)">
            <button class="btn-primary absolute top-1/2 right-2 -translate-y-1/2 px-5 py-2.5">Chercher</button>
            <datalist id="trades">
                @foreach (config('tegv.trades') as $trade)
                    <option value="{{ $trade }}"></option>
                @endforeach
            </datalist>
        </div>
        <label for="secteur" class="sr-only">Secteur d’activité</label>
        <div class="relative">
            <x-icon name="briefcase" class="pointer-events-none absolute top-1/2 left-5 -translate-y-1/2 text-muted" />
            <select id="secteur" name="secteur" onchange="this.form.requestSubmit()" @class(['input rounded-full py-3.5 pl-13 shadow-(--shadow-card)', 'bg-white' => ! $activeSector, 'bg-navy-50 font-semibold text-navy-900 ring-navy-500' => $activeSector])>
                <option value="">Tous les secteurs d’activité</option>
                @foreach (config('tegv.sectors') as $key => $label)
                    <option value="{{ $key }}" @selected($activeSector === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="-mx-4 mt-4 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none]">
        <a href="{{ route('directory', array_filter(['q' => $term, 'secteur' => $activeSector])) }}" @class(['chip min-h-11 shrink-0 px-4 py-2 text-sm', 'bg-navy-700 text-white' => ! $activeRole, 'bg-white text-navy-800 ring-1 ring-sand' => $activeRole])>Tous</a>
        @foreach (ChurchRole::cases() as $role)
            @continue($role === ChurchRole::Autre)
            <a href="{{ route('directory', array_filter(['q' => $term, 'secteur' => $activeSector, 'role' => $role->value])) }}" @class([
                'chip min-h-11 shrink-0 px-4 py-2 text-sm',
                'bg-navy-700 text-white' => $activeRole === $role,
                'bg-white text-navy-800 ring-1 ring-sand' => $activeRole !== $role,
            ])>{{ $role->label() }}</a>
        @endforeach
    </div>

    <p class="mt-5 text-sm text-muted">
        {{ $members->count() }} {{ $members->count() > 1 ? 'membres' : 'membre' }}{{ $term !== '' ? " pour « {$term} »" : '' }}{{ $activeSector ? ' · '.config('tegv.sectors')[$activeSector] : '' }} · classés par années d’expérience
    </p>

    @if ($members->isEmpty())
        <div class="card mt-4 flex flex-col items-center p-10 text-center">
            <span class="flex size-14 items-center justify-center rounded-full bg-gold-100 text-gold-600"><x-icon name="search" class="size-6" /></span>
            <p class="mt-3 font-bold text-navy-900">Aucun membre trouvé</p>
            <p class="text-sm text-muted">Essayez un autre mot, comme un métier proche.</p>
        </div>
    @else
        <div class="mt-4 columns-2 gap-3 sm:gap-4 md:columns-3 xl:columns-4">
            @foreach ($members as $member)
                <a href="{{ route('members.show', $member) }}" class="card mb-3 block break-inside-avoid overflow-hidden transition hover:-translate-y-0.5 sm:mb-4">
                    @if ($member->photo_url)
                        <img src="{{ $member->photo_url }}" alt="{{ $member->full_name }}" loading="lazy" @class(['w-full object-cover', 'aspect-[4/5]' => $loop->odd, 'aspect-square' => $loop->even])>
                    @else
                        <div @class([
                            'flex items-center justify-center',
                            'aspect-[4/5]' => $loop->index % 3 === 0,
                            'aspect-square' => $loop->index % 3 !== 0,
                            ['bg-navy-50', 'bg-gold-50', 'bg-crimson-50'][$member->id % 3],
                        ])>
                            <x-avatar :member="$member" size="xl" class="ring-4" />
                        </div>
                    @endif
                    <div class="p-3.5 sm:p-4">
                        <p class="leading-tight font-extrabold text-navy-900">{{ $member->full_name }}</p>
                        <p class="mt-0.5 text-xs text-muted">{{ collect([$member->job_title, $member->company])->filter()->implode(' · ') ?: $member->primary_profile }}</p>
                        <div class="mt-2.5 flex flex-wrap gap-1.5">
                            <span class="chip bg-gold-100 text-gold-600">{{ $member->primary_profile }}</span>
                            @if ($member->years_experience)
                                <span class="chip bg-navy-50 text-navy-700"><x-icon name="briefcase" class="size-3.5" /> {{ $member->years_experience }} {{ $member->years_experience > 1 ? 'ans' : 'an' }} d’expérience</span>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
