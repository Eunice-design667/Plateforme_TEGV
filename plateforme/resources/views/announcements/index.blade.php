<x-layouts.app title="Informations">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">Vie de l’église</p>
            <h1 class="mt-1 text-3xl font-serif font-semibold tracking-tight text-navy-900">Informations</h1>
            <p class="mt-1 text-sm text-muted">Validées par les pasteurs et diacres, pour les chrétiens de TEGV.</p>
        </div>
        <a href="{{ route('announcements.create') }}" class="btn-gold"><x-icon name="plus" class="size-4" /> Proposer une information</a>
    </div>

    @if ($announcements->isEmpty())
        <p class="card mt-6 p-6 text-sm text-muted">Aucune information publiée pour le moment.</p>
    @else
        <div class="mt-6 columns-1 gap-4 sm:columns-2 lg:columns-3">
            @foreach ($announcements as $announcement)
                <x-announcement-card :announcement="$announcement" class="mb-4" />
            @endforeach
        </div>
        <div class="mt-4">{{ $announcements->links() }}</div>
    @endif
</x-layouts.app>
