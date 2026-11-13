@php
    use App\Support\PhoneNumber;

    $whatsappLink = PhoneNumber::whatsappLink($member->whatsapp, "Bonjour {$member->first_name}, je vous contacte via l'annuaire du TEGV.");
    $isMe = auth()->id() === $member->id;
@endphp

<x-layouts.app :title="$member->full_name">
    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('directory') }}" class="-my-2 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-muted hover:text-navy-800"><x-icon name="arrow-left" class="size-4" /> Retour</a>

    <div class="mt-4 grid grid-cols-1 gap-5 lg:grid-cols-[22rem_1fr]">
        <section class="card overflow-hidden">
            <div class="h-28" style="background: {{ $member->coverBackground() }}" role="img" aria-label="Couverture du profil"></div>
            <div class="relative -mt-14 flex flex-col items-center px-5 pb-6 text-center">
                <x-avatar :member="$member" size="xl" class="ring-4" />
                <h1 class="mt-3 text-2xl font-serif font-semibold tracking-tight text-navy-900">{{ $member->full_name }}</h1>
                <p class="text-sm text-muted">{{ collect([$member->job_title, $member->company])->filter()->implode(' · ') }}</p>
                @if ($member->roleLabels())
                    <div class="mt-3 flex flex-wrap justify-center gap-1.5">
                        @foreach ($member->roleLabels() as $label)
                            <span class="chip bg-crimson-50 text-crimson-700">{{ $label }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-5 grid grid-cols-1 w-full gap-2">
                    @if ($whatsappLink)
                        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="btn w-full bg-[#1fa855] py-3.5 text-white hover:bg-[#178a45]"><x-icon name="whatsapp" /> Écrire sur WhatsApp</a>
                    @endif
                    @if ($member->contact_email)
                        <a href="mailto:{{ $member->contact_email }}" class="btn-light w-full py-3.5"><x-icon name="mail" /> Envoyer un email</a>
                    @endif
                    @if ($isMe)
                        <a href="{{ route('profile.edit') }}" class="btn-gold w-full py-3.5"><x-icon name="edit" /> Modifier mon profil</a>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 content-start gap-4">
            <section class="grid grid-cols-2 gap-3 xl:grid-cols-3">
                <div class="card p-4">
                    <p class="text-xs font-semibold text-muted">Années d’expérience</p>
                    <p class="mt-1 text-2xl font-extrabold text-navy-900">{{ $member->years_experience ?? '—' }} <span class="text-sm font-semibold text-muted">{{ $member->years_experience ? ($member->years_experience > 1 ? 'ans' : 'an') : '' }}</span></p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-semibold text-muted">Poste</p>
                    <p class="mt-1 font-bold break-words text-navy-900">{{ $member->job_title ?: '—' }}</p>
                </div>
                <div class="card col-span-2 p-4 xl:col-span-1">
                    <p class="text-xs font-semibold text-muted">Entreprise</p>
                    <p class="mt-1 font-bold break-words text-navy-900">{{ $member->company ?: '—' }}</p>
                </div>
            </section>

            <section class="card p-5">
                <h2 class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="briefcase" class="text-gold-600" /> Compétences</h2>
                @if ($member->sectorLabel())
                    <p class="mt-4 text-xs font-semibold text-muted uppercase">Secteur d’activité</p>
                    <p class="mt-1 font-semibold text-navy-900">{{ $member->sectorLabel() }}</p>
                @endif
                <p class="mt-4 text-xs font-semibold text-muted uppercase">Profil principal</p>
                <p class="mt-1"><span class="chip bg-gold-100 px-4 py-2 text-sm text-gold-600">{{ $member->primary_profile }}</span></p>
                @if ($member->secondary_profiles)
                    <p class="mt-4 text-xs font-semibold text-muted uppercase">Profils secondaires</p>
                    <div class="mt-1 flex flex-wrap gap-2">
                        @foreach ($member->secondary_profiles as $profile)
                            <span class="chip bg-navy-50 px-4 py-2 text-sm text-navy-700">{{ $profile }}</span>
                        @endforeach
                    </div>
                @endif
            </section>

            @if ($member->whatsapp)
                <section class="card flex items-center gap-4 p-5">
                    <span class="flex size-11 items-center justify-center rounded-2xl bg-navy-50 text-navy-700"><x-icon name="phone" /></span>
                    <div>
                        <p class="text-xs font-semibold text-muted">WhatsApp</p>
                        <p class="font-bold text-navy-900">{{ PhoneNumber::format($member->whatsapp) }}</p>
                    </div>
                </section>
            @endif
        </div>
    </div>
    @if ($announcements->isNotEmpty())
        <section class="mt-8">
            <div class="flex items-end justify-between gap-3">
                <div>
                    <p class="eyebrow">Pour vous</p>
                    <h2 class="mt-1 font-serif text-2xl font-semibold text-navy-900">Informations de l’église</h2>
                </div>
                <a href="{{ route('announcements.index') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-navy-700 underline underline-offset-4">Tout voir</a>
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                @foreach ($announcements as $announcement)
                    <x-announcement-card :announcement="$announcement" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Affiches du membre --}}
    <section id="affiches" class="mt-8 scroll-mt-20" x-data="{ open: null }">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="eyebrow">Affiches</p>
                <h2 class="mt-1 font-serif text-2xl font-semibold text-navy-900">{{ $isMe ? 'Mes affiches' : 'Les affiches de '.$member->first_name }}</h2>
            </div>
            @if ($isMe)
                <span class="text-xs text-muted">{{ $posters->count() }} / {{ config('tegv.max_posters') }}</span>
            @endif
        </div>

        @if ($isMe)
            <form method="POST" action="{{ route('posters.store') }}" enctype="multipart/form-data" class="card mt-4 grid grid-cols-1 gap-4 p-5 sm:grid-cols-[auto_1fr_auto] sm:items-end"
                  x-data="{ preview: null }">
                @csrf
                <label class="relative flex size-24 shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-gold-300 bg-gold-50 text-gold-600">
                    <template x-if="preview"><img :src="preview" alt="" class="absolute inset-0 size-full object-cover"></template>
                    <span x-show="!preview" class="flex flex-col items-center gap-1 text-[11px] font-bold"><x-icon name="plus" /> Image</span>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-max-mb="6" required class="sr-only"
                           @change="const f = $event.target.files[0]; if (f && f.size <= 6 * 1024 * 1024) preview = URL.createObjectURL(f)">
                </label>
                <x-field label="Légende (facultatif)" name="caption" bag="poster">
                    <input id="caption" name="caption" maxlength="200" value="{{ old('caption') }}" class="input" placeholder="Ex. : Mon atelier de couture est ouvert le samedi">
                    <p class="mt-1.5 text-xs text-muted">Image JPG, PNG ou WebP, 6 Mo maximum.</p>
                </x-field>
                <button class="btn-gold py-3"><x-icon name="plus" class="size-4" /> Publier l’affiche</button>
                @error('image', 'poster')<p class="text-xs font-semibold text-crimson-600 sm:col-span-3">{{ $message }}</p>@enderror
            </form>
        @endif

        @if ($posters->isEmpty())
            <p class="card mt-4 p-5 text-sm text-muted">{{ $isMe ? 'Ajoutez vos affiches : un événement, votre activité, une annonce… Elles apparaîtront aussi quelques jours dans les affiches du moment, sur l’accueil.' : 'Aucune affiche pour le moment.' }}</p>
        @else
            <div class="mt-4 columns-2 gap-3 sm:columns-3 lg:columns-4">
                @foreach ($posters as $poster)
                    <figure class="card group relative mb-3 break-inside-avoid overflow-hidden">
                        <button type="button" class="block w-full" @click="open = @js(['url' => $poster->image_url, 'caption' => $poster->caption])">
                            <img src="{{ $poster->image_url }}" alt="{{ $poster->caption ?? 'Affiche de '.$member->first_name }}" loading="lazy" class="w-full object-cover transition duration-500 group-hover:scale-[1.03]">
                        </button>
                        @if ($poster->caption)
                            <figcaption class="p-3 text-xs text-ink/80">{{ $poster->caption }}</figcaption>
                        @endif
                        @if ($isMe || auth()->user()->is_admin)
                            <form method="POST" action="{{ route('posters.destroy', $poster) }}" class="absolute top-2 right-2" onsubmit="return confirm('Supprimer cette affiche ?')">
                                @csrf @method('DELETE')
                                <button class="flex size-11 items-center justify-center rounded-full bg-white/90 text-crimson-600 shadow" aria-label="Supprimer l’affiche"><x-icon name="x" class="size-4" /></button>
                            </form>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif

        {{-- Agrandissement --}}
        <div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-navy-900/90 p-4" @click.self="open = null" @keydown.escape.window="open = null">
            <button type="button" class="absolute top-4 right-4 rounded-full bg-white/10 p-2 text-white" @click="open = null" aria-label="Fermer"><x-icon name="x" /></button>
            <figure class="max-h-full max-w-lg">
                <img :src="open?.url" alt="" class="max-h-[80dvh] w-auto rounded-2xl">
                <figcaption x-show="open?.caption" x-text="open?.caption" class="mt-3 text-center text-sm text-white/90"></figcaption>
            </figure>
        </div>
    </section>
</x-layouts.app>
