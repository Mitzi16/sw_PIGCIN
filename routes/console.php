<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Ejecutar diariamente a media noche
Schedule::call(function () {
    // Borrar notificaciones de más de 30 días
    DB::table('notifications')
        ->where('created_at', '<', now()->subDays(30))
        ->delete();
})->daily();

// Ejecutar a las 8:00 AM solo días de semana (Lunes a Viernes)
Schedule::command('reviews:check-delays')
        ->weekdays() 
        ->dailyAt('08:00');
