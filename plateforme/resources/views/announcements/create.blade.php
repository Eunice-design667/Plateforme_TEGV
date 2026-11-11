<x-layouts.app title="Proposer une information">
    <div class="mx-auto max-w-2xl">
        <p class="eyebrow">Proposer</p>
        <h1 class="mt-1 text-3xl font-serif font-semibold tracking-tight text-navy-900">Une information pour l’église</h1>
        <p class="mt-1 text-sm text-muted">Un pasteur ou un diacre la relira, pourra la corriger, puis la publiera pour les chrétiens de TEGV.</p>

        <form method="POST" action="{{ route('announcements.store') }}" data-draft="information-{{ auth()->id() }}" class="card mt-5 grid grid-cols-1 gap-5 p-5 sm:p-6">
            @csrf
            <x-field label="Titre" name="title">
                <input id="title" name="title" value="{{ old('title') }}" required maxlength="120" class="input" placeholder="Ex. : Veillée de prière vendredi">
            </x-field>
            <x-field label="Texte" name="body">
                <textarea id="body" name="body" rows="6" required maxlength="3000" class="input" placeholder="Date, lieu, à qui s’adresse l’information…">{{ old('body') }}</textarea>
            </x-field>
            <button class="btn-primary w-full py-3.5">Envoyer pour relecture</button>
        </form>

        @if ($myProposals->isNotEmpty())
            <h2 class="mt-8 font-bold text-navy-900">Mes dernières propositions</h2>
            <ul class="mt-3 grid grid-cols-1 gap-2">
                @foreach ($myProposals as $proposal)
                    <li class="card flex items-center justify-between gap-3 p-4">
                        <span class="min-w-0 truncate text-sm font-semibold text-navy-900">{{ $proposal->title }}</span>
                        <span @class(['chip shrink-0', 'bg-gold-100 text-gold-600' => $proposal->status->value === 'proposed', 'bg-navy-50 text-navy-700' => $proposal->status->value === 'published'])>{{ $proposal->status->label() }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.app>
