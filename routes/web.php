<?php

use App\Http\Controllers\Employe\PresenceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');



// Routes employé - Présences
Route::prefix('presence')->name('presence.')->group(function () {
    Route::get('/arrivee', [PresenceController::class, 'createArrivee'])->name('arrivee');
    Route::post('/arrivee', [PresenceController::class, 'storeArrivee'])->name('arrivee.store');
    Route::get('/depart', [PresenceController::class, 'createDepart'])->name('depart');
    Route::post('/depart/{presence}', [PresenceController::class, 'storeDepart'])->name('depart.store');
    Route::get('/historique', [PresenceController::class, 'historique'])->name('historique');
});





Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
