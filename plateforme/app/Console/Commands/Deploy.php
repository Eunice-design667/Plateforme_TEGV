<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

/**
 * Run during each Vercel build: updates the database tables and, when DEMO_SEED
 * is on and the platform is still empty, adds the demo data for the presentation.
 */
#[Signature('tegv:deploy')]
#[Description('Met la base à jour au déploiement (et ajoute les données de démo si elle est vide)')]
class Deploy extends Command
{
    public function handle(): int
    {
        $this->call('migrate', ['--force' => true]);

        if (config('tegv.demo_seed') && User::query()->doesntExist()) {
            $this->call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);
            $this->info('Données de démo ajoutées.');
        }

        return self::SUCCESS;
    }
}
