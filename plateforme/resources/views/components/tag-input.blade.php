@props(['name', 'values' => [], 'placeholder' => '', 'suggestions' => [], 'max' => 5])

{{-- Saisie d'étiquettes : on écrit, on touche « Ajouter », chaque valeur devient une étiquette qu'on peut retirer. --}}
<div x-data="{
        tags: @js(array_values($values)),
        draft: '',
        add() {
            const value = this.draft.trim();
            if (value && ! this.tags.some(tag => tag.toLowerCase() === value.toLowerCase()) && this.tags.length < {{ $max }}) {
                this.tags.push(value);
            }
            this.draft = '';
        },
    }">
    <div class="flex gap-2">
        <input id="{{ $name }}" type="text" x-model="draft" @keydown.enter.prevent="add()" maxlength="60"
               placeholder="{{ $placeholder }}" class="input" @if ($suggestions) list="{{ $name }}-suggestions" @endif>
        <button type="button" class="btn-light shrink-0 px-4" @click="add()" :disabled="tags.length >= {{ $max }}"><x-icon name="plus" class="size-4" /> Ajouter</button>
    </div>
    @if ($suggestions)
        <datalist id="{{ $name }}-suggestions">
            @foreach ($suggestions as $suggestion)
                <option value="{{ $suggestion }}"></option>
            @endforeach
        </datalist>
    @endif

    <div class="mt-2.5 flex flex-wrap gap-2" x-show="tags.length">
        <template x-for="(tag, index) in tags" :key="tag">
            <span class="chip bg-navy-700 py-1 pr-1 pl-3.5 text-sm text-white">
                <span x-text="tag"></span>
                <input type="hidden" name="{{ $name }}[]" :value="tag">
                <button type="button" class="-my-1 flex size-11 items-center justify-center rounded-full hover:bg-white/20" @click="tags.splice(index, 1)" :aria-label="`Retirer ${tag}`"><x-icon name="x" class="size-3.5" /></button>
            </span>
        </template>
    </div>
    <p class="mt-1.5 text-xs text-muted" x-show="tags.length >= {{ $max }}" x-cloak>{{ $max }} au maximum.</p>
</div>
