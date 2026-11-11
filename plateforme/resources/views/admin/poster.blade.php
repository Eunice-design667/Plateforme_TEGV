<!DOCTYPE html>
<html lang="fr">
<head>
    <x-layouts.head title="Affiche visiteurs" />
    <style>
        @page { size: A4; margin: 0; }
        @media print { .no-print { display: none !important; } body { background: white; } }
    </style>
</head>
<body class="bg-sand/60 print:bg-white">
    <div class="no-print mx-auto flex max-w-[210mm] justify-end gap-2 px-4 py-4">
        <button onclick="window.print()" class="btn-primary"><x-icon name="printer" class="size-4" /> Imprimer</button>
    </div>

    <article class="mx-auto flex h-[297mm] w-[210mm] flex-col overflow-hidden bg-white shadow-xl print:shadow-none">
        <header class="relative h-[78mm] shrink-0 bg-navy-900 text-white">
            <img src="/images/eglise/culte.jpg" alt="" class="absolute inset-0 size-full object-cover opacity-45">
            <div class="absolute inset-0 bg-gradient-to-b from-navy-900/40 to-navy-900"></div>
            <div class="relative flex h-full flex-col items-center justify-center px-12 text-center">
                <img src="/images/logo-rond.png" alt="Logo TEGV" class="size-24 rounded-full ring-4 ring-white/40">
                <p class="mt-4 text-sm font-bold tracking-[0.2em] text-gold-300 uppercase">Temple Évangélique Grâce et Victoire</p>
                <h1 class="mt-2 text-5xl font-serif font-semibold tracking-tight">Bienvenue !</h1>
            </div>
        </header>

        <div class="flex flex-1 flex-col items-center px-14 pt-10 text-center">
            <p class="text-2xl font-bold text-navy-900">Vous nous rendez visite ? <span class="text-crimson-600">Faites-vous connaître.</span></p>
            <div class="mt-8 rounded-[2rem] bg-white p-5 ring-4 ring-gold-400 [&_svg]:size-[88mm]">{!! $svg !!}</div>

            <ol class="mt-10 grid w-full grid-cols-3 gap-5 text-left">
                @foreach ([['camera', 'Ouvrez l’appareil photo', 'de votre téléphone'], ['qr', 'Visez le code', 'et touchez le lien qui apparaît'], ['edit', 'Remplissez le formulaire', 'en moins d’une minute']] as $i => [$icon, $title, $text])
                    <li class="rounded-3xl bg-cream p-5">
                        <span class="flex items-center gap-2">
                            <span class="flex size-9 items-center justify-center rounded-full bg-navy-800 text-sm font-extrabold text-white">{{ $i + 1 }}</span>
                            <x-icon :name="$icon" class="size-6 text-gold-600" />
                        </span>
                        <p class="mt-3 font-extrabold text-navy-900">{{ $title }}</p>
                        <p class="text-sm text-muted">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
            <p class="mt-6 text-xs text-muted">Aucune application à installer. {{ preg_replace('#^https?://#', '', $visitorUrl) }}</p>
        </div>

        <footer class="flex items-center justify-between bg-gold-400 px-12 py-5 text-navy-900">
            <span class="font-serif text-xl italic">Solo Deo Gloria</span>
            <span class="text-sm font-bold">Culte tous les dimanches · 08h – 10h30</span>
        </footer>
    </article>
</body>
</html>
