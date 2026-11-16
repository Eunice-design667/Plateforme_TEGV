<?php

namespace App\Support;

use Carbon\CarbonInterface;

class FrenchDate
{
    /**
     * "dimanche 1er novembre 2026": French writes the first day of the month "1er".
     */
    public static function long(CarbonInterface $date, bool $withYear = true): string
    {
        $day = $date->day === 1 ? '1er' : (string) $date->day;

        return $date->translatedFormat('l').' '.$day.' '.$date->translatedFormat($withYear ? 'F Y' : 'F');
    }
}
