@php
    use App\Enums\ChurchRole;
    use App\Enums\LeaderStatus;

    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $selectedRoles = old('church_roles', $member->church_roles ?? []);
@endphp

<x-dynamic-component :component="$isCreation ? 'layouts.guest' : 'layouts.app'" :title="$isCreation ? 'Créer mon profil' : 'Mon profil'">
    <div @class(['mx-auto w-full max-w-2xl' => ! $isCreation])>
        @if ($isCreation)
            <a href="{{ route('register') }}" class="-my-2 inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-muted hover:text-navy-800"><x-icon name="arrow-left" class="size-4" /> Étape 1 : mon compte</a>
            <div class="mt-4 flex items-center gap-3">
                <img src="/images/logo-rond.png" alt="" class="size-12 rounded-full ring-1 ring-sand">
                <div>
                    <p class="eyebrow">Étape 2 sur 2</p>
                    <h1 class="text-2xl font-serif font-semibold tracking-tight text-navy-900">Créer mon profil</h1>
                </div>
            </div>
            <div class="mt-5 flex gap-2" aria-hidden="true"><span class="h-1.5 flex-1 rounded-full bg-gold-400"></span><span class="h-1.5 flex-1 rounded-full bg-gold-400"></span></div>
            <p class="mt-4 text-sm text-muted">Votre fiche sera visible par les membres connectés : elle permet de vous trouver pour votre métier ou vos compétences.</p>
        @else
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="eyebrow">Mon profil</p>
                    <h1 class="mt-1 text-3xl font-serif font-semibold tracking-tight text-navy-900">Modifier ma fiche</h1>
                </div>
                <a href="{{ route('members.show', $member) }}" class="btn-light"><x-icon name="eye" class="size-4" /> Voir ma fiche</a>
            </div>
            @if ($member->leader_status === LeaderStatus::Pending)
                <p class="mt-4 rounded-2xl bg-gold-50 p-4 text-sm text-navy-900 ring-1 ring-gold-100"><strong>Droits en attente.</strong> Vous vous êtes déclaré pasteur ou diacre : un administrateur doit valider vos droits. En attendant, votre compte fonctionne comme celui d’un membre.</p>
            @elseif ($member->leader_status === LeaderStatus::Rejected)
                <p class="mt-4 rounded-2xl bg-crimson-50 p-4 text-sm text-crimson-700">Votre demande de droits de pasteur ou diacre n’a pas été validée. Contactez l’administrateur si c’est une erreur.</p>
            @endif
        @endif

        <form method="POST" action="{{ route('profile.update') }}" data-draft="profil-{{ $member->id }}" enctype="multipart/form-data" class="mt-6 grid grid-cols-1 gap-5"
              x-data="{ preview: @js($member->photo_url), roles: @js($selectedRoles) }">
            @csrf
            @method('PUT')

            <section class="card grid grid-cols-1 gap-5 p-5 sm:p-6">
                <div class="flex items-center gap-4">
                    <label for="photo" class="relative cursor-pointer">
                        <template x-if="preview"><img :src="preview" alt="" class="size-24 rounded-full object-cover ring-4 ring-gold-100"></template>
                        <template x-if="!preview"><span class="flex size-24 items-center justify-center rounded-full bg-navy-50 text-navy-500 ring-4 ring-gold-100"><x-icon name="camera" class="size-8" /></span></template>
                        <span class="absolute -right-1 -bottom-1 flex size-8 items-center justify-center rounded-full bg-gold-400 text-navy-900 ring-4 ring-white"><x-icon name="plus" class="size-4" /></span>
                    </label>
                    <div class="text-sm">
                        <p class="font-bold text-navy-900">Ma photo</p>
                        <p class="text-muted">JPG, PNG ou WebP, <strong>3 Mo maximum</strong>.</p>
                        @error('photo')<p class="mt-1 text-xs font-semibold text-crimson-600">{{ $message }}</p>@enderror
                    </div>
                    <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" data-max-mb="3" class="sr-only"
                           @change="const f = $event.target.files[0]; if (f && f.size <= 3 * 1024 * 1024) preview = URL.createObjectURL(f)">
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-field label="Prénom" name="first_name">
                        <input id="first_name" name="first_name" value="{{ old('first_name', $member->first_name) }}" required class="input" autocomplete="given-name">
                    </x-field>
                    <x-field label="Nom" name="last_name">
                        <input id="last_name" name="last_name" value="{{ old('last_name', $member->last_name) }}" required class="input" autocomplete="family-name">
                    </x-field>
                </div>
            </section>

            <section class="card grid grid-cols-1 gap-4 p-5 sm:p-6"
                     x-data="{ style: @js(old('cover_style', $member->cover_path ? 'photo' : ($member->cover_style ?? 'neutre'))), coverPreview: @js($member->cover_path ? $member->coverBackground() : null) }">
                <h2 class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="sparkles" class="text-gold-600" /> Ma couverture</h2>
                <p class="-mt-2 text-sm text-muted">Le bandeau en haut de votre fiche. Neutre par défaut : choisissez une couleur ou votre propre image.</p>
                <div class="grid grid-cols-4 gap-2 sm:grid-cols-5">
                    @foreach (config('tegv.covers') as $key => $cover)
                        <label class="cursor-pointer">
                            <input type="radio" name="cover_style" value="{{ $key }}" x-model="style" class="peer sr-only">
                            <span class="block h-14 rounded-2xl ring-1 ring-sand peer-checked:ring-4 peer-checked:ring-navy-600 peer-focus-visible:outline-2 peer-focus-visible:outline-gold-400" style="background: {{ $cover['css'] }}"></span>
                            <span class="mt-1 block text-center text-[11px] font-semibold text-muted">{{ $cover['label'] }}</span>
                        </label>
                    @endforeach
                    <label class="cursor-pointer">
                        <input type="radio" name="cover_style" value="photo" x-model="style" class="peer sr-only">
                        <span class="relative flex h-14 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-gold-300 bg-gold-50 text-gold-600 peer-checked:ring-4 peer-checked:ring-navy-600" :style="coverPreview ? `background: ${coverPreview}` : ''">
                            <x-icon name="camera" x-show="! coverPreview" />
                        </span>
                        <span class="mt-1 block text-center text-[11px] font-semibold text-muted">Mon image</span>
                    </label>
                </div>
                <div x-show="style === 'photo'" x-cloak>
                    <x-field label="Image de couverture" name="cover" hint="Format paysage conseillé. JPG, PNG ou WebP, 6 Mo maximum.">
                        <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp" data-max-mb="6" class="input py-2.5"
                               @change="const f = $event.target.files[0]; if (f && f.size <= 6 * 1024 * 1024) coverPreview = `center / cover no-repeat url(${URL.createObjectURL(f)})`">
                    </x-field>
                </div>
            </section>

            <section class="card grid grid-cols-1 gap-5 p-5 sm:p-6">
                <h2 class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="briefcase" class="text-gold-600" /> Mon métier</h2>
                <x-field label="Secteur d’activité" name="sector" hint="Le grand domaine dans lequel vous travaillez : il sert de filtre dans l’annuaire.">
                    <select id="sector" name="sector" required class="input">
                        <option value="">Choisir dans la liste…</option>
                        @foreach (config('tegv.sectors') as $key => $label)
                            <option value="{{ $key }}" @selected(old('sector', $member->sector) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-field>
                <x-field label="Profil principal" name="primary_profile" hint="Choisissez dans la liste ou écrivez votre métier s’il n’y est pas.">
                    <input id="primary_profile" name="primary_profile" list="trades" value="{{ old('primary_profile', $member->primary_profile) }}" required class="input" placeholder="Ex. : Plomberie" autocomplete="off">
                    <datalist id="trades">
                        @foreach (config('tegv.trades') as $trade)
                            <option value="{{ $trade }}"></option>
                        @endforeach
                    </datalist>
                </x-field>
                <x-field label="Profils secondaires" name="secondary_profiles" hint="Choix multiple : choisissez ou écrivez une compétence, puis touchez « Ajouter ».">
                    <x-tag-input name="secondary_profiles" :values="old('secondary_profiles', $member->secondary_profiles ?? [])" :suggestions="config('tegv.trades')" placeholder="Ex. : Carrelage" />
                </x-field>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-[1fr_1fr_9rem]">
                    <x-field label="Entreprise" name="company">
                        <input id="company" name="company" value="{{ old('company', $member->company) }}" class="input" autocomplete="organization">
                    </x-field>
                    <x-field label="Poste" name="job_title">
                        <input id="job_title" name="job_title" value="{{ old('job_title', $member->job_title) }}" class="input" autocomplete="organization-title">
                    </x-field>
                    <x-field label="Expérience" name="years_experience">
                        <div class="relative">
                            <input id="years_experience" name="years_experience" type="number" min="0" max="70" inputmode="numeric" value="{{ old('years_experience', $member->years_experience) }}" class="input pr-12">
                            <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-sm text-muted">ans</span>
                        </div>
                    </x-field>
                </div>
            </section>

            <section class="card grid grid-cols-1 gap-4 p-5 sm:p-6">
                <h2 class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="cross" class="text-gold-600" /> Mes rôles à l’église</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach (ChurchRole::cases() as $role)
                        <label class="cursor-pointer">
                            <input type="checkbox" name="church_roles[]" value="{{ $role->value }}" x-model="roles" class="peer sr-only">
                            <span class="chip bg-cream px-4 py-2 text-sm text-navy-800 ring-1 ring-sand peer-checked:bg-navy-700 peer-checked:text-white peer-checked:ring-navy-700 peer-focus-visible:outline-2 peer-focus-visible:outline-gold-400">{{ $role->label() }}</span>
                        </label>
                    @endforeach
                </div>
                <div x-show="roles.includes('autre')" x-cloak>
                    <x-field label="Mes autres rôles" name="other_roles" hint="Écrivez un rôle puis touchez « Ajouter ». Vous pouvez en ajouter plusieurs.">
                        <x-tag-input name="other_roles" :values="old('other_roles', $member->other_roles ?? [])" placeholder="Ex. : Accueil" />
                    </x-field>
                </div>
                <p x-show="roles.includes('pasteur') || roles.includes('diacre')" x-cloak class="rounded-2xl bg-gold-50 p-3 text-xs text-navy-900">Les droits de pasteur et de diacre s’activent après validation par un administrateur.</p>
                @error('church_roles.*')<p class="text-xs font-semibold text-crimson-600">{{ $message }}</p>@enderror
            </section>

            <section class="card grid grid-cols-1 gap-5 p-5 sm:p-6">
                <h2 class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="phone" class="text-gold-600" /> Me contacter</h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <x-field label="Numéro WhatsApp" name="whatsapp">
                        <input id="whatsapp" name="whatsapp" type="tel" inputmode="tel" value="{{ old('whatsapp', $member->whatsapp) }}" required class="input" placeholder="+226 70 00 00 00" autocomplete="tel">
                    </x-field>
                    <x-field label="Email de contact" name="contact_email">
                        <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $member->contact_email) }}" class="input">
                    </x-field>
                </div>
            </section>

            <section class="card grid grid-cols-1 gap-4 p-5 sm:p-6">
                <h2 class="flex items-center gap-2 font-bold text-navy-900"><x-icon name="cake" class="text-gold-600" /> Mon anniversaire</h2>
                <p class="text-sm text-muted">Seuls le <strong>jour</strong> et le <strong>mois</strong> sont demandés, uniquement pour connaître le jour de votre anniversaire, jamais votre âge. Ce jour-là, votre prénom s’affiche sur l’accueil.</p>
                <div class="grid grid-cols-[7rem_1fr] gap-3">
                    <x-field label="Jour" name="birth_day">
                        <select id="birth_day" name="birth_day" class="input">
                            <option value="">—</option>
                            @for ($day = 1; $day <= 31; $day++)
                                <option value="{{ $day }}" @selected((int) old('birth_day', $member->birth_day) === $day)>{{ $day }}</option>
                            @endfor
                        </select>
                    </x-field>
                    <x-field label="Mois" name="birth_month">
                        <select id="birth_month" name="birth_month" class="input">
                            <option value="">—</option>
                            @foreach ($months as $index => $month)
                                <option value="{{ $index + 1 }}" @selected((int) old('birth_month', $member->birth_month) === $index + 1)>{{ ucfirst($month) }}</option>
                            @endforeach
                        </select>
                    </x-field>
                </div>
            </section>

            <label class="card flex cursor-pointer items-center gap-4 p-5">
                <input type="checkbox" name="is_tegv_christian" value="1" @checked(old('is_tegv_christian', $member->is_tegv_christian)) class="size-5 rounded accent-navy-700">
                <span>
                    <span class="block font-bold text-navy-900">Je suis chrétien de TEGV</span>
                    <span class="block text-sm text-muted">Vous recevez alors les informations publiées par les pasteurs et diacres.</span>
                </span>
            </label>

            <button class="btn-primary w-full py-4 text-base">{{ $isCreation ? 'Créer mon profil' : 'Enregistrer' }}</button>
        </form>

        @unless ($isCreation)
            <section class="mt-8 grid grid-cols-1 gap-3 border-t border-sand pt-6">
                <h2 class="font-bold text-navy-900">Mon compte</h2>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-light w-full"><x-icon name="logout" class="size-4" /> Se déconnecter</button>
                </form>
                <a href="{{ route('account-deletion.create') }}" class="btn-danger w-full">Supprimer mon compte</a>
            </section>
        @endunless
    </div>
</x-dynamic-component>
