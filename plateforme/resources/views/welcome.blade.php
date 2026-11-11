@php
    $points = [
        ['users', 'Trouver un frère ou une sœur', 'Experts et main-d’œuvre de l’église.'],
        ['calendar', 'Prévenir les responsables', 'Une absence, un décès ou un souci : les pasteurs sont informés.'],
        ['megaphone', 'Suivre la vie de l’église', 'Les informations et les anniversaires du jour.'],
    ];
    $ticker = ['Le culte commence à 8h00', 'Étude biblique le mardi à 19h', 'Réunion de prière le vendredi à 19h', 'Solo Deo Gloria', 'Ouaga 2000, non loin de Salitas'];
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <x-layouts.head />
</head>
<body class="min-h-dvh overflow-x-hidden bg-navy-900 text-white">
    {{-- Couverture --}}
    <header class="relative isolate flex min-h-dvh flex-col overflow-hidden">
        {{-- Ambiance : halo doré, scintillements --}}
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute top-[30%] left-1/2 size-[40rem] -translate-x-1/2 rounded-full bg-gold-400/15 blur-3xl" style="animation: glow 8s ease-in-out infinite"></div>
            <div class="absolute -top-40 -left-40 size-96 rounded-full bg-crimson-600/20 blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,transparent_30%,rgb(10_14_40/0.9)_85%)]"></div>
            @foreach ([[8, 18], [22, 62], [35, 12], [52, 30], [68, 15], [80, 58], [91, 25], [15, 80], [60, 82], [45, 70], [75, 40], [5, 45]] as $i => [$x, $y])
                <span class="absolute size-1.5 rotate-45 bg-gold-300" style="left: {{ $x }}%; top: {{ $y }}%; animation: twinkle {{ 3 + $i % 4 }}s ease-in-out {{ $i * 0.45 }}s infinite"></span>
            @endforeach
        </div>

        <nav class="reveal mx-auto flex w-full max-w-6xl items-center justify-between px-5 pt-5 sm:px-8">
            <span class="flex items-center gap-3">
                <img src="/images/logo-rond.png" alt="Logo du TEGV" class="size-11 rounded-full ring-2 ring-gold-400/70">
                <span class="hidden text-xs leading-tight font-semibold tracking-[0.18em] text-white/80 uppercase sm:block">Temple Évangélique<br>Grâce et Victoire</span>
            </span>
            <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/25 transition hover:bg-white/10">Se connecter</a>
        </nav>

        {{-- Éventail de photos : elles glissent d'une place le long de l'arc toutes les 3 secondes --}}
        @php($count = count($photos))
        <div class="relative mx-auto mt-10 h-[19rem] w-full max-w-5xl [mask-image:linear-gradient(to_bottom,black_72%,transparent)] sm:mt-10 sm:h-[19rem] lg:h-[22rem]"
             x-data="fanCarousel({{ $count }})" aria-hidden="true">
            @foreach ($photos as $index => $photo)
                @php($k = $index > $count / 2 ? $index - $count : $index)
                <div class="fan-card w-[44vw] max-w-[13rem] sm:w-48 lg:w-56"
                     style="--k: {{ $k }}; --ak: {{ min(abs($k), 4) }}; opacity: {{ abs($k) > 3 ? 0 : 1 }}"
                     :style="slot({{ $index }})">
                    <div class="overflow-hidden rounded-2xl shadow-[0_25px_50px_-12px_rgb(0_0_0/0.7)] ring-1 ring-white/20">
                        <img src="{{ $photo }}" alt="" class="aspect-[3/4] w-full object-cover" style="animation: ken-burns {{ 14 + $index * 2 }}s ease-in-out infinite alternate">
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Titre --}}
        <div class="relative z-30 mx-auto -mt-10 px-5 text-center sm:-mt-12">
            <p class="reveal font-serif text-4xl text-gold-300 italic sm:text-6xl" style="animation-delay: 0.6s">Bienvenue au</p>
            <h1 class="reveal relative inline-block font-sans text-[4.6rem] leading-none font-black tracking-[0.06em] text-white sm:text-[8rem]" style="animation-delay: 0.75s">
                TEGV
                <span class="absolute -top-1 -right-9 rotate-12 rounded-md bg-crimson-600 px-2 py-1 text-center text-[10px] leading-tight font-extrabold tracking-normal shadow-lg sm:-right-14 sm:text-sm">DIM.<br>8H</span>
            </h1>
            <p class="reveal mt-1 text-[11px] font-semibold tracking-[0.3em] text-white/70 uppercase sm:text-sm" style="animation-delay: 0.9s">Temple Évangélique Grâce et Victoire</p>
        </div>

        <div class="reveal relative z-30 mx-auto mt-7 grid grid-cols-1 w-full max-w-lg gap-3 px-5 whitespace-nowrap sm:grid-cols-2" style="animation-delay: 1.05s">
            <a href="{{ route('register') }}" class="btn-gold py-3.5">Créer mon compte <x-icon name="arrow-right" class="size-4" /></a>
            <a href="{{ route('login') }}" class="btn py-3.5 text-white ring-1 ring-white/30 hover:bg-white/10">J’ai déjà un compte</a>
        </div>

        {{-- Bandeau qui défile --}}
        <div class="mt-auto pt-10"></div>
        <div class="overflow-hidden border-y border-white/10 bg-crimson-600 py-3 pt-3" aria-hidden="true">
            <div class="flex w-max gap-8 whitespace-nowrap" style="animation: marquee 32s linear infinite">
                @foreach ([1, 2] as $copy)
                    @foreach ($ticker as $item)
                        <span class="flex items-center gap-8 text-sm font-bold tracking-[0.2em] uppercase">{{ $item }} <span class="text-gold-300">✦</span></span>
                    @endforeach
                @endforeach
            </div>
        </div>
    </header>

    {{-- Présentation --}}
    <main class="bg-cream text-ink">
        <div class="mx-auto grid grid-cols-1 max-w-6xl gap-10 px-5 py-14 sm:px-8 lg:grid-cols-[1fr_1.1fr] lg:items-center lg:py-20">
            <div>
                <p class="eyebrow">La plateforme de la famille TEGV</p>
                <h2 class="mt-2 font-serif text-4xl leading-tight font-semibold text-navy-900 sm:text-5xl">L’église, <em class="text-crimson-600">dans votre téléphone</em>.</h2>
                <p class="mt-4 max-w-md text-muted">« {{ config('tegv.motto') }} »</p>

                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach (config('tegv.programme') as $item)
                        <span @class([
                            'rounded-full px-3 py-1.5 text-xs font-bold',
                            'bg-crimson-600 text-white' => $item['weekday'] === 0,
                            'bg-gold-400 text-navy-900' => $item['weekday'] !== 0,
                        ])>{{ $item['title'] }} · {{ mb_substr($item['day'], 0, 3) }}. {{ $item['time'] }}</span>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3">
                @foreach ($points as [$icon, $title, $text])
                    <div class="flex items-center gap-4 rounded-3xl bg-white p-4 ring-1 ring-sand">
                        <span class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-navy-800 text-gold-300"><x-icon :name="$icon" /></span>
                        <span>
                            <span class="block font-bold text-navy-900">{{ $title }}</span>
                            <span class="block text-sm text-muted">{{ $text }}</span>
                        </span>
                    </div>
                @endforeach

                <a href="{{ route('visitors.create') }}" class="mt-2 flex items-center gap-3 rounded-3xl border-2 border-dashed border-gold-400 bg-gold-50 p-4 text-sm font-semibold text-navy-900">
                    <x-icon name="qr" class="size-6 text-gold-600" />
                    <span class="flex-1">Scannez le QR code affiché et remplissez la fiche visiteur.</span>
                    <x-icon name="arrow-right" class="size-4 text-gold-600" />
                </a>
            </div>
        </div>

        <footer class="border-t border-sand py-6 text-center">
            <p class="font-serif text-lg text-crimson-600 italic">Solo Deo Gloria</p>
            <p class="mt-1 text-xs text-muted">{{ config('tegv.full_name') }} · {{ config('tegv.phone') }}</p>
        </footer>
    </main>
</body>
</html>
