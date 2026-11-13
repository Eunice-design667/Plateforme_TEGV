<?php

namespace App\Console\Commands;

use App\Enums\LeaderStatus;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Creates (or resets) one ready-to-use account per profile for the testers,
 * with a fresh random password each time. The access list is written to a file
 * and never printed, so passwords stay out of logs.
 */
#[Signature('tegv:comptes-testeurs {fichier : Fichier CSV où écrire les accès (email, mot de passe, profil)}')]
#[Description('Crée ou réinitialise un compte de test par profil (pasteur, diacre, ancien, administrateur…)')]
class CreateTesterAccounts extends Command
{
    /**
     * @var list<array{key: string, label: string, first_name: string, last_name: string, roles: list<string>, status: LeaderStatus, admin: bool, christian: bool, trade: string, can: string}>
     */
    private const PROFILES = [
        ['key' => 'admin', 'label' => 'Administrateur', 'first_name' => 'Testeur', 'last_name' => 'Administrateur', 'roles' => [], 'status' => LeaderStatus::None, 'admin' => true, 'christian' => true, 'trade' => 'Informatique',
            'can' => 'Valide les droits de pasteur et diacre, génère le QR code et l’affiche. Ne voit pas les absences, signalements ni visiteurs.'],
        ['key' => 'pasteur', 'label' => 'Pasteur validé', 'first_name' => 'Testeur', 'last_name' => 'Pasteur', 'roles' => ['pasteur'], 'status' => LeaderStatus::Approved, 'admin' => false, 'christian' => true, 'trade' => 'Enseignement',
            'can' => 'Espace pasteur et diacre : absences, visiteurs, signalements, informations à valider.'],
        ['key' => 'diacre', 'label' => 'Diacre validé', 'first_name' => 'Testeur', 'last_name' => 'Diacre', 'roles' => ['diacre'], 'status' => LeaderStatus::Approved, 'admin' => false, 'christian' => true, 'trade' => 'Comptabilité',
            'can' => 'Mêmes droits que le pasteur dans l’espace pasteur et diacre.'],
        ['key' => 'ancien', 'label' => 'Ancien', 'first_name' => 'Testeur', 'last_name' => 'Ancien', 'roles' => ['ancien'], 'status' => LeaderStatus::None, 'admin' => false, 'christian' => true, 'trade' => 'Commerce',
            'can' => 'Rôle affiché sur la fiche et filtrable dans l’annuaire, sans droits particuliers (comme un membre).'],
        ['key' => 'membre', 'label' => 'Membre (chrétien de TEGV)', 'first_name' => 'Testeur', 'last_name' => 'Membre', 'roles' => ['chorale'], 'status' => LeaderStatus::None, 'admin' => false, 'christian' => true, 'trade' => 'Électricité',
            'can' => 'Annuaire, profil, absence, signalement, proposer une information ; voit les informations publiées.'],
        ['key' => 'externe', 'label' => 'Membre non « chrétien de TEGV »', 'first_name' => 'Testeur', 'last_name' => 'Externe', 'roles' => [], 'status' => LeaderStatus::None, 'admin' => false, 'christian' => false, 'trade' => 'Couture',
            'can' => 'Comme un membre, mais ne voit pas les informations publiées (test F5-05).'],
        ['key' => 'attente', 'label' => 'Pasteur en attente de validation', 'first_name' => 'Testeur', 'last_name' => 'En attente', 'roles' => ['pasteur'], 'status' => LeaderStatus::Pending, 'admin' => false, 'christian' => true, 'trade' => 'Droit',
            'can' => 'Se comporte comme un membre tant que l’administrateur n’a pas validé (tests D-01, D-03, D-04).'],
    ];

    public function handle(): int
    {
        $rows = [['Profil', 'Email', 'Mot de passe', 'Ce que ce compte permet de tester']];
        $admin = null;

        foreach (self::PROFILES as $profile) {
            $email = "testeur.{$profile['key']}@tegv.test";
            $password = 'Tegv-'.$this->readableCharacters('ABCDEFGHJKMNPQRSTUVWXYZ', 4).'-'.$this->readableCharacters('23456789', 4);

            $member = User::query()->firstOrNew(['email' => $email]);
            $member->forceFill([
                'name' => "{$profile['first_name']} {$profile['last_name']}",
                'password' => $password,
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'],
                'primary_profile' => $profile['trade'],
                'sector' => config('tegv.trade_sectors')[$profile['trade']] ?? 'autre',
                'secondary_profiles' => [],
                'years_experience' => 5,
                'company' => 'Compte de test',
                'job_title' => $profile['label'],
                'church_roles' => $profile['roles'],
                'other_roles' => [],
                'whatsapp' => '+226 70 00 00 00',
                'contact_email' => $email,
                'is_tegv_christian' => $profile['christian'],
                'is_admin' => $profile['admin'],
                'leader_status' => $profile['status'],
                'leader_requested_at' => $profile['status'] === LeaderStatus::None ? null : now(),
                'leader_validated_by' => $profile['status'] === LeaderStatus::Approved ? $admin?->id : null,
                'leader_validated_at' => $profile['status'] === LeaderStatus::Approved ? now() : null,
                'profile_completed_at' => $member->profile_completed_at ?? now(),
                'deletion_requested_at' => null,
            ])->save();

            $admin ??= $profile['admin'] ? $member : null;
            $rows[] = [$profile['label'], $email, $password, $profile['can']];
            $this->line("{$profile['label']} : {$email}");
        }

        $handle = fopen($this->argument('fichier'), 'w');

        foreach ($rows as $row) {
            fputcsv($handle, $row, escape: '\\');
        }

        fclose($handle);
        $this->info(count(self::PROFILES).' comptes prêts. Accès écrits dans '.$this->argument('fichier'));

        return self::SUCCESS;
    }

    /**
     * Characters that cannot be mistaken for one another when typed from a printed sheet
     * (no I, L, O, 0 or 1).
     */
    private function readableCharacters(string $alphabet, int $length): string
    {
        return collect(range(1, $length))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
    }
}
