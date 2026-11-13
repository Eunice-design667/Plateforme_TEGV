<?php

use App\Http\Controllers\AbsenceController;
use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\Admin\LeaderApprovalController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BirthdayMessageController;
use App\Http\Controllers\DailyTasksController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Leader\AbsenceHandlingController;
use App\Http\Controllers\Leader\DashboardController;
use App\Http\Controllers\Leader\PublishedAnnouncementController;
use App\Http\Controllers\Leader\VisitorController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\PosterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VisitorFormController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('welcome');

// Daily tasks triggered by Vercel Cron (protected by CRON_SECRET).
Route::get('/taches/quotidiennes', DailyTasksController::class)->name('daily-tasks');

// Public visitor form opened by the QR code.
Route::get('/visiteur', [VisitorFormController::class, 'create'])->name('visitors.create');
Route::post('/visiteur', [VisitorFormController::class, 'store'])->middleware('throttle:visitor')->name('visitors.store');
Route::get('/visiteur/merci', [VisitorFormController::class, 'thanks'])->name('visitors.thanks');

// Step 1 of registration stays reachable from step 2 (back button) until the profile is created.
Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
Route::post('/inscription', [RegisterController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:login');
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'store'])->middleware('throttle:5,10')->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'update'])->name('password.update');
});

// Private pages are never cached, so the back button shows nothing after logout.
Route::middleware(['auth', 'cache.headers:private;no_store;no_cache;must_revalidate'])->group(function () {
    Route::post('/deconnexion', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/profil/creer', [ProfileController::class, 'create'])->name('profile.create');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('profile.completed')->group(function () {
        Route::get('/accueil', HomeController::class)->name('home');
        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/profil/supprimer', [AccountDeletionController::class, 'create'])->name('account-deletion.create');
        Route::post('/profil/supprimer', [AccountDeletionController::class, 'store'])->name('account-deletion.store');
        Route::delete('/profil/supprimer', [AccountDeletionController::class, 'destroy'])->name('account-deletion.destroy');

        Route::get('/annuaire', DirectoryController::class)->name('directory');
        Route::get('/membres/{member}', [MemberController::class, 'show'])->name('members.show');
        Route::post('/affiches', [PosterController::class, 'store'])->middleware('throttle:20,60')->name('posters.store');
        Route::delete('/affiches/{poster}', [PosterController::class, 'destroy'])->name('posters.destroy');

        Route::get('/prevenir', [AbsenceController::class, 'create'])->name('notices.create');
        Route::post('/absences', [AbsenceController::class, 'store'])->name('absences.store');
        Route::post('/signalements', [ReportController::class, 'store'])->name('reports.store');

        Route::get('/informations', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/informations/proposer', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('/informations', [AnnouncementController::class, 'store'])->name('announcements.store');

        Route::patch('/anniversaire/{birthdayMessage}', [BirthdayMessageController::class, 'update'])->name('birthday-messages.update');

        Route::middleware('leader')->prefix('espace-responsables')->name('leader.')->group(function () {
            Route::get('/', DashboardController::class)->name('dashboard');
            Route::get('/informations/{announcement}', [PublishedAnnouncementController::class, 'edit'])->name('announcements.edit');
            Route::put('/informations/{announcement}', [PublishedAnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('/informations/{announcement}', [PublishedAnnouncementController::class, 'destroy'])->name('announcements.destroy');
            Route::patch('/signalements/{report}', [ReportController::class, 'update'])->name('reports.update');
            Route::patch('/absences/{absence}', [AbsenceHandlingController::class, 'update'])->name('absences.update');
            Route::delete('/visiteurs/{visitor}', [VisitorController::class, 'destroy'])->name('visitors.destroy');
        });

        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/droits', [LeaderApprovalController::class, 'index'])->name('rights.index');
            Route::patch('/droits/{member}', [LeaderApprovalController::class, 'update'])->name('rights.update');
            Route::delete('/droits/{member}', [LeaderApprovalController::class, 'destroy'])->name('rights.destroy');
            Route::get('/qr-code', [QrCodeController::class, 'show'])->name('qr-code');
            Route::get('/qr-code/telecharger', [QrCodeController::class, 'download'])->name('qr-code.download');
            Route::get('/affiche', [QrCodeController::class, 'poster'])->name('poster');
        });
    });
});
