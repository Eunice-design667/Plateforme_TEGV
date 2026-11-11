<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\BirthdayWishes;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tegv:anniversaires')]
#[Description("Rédige le message de vœux de chaque membre qui fête son anniversaire aujourd'hui")]
class SendBirthdayMessages extends Command
{
    public function handle(BirthdayWishes $wishes): int
    {
        $today = today();

        $members = User::query()
            ->withCompletedProfile()
            ->celebratingOn($today)
            ->whereDoesntHave('birthdayMessages', fn ($query) => $query->whereDate('birthday_on', $today))
            ->get();

        foreach ($members as $member) {
            $wishes->ensureFor($member, $today);

            $this->info("Vœux rédigés pour {$member->full_name}");
        }

        if ($members->isEmpty()) {
            $this->info("Aucun nouvel anniversaire aujourd'hui.");
        }

        return self::SUCCESS;
    }
}
