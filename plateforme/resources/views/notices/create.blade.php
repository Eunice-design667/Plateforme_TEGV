@php
    use App\Enums\ReportType;

    $initialTab = $errors->report->any() ? 'report' : ($errors->absence->any() ? 'absence' : $tab);
@endphp

<x-layouts.app title="Absence et signalement">
    <div class="mx-auto max-w-2xl" x-data="{ tab: @js($initialTab) }">
        <p class="eyebrow">Prévenir</p>
        <h1 class="mt-1 text-3xl font-serif font-semibold tracking-tight text-navy-900">Absence et signalement</h1>
        <p class="mt-1 flex items-center gap-1.5 text-sm text-muted"><x-icon name="shield" class="size-4" /> Visible uniquement par les pasteurs et les diacres.</p>

        <div class="mt-5 grid grid-cols-2 rounded-full bg-white p-1.5 ring-1 ring-sand" role="tablist">
            <button type="button" role="tab" :aria-selected="tab === 'absence'" @click="tab = 'absence'" :class="tab === 'absence' ? 'bg-navy-700 text-white shadow' : 'text-muted'" class="flex min-h-11 items-center justify-center gap-2 rounded-full py-2.5 text-sm font-bold transition"><x-icon name="calendar" class="size-4" /> Absence</button>
            <button type="button" role="tab" :aria-selected="tab === 'report'" @click="tab = 'report'" :class="tab === 'report' ? 'bg-crimson-600 text-white shadow' : 'text-muted'" class="flex min-h-11 items-center justify-center gap-2 rounded-full py-2.5 text-sm font-bold transition"><x-icon name="alert" class="size-4" /> Signaler</button>
        </div>

        <form x-show="tab === 'absence'" method="POST" action="{{ route('absences.store') }}" data-draft="absence-{{ $user->id }}" class="card mt-5 grid grid-cols-1 gap-5 p-5 sm:p-6">
            @csrf
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-field label="Prénom" name="first_name" bag="absence">
                    <input id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="input">
                </x-field>
                <x-field label="Nom" name="last_name" bag="absence">
                    <input id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="input">
                </x-field>
            </div>
            <x-field label="Motif" name="reason" bag="absence">
                <textarea id="reason" name="reason" rows="3" required class="input" placeholder="Voyage, maladie, travail…">{{ old('reason') }}</textarea>
            </x-field>
            <div class="grid grid-cols-2 gap-3">
                <x-field label="Du" name="starts_on" bag="absence">
                    <input id="starts_on" name="starts_on" type="date" value="{{ old('starts_on', today()->toDateString()) }}" required class="input">
                </x-field>
                <x-field label="Au" name="ends_on" bag="absence">
                    <input id="ends_on" name="ends_on" type="date" value="{{ old('ends_on') }}" required class="input">
                </x-field>
            </div>
            <label class="flex items-center gap-3 text-sm font-semibold text-navy-900">
                <input type="checkbox" name="is_tegv_christian" value="1" @checked(old('is_tegv_christian', $user->is_tegv_christian)) class="size-5 rounded accent-navy-700"> Je suis chrétien de TEGV
            </label>
            <button class="btn-primary w-full py-3.5">Prévenir de mon absence</button>
        </form>

        <form x-show="tab === 'report'" x-cloak method="POST" action="{{ route('reports.store') }}" data-draft="signalement-{{ $user->id }}" class="card mt-5 grid grid-cols-1 gap-5 p-5 sm:p-6">
            @csrf
            <fieldset>
                <legend class="label">Type</legend>
                <div class="grid grid-cols-2 gap-3">
                    @foreach (ReportType::cases() as $type)
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="{{ $type->value }}" @checked(old('type', ReportType::Deces->value) === $type->value) class="peer sr-only">
                            <span class="flex items-center justify-center gap-2 rounded-2xl bg-cream py-3 text-sm font-bold text-navy-800 ring-1 ring-sand peer-checked:bg-crimson-50 peer-checked:text-crimson-700 peer-checked:ring-2 peer-checked:ring-crimson-500">{{ $type->label() }}</span>
                        </label>
                    @endforeach
                </div>
                @error('type', 'report')<p class="mt-1.5 text-xs font-semibold text-crimson-600">{{ $message }}</p>@enderror
            </fieldset>
            <x-field label="Personne concernée" name="concerned_person" bag="report">
                <input id="concerned_person" name="concerned_person" value="{{ old('concerned_person') }}" required class="input" placeholder="Nom et prénom">
            </x-field>
            <x-field label="Description" name="description" bag="report">
                <textarea id="description" name="description" rows="4" required class="input" placeholder="Que s’est-il passé ? Comment joindre la famille ?">{{ old('description') }}</textarea>
            </x-field>
            <button class="btn w-full bg-crimson-600 py-3.5 text-white hover:bg-crimson-700">Envoyer le signalement</button>
        </form>
    </div>
</x-layouts.app>
