<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('tegv:supprimer-comptes')]
#[Description('Supprime définitivement les comptes dont la demande de suppression a dépassé le délai')]
class DeleteExpiredAccounts extends Command
{
    public function handle(): int
    {
        $members = User::query()
            ->where('deletion_requested_at', '<=', now()->subDays(config('tegv.account_deletion_days')))
            ->with('posters')
            ->get();

        foreach ($members as $member) {
            $disk = Storage::disk('media');

            if ($member->photo_path) {
                $disk->delete($member->photo_path);
            }

            foreach ($member->posters as $poster) {
                $disk->delete($poster->image_path);
            }

            // Absences, reports, information, posters and birthday messages go with the account (cascade).
            $member->delete();
            $this->info("Compte supprimé : {$member->email}");
        }

        if ($members->isEmpty()) {
            $this->info('Aucun compte à supprimer.');
        }

        return self::SUCCESS;
    }
}
