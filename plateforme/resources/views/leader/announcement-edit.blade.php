<x-layouts.app title="Relire une information">
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('leader.dashboard') }}" class="-my-2 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-muted hover:text-navy-800"><x-icon name="arrow-left" class="size-4" /> Espace responsables</a>
        <h1 class="mt-3 text-3xl font-serif font-semibold tracking-tight text-navy-900">Relire et publier</h1>
        <p class="mt-1 text-sm text-muted">Proposée par {{ $announcement->author->full_name }}. Une fois publiée, elle apparaît chez tous les chrétiens de TEGV.</p>

        @if ($announcement->status->value === 'published')
            <p class="mt-4 rounded-2xl bg-navy-50 p-4 text-sm text-navy-900 ring-1 ring-navy-100"><strong>Déjà publiée</strong> par {{ $announcement->validator?->full_name ?? 'un responsable' }} le {{ $announcement->published_at?->translatedFormat('j F à H\hi') }}.</p>
        @elseif ($reviewedByAnother)
            <p class="mt-4 rounded-2xl bg-gold-50 p-4 text-sm text-navy-900 ring-1 ring-gold-100"><strong>En cours de relecture</strong> par {{ $announcement->claimer?->full_name }} depuis {{ $announcement->claimed_at->format('H\hi') }}. Vérifiez avec lui ou elle avant de la publier, pour ne pas la traiter deux fois.</p>
        @endif

        <form method="POST" action="{{ route('leader.announcements.update', $announcement) }}" data-draft="relecture-{{ $announcement->id }}" class="card mt-5 grid grid-cols-1 gap-5 p-5 sm:p-6">
            @csrf @method('PUT')
            <x-field label="Titre" name="title">
                <input id="title" name="title" value="{{ old('title', $announcement->title) }}" required maxlength="120" class="input">
            </x-field>
            <x-field label="Texte" name="body">
                <textarea id="body" name="body" rows="8" required maxlength="3000" class="input">{{ old('body', $announcement->body) }}</textarea>
            </x-field>
            <button class="btn-primary w-full py-3.5"><x-icon name="check" class="size-4" /> Valider et publier</button>
        </form>
        <form method="POST" action="{{ route('leader.announcements.destroy', $announcement) }}" class="mt-3" onsubmit="return confirm('Retirer cette information ?')">
            @csrf @method('DELETE')
            <button class="btn-danger w-full">Refuser et retirer</button>
        </form>
    </div>
</x-layouts.app>
