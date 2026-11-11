@if (session('status'))
    <div x-data="{ open: true }" x-show="open" x-transition class="flex items-start gap-3 rounded-2xl bg-navy-800 px-4 py-3 text-sm text-white shadow-(--shadow-card)" role="status">
        <x-icon name="check" class="mt-0.5 size-4 text-gold-400" />
        <p class="flex-1">{{ session('status') }}</p>
        <button type="button" @click="open = false" class="text-white/60 hover:text-white" aria-label="Fermer"><x-icon name="x" class="size-4" /></button>
    </div>
@endif
