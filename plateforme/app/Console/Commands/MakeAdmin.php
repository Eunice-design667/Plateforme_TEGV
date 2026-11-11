<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('tegv:admin
    {email? : Email du compte (par défaut, celui de config/tegv.php)}
    {--prenom= : Prénom, si le compte est à créer}
    {--nom= : Nom, si le compte est à créer}
    {--retirer : Retire le rôle administrateur}')]
#[Description("Donne (ou retire) le rôle d'administrateur, en créant le compte s'il n'existe pas")]
class MakeAdmin extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email') ?? config('tegv.admin.email');
        $user = User::query()->where('email', $email)->first();

        if ($user === null && $this->option('retirer')) {
            $this->error("Aucun compte avec l'email {$email}.");

            return self::FAILURE;
        }

        if ($user === null) {
            $user = $this->createAccount($email);

            if ($user === null) {
                return self::FAILURE;
            }
        }

        $user->is_admin = ! $this->option('retirer');
        $user->save();

        $this->info($user->is_admin ? "{$user->email} est maintenant administrateur." : "{$user->email} n'est plus administrateur.");

        return self::SUCCESS;
    }

    /**
     * The password is typed by the administrator, never passed on the command line.
     */
    private function createAccount(string $email): ?User
    {
        $isDefaultAdmin = $email === config('tegv.admin.email');
        $firstName = $this->option('prenom') ?? ($isDefaultAdmin ? config('tegv.admin.first_name') : $this->ask('Prénom'));
        $lastName = $this->option('nom') ?? ($isDefaultAdmin ? config('tegv.admin.last_name') : $this->ask('Nom'));

        $this->line("Création du compte {$firstName} {$lastName} ({$email}).");
        $password = $this->secret('Mot de passe (8 caractères minimum, lettres et chiffres)');
        $confirmation = $this->secret('Confirmez le mot de passe');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            ['email' => ['required', 'email'], 'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return null;
        }

        $user = User::create([
            'name' => "{$firstName} {$lastName}",
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'contact_email' => $email,
        ]);

        $this->line('Le compte devra compléter son profil (étape 2) à la première connexion.');

        return $user;
    }
}
