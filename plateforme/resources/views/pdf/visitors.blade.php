<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 28px 32px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: #1a1c2e; }
        .head { width: 100%; border-bottom: 3px solid #f5c22a; padding-bottom: 10px; margin-bottom: 14px; }
        .head td { vertical-align: middle; }
        .logo { width: 52px; height: 52px; }
        .church { font-size: 13px; font-weight: bold; color: #1f2a63; }
        .motto { font-size: 9.5px; color: #c41c14; font-style: italic; }
        h1 { font-size: 17px; color: #1f2a63; margin: 0 0 2px; }
        .meta { color: #6c6e82; margin-bottom: 12px; }
        table.list { width: 100%; border-collapse: collapse; }
        table.list th { background: #1f2a63; color: #fff; text-align: left; padding: 6px 7px; font-size: 9.5px; }
        table.list td { padding: 6px 7px; border-bottom: 1px solid #efe7d7; }
        table.list tr:nth-child(even) td { background: #faf6ee; }
        .group td { background: #fdf0c4 !important; font-weight: bold; color: #1f2a63; }
        .empty { padding: 24px; text-align: center; color: #6c6e82; }
        .foot { position: fixed; bottom: -24px; left: 0; right: 0; font-size: 8.5px; color: #6c6e82; text-align: center; }
    </style>
</head>
<body>
    <table class="head">
        <tr>
            <td style="width: 62px"><img class="logo" src="{{ public_path('images/logo-rond.png') }}" alt=""></td>
            <td>
                <div class="church">{{ config('tegv.full_name') }}</div>
                <div class="motto">{{ config('tegv.tagline') }} · {{ config('tegv.address') }}</div>
            </td>
        </tr>
    </table>

    <h1>{{ $title }}</h1>
    <div class="meta">{{ $visitors->count() }} {{ $visitors->count() > 1 ? 'visiteurs' : 'visiteur' }} · liste éditée le {{ now()->translatedFormat('j F Y à H\hi') }} · document confidentiel</div>

    @if ($visitors->isEmpty())
        <div class="empty">Aucun visiteur enregistré pour cette date.</div>
    @else
        <table class="list">
            <thead>
                <tr>
                    @unless ($date)<th>Date</th>@endunless
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Église d’origine</th>
                    <th>WhatsApp</th>
                    <th>Arrivée</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($visitors as $visitor)
                    <tr>
                        @unless ($date)<td>{{ $visitor->visited_on->format('d/m/Y') }}</td>@endunless
                        <td>{{ $visitor->first_name }}</td>
                        <td>{{ $visitor->last_name }}</td>
                        <td>{{ $visitor->has_no_church ? 'Pas d’église' : $visitor->origin_church }}</td>
                        <td>{{ \App\Support\PhoneNumber::format($visitor->whatsapp) }}</td>
                        <td>{{ $visitor->created_at?->format('H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="foot">TEGV · Liste des visiteurs · données personnelles à usage interne de l’église</div>
</body>
</html>
