<?php

namespace App\Providers;

use App\Http\Controllers\VisitorFormController;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        if ($this->app->isProduction()) {
            URL::forceHttps();
        }

        ResetPassword::toMailUsing(fn (object $notifiable, string $token) => (new MailMessage)
            ->subject('TEGV · Choisir un nouveau mot de passe')
            ->greeting('Bonjour'.($notifiable->first_name ? " {$notifiable->first_name}" : '').',')
            ->line('Vous avez demandé à changer le mot de passe de votre compte sur la plateforme du Temple Évangélique Grâce et Victoire.')
            ->action('Choisir un nouveau mot de passe', route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line('Ce lien est valable 60 minutes.')
            ->line('Si vous n’êtes pas à l’origine de cette demande, ignorez simplement cet email : votre mot de passe reste inchangé.')
            ->salutation('Que Dieu vous bénisse, la famille TEGV'));

        // Visitor form: a few submissions per phone and per day, plus a broad limit
        // per connection (the church Wi-Fi is shared by everybody on Sunday).
        RateLimiter::for('visitor', fn (Request $request) => [
            Limit::perDay(config('tegv.visitor_daily_limit'))->by('device:'.VisitorFormController::deviceHash($request)),
            Limit::perHour(120)->by('ip:'.$request->ip()),
        ]);

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(mb_strtolower((string) $request->input('email')).'|'.$request->ip()),
        ]);
    }
}
