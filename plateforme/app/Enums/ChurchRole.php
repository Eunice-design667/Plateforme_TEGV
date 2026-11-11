<?php

namespace App\Enums;

enum ChurchRole: string
{
    case Pasteur = 'pasteur';
    case Diacre = 'diacre';
    case Ancien = 'ancien';
    case Louange = 'louange';
    case Chorale = 'chorale';
    case Autre = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::Pasteur => 'Pasteur',
            self::Diacre => 'Diacre',
            self::Ancien => 'Ancien',
            self::Louange => 'Équipe de louange',
            self::Chorale => 'Chorale',
            self::Autre => 'Autre',
        };
    }

    /**
     * Roles whose rights need an administrator's approval.
     */
    public function needsApproval(): bool
    {
        return in_array($this, [self::Pasteur, self::Diacre], true);
    }
}
