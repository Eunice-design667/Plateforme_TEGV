<?php

namespace App\Enums;

enum AnnouncementStatus: string
{
    case Proposed = 'proposed';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Proposed => 'Proposée',
            self::Published => 'Publiée',
        };
    }
}
