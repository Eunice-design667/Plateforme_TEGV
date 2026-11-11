@props(['title' => null])

@php
    $user = auth()->user();
    $nav = [
        ['route' => 'home', 'label' => 'Accueil', 'icon' => 'home', 'match' => 'home'],
        ['route' => 'directory', 'label' => 'Annuaire', 'icon' => 'users', 'match' => 'directory|members.*'],
        ['route' => 'notices.create', 'label' => 'Prévenir', 'icon' => 'calendar', 'match' => 'notices.*'],
        ['route' => 'announcements.create', 'label' => 'Proposer une info', 'icon' => 'megaphone', 'match' => 'announcements.create'],
        ['route' => 'profile.edit', 'label' => 'Mon profil', 'icon' => 'user', 'match' => 'profile.*'],
    ];
    if ($user->canSeeAnnouncements()) {
        array_splice($nav, 3, 0, [['route' => 'announcements.index', 'label' => 'Informations', 'icon' => 'book', 'match' => 'announcements.index']]);
    }
    $mobileNav = [
        ['route' => 'home', 'label' => 'Accueil', 'icon' => 'home', 'match' => 'home'],
        ['route' => 'directory', 'label' => 'Annuaire', 'icon' => 'users', 'match' => 'directory|members.*'],
        ['route' => 'notices.create', 'label' => 'Prévenir', 'icon' => 'calendar', 'match' => 'notices.*'],
        $user->isLeader()
            ? ['route' => 'leader.dashboard', 'label' => 'Responsables', 'icon' => 'shield', 'match' => 'leader.*']
            : ['route' => 'announcements.create', 'label' => 'Infos', 'icon' => 'megaphone', 'match' => 'announcements.*'],
        ['route' => 'profile.edit', 'label' => 'Profil', 'icon' => 'user', 'match' => 'profile.*'],
    ];
    $isActive = fn (string $pattern) => request()->routeIs(...explode('|', $pattern));
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <x-layouts.head :title="$title" />
</head>
<body class="min-h-dvh" @if (collect($errors->getBags())->contains(fn ($bag) => $bag->any())) data-has-errors @endif>
    <div class="lg:flex">
        {{-- Barre latérale (ordinateur) --}}
        <aside class="sticky top-0 hidden h-dvh w-72 shrink-0 flex-col border-r border-sand bg-white/70 px-5 py-6 backdrop-blur lg:flex">
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-2">
                <img src="/images/logo-rond.png" alt="" class="size-11 rounded-full ring-1 ring-sand">
                <span class="leading-tight">
                    <span class="block text-lg font-extrabold tracking-tight text-navy-800">TEGV</span>
                    <span class="block font-serif text-xs text-crimson-600 italic">Solo Deo Gloria</span>
                </span>
            </a>

            <nav class="mt-8 flex flex-1 flex-col gap-1" aria-label="Navigation principale">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}" @class([
                        'flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition',
                        'bg-gold-50 text-navy-900 ring-1 ring-gold-100' => $isActive($item['match']),
                        'text-muted hover:bg-cream hover:text-navy-800' => ! $isActive($item['match']),
                    ])>
                        <x-icon :name="$item['icon']" @class(['text-gold-600' => $isActive($item['match'])]) />
                        {{ $item['label'] }}
                    </a>
                @endforeach

                @if ($user->isLeader() || $user->is_admin)
                    <p class="mt-6 mb-1 px-3 text-[11px] font-bold tracking-[0.14em] text-muted/80 uppercase">Responsables</p>
                @endif
                @if ($user->isLeader())
                    <a href="{{ route('leader.dashboard') }}" @class([
                        'flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition',
                        'bg-gold-50 text-navy-900 ring-1 ring-gold-100' => request()->routeIs('leader.*'),
                        'text-muted hover:bg-cream hover:text-navy-800' => ! request()->routeIs('leader.*'),
                    ])><x-icon name="shield" /> Espace pasteur et diacre</a>
                @endif
                @if ($user->is_admin)
                    <a href="{{ route('admin.rights.index') }}" @class([
                        'flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition',
                        'bg-gold-50 text-navy-900 ring-1 ring-gold-100' => request()->routeIs('admin.rights.*'),
                        'text-muted hover:bg-cream hover:text-navy-800' => ! request()->routeIs('admin.rights.*'),
                    ])><x-icon name="check" /> Validation des droits</a>
                    <a href="{{ route('admin.qr-code') }}" @class([
                        'flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition',
                        'bg-gold-50 text-navy-900 ring-1 ring-gold-100' => request()->routeIs('admin.qr-code', 'admin.poster'),
                        'text-muted hover:bg-cream hover:text-navy-800' => ! request()->routeIs('admin.qr-code', 'admin.poster'),
                    ])><x-icon name="qr" /> QR code visiteurs</a>
                @endif
            </nav>

            <div class="mt-4 flex items-center gap-3 rounded-2xl bg-cream p-3">
                <x-avatar :member="$user" size="sm" />
                <a href="{{ route('profile.edit') }}" class="min-w-0 flex-1 leading-tight">
                    <span class="block truncate text-sm font-bold text-navy-900">{{ $user->full_name }}</span>
                    <span class="block text-xs text-muted">Voir mon profil</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-full p-2 text-muted hover:bg-white hover:text-crimson-600" title="Se déconnecter" aria-label="Se déconnecter"><x-icon name="logout" class="size-4" /></button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            {{-- En-tête (téléphone) --}}
            <header class="sticky top-0 z-30 flex items-center justify-between border-b border-sand/70 bg-cream/90 px-4 py-3 backdrop-blur lg:hidden">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <img src="/images/logo-rond.png" alt="" class="size-9 rounded-full ring-1 ring-sand">
                    <span class="text-base font-extrabold tracking-tight text-navy-800">TEGV</span>
                </a>
                <div class="flex items-center gap-1">
                    @if ($user->is_admin)
                        <a href="{{ route('admin.rights.index') }}" class="rounded-full p-2 text-navy-700 hover:bg-white" aria-label="Administration"><x-icon name="qr" /></a>
                    @endif
                    <a href="{{ route('profile.edit') }}" aria-label="Mon profil"><x-avatar :member="$user" size="sm" /></a>
                </div>
            </header>

            <main class="mx-auto w-full max-w-6xl px-4 pt-5 pb-28 sm:px-6 lg:px-10 lg:pt-8 lg:pb-12">
                <x-flash />
                <div @class(['mt-4' => session('status')])>
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    {{-- Barre de navigation (téléphone) --}}
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-sand bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden" aria-label="Navigation">
        <div class="mx-auto grid max-w-md grid-cols-5">
            @foreach ($mobileNav as $item)
                <a href="{{ route($item['route']) }}" @class([
                    'flex flex-col items-center gap-1 py-2.5 text-[11px] font-semibold',
                    'text-navy-800' => $isActive($item['match']),
                    'text-muted' => ! $isActive($item['match']),
                ])>
                    <span @class(['rounded-full px-4 py-1 transition', 'bg-gold-100 text-gold-600' => $isActive($item['match'])])>
                        <x-icon :name="$item['icon']" />
                    </span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </nav>
</body>
</html>
