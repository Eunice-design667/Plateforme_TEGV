<?php

namespace Database\Seeders;

use App\Enums\ChurchRole;
use App\Enums\LeaderStatus;
use App\Enums\ReportType;
use App\Enums\ThanksStatus;
use App\Models\Absence;
use App\Models\Announcement;
use App\Models\Poster;
use App\Models\Report;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * Demo data for local development and the presentation. Every demo account uses
 * the password from DEMO_PASSWORD ("password" when it is not set, locally).
 *
 * - admin@tegv.test    administrateur (et pasteur validé)
 * - diacre@tegv.test   diacre validé
 * - membre@tegv.test   membre simple
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->pastor()->create([
            'email' => 'admin@tegv.test',
            'first_name' => 'Samuel', 'last_name' => 'Ouédraogo', 'name' => 'Samuel Ouédraogo',
            'primary_profile' => 'Accompagnement pastoral', 'job_title' => 'Pasteur principal', 'company' => 'TEGV', 'years_experience' => 25,
        ]);

        $deacon = User::factory()->deacon()->create([
            'email' => 'diacre@tegv.test',
            'first_name' => 'Rasmata', 'last_name' => 'Kaboré', 'name' => 'Rasmata Kaboré',
            'primary_profile' => 'Comptabilité', 'job_title' => 'Cheffe comptable', 'company' => 'Coris Bank', 'years_experience' => 14,
            'leader_validated_by' => $admin->id,
        ]);

        $member = User::factory()->create([
            'email' => 'membre@tegv.test',
            'first_name' => 'Paul', 'last_name' => 'Zongo', 'name' => 'Paul Zongo',
            'primary_profile' => 'Électricité', 'secondary_profiles' => ['Panneaux solaires', 'Climatisation'],
            'job_title' => 'Technicien', 'company' => 'SONABEL', 'years_experience' => 9,
            'church_roles' => [ChurchRole::Chorale->value],
        ]);

        User::factory()->bornToday()->create(['first_name' => 'Jean', 'last_name' => 'Compaoré', 'name' => 'Jean Compaoré', 'church_roles' => [ChurchRole::Louange->value]]);
        User::factory()->bornToday()->create(['first_name' => 'Esther', 'last_name' => 'Traoré', 'name' => 'Esther Traoré']);
        User::factory()->deacon(LeaderStatus::Pending)->create(['first_name' => 'Daniel', 'last_name' => 'Nikiéma', 'name' => 'Daniel Nikiéma']);
        User::factory()->count(14)->create();
        User::factory()->count(3)->create(['church_roles' => [ChurchRole::Ancien->value]]);
        User::factory()->create(['church_roles' => [ChurchRole::Autre->value], 'other_roles' => ['Protocole', 'Accueil']]);

        Announcement::factory()->published()->create([
            'user_id' => $deacon->id, 'validated_by' => $admin->id,
            'title' => 'Veillée de prière du dernier vendredi du mois',
            'body' => "Toute l'église est invitée à la veillée de prière de vendredi, de 21h à minuit.\nVenez avec vos sujets de prière et vos familles.",
        ]);
        Announcement::factory()->published()->create([
            'user_id' => $admin->id, 'validated_by' => $admin->id, 'published_at' => now()->subDays(3),
            'title' => 'Répétition de la chorale',
            'body' => 'La chorale répète samedi à 16h dans la grande salle. Les nouveaux choristes sont les bienvenus.',
        ]);
        Announcement::factory()->published()->create([
            'user_id' => $deacon->id, 'validated_by' => $admin->id, 'published_at' => now()->subDays(6),
            'title' => 'Étude biblique : l’épître aux Philippiens',
            'body' => 'Nouvelle série chaque mardi à 19h. Apportez votre Bible et un cahier.',
        ]);
        Announcement::factory()->create([
            'user_id' => $member->id,
            'title' => 'Journée de salubrité au temple',
            'body' => 'Proposition : nettoyer la cour et les salles le samedi 14 novembre à 7h. Prévoir balais et gants.',
        ]);

        foreach ([
            [$admin, 'banniere.jpg', 'Programme de la semaine : culte, étude biblique et prière.'],
            [$deacon, 'culte.jpg', 'Retour en images sur la présentation des enfants au culte.'],
        ] as [$author, $file, $caption]) {
            Storage::disk('media')->put("affiches/demo-{$file}", (string) file_get_contents(public_path("images/eglise/{$file}")));
            Poster::factory()->for($author)->create(['image_path' => "affiches/demo-{$file}", 'caption' => $caption]);
        }

        Absence::factory()->for($member)->create(['first_name' => 'Paul', 'last_name' => 'Zongo', 'reason' => 'Mission à Bobo-Dioulasso']);
        Absence::factory()->count(2)->create();

        Report::factory()->for($member)->create([
            'type' => ReportType::Deces,
            'concerned_person' => 'Mère de sœur Awa Ilboudo',
            'description' => "La maman de sœur Awa est décédée ce matin à Koudougou. Les obsèques auront lieu samedi.\nFamille joignable au +226 70 00 00 00.",
        ]);

        Visitor::factory()->count(6)->create();
        Visitor::factory()->count(4)->create([
            'visited_on' => today()->previous('sunday')->subWeek(),
            'thanks_status' => ThanksStatus::Sent,
            'thanks_channel' => 'gateway',
            'thanks_sent_at' => today()->previous('sunday')->subWeek()->addDay()->setTime(10, 0),
        ]);

        User::query()->update(['password' => Hash::make(config('tegv.demo_password'))]);
    }
}
