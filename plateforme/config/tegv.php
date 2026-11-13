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
