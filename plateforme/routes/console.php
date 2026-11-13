<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tegv:remercier-visiteurs')
    ->weeklyOn(1, config('tegv.thanks_time'))
    ->withoutOverlapping(120)
    ->onOneServer();

Schedule::command('tegv:anniversaires')
    ->dailyAt(config('tegv.birthday_time'))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('tegv:photos-facebook')
    ->weeklyOn(2, '05:30')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('tegv:supprimer-comptes')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->onOneServer();
