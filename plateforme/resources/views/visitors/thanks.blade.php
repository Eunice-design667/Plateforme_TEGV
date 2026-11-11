<x-layouts.guest title="Merci">
    <div class="flex flex-1 flex-col items-center justify-center text-center">
        <span class="flex size-20 items-center justify-center rounded-full bg-gold-400 text-navy-900 shadow-(--shadow-card)"><x-icon name="heart" class="size-9" /></span>
        <h1 class="mt-6 text-3xl font-serif font-semibold tracking-tight text-navy-900">Merci{{ session('visitor_first_name') ? ', '.session('visitor_first_name') : '' }} !</h1>
        <p class="mt-3 max-w-sm text-[15px] leading-relaxed text-muted">Votre visite est bien enregistrée. Nous vous enverrons un petit message sur WhatsApp. Que Dieu vous bénisse, et à dimanche prochain !</p>
        <div class="card mt-8 w-full p-5 text-left">
            <p class="eyebrow">Nos rendez-vous</p>
            <ul class="mt-3 grid grid-cols-1 gap-2 text-sm">
                @foreach (config('tegv.programme') as $item)
                    <li class="flex justify-between"><span class="font-semibold text-navy-900">{{ $item['title'] }}</span><span class="text-muted">{{ $item['day'] }} {{ $item['time'] }}</span></li>
                @endforeach
            </ul>
        </div>
        <p class="mt-8 font-serif text-lg text-crimson-600 italic">Solo Deo Gloria</p>
    </div>
</x-layouts.guest>
