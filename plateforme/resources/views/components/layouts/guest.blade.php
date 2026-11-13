@props(['title' => null, 'wide' => false])

<!DOCTYPE html>
<html lang="fr">
<head>
    <x-layouts.head :title="$title" />
</head>
<body class="min-h-dvh" @if (isset($errors) && collect($errors->getBags())->contains(fn ($bag) => $bag->any())) data-has-errors @endif>
    <main class="mx-auto flex min-h-dvh w-full {{ $wide ? 'max-w-6xl' : 'max-w-md' }} flex-col px-4 py-6 sm:py-10">
        {{ $slot }}
    </main>
</body>
</html>
