<?php

use App\Http\Controllers\Admin\ApprobationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Employe\DashboardUserConttoller;
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

Route::get('/employe/dashboard', [DashboardUserConttoller::class, 'dashboard'])->name('employe.dashboard');




Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');









Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard admin
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/rapports', [DashboardController::class, 'rapportsEmployes'])->name('rapports');
    Route::get('/employes/{user}/detail', [DashboardController::class, 'detailEmploye'])->name('employe.detail');

    // Routes des approbations
    Route::get('/approbations', [ApprobationController::class, 'index'])->name('approbations');
    Route::get('/approbations/{presence}', [ApprobationController::class, 'show'])->name('approbations.show');
    Route::post('/approbations/{presence}/approuver-arrivee', [ApprobationController::class, 'approuverArrivee'])->name('approbations.approuver-arrivee');
    Route::post('/approbations/{presence}/approuver-depart', [ApprobationController::class, 'approuverDepart'])->name('approbations.approuver-depart');
    Route::post('/approbations/{presence}/rejeter', [ApprobationController::class, 'rejeter'])->name('approbations.rejeter');
    Route::post('/approbations/lot', [ApprobationController::class, 'approbationLot'])->name('approbations.lot');
    Route::get('/approbations/{presence}/justificatif/{justificatif}/download', [ApprobationController::class, 'telechargerJustificatif'])->name('approbations.justificatif.download');

    // Routes des utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});




require __DIR__ . '/settings.php';
