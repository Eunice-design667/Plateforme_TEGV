<x-layouts.app title="Validation des droits">
    <p class="eyebrow">Administration</p>
    <h1 class="mt-1 text-3xl font-serif font-semibold tracking-tight text-navy-900">Droits de pasteur et diacre</h1>
    <p class="mt-1 text-sm text-muted">Un compte qui se déclare pasteur ou diacre reste un membre simple tant que vous ne l’avez pas validé.</p>

    <h2 class="mt-6 font-bold text-navy-900">En attente ({{ $pending->count() }})</h2>
    <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
        @forelse ($pending as $member)
            <div class="card flex items-center gap-4 p-4">
                <x-avatar :member="$member" />
                <div class="min-w-0 flex-1">
                    <p class="truncate font-bold text-navy-900">{{ $member->full_name }}</p>
                    <p class="truncate text-xs text-muted">{{ implode(', ', $member->roleLabels()) }} · {{ $member->email }}</p>
                    <p class="text-xs text-muted">Demande du {{ ($member->leader_requested_at ?? $member->updated_at)->translatedFormat('j F Y à H\hi') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.rights.update', $member) }}">
                    @csrf @method('PATCH')
                    <button class="btn-primary px-3 py-2" title="Valider"><x-icon name="check" class="size-4" /><span class="sr-only sm:not-sr-only">Valider</span></button>
                </form>
                <form method="POST" action="{{ route('admin.rights.destroy', $member) }}">
                    @csrf @method('DELETE')
                    <button class="btn-danger px-3 py-2" title="Refuser"><x-icon name="x" class="size-4" /></button>
                </form>
            </div>
        @empty
            <p class="card p-5 text-sm text-muted md:col-span-2">Aucune demande en attente.</p>
        @endforelse
    </div>

    <h2 class="mt-8 font-bold text-navy-900">Responsables validés ({{ $approved->count() }})</h2>
    <div class="card mt-3 divide-y divide-sand">
        @forelse ($approved as $member)
            <div class="flex items-center gap-3 p-4">
                <x-avatar :member="$member" size="sm" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-navy-900">{{ $member->full_name }}</p>
                    <p class="truncate text-xs text-muted">{{ implode(', ', $member->roleLabels()) }} · validé {{ $member->leader_validated_at?->translatedFormat('j M Y') }}{{ $member->leaderValidator ? ' par '.$member->leaderValidator->full_name : '' }}</p>
                </div>
                <form method="POST" action="{{ route('admin.rights.destroy', $member) }}" onsubmit="return confirm(@js('Retirer les droits de '.$member->full_name.' ?'))">
                    @csrf @method('DELETE')
                    <button class="min-h-11 rounded-full px-3 text-xs font-semibold text-crimson-600 hover:bg-crimson-50">Retirer</button>
                </form>
            </div>
        @empty
            <p class="p-5 text-sm text-muted">Aucun responsable validé.</p>
        @endforelse
    </div>

    <h2 class="mt-8 font-bold text-navy-900">Demandes de suppression de compte ({{ $deletions->count() }})</h2>
    <div class="card mt-3 divide-y divide-sand">
        @forelse ($deletions as $member)
            <div class="flex items-center gap-3 p-4">
                <x-avatar :member="$member" size="sm" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-navy-900">{{ $member->full_name }}</p>
                    <p class="truncate text-xs text-muted">Demandée le {{ $member->deletion_requested_at->translatedFormat('j F Y') }} · suppression définitive le {{ $member->deletionDate()->translatedFormat('j F Y') }}</p>
                </div>
            </div>
        @empty
            <p class="p-5 text-sm text-muted">Aucune demande en cours.</p>
        @endforelse
    </div>
</x-layouts.app>
