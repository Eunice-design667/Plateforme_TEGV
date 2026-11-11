<?php

namespace App\Enums;

enum ReportType: string
{
    case Deces = 'deces';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Deces => 'Décès',
            self::Autre => 'Autre problème',
        };
    }
}
