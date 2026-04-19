<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AgenceController;
use App\Http\Controllers\Admin\AlerteController;
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
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.store');

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
        // Module Direction Générale
        Route::get('/admin/direction-generale', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'index'])->name('admin.direction-generale.index');
        Route::get('/admin/direction-generale/creer', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'create'])->name('admin.direction-generale.create');
        Route::post('/admin/direction-generale', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'store'])->name('admin.direction-generale.store');
        // Secrétaires DG
        Route::get('/admin/direction-generale/secretaires/creer', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'createSecretaire'])->name('admin.direction-generale.secretaires.create');
        Route::post('/admin/direction-generale/secretaires', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'storeSecretaire'])->name('admin.direction-generale.secretaires.store');
        Route::delete('/admin/direction-generale/secretaires/{user}', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'destroySecretaire'])->name('admin.direction-generale.secretaires.destroy');
        // Conseillers DG
        Route::get('/admin/direction-generale/conseillers/creer', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'createConseiller'])->name('admin.direction-generale.conseillers.create');
        Route::post('/admin/direction-generale/conseillers', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'storeConseiller'])->name('admin.direction-generale.conseillers.store');
        Route::delete('/admin/direction-generale/conseillers/{user}', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'destroyConseiller'])->name('admin.direction-generale.conseillers.destroy');
        // Modification des membres principaux (DG, DGA, Assistante)
        Route::get('/admin/direction-generale/membres/{user}/modifier', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'editMembre'])->name('admin.direction-generale.membres.edit');
        Route::put('/admin/direction-generale/membres/{user}', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'updateMembre'])->name('admin.direction-generale.membres.update');
    // Route pour enregistrer un secrétaire depuis la modale de la Faitière
    Route::post('/admin/secretaires', [EntiteController::class, 'storeSecretaire'])->name('admin.secretaires.store');
    Route::get('/admin/secretaires/{direction}', [EntiteController::class, 'showSecretaire'])->name('admin.secretaires.show');
    Route::get('/admin/secretaires/{direction}/modifier', [EntiteController::class, 'editSecretaire'])->name('admin.secretaires.edit');
    Route::delete('/admin/secretaires/{direction}', [EntiteController::class, 'destroySecretaire'])->name('admin.secretaires.destroy');
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
    Route::get('/admin', DashboardController::class)->name('admin.dashboard');
    Route::get('/admin/statistiques', StatistiqueController::class)->name('admin.statistiques.index');

    Route::get('/admin/entites', [EntiteController::class, 'index'])->name('admin.entites.index');
    Route::get('/admin/entites/agents', [EntiteController::class, 'indexAgents'])->name('admin.entites.agents.index');
    Route::post('/admin/entites/agents', [EntiteController::class, 'storeFaitiereAgent'])->name('admin.entites.agents.store');
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
    Route::patch('/admin/entites/{entite}/telephone', [EntiteController::class, 'updatePhone'])->name('admin.entites.update-phone');
    Route::delete('/admin/entites/{entite}', [EntiteController::class, 'destroy'])->name('admin.entites.destroy');

    Route::get('/admin/directions', function () {
        return redirect()->route('admin.entites.directions.index');
    })->name('admin.directions.index');
    Route::get('/admin/delegations-techniques', [DirectionController::class, 'index'])->name('admin.delegations-techniques.index');
    Route::get('/admin/delegations-techniques/directeurs', function () {
        return redirect()->route('admin.delegations-techniques.index');
    })->name('admin.delegations-techniques.directeurs.index');
    Route::get('/admin/delegations-techniques/services', [DirectionController::class, 'servicesIndex'])->name('admin.delegations-techniques.services.index');
    Route::get('/admin/delegations-techniques/secretaires', [DirectionController::class, 'secretairesIndex'])->name('admin.delegations-techniques.secretaires.index');
    Route::get('/admin/delegations-techniques/agents', [DirectionController::class, 'agentsIndex'])->name('admin.delegations-techniques.agents.index');
    Route::post('/admin/delegations-techniques', [DirectionController::class, 'storeDelegation'])->name('admin.delegations-techniques.store');
    Route::post('/admin/delegations-techniques/caisses', [DirectionController::class, 'storeCaisse'])->name('admin.delegations-techniques.caisses.store');
    Route::post('/admin/delegations-techniques/agents', [DirectionController::class, 'storeDelegationAgent'])->name('admin.delegations-techniques.agents.store');
    Route::post('/admin/delegations-techniques/services', [DirectionController::class, 'storeDelegationService'])->name('admin.delegations-techniques.services.store');
    Route::get('/admin/delegations-techniques/{delegationTechnique}', [DirectionController::class, 'showDelegation'])->name('admin.delegations-techniques.show');
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
    Route::get('/admin/services/faitiere', [ServiceController::class, 'faitiereServices'])->name('admin.services.faitiere');
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
    Route::get('/admin/agences/{agence}', [AgenceController::class, 'show'])->name('admin.agences.show');
    Route::get('/admin/agences/{agence}/modifier', [AgenceController::class, 'edit'])->name('admin.agences.edit');
    Route::put('/admin/agences/{agence}', [AgenceController::class, 'update'])->name('admin.agences.update');
    Route::delete('/admin/agences/{agence}', [AgenceController::class, 'destroy'])->name('admin.agences.destroy');
    Route::get('/admin/agences/{agence}/agents', [AgenceController::class, 'agentsIndex'])->name('admin.agences.agents.index');
    Route::get('/admin/agences/{agence}/agents/creer', [AgenceController::class, 'createAgent'])->name('admin.agences.agents.create');
    Route::post('/admin/agences/{agence}/agents', [AgenceController::class, 'storeAgent'])->name('admin.agences.agents.store');
    Route::get('/admin/guichets', [GuichetController::class, 'index'])->name('admin.guichets.index');
    Route::get('/admin/guichets/creer', [GuichetController::class, 'create'])->name('admin.guichets.create');
    Route::post('/admin/guichets', [GuichetController::class, 'store'])->name('admin.guichets.store');
    Route::get('/admin/guichets/{guichet}/modifier', [GuichetController::class, 'edit'])->name('admin.guichets.edit');
    Route::put('/admin/guichets/{guichet}', [GuichetController::class, 'update'])->name('admin.guichets.update');
    Route::delete('/admin/guichets/{guichet}', [GuichetController::class, 'destroy'])->name('admin.guichets.destroy');

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

    Route::get('/admin/alertes', [AlerteController::class, 'index'])->name('admin.alertes.index');
    Route::post('/admin/alertes', [AlerteController::class, 'store'])->name('admin.alertes.store');
    Route::patch('/admin/alertes/{alerte}/statut', [AlerteController::class, 'updateStatut'])->name('admin.alertes.statut');
    Route::delete('/admin/alertes/{alerte}', [AlerteController::class, 'destroy'])->name('admin.alertes.destroy');
    Route::delete('/admin/alertes', [AlerteController::class, 'destroyAll'])->name('admin.alertes.destroy-all');
    Route::post('/admin/alertes/lire-tout', [AlerteController::class, 'lireTout'])->name('admin.alertes.lire-tout');

    Route::get('/admin/parametres', [SettingsController::class, 'edit'])->name('admin.settings.edit');
    Route::put('/admin/parametres/theme', [SettingsController::class, 'updateTheme'])->name('admin.settings.theme.update');
    Route::put('/admin/parametres/securite', [SettingsController::class, 'updateSecurity'])->name('admin.settings.security.update');
    Route::put('/admin/parametres/mot-de-passe', [SettingsController::class, 'updatePassword'])->name('admin.settings.password.update');
    Route::delete('/admin/parametres/compte', [SettingsController::class, 'destroyAccount'])->name('admin.settings.account.destroy');
    Route::get('/admin/parametres/utilisateurs/recherche', [SettingsController::class, 'searchUsers'])->name('admin.settings.users.search');
    Route::put('/admin/parametres/utilisateurs/mot-de-passe', [SettingsController::class, 'updateUserPassword'])->name('admin.settings.users.password.update');
    Route::put('/admin/parametres/utilisateurs/role', [SettingsController::class, 'updateUserRole'])->name('admin.settings.users.role.update');
});

