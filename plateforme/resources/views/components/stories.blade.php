@props(['stories'])

{{-- Affiches du moment, à la manière des statuts / stories --}}
<section class="mt-5" aria-label="Affiches du moment"
    x-data="storyViewer(@js($stories))" @keydown.escape.window="close()" @keydown.arrow-right.window="active && next()" @keydown.arrow-left.window="active && previous()">
    <div class="flex items-center justify-between">
        <h2 class="flex items-center gap-2 text-sm font-bold text-navy-900"><x-icon name="sparkles" class="size-4 text-gold-600" /> Affiches du moment</h2>
        <a href="{{ route('members.show', auth()->user()) }}#affiches" class="-my-2 inline-flex min-h-11 items-center px-1 text-xs font-semibold text-navy-700 underline underline-offset-4">Ajouter la mienne</a>
    </div>

    <div class="-mx-4 mt-3 flex gap-4 overflow-x-auto px-4 pb-1 [scrollbar-width:none] sm:mx-0 sm:px-0">
        <a href="{{ route('members.show', auth()->user()) }}#affiches" class="flex w-16 shrink-0 flex-col items-center gap-1.5 text-center">
            <span class="relative flex size-16 items-center justify-center rounded-full bg-white ring-2 ring-sand">
                <x-avatar :member="auth()->user()" class="size-14! ring-0!" />
                <span class="absolute -right-0.5 -bottom-0.5 flex size-6 items-center justify-center rounded-full bg-gold-400 text-navy-900 ring-2 ring-cream"><x-icon name="plus" class="size-3.5" /></span>
            </span>
            <span class="w-full truncate text-[11px] font-semibold text-muted">Ajouter</span>
        </a>

        <template x-for="(story, index) in stories" :key="story.id">
            <button type="button" class="flex w-16 shrink-0 flex-col items-center gap-1.5 text-center" @click="open(index)">
                <span class="rounded-full p-[3px] transition" :class="seen.includes(story.id) ? 'bg-sand' : 'bg-gradient-to-tr from-gold-400 via-crimson-500 to-navy-600'">
                    <span class="flex size-[58px] items-center justify-center overflow-hidden rounded-full bg-navy-100 text-sm font-bold text-navy-800 ring-2 ring-cream">
                        <template x-if="story.photo"><img :src="story.photo" alt="" class="size-full object-cover"></template>
                        <template x-if="!story.photo"><span x-text="story.initials"></span></template>
                    </span>
                </span>
                <span class="w-full truncate text-[11px] font-semibold text-navy-900" x-text="story.isMe ? 'Moi' : story.name"></span>
            </button>
        </template>

        <p x-show="stories.length === 0" class="self-center text-xs text-muted">Aucune affiche cette semaine. Soyez le premier à partager !</p>
    </div>

    {{-- Lecteur plein écran --}}
    <template x-teleport="body">
        <div x-show="active" x-cloak x-transition.opacity.duration.200ms class="fixed inset-0 z-[60] flex items-center justify-center bg-navy-900">
            <div class="relative flex h-full w-full max-w-md flex-col sm:h-[92dvh] sm:overflow-hidden sm:rounded-3xl"
                 @pointerdown="pause()" @pointerup="resume()" @pointerleave="resume()">
                <img :src="current?.url" alt="" aria-hidden="true" class="absolute inset-0 size-full scale-125 object-cover opacity-60 blur-2xl">
                <img :src="current?.url" alt="" class="absolute inset-0 size-full object-contain">
                <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-black/60 to-transparent"></div>
                <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-black/70 to-transparent"></div>

                {{-- Barres de progression --}}
                <div class="relative flex gap-1 px-3 pt-3">
                    <template x-for="(poster, i) in story?.posters ?? []" :key="i">
                        <span class="h-[3px] flex-1 overflow-hidden rounded-full bg-white/30">
                            <span class="block h-full bg-white" :style="`width: ${i < posterIndex ? 100 : (i === posterIndex ? progress : 0)}%`"></span>
                        </span>
                    </template>
                </div>

                <div class="relative flex items-center gap-3 px-4 pt-3 text-white">
                    <span class="flex size-9 items-center justify-center overflow-hidden rounded-full bg-navy-100 text-xs font-bold text-navy-800">
                        <template x-if="story?.photo"><img :src="story.photo" alt="" class="size-full object-cover"></template>
                        <template x-if="!story?.photo"><span x-text="story?.initials"></span></template>
                    </span>
                    <span class="flex-1 leading-tight">
                        <span class="block text-sm font-bold" x-text="story?.name"></span>
                        <span class="block text-[11px] text-white/70" x-text="current?.ago"></span>
                    </span>
                    <a :href="`/membres/${story?.id}`" class="rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold">Profil</a>
                    <button type="button" class="rounded-full p-2 hover:bg-white/10" @click.stop="close()" aria-label="Fermer"><x-icon name="x" /></button>
                </div>

                {{-- Zones tactiles : gauche = précédente, droite = suivante --}}
                <button type="button" class="absolute top-24 bottom-24 left-0 w-1/3" @click="previous()" aria-label="Affiche précédente"></button>
                <button type="button" class="absolute top-24 right-0 bottom-24 w-2/3" @click="next()" aria-label="Affiche suivante"></button>

                <p x-show="current?.caption" x-text="current?.caption" class="absolute inset-x-0 bottom-0 px-5 pb-8 text-center text-[15px] font-medium text-white"></p>
            </div>
        </div>
    </template>
</section>
