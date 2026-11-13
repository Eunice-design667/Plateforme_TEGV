@php
    use App\Enums\ReportType;
    use App\Enums\ThanksStatus;
    use App\Support\PhoneNumber;

    $tabs = [
        'validations' => ['Informations à valider', 'megaphone'],
        'absences' => ['Absences', 'calendar'],
        'visiteurs' => ['Visiteurs', 'users'],
        'signalements' => ['Signalements', 'alert'],
    ];
@endphp

<x-layouts.app title="Espace pasteur et diacre">
    <p class="eyebrow">Espace réservé</p>
    <h1 class="mt-1 text-3xl font-serif font-semibold tracking-tight text-navy-900">Pasteurs et diacres</h1>

    <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($tabs as $key => [$label, $icon])
            <a href="{{ route('leader.dashboard', ['onglet' => $key]) }}" @class([
                'rounded-(--radius-card) p-4 shadow-(--shadow-card) transition sm:p-5',
                'bg-navy-800 text-white' => $tab === $key,
                'bg-white text-navy-900 ring-1 ring-sand/70 hover:-translate-y-0.5' => $tab !== $key,
            ])>
                <span class="flex items-center justify-between">
                    <x-icon :name="$icon" @class(['text-gold-300' => $tab === $key, 'text-gold-600' => $tab !== $key]) />
                    @if ($key === 'signalements' && $counts[$key] > 0)
                        <span class="size-2.5 rounded-full bg-crimson-500"></span>
                    @endif
                </span>
                <span class="mt-3 block text-3xl font-extrabold">{{ $counts[$key] }}</span>
                <span @class(['block text-xs font-semibold', 'text-white/70' => $tab === $key, 'text-muted' => $tab !== $key])>
                    {{ $label }}{{ $key === 'visiteurs' ? ' (7 jours)' : ($key === 'absences' ? ' à traiter' : ($key === 'signalements' ? ' à traiter' : '')) }}
                </span>
            </a>
        @endforeach
    </div>

    @if (auth()->user()->is_admin && $counts['droits'] > 0)
        <a href="{{ route('admin.rights.index') }}" class="mt-4 flex items-center gap-3 rounded-2xl bg-gold-50 p-4 text-sm text-navy-900 ring-1 ring-gold-100">
            <x-icon name="shield" class="text-gold-600" /> <span class="flex-1"><strong>{{ $counts['droits'] }}</strong> {{ $counts['droits'] > 1 ? 'demandes de droits en attente' : 'demande de droits en attente' }}</span> <x-icon name="arrow-right" class="size-4" />
        </a>
    @endif

    <section class="mt-6">
        @if ($tab === 'validations')
            <h2 class="text-xl font-serif font-semibold text-navy-900">Informations proposées</h2>
            @forelse ($proposals as $proposal)
                <article class="card mt-3 p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="font-extrabold text-navy-900">{{ $proposal->title }}</h3>
                            <p class="text-xs text-muted">Proposée par {{ $proposal->author->full_name }} · {{ $proposal->created_at->diffForHumans() }}</p>
                            @if ($proposal->isBeingReviewedByAnotherThan(auth()->user()))
                                <span class="chip mt-2 bg-gold-100 text-gold-600"><x-icon name="eye" class="size-3.5" /> En relecture par {{ $proposal->claimer?->full_name }} depuis {{ $proposal->claimed_at->format('H\hi') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('leader.announcements.edit', $proposal) }}" class="btn-primary px-4 py-2"><x-icon name="edit" class="size-4" /> Relire et publier</a>
                    </div>
                    <p class="mt-3 line-clamp-3 text-sm whitespace-pre-line text-ink/80">{{ $proposal->body }}</p>
                </article>
            @empty
                <p class="card mt-3 p-5 text-sm text-muted">Aucune information en attente de validation.</p>
            @endforelse
        @elseif ($tab === 'absences')
            <h2 class="text-xl font-serif font-semibold text-navy-900">Absences</h2>
            <div class="card mt-3 divide-y divide-sand overflow-hidden">
                @forelse ($absences as $absence)
                    <div @class(['flex flex-wrap items-center gap-x-4 gap-y-2 p-4', 'opacity-60' => $absence->handled_at])>
                        <span class="flex w-24 shrink-0 flex-col rounded-2xl bg-cream px-3 py-2 text-center ring-1 ring-sand">
                            <span class="text-xs font-bold text-navy-900">{{ $absence->starts_on->translatedFormat('j M') }}</span>
                            <span class="text-[11px] text-muted">au {{ $absence->ends_on->translatedFormat('j M') }}</span>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-navy-900">{{ $absence->first_name }} {{ $absence->last_name }}
                                @if ($absence->is_tegv_christian)<span class="chip ml-1 bg-navy-50 text-navy-700">TEGV</span>@endif
                                @if ($absence->ends_on->isPast())<span class="chip ml-1 bg-sand text-muted">Terminée</span>@endif
                            </p>
                            <p class="text-sm text-muted">{{ $absence->reason }}</p>
                            @if ($absence->handled_at)
                                <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-navy-700"><x-icon name="check" class="size-3.5" /> Traitée par {{ $absence->handler?->full_name ?? 'un responsable' }} le {{ $absence->handled_at->translatedFormat('j M à H\hi') }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('leader.absences.update', $absence) }}">
                            @csrf @method('PATCH')
                            @if ($absence->handled_at)
                                <input type="hidden" name="reopen" value="1">
                                <button class="btn-light px-4 py-2">Rouvrir</button>
                            @else
                                <button class="btn-primary px-4 py-2"><x-icon name="check" class="size-4" /> Marquer comme traitée</button>
                            @endif
                        </form>
                    </div>
                @empty
                    <p class="p-5 text-sm text-muted">Aucune absence signalée.</p>
                @endforelse
            </div>
        @elseif ($tab === 'visiteurs')
            <h2 class="text-xl font-serif font-semibold text-navy-900">Visiteurs</h2>
            <div class="card relative mt-3 overflow-x-auto">
                <table class="w-full min-w-[40rem] text-left text-sm">
                    <thead class="bg-cream text-xs text-muted uppercase">
                        <tr><th class="px-4 py-3">Visite</th><th class="px-4 py-3">Visiteur</th><th class="px-4 py-3">Église d’origine</th><th class="px-4 py-3">WhatsApp</th><th class="px-4 py-3">Remerciement</th><th class="px-4 py-3"><span class="sr-only">Actions</span></th></tr>
                    </thead>
                    <tbody class="divide-y divide-sand">
                        @forelse ($visitors as $visitor)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-muted">{{ $visitor->visited_on->translatedFormat('D j M') }}</td>
                                <td class="px-4 py-3 font-bold text-navy-900">{{ $visitor->fullName() }}</td>
                                <td class="px-4 py-3">{{ $visitor->has_no_church ? 'Pas d’église' : $visitor->origin_church }}</td>
                                <td class="px-4 py-3 whitespace-nowrap"><a href="{{ PhoneNumber::whatsappLink($visitor->whatsapp) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center text-navy-700 underline">{{ PhoneNumber::format($visitor->whatsapp) }}</a></td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'chip',
                                        'bg-gold-100 text-gold-600' => $visitor->thanks_status === ThanksStatus::Pending,
                                        'bg-navy-50 text-navy-700' => $visitor->thanks_status === ThanksStatus::Sent,
                                        'bg-crimson-50 text-crimson-700' => $visitor->thanks_status === ThanksStatus::Failed,
                                    ]) title="{{ $visitor->thanks_error ?? $visitor->thanks_message }}">{{ $visitor->thanks_status->label() }}</span>
                                    @if ($visitor->thanks_sent_at)
                                        <span class="mt-1 block text-[11px] whitespace-nowrap text-muted">le {{ $visitor->thanks_sent_at->translatedFormat('j M à H\hi') }}</span>
                                    @elseif ($visitor->thanks_error)
                                        <span class="mt-1 block max-w-48 text-[11px] text-crimson-700">{{ $visitor->thanks_error }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('leader.visitors.destroy', $visitor) }}" onsubmit="return confirm(@js('Supprimer définitivement les données de '.$visitor->fullName().' ?'))">
                                        @csrf @method('DELETE')
                                        <button class="flex size-11 items-center justify-center rounded-full text-muted hover:bg-crimson-50 hover:text-crimson-600" title="Supprimer les données (à la demande du visiteur)" aria-label="Supprimer {{ $visitor->fullName() }}"><x-icon name="x" class="size-4" /></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-5 text-muted">Aucun visiteur enregistré.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($visitors instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="mt-4">{{ $visitors->links() }}</div>
            @endif
        @else
            <h2 class="text-xl font-serif font-semibold text-navy-900">Signalements</h2>
            @forelse ($reports as $report)
                <article @class(['card mt-3 p-5', 'opacity-60' => $report->handled_at])>
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <span @class(['chip', 'bg-crimson-500 text-white' => $report->type === ReportType::Deces, 'bg-gold-100 text-gold-600' => $report->type !== ReportType::Deces])>{{ $report->type->label() }}</span>
                            <h3 class="mt-2 font-extrabold text-navy-900">{{ $report->concerned_person }}</h3>
                            <p class="text-xs text-muted">Signalé par {{ $report->user->full_name }} · {{ $report->created_at->diffForHumans() }}</p>
                            @if ($report->handled_at)
                                <p class="mt-1 flex items-center gap-1 text-xs font-semibold text-navy-700"><x-icon name="check" class="size-3.5" /> Traité par {{ $report->handler?->full_name ?? 'un responsable' }} le {{ $report->handled_at->translatedFormat('j M à H\hi') }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('leader.reports.update', $report) }}">
                            @csrf @method('PATCH')
                            @if ($report->handled_at)<input type="hidden" name="reopen" value="1">@endif
                            <button class="{{ $report->handled_at ? 'btn-light' : 'btn-primary' }} px-4 py-2"><x-icon name="check" class="size-4" /> {{ $report->handled_at ? 'Rouvrir' : 'Marquer comme traité' }}</button>
                        </form>
                    </div>
                    <p class="mt-3 text-sm whitespace-pre-line text-ink/80">{{ $report->description }}</p>
                </article>
            @empty
                <p class="card mt-3 p-5 text-sm text-muted">Aucun signalement.</p>
            @endforelse
        @endif
    </section>
</x-layouts.app>