Route::middleware(['auth', 'pca'])->prefix('pca')->name('pca.')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/', PcaDashboardController::class)->name('dashboard');
    Route::get('/statistiques', PcaStatistiqueController::class)->name('statistiques.index');

    Route::get('/objectifs', [PcaObjectifController::class, 'index'])->name('objectifs.index');
    Route::get('/objectifs/creer', [PcaObjectifController::class, 'create'])->name('objectifs.create');
    Route::post('/objectifs', [PcaObjectifController::class, 'store'])->name('objectifs.store');
    Route::get('/objectifs/{objectif}/modifier', [PcaObjectifController::class, 'edit'])->name('objectifs.edit');
    Route::get('/objectifs/{objectif}', [PcaObjectifController::class, 'show'])->name('objectifs.show');
    Route::get('/objectifs/{objectif}/contrat', [PcaObjectifController::class, 'contrat'])->name('objectifs.contrat');
    Route::get('/objectifs/{objectif}/contrat/telecharger', [PcaObjectifController::class, 'contratDownload'])->name('objectifs.contrat.download');
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

// Shared route — all authenticated users can mark notifications as read
Route::middleware('auth')->post('/alertes/lire-tout', [AlerteController::class, 'lireTout'])->name('alertes.lire-tout');

// Routes DG
Route::middleware(['auth', 'dg'])->prefix('dg')->name('dg.')->group(function (): void {
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/', \App\Http\Controllers\Dg\DgDashboardController::class)->name('dashboard');
    Route::get('/objectifs', function () {
        return view('dg.objectifs');
    })->name('objectifs');
    Route::get('/evaluations', function () {
        return view('dg.evaluations');
    })->name('evaluations');
    Route::get('/objectifs/creer', [\App\Http\Controllers\Dg\DgObjectifController::class, 'create'])->name('objectifs.create');
    Route::post('/objectifs', [\App\Http\Controllers\Dg\DgObjectifController::class, 'store'])->name('objectifs.store');
    Route::get('/objectifs/{fiche}', [\App\Http\Controllers\Dg\DgObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut', [\App\Http\Controllers\Dg\DgObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement', [\App\Http\Controllers\Dg\DgObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::delete('/objectifs/{fiche}', [\App\Http\Controllers\Dg\DgObjectifController::class, 'destroy'])->name('objectifs.destroy');
    // Route pour afficher une évaluation DG
    Route::get('/evaluations/{evaluation}', [App\Http\Controllers\Dg\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut', [App\Http\Controllers\Dg\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::get('/evaluations/{evaluation}/pdf', [App\Http\Controllers\Dg\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::get('/subordonne-evaluations/creer',         [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'create'])->name('sub-evaluations.create');
    Route::post('/subordonne-evaluations',              [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'store'])->name('sub-evaluations.store');
    Route::get('/subordonne-evaluations/{evaluation}',      [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'show'])->name('sub-evaluations.show');
    Route::get('/subordonne-evaluations/{evaluation}/pdf',  [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'exportPdf'])->name('sub-evaluations.pdf');
    Route::patch('/subordonne-evaluations/{evaluation}/soumettre', [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'submit'])->name('sub-evaluations.submit');
    Route::delete('/subordonne-evaluations/{evaluation}',   [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'destroy'])->name('sub-evaluations.destroy');
    Route::get('/objectifs/{fiche}/pdf',                    [\App\Http\Controllers\Dg\DgObjectifController::class, 'exportPdf'])->name('objectifs.pdf');

    // Subordonnés du DG
    Route::get('/dga',                    [\App\Http\Controllers\Dg\DgSubordonneController::class, 'dga'])->name('dga');
    Route::get('/assistante',             [\App\Http\Controllers\Dg\DgSubordonneController::class, 'assistante'])->name('assistante');
    Route::get('/conseillers',            [\App\Http\Controllers\Dg\DgSubordonneController::class, 'conseillers'])->name('conseillers');
    Route::get('/conseillers/{user}',     [\App\Http\Controllers\Dg\DgSubordonneController::class, 'conseiller'])->name('conseillers.show');

    // Personnel du réseau
    Route::get('/personnel/pdf', [\App\Http\Controllers\Dg\DgPersonnelController::class, 'pdf'])->name('personnel.pdf');
    Route::get('/personnel', \App\Http\Controllers\Dg\DgPersonnelController::class)->name('personnel');

    // Réseau RCPB
    Route::get('/delegations',                        [\App\Http\Controllers\Dg\DgReseauController::class, 'delegations'])->name('delegations');
    Route::get('/delegations/{delegation}/pdf',       [\App\Http\Controllers\Dg\DgReseauController::class, 'delegationPdf'])->name('delegations.pdf');
    Route::get('/delegations/{delegation}',           [\App\Http\Controllers\Dg\DgReseauController::class, 'delegation'])->name('delegations.show');
    Route::get('/caisses',                            [\App\Http\Controllers\Dg\DgReseauController::class, 'caisses'])->name('caisses');
    Route::get('/caisses/{caisse}/pdf',               [\App\Http\Controllers\Dg\DgReseauController::class, 'caissePdf'])->name('caisses.pdf');
    Route::get('/caisses/{caisse}',                   [\App\Http\Controllers\Dg\DgReseauController::class, 'caisse'])->name('caisses.show');
    Route::get('/agences',                            [\App\Http\Controllers\Dg\DgReseauController::class, 'agences'])->name('agences');
    Route::get('/agences/{agence}/pdf',               [\App\Http\Controllers\Dg\DgReseauController::class, 'agencePdf'])->name('agences.pdf');
    Route::get('/agences/{agence}',                   [\App\Http\Controllers\Dg\DgReseauController::class, 'agence'])->name('agences.show');
    Route::get('/guichets',                           [\App\Http\Controllers\Dg\DgReseauController::class, 'guichets'])->name('guichets');
    Route::get('/guichets/{guichet}/pdf',             [\App\Http\Controllers\Dg\DgReseauController::class, 'guichetPdf'])->name('guichets.pdf');
    Route::get('/guichets/{guichet}',                 [\App\Http\Controllers\Dg\DgReseauController::class, 'guichet'])->name('guichets.show');

    // Directions de la faitière
    Route::get('/directions',                                         [\App\Http\Controllers\Dg\DgDirectionController::class, 'index'])->name('directions');
    Route::get('/directions/{direction}',                             [\App\Http\Controllers\Dg\DgDirectionController::class, 'show'])->name('directions.show');
    Route::get('/directions/{direction}/objectifs/creer',             [\App\Http\Controllers\Dg\DgDirectionController::class, 'createObjectif'])->name('directions.objectifs.create');
    Route::post('/directions/objectifs',                              [\App\Http\Controllers\Dg\DgDirectionController::class, 'storeObjectif'])->name('directions.objectifs.store');
    Route::get('/directions/objectifs/{fiche}',                       [\App\Http\Controllers\Dg\DgDirectionController::class, 'showObjectif'])->name('directions.objectifs.show');
    Route::patch('/directions/objectifs/{fiche}/avancement',          [\App\Http\Controllers\Dg\DgDirectionController::class, 'avancements'])->name('directions.objectifs.avancement');
    Route::delete('/directions/objectifs/{fiche}',                    [\App\Http\Controllers\Dg\DgDirectionController::class, 'destroyObjectif'])->name('directions.objectifs.destroy');
    Route::get('/directions/{direction}/evaluations/creer',           [\App\Http\Controllers\Dg\DgDirectionController::class, 'createEvaluation'])->name('directions.evaluations.create');
    Route::post('/directions/evaluations',                            [\App\Http\Controllers\Dg\DgDirectionController::class, 'storeEvaluation'])->name('directions.evaluations.store');
    Route::get('/directions/evaluations/{evaluation}',                [\App\Http\Controllers\Dg\DgDirectionController::class, 'showEvaluation'])->name('directions.evaluations.show');
    Route::get('/directions/evaluations/{evaluation}/pdf',            [\App\Http\Controllers\Dg\DgDirectionController::class, 'exportEvaluationPdf'])->name('directions.evaluations.pdf');
    Route::patch('/directions/evaluations/{evaluation}/soumettre',    [\App\Http\Controllers\Dg\DgDirectionController::class, 'submitEvaluation'])->name('directions.evaluations.submit');
    Route::delete('/directions/evaluations/{evaluation}',             [\App\Http\Controllers\Dg\DgDirectionController::class, 'destroyEvaluation'])->name('directions.evaluations.destroy');
});

// DG - Enregistrer le commentaire de l'évalué
Route::middleware(['auth'])->group(function () {
    Route::post('/dg/evaluations/{evaluation}/commentaire', [\App\Http\Controllers\Dg\EvaluationController::class, 'commentaire'])->name('dg.evaluations.commentaire');
});
// DG - Objectifs et Evaluations subordonnés
// Routes DG - Objectifs et Evaluations (listes)
Route::middleware(['auth', 'dg'])->prefix('dg')->name('dg.')->group(function (): void {
    Route::get('/objectifs', function () {
        return view('dg.objectifs');
    })->name('objectifs');
    Route::get('/evaluations', function () {
        return view('dg.evaluations');
    })->name('evaluations');
});


// Espace DG : Mon espace
Route::middleware(['auth', 'dg'])->get('/dg/mon-espace', \App\Http\Controllers\Dg\MonEspaceController::class)->name('dg.mon-espace');

// Espace DGA (Directeur General Adjoint)
Route::middleware(['auth', 'dga_espace'])->prefix('espace-dga')->name('dga.')->group(function (): void {
    Route::post('/logout',                                    [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/',                                           \App\Http\Controllers\Dga\DgaMonEspaceController::class)->name('mon-espace');
    Route::get('/evaluations/{evaluation}',                   [\App\Http\Controllers\Dga\DgaEvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',          [\App\Http\Controllers\Dga\DgaEvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::get('/evaluations/{evaluation}/pdf',               [\App\Http\Controllers\Dga\DgaEvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::post('/evaluations/{evaluation}/commentaire',      [\App\Http\Controllers\Dga\DgaEvaluationController::class, 'commentaire'])->name('evaluations.commentaire');
    Route::get('/objectifs/{fiche}',                          [\App\Http\Controllers\Dga\DgaObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',                 [\App\Http\Controllers\Dga\DgaObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',             [\App\Http\Controllers\Dga\DgaObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::get('/objectifs/{fiche}/pdf',                      [\App\Http\Controllers\Dga\DgaObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
});

// Espace Subordonnés (Assistante_Dg, Conseillers_Dg)
Route::middleware(['auth', 'subordonne'])->prefix('mon-espace')->name('subordonne.')->group(function (): void {
    Route::post('/logout',  [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/',         \App\Http\Controllers\Subordonne\SubordonneMonEspaceController::class)->name('mon-espace');
    Route::get('/evaluations/{evaluation}',            [\App\Http\Controllers\Subordonne\SubordonneEvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',   [\App\Http\Controllers\Subordonne\SubordonneEvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::get('/evaluations/{evaluation}/pdf',        [\App\Http\Controllers\Subordonne\SubordonneEvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::post('/evaluations/{evaluation}/commentaire', [\App\Http\Controllers\Subordonne\SubordonneEvaluationController::class, 'commentaire'])->name('evaluations.commentaire');
    Route::get('/objectifs/{fiche}',                   [\App\Http\Controllers\Subordonne\SubordonneObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',          [\App\Http\Controllers\Subordonne\SubordonneObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',      [\App\Http\Controllers\Subordonne\SubordonneObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::get('/objectifs/{fiche}/pdf',               [\App\Http\Controllers\Subordonne\SubordonneObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
});

// ── Espace Directeur de Direction ─────────────────────────────────────────────
Route::middleware(['auth', 'directeur_espace'])->prefix('espace-directeur')->name('directeur.')->group(function (): void {
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/',        \App\Http\Controllers\Directeur\DirecteurMonEspaceController::class)->name('mon-espace');

    // Évaluations reçues (direction = évalué) + créées (chef de service = évalué)
    Route::get('/evaluations/creer',                    [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'create'])->name('evaluations.create');
    Route::post('/evaluations',                          [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'store'])->name('evaluations.store');
    Route::get('/evaluations/{evaluation}',              [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/evaluations/{evaluation}/pdf',          [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::patch('/evaluations/{evaluation}/statut',     [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::patch('/evaluations/{evaluation}/soumettre',  [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'submit'])->name('evaluations.submit');
    Route::delete('/evaluations/{evaluation}',           [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'destroy'])->name('evaluations.destroy');

    // Objectifs reçus
    Route::get('/objectifs/{fiche}',              [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',     [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement', [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'avancement'])->name('objectifs.avancement');

    // ── Subordonnés ───────────────────────────────────────────────────────────
    Route::get('/subordonnes',                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'index'])->name('subordonnes');
    Route::get('/subordonnes/services/{service}',[\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showService'])->name('subordonnes.service');
    Route::get('/subordonnes/secretaire',        [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showSecretaire'])->name('subordonnes.secretaire');

    // Évaluations secrétaire
    Route::get('/subordonnes/secretaire/evaluations/creer',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createSecretaireEval'])->name('subordonnes.secretaire.evaluations.create');
    Route::post('/subordonnes/secretaire/evaluations',                              [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeSecretaireEval'])->name('subordonnes.secretaire.evaluations.store');
    Route::get('/subordonnes/secretaire/evaluations/{evaluation}',                  [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showSecretaireEval'])->name('subordonnes.secretaire.evaluations.show');
    Route::patch('/subordonnes/secretaire/evaluations/{evaluation}/soumettre',      [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'submitSecretaireEval'])->name('subordonnes.secretaire.evaluations.submit');
    Route::delete('/subordonnes/secretaire/evaluations/{evaluation}',               [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroySecretaireEval'])->name('subordonnes.secretaire.evaluations.destroy');

    // Objectifs services
    Route::get('/subordonnes/services/{service}/objectifs/creer',                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createServiceObjectif'])->name('subordonnes.service.objectifs.create');
    Route::post('/subordonnes/services/objectifs',                                  [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeServiceObjectif'])->name('subordonnes.service.objectifs.store');
    Route::get('/subordonnes/services/objectifs/{fiche}',                           [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showServiceObjectif'])->name('subordonnes.service.objectifs.show');
    Route::delete('/subordonnes/services/objectifs/{fiche}',                        [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroyServiceObjectif'])->name('subordonnes.service.objectifs.destroy');

    // Objectifs secrétaire
    Route::get('/subordonnes/secretaire/objectifs/creer',                           [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.create');
    Route::post('/subordonnes/secretaire/objectifs',                                [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.store');
    Route::get('/subordonnes/secretaire/objectifs/{fiche}',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.show');
    Route::delete('/subordonnes/secretaire/objectifs/{fiche}',                      [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroySecretaireObjectif'])->name('subordonnes.secretaire.objectifs.destroy');

    // ── Personnel ─────────────────────────────────────────────────────────────
    Route::get('/personnel',                     [\App\Http\Controllers\Directeur\DirecteurPersonnelController::class, 'index'])->name('personnel');
});
