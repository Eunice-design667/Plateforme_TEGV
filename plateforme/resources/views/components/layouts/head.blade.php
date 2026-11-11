@props(['title' => null])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#1f2a63">
<meta name="description" content="{{ config('tegv.full_name') }} — annuaire des membres, accueil des visiteurs et vie de l'église.">
<title>{{ $title ? "{$title} · TEGV" : config('tegv.name') }}</title>
<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" type="image/png" href="/icons/icon-192.png">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="TEGV">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700,800,900|bodoni-moda:400,500,600,700,400i,500i,600i" rel="stylesheet">
@vite(['resources/css/app.css', 'resources/js/app.js'])
