<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AgenceController;
use App\Http\Controllers\Admin\CaisseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DirectionController;
use App\Http\Controllers\Admin\EntiteController;
use App\Http\Controllers\Admin\EvaluationController;
use App\Http\Controllers\Admin\GuichetController;
use App\Http\Controllers\Admin\ObjectifController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StatistiqueController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Personnel\PersonnelDashboardController;
use App\Http\Controllers\Pca\PcaDashboardController;
use App\Http\Controllers\Pca\PcaEvaluationController;
use App\Http\Controllers\Pca\PcaObjectifController;
use App\Http\Controllers\Pca\PcaStatistiqueController;
use App\Http\Controllers\Pca\PcaSettingsController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
Route::redirect('/login', '/admin/login')->name('legacy.login');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
    // Livewire CRUD for admin users
    \Livewire\Livewire::component('admin-user-crud', \App\Http\Livewire\Admin\AdminUserCrud::class);
    Route::get('/admin/utilisateurs', function () {
        return view('admin.utilisateurs');
    })->name('admin.utilisateurs.index');
});

Route::middleware(['auth', 'admin'])->group(function (): void {
    // Route pour enregistrer un secrétaire depuis la modale de la Faitière
    Route::post('/admin/secretaires', [EntiteController::class, 'storeSecretaire'])->name('admin.secretaires.store');
    Route::get('/admin/secretaires/{direction}', [EntiteController::class, 'showSecretaire'])->name('admin.secretaires.show');
    Route::get('/admin/secretaires/{direction}/modifier', [EntiteController::class, 'editSecretaire'])->name('admin.secretaires.edit');
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
    Route::get('/admin', DashboardController::class)->name('admin.dashboard');
    Route::get('/admin/statistiques', StatistiqueController::class)->name('admin.statistiques.index');

    Route::get('/admin/entites', [EntiteController::class, 'index'])->name('admin.entites.index');
    Route::get('/admin/entites/secretaires', [EntiteController::class, 'indexSecretaires'])->name('admin.entites.secretaires.index');
    Route::post('/admin/entites/reinitialiser', [EntiteController::class, 'reset'])->name('admin.entites.reset');
    Route::get('/admin/entites/creer', [EntiteController::class, 'create'])->name('admin.entites.create');
    Route::get('/admin/entites/directions', [EntiteController::class, 'indexDirections'])->name('admin.entites.directions.index');
    Route::get('/admin/entites/directions/creer', [EntiteController::class, 'createDirection'])->name('admin.entites.directions.create');
    Route::post('/admin/entites/directions', [EntiteController::class, 'storeDirection'])->name('admin.entites.directions.store');
    Route::post('/admin/entites', [EntiteController::class, 'store'])->name('admin.entites.store');
    Route::get('/admin/entites/{entite}', [EntiteController::class, 'show'])->name('admin.entites.show');
    Route::get('/admin/entites/{entite}/modifier', [EntiteController::class, 'edit'])->name('admin.entites.edit');
    Route::put('/admin/entites/{entite}', [EntiteController::class, 'update'])->name('admin.entites.update');
    Route::delete('/admin/entites/{entite}', [EntiteController::class, 'destroy'])->name('admin.entites.destroy');

    Route::get('/admin/directions', [DirectionController::class, 'index'])->name('admin.directions.index');
    Route::get('/admin/delegations-techniques/directeurs', [DirectionController::class, 'directeursIndex'])->name('admin.delegations-techniques.directeurs.index');
    Route::get('/admin/delegations-techniques/services', [DirectionController::class, 'servicesIndex'])->name('admin.delegations-techniques.services.index');
    Route::get('/admin/delegations-techniques/secretaires', [DirectionController::class, 'secretairesIndex'])->name('admin.delegations-techniques.secretaires.index');
    Route::get('/admin/delegations-techniques/agents', [DirectionController::class, 'agentsIndex'])->name('admin.delegations-techniques.agents.index');
    Route::post('/admin/delegations-techniques', [DirectionController::class, 'storeDelegation'])->name('admin.delegations-techniques.store');
    Route::get('/admin/delegations-techniques/{delegationTechnique}/modifier', [DirectionController::class, 'editDelegation'])->name('admin.delegations-techniques.edit');
    Route::put('/admin/delegations-techniques/{delegationTechnique}', [DirectionController::class, 'updateDelegation'])->name('admin.delegations-techniques.update');
    Route::delete('/admin/delegations-techniques/{delegationTechnique}', [DirectionController::class, 'destroyDelegation'])->name('admin.delegations-techniques.destroy');
    Route::get('/admin/directions/creer', [DirectionController::class, 'create'])->name('admin.directions.create');
    Route::post('/admin/directions', [DirectionController::class, 'store'])->name('admin.directions.store');
    Route::get('/admin/directions/{direction}', [DirectionController::class, 'show'])->name('admin.directions.show');
    Route::get('/admin/directions/{direction}/modifier', [DirectionController::class, 'edit'])->name('admin.directions.edit');
    Route::put('/admin/directions/{direction}', [DirectionController::class, 'update'])->name('admin.directions.update');
    Route::delete('/admin/directions/{direction}', [DirectionController::class, 'destroy'])->name('admin.directions.destroy');

    Route::get('/admin/services', [ServiceController::class, 'index'])->name('admin.services.index');
    Route::get('/admin/services/creer', [ServiceController::class, 'create'])->name('admin.services.create');
    Route::post('/admin/services', [ServiceController::class, 'store'])->name('admin.services.store');
    Route::get('/admin/services/{service}', [ServiceController::class, 'show'])->name('admin.services.show');
    Route::get('/admin/services/{service}/modifier', [ServiceController::class, 'edit'])->name('admin.services.edit');
    Route::put('/admin/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
    Route::delete('/admin/services/{service}', [ServiceController::class, 'destroy'])->name('admin.services.destroy');

    Route::get('/admin/agents', [AgentController::class, 'index'])->name('admin.agents.index');
    Route::get('/admin/agents/creer', [AgentController::class, 'create'])->name('admin.agents.create');
    Route::post('/admin/agents', [AgentController::class, 'store'])->name('admin.agents.store');
    Route::get('/admin/agents/{agent}', [AgentController::class, 'show'])->name('admin.agents.show');
    Route::get('/admin/agents/{agent}/modifier', [AgentController::class, 'edit'])->name('admin.agents.edit');
    Route::put('/admin/agents/{agent}', [AgentController::class, 'update'])->name('admin.agents.update');
    Route::delete('/admin/agents/{agent}', [AgentController::class, 'destroy'])->name('admin.agents.destroy');

    Route::get('/admin/caisses', [CaisseController::class, 'index'])->name('admin.caisses.index');
    Route::get('/admin/caisses/creer', [CaisseController::class, 'create'])->name('admin.caisses.create');
    Route::post('/admin/caisses', [CaisseController::class, 'store'])->name('admin.caisses.store');
    Route::get('/admin/caisses/{caisse}', [CaisseController::class, 'show'])->name('admin.caisses.show');
    Route::get('/admin/caisses/{caisse}/directions', [CaisseController::class, 'directionsIndex'])->name('admin.caisses.directions.index');
    Route::get('/admin/caisses/{caisse}/services', [CaisseController::class, 'servicesIndex'])->name('admin.caisses.services.index');
    Route::get('/admin/caisses/{caisse}/modifier', [CaisseController::class, 'edit'])->name('admin.caisses.edit');
    Route::put('/admin/caisses/{caisse}', [CaisseController::class, 'update'])->name('admin.caisses.update');
    Route::delete('/admin/caisses/{caisse}', [CaisseController::class, 'destroy'])->name('admin.caisses.destroy');

    Route::get('/admin/agences', [AgenceController::class, 'index'])->name('admin.agences.index');
    Route::get('/admin/agences/creer', [AgenceController::class, 'create'])->name('admin.agences.create');
    Route::post('/admin/agences', [AgenceController::class, 'store'])->name('admin.agences.store');
    Route::get('/admin/agences/{agence}/agents', [AgenceController::class, 'agentsIndex'])->name('admin.agences.agents.index');
    Route::get('/admin/agences/{agence}/agents/creer', [AgenceController::class, 'createAgent'])->name('admin.agences.agents.create');
    Route::post('/admin/agences/{agence}/agents', [AgenceController::class, 'storeAgent'])->name('admin.agences.agents.store');
    Route::get('/admin/guichets', [GuichetController::class, 'index'])->name('admin.guichets.index');
    Route::get('/admin/guichets/creer', [GuichetController::class, 'create'])->name('admin.guichets.create');
    Route::post('/admin/guichets', [GuichetController::class, 'store'])->name('admin.guichets.store');

    Route::get('/admin/objectifs', [ObjectifController::class, 'index'])->name('admin.objectifs.index');
    Route::get('/admin/objectifs/creer', [ObjectifController::class, 'create'])->name('admin.objectifs.create');
    Route::post('/admin/objectifs', [ObjectifController::class, 'store'])->name('admin.objectifs.store');
    Route::get('/admin/objectifs/{objectif}', [ObjectifController::class, 'show'])->name('admin.objectifs.show');
    Route::get('/admin/objectifs/{objectif}/modifier', [ObjectifController::class, 'edit'])->name('admin.objectifs.edit');
    Route::put('/admin/objectifs/{objectif}', [ObjectifController::class, 'update'])->name('admin.objectifs.update');
    Route::post('/admin/objectifs/{objectif}/progression', [ObjectifController::class, 'adjustProgress'])->name('admin.objectifs.progress');
    Route::delete('/admin/objectifs/{objectif}', [ObjectifController::class, 'destroy'])->name('admin.objectifs.destroy');

    Route::get('/admin/evaluations', [EvaluationController::class, 'index'])->name('admin.evaluations.index');
    Route::get('/admin/evaluations/creer', [EvaluationController::class, 'create'])->name('admin.evaluations.create');
    Route::post('/admin/evaluations', [EvaluationController::class, 'store'])->name('admin.evaluations.store');
    Route::get('/admin/evaluations/{evaluation}', [EvaluationController::class, 'show'])->name('admin.evaluations.show');
    Route::get('/admin/evaluations/{evaluation}/modifier', [EvaluationController::class, 'edit'])->name('admin.evaluations.edit');
    Route::put('/admin/evaluations/{evaluation}', [EvaluationController::class, 'update'])->name('admin.evaluations.update');
    Route::post('/admin/evaluations/{evaluation}/soumettre', [EvaluationController::class, 'submit'])->name('admin.evaluations.submit');
    Route::post('/admin/evaluations/{evaluation}/valider', [EvaluationController::class, 'approve'])->name('admin.evaluations.approve');
    Route::get('/admin/evaluations/{evaluation}/pdf', [EvaluationController::class, 'exportPdf'])->name('admin.evaluations.pdf');
    Route::delete('/admin/evaluations/{evaluation}', [EvaluationController::class, 'destroy'])->name('admin.evaluations.destroy');

    Route::get('/admin/parametres', [SettingsController::class, 'edit'])->name('admin.settings.edit');
    Route::put('/admin/parametres/theme', [SettingsController::class, 'updateTheme'])->name('admin.settings.theme.update');
    Route::put('/admin/parametres/securite', [SettingsController::class, 'updateSecurity'])->name('admin.settings.security.update');
    Route::put('/admin/parametres/mot-de-passe', [SettingsController::class, 'updatePassword'])->name('admin.settings.password.update');
    Route::delete('/admin/parametres/compte', [SettingsController::class, 'destroyAccount'])->name('admin.settings.account.destroy');
});

Route::middleware(['auth', 'pca'])->prefix('pca')->name('pca.')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/', PcaDashboardController::class)->name('dashboard');
    Route::get('/statistiques', PcaStatistiqueController::class)->name('statistiques.index');

    Route::get('/objectifs', [PcaObjectifController::class, 'index'])->name('objectifs.index');
    Route::get('/objectifs/creer', [PcaObjectifController::class, 'create'])->name('objectifs.create');
    Route::post('/objectifs', [PcaObjectifController::class, 'store'])->name('objectifs.store');
    Route::get('/objectifs/{objectif}', [PcaObjectifController::class, 'show'])->name('objectifs.show');
    Route::post('/objectifs/{objectif}/progression', [PcaObjectifController::class, 'adjustProgress'])->name('objectifs.progress');
    Route::delete('/objectifs/{objectif}', [PcaObjectifController::class, 'destroy'])->name('objectifs.destroy');

    Route::get('/evaluations', [PcaEvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('/evaluations/creer', [PcaEvaluationController::class, 'create'])->name('evaluations.create');
    Route::post('/evaluations', [PcaEvaluationController::class, 'store'])->name('evaluations.store');
    Route::get('/evaluations/{evaluation}', [PcaEvaluationController::class, 'show'])->name('evaluations.show');
    Route::post('/evaluations/{evaluation}/soumettre', [PcaEvaluationController::class, 'submit'])->name('evaluations.submit');
    Route::post('/evaluations/{evaluation}/valider', [PcaEvaluationController::class, 'approve'])->name('evaluations.approve');
    Route::get('/evaluations/{evaluation}/pdf', [PcaEvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::delete('/evaluations/{evaluation}', [PcaEvaluationController::class, 'destroy'])->name('evaluations.destroy');

    Route::get('/parametres', [PcaSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/parametres/theme', [PcaSettingsController::class, 'updateTheme'])->name('settings.theme.update');
    Route::put('/parametres/mot-de-passe', [PcaSettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::delete('/parametres/compte', [PcaSettingsController::class, 'destroyAccount'])->name('settings.account.destroy');
});

Route::middleware(['auth', 'personnel'])->group(function (): void {
    Route::post('/personnel/logout', [AuthenticatedSessionController::class, 'destroy'])->name('personnel.logout');
    Route::get('/personnel', PersonnelDashboardController::class)->name('personnel.dashboard');
});
