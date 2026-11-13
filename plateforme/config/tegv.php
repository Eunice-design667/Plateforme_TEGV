<?php

return [

    'name' => 'Temple Évangélique Grâce et Victoire',
    'short_name' => 'TEGV',
    'full_name' => 'Église AD, Temple Évangélique Grâce et Victoire',
    'motto' => 'Nous ne faisons pas autre chose, nous faisons autrement les choses.',
    'tagline' => 'Solo Deo Gloria',
    'phone' => '+226 70 26 83 95',
    'address' => 'Ouaga 2000, non loin de Salitas',
    'website' => 'https://tegv-bf.org',
    'facebook' => 'https://www.facebook.com/Tegv11/',

    /*
     * First administrator, created with: php artisan tegv:admin
     */
    'admin' => [
        'email' => env('TEGV_ADMIN_EMAIL', 'romasaw2@gmail.com'),
        'first_name' => env('TEGV_ADMIN_FIRST_NAME', 'Romaric'),
        'last_name' => env('TEGV_ADMIN_LAST_NAME', 'Sawadogo'),
    ],

    /*
     * Hosting on Vercel: secret sent by Vercel Cron to /taches/quotidiennes,
     * and demo data added on the first deployment (presentation).
     */
    'cron_secret' => env('CRON_SECRET'),
    'demo_seed' => (bool) env('DEMO_SEED', false),
    'demo_password' => env('DEMO_PASSWORD', 'password'),

    /*
     * A member who asks to delete their account can cancel for this many days,
     * then the account and its pictures are deleted for good.
     */
    'account_deletion_days' => 30,

    'default_country_code' => env('TEGV_COUNTRY_CODE', '226'),

    /*
     * Trades suggested for the main and secondary profiles (members may type another one).
     */
    'trades' => [
        'Agriculture', 'Architecture', 'Banque et finance', 'Coiffure', 'Commerce', 'Communication', 'Comptabilité',
        'Couture', 'Droit', 'Élevage', 'Électricité', 'Énergie solaire', 'Enseignement', 'Froid et climatisation',
        'Génie civil', 'Informatique', 'Maçonnerie', 'Mécanique auto', 'Médecine', 'Menuiserie', 'Peinture',
        'Pharmacie', 'Photographie', 'Plomberie', 'Ressources humaines', 'Restauration', 'Sécurité',
        'Soins infirmiers', 'Soudure', 'Transport',
    ],

    /*
     * Sectors of activity, after the NAEMA classification (AFRISTAT) used by the INSD,
     * itself aligned with the UN's ISIC sections, in plain words.
     */
    'sectors' => [
        'agriculture' => 'Agriculture, élevage, pêche',
        'mines' => 'Mines et carrières',
        'industrie' => 'Industrie, artisanat de production',
        'energie' => 'Énergie (électricité, gaz, solaire)',
        'eau' => 'Eau, assainissement, déchets',
        'btp' => 'Bâtiment et travaux publics',
        'commerce' => 'Commerce, réparation de véhicules',
        'transport' => 'Transport et logistique',
        'hotellerie' => 'Hôtellerie et restauration',
        'numerique' => 'Informatique, télécoms, médias',
        'finance' => 'Banque, finance, assurance',
        'immobilier' => 'Immobilier',
        'conseil' => 'Droit, comptabilité, conseil, ingénierie',
        'services_entreprises' => 'Services aux entreprises (sécurité, nettoyage, intérim)',
        'administration' => 'Administration publique, défense',
        'enseignement' => 'Enseignement, formation',
        'sante' => 'Santé et action sociale',
        'culture' => 'Arts, culture, sport, loisirs',
        'services_personnes' => 'Services aux personnes (couture, coiffure, beauté…)',
        'ong' => 'ONG, associations, organisations religieuses',
        'etudiant' => 'Étudiant(e), élève',
        'retraite' => 'Retraité(e)',
        'autre' => 'Autre',
    ],

    /*
     * Usual sector of the suggested trades (used to fill the sector of existing profiles).
     */
    'trade_sectors' => [
        'Agriculture' => 'agriculture', 'Élevage' => 'agriculture', 'Architecture' => 'conseil', 'Banque et finance' => 'finance',
        'Coiffure' => 'services_personnes', 'Commerce' => 'commerce', 'Communication' => 'numerique', 'Comptabilité' => 'conseil',
        'Couture' => 'services_personnes', 'Droit' => 'conseil', 'Électricité' => 'energie', 'Énergie solaire' => 'energie',
        'Enseignement' => 'enseignement', 'Froid et climatisation' => 'btp', 'Génie civil' => 'btp', 'Informatique' => 'numerique',
        'Maçonnerie' => 'btp', 'Mécanique auto' => 'commerce', 'Médecine' => 'sante', 'Menuiserie' => 'industrie',
        'Peinture' => 'btp', 'Pharmacie' => 'sante', 'Photographie' => 'culture', 'Plomberie' => 'btp',
        'Ressources humaines' => 'services_entreprises', 'Restauration' => 'hotellerie', 'Sécurité' => 'services_entreprises',
        'Soins infirmiers' => 'sante', 'Soudure' => 'industrie', 'Transport' => 'transport', 'Accompagnement pastoral' => 'ong',
    ],

    /*
     * Profile covers: neutral by default, or chosen by the member (or their own picture).
     */
    'covers' => [
        'neutre' => ['label' => 'Neutre', 'css' => 'linear-gradient(135deg, #efe7d7 0%, #faf6ee 100%)'],
        'nuit' => ['label' => 'Bleu nuit', 'css' => 'linear-gradient(135deg, #151c45 0%, #2e3c91 100%)'],
        'or' => ['label' => 'Or', 'css' => 'linear-gradient(135deg, #e9ac00 0%, #f8d35a 100%)'],
        'bordeaux' => ['label' => 'Bordeaux', 'css' => 'linear-gradient(135deg, #7a1410 0%, #c41c14 100%)'],
        'ciel' => ['label' => 'Ciel', 'css' => 'linear-gradient(135deg, #b9c1e8 0%, #eef0fa 100%)'],
        'savane' => ['label' => 'Savane', 'css' => 'linear-gradient(135deg, #8a5a2b 0%, #e2b77a 100%)'],
        'foret' => ['label' => 'Forêt', 'css' => 'linear-gradient(135deg, #1f4d3a 0%, #4f8a6b 100%)'],
        'aube' => ['label' => 'Aube', 'css' => 'linear-gradient(135deg, #f5c22a 0%, #e2231a 55%, #25327a 100%)'],
    ],

    /*
     * Weekly programme shown on the home screen.
     */
    'programme' => [
        ['day' => 'Dimanche', 'title' => 'Culte dominical', 'time' => '08h – 10h30', 'weekday' => 0],
        ['day' => 'Mardi', 'title' => 'Étude biblique', 'time' => '19h – 20h30', 'weekday' => 2],
        ['day' => 'Vendredi', 'title' => 'Réunion de prière', 'time' => '19h – 20h30', 'weekday' => 5],
    ],

    /*
     * Posters: how many a member may keep on their profile, and how many days
     * a new poster stays in the "Affiches" stories of the home screen.
     */
    'max_posters' => (int) env('TEGV_MAX_POSTERS', 12),
    'story_days' => (int) env('TEGV_STORY_DAYS', 7),

    /*
     * Church photos copied every Tuesday at 05:30 from the Facebook page (Graph API).
     * The page token comes from a page administrator (see SyncFacebookPhotos).
     */
    'facebook' => [
        'page_id' => env('FACEBOOK_PAGE_ID', 'Tegv11'),
        'token' => env('FACEBOOK_PAGE_TOKEN'),
        'version' => env('FACEBOOK_GRAPH_VERSION', 'v21.0'),
        'keep' => (int) env('FACEBOOK_PHOTOS_KEEP', 30),
    ],

    /*
     * Visitor form: maximum submissions per device and per day.
     */
    'visitor_daily_limit' => (int) env('TEGV_VISITOR_DAILY_LIMIT', 3),

    /*
     * Monday agent: hour of the thank-you messages and pause between two sends (seconds).
     */
    'thanks_time' => env('TEGV_THANKS_TIME', '10:00'),
    'thanks_delay_seconds' => (int) env('TEGV_THANKS_DELAY', 8),
    'birthday_time' => env('TEGV_BIRTHDAY_TIME', '06:00'),

];
