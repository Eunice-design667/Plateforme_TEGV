<?php

namespace App\Enums;

enum LeaderStatus: string
{
    case None = 'none';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Membre',
            self::Pending => 'En attente de validation',
            self::Approved => 'Validé',
            self::Rejected => 'Refusé',
        };
    }
}
