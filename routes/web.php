<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\PosteController;
use App\Http\Controllers\Admin\AgenceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AlerteController;
use App\Http\Controllers\Admin\AnneeController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CaisseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DirectionController;
use App\Http\Controllers\Admin\EntiteController;
use App\Http\Controllers\Admin\GuichetController;
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
use App\Http\Controllers\Dg\DgObjectifController;
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

        // ── Direction Générale Adjointe (DGA) ─────────────────────────────────
        Route::get('/admin/direction-dga', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'index'])->name('admin.direction-dga.index');
        Route::get('/admin/direction-dga/configurer-dga', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'configurerDga'])->name('admin.direction-dga.configurer');
        Route::post('/admin/direction-dga/configurer-dga', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'stockerDga'])->name('admin.direction-dga.stocker');
        Route::get('/admin/direction-dga/services/{service}/chef', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'editChefService'])->name('admin.direction-dga.services.chef.edit');
        Route::put('/admin/direction-dga/services/{service}/chef', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'updateChefService'])->name('admin.direction-dga.services.chef.update');
        Route::get('/admin/direction-dga/secretaire', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'editSecretaire'])->name('admin.direction-dga.secretaire.edit');
        Route::put('/admin/direction-dga/secretaire', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'updateSecretaire'])->name('admin.direction-dga.secretaire.update');
    // Route pour enregistrer un secrétaire depuis la modale de la Faitière
    Route::post('/admin/secretaires', [EntiteController::class, 'storeSecretaire'])->name('admin.secretaires.store');
    Route::get('/admin/secretaires/{direction}', [EntiteController::class, 'showSecretaire'])->name('admin.secretaires.show');
    Route::get('/admin/secretaires/{direction}/modifier', [EntiteController::class, 'editSecretaire'])->name('admin.secretaires.edit');
    Route::delete('/admin/secretaires/{direction}', [EntiteController::class, 'destroySecretaire'])->name('admin.secretaires.destroy');
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
    Route::get('/admin/logout', fn () => redirect()->route('login'));
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
    Route::get('/admin/delegations-techniques', [DirectionController::class, 'delegationsIndex'])->name('admin.delegations-techniques.index');
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
    Route::post('/admin/services/affecter-caisse', [ServiceController::class, 'affecterCaisse']) ->name('admin.services.affecter-caisse');
    Route::post('/admin/services', [ServiceController::class, 'store'])->name('admin.services.store');
    Route::get('/admin/services/{service}', [ServiceController::class, 'show'])->name('admin.services.show');
    Route::get('/admin/services/{service}/modifier', [ServiceController::class, 'edit'])->name('admin.services.edit');
    Route::put('/admin/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
    Route::delete('/admin/services/{service}', [ServiceController::class, 'destroy'])->name('admin.services.destroy');
    Route::post('/admin/services/{service}/attach-agent', [ServiceController::class, 'attachAgent'])
     ->name('admin.services.attach-agent');

    // Gestion des comptes utilisateurs
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/creer', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/modifier', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::patch('/admin/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('admin.users.toggle-active');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/admin/postes', [PosteController::class, 'index'])->name('admin.postes.index');
    Route::post('/admin/postes', [PosteController::class, 'store'])->name('admin.postes.store');
    Route::delete('/admin/postes/{poste}', [PosteController::class, 'destroy'])->name('admin.postes.destroy');
    Route::get('/admin/postes/par-fonction/{fonction}', [PosteController::class, 'byFonction'])->name('admin.postes.by-fonction');

    Route::get('/admin/agents', [AgentController::class, 'index'])->name('admin.agents.index');
    Route::get('/admin/agents/creer', [AgentController::class, 'create'])->name('admin.agents.create');
    Route::post('/admin/agents', [AgentController::class, 'store'])->name('admin.agents.store');
    Route::post('/admin/agents/sync-comptes', [AgentController::class, 'syncAllAccounts'])->name('admin.agents.sync-accounts');
    Route::get('/admin/agents/{agent}', [AgentController::class, 'show'])->name('admin.agents.show');
    Route::get('/admin/agents/{agent}/modifier', [AgentController::class, 'edit'])->name('admin.agents.edit');
    Route::put('/admin/agents/{agent}', [AgentController::class, 'update'])->name('admin.agents.update');
    Route::delete('/admin/agents/{agent}', [AgentController::class, 'destroy'])->name('admin.agents.destroy');
    Route::post('/admin/agents/{agent}/activer-compte', [AgentController::class, 'activateAccount'])->name('admin.agents.activate-account');

    Route::get('/admin/caisses', [CaisseController::class, 'index'])->name('admin.caisses.index');
    Route::get('/admin/caisses/{caisse}/affecter-service', [CaisseController::class, 'affecterService'])->name('admin.caisses.affecter-service');
     
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
    Route::get('/admin/guichets/{guichet}/agents', [GuichetController::class, 'agentsIndex'])->name('admin.guichets.agents.index');
    Route::get('/admin/guichets/{guichet}/agents/ajouter', [GuichetController::class, 'createAgent'])->name('admin.guichets.agents.create');
    Route::post('/admin/guichets/{guichet}/agents', [GuichetController::class, 'storeAgent'])->name('admin.guichets.agents.store');


    Route::get('/admin/alertes', [AlerteController::class, 'index'])->name('admin.alertes.index');
    Route::post('/admin/alertes', [AlerteController::class, 'store'])->name('admin.alertes.store');
    Route::patch('/admin/alertes/{alerte}/statut', [AlerteController::class, 'updateStatut'])->name('admin.alertes.statut');
    Route::delete('/admin/alertes/{alerte}', [AlerteController::class, 'destroy'])->name('admin.alertes.destroy');
    Route::delete('/admin/alertes', [AlerteController::class, 'destroyAll'])->name('admin.alertes.destroy-all');
    Route::post('/admin/alertes/lire-tout', [AlerteController::class, 'lireTout'])->name('admin.alertes.lire-tout');

    Route::get('/admin/audit', [AuditLogController::class, 'index'])->name('admin.audit.index');

    // Gestion des années d'exercices
    Route::get('/admin/annees', [AnneeController::class, 'index'])->name('admin.annees.index');
    Route::post('/admin/annees', [AnneeController::class, 'store'])->name('admin.annees.store');
    Route::patch('/admin/annees/{annee}/toggle-statut', [AnneeController::class, 'toggleStatut'])->name('admin.annees.toggle-statut');
    Route::patch('/admin/annees/{annee}/semestres/{numero}/toggle', [AnneeController::class, 'toggleSemestre'])->name('admin.annees.semestres.toggle');
    Route::delete('/admin/annees/{annee}', [AnneeController::class, 'destroy'])->name('admin.annees.destroy');

    Route::get('/admin/parametres', [SettingsController::class, 'edit'])->name('admin.settings.edit');
    Route::put('/admin/parametres/theme', [SettingsController::class, 'updateTheme'])->name('admin.settings.theme.update');
    Route::put('/admin/parametres/securite', [SettingsController::class, 'updateSecurity'])->name('admin.settings.security.update');
    Route::put('/admin/parametres/mot-de-passe', [SettingsController::class, 'updatePassword'])->name('admin.settings.password.update');
    Route::delete('/admin/parametres/compte', [SettingsController::class, 'destroyAccount'])->name('admin.settings.account.destroy');
    Route::get('/admin/parametres/utilisateurs/recherche', [SettingsController::class, 'searchUsers'])->name('admin.settings.users.search');
    Route::put('/admin/parametres/utilisateurs/mot-de-passe', [SettingsController::class, 'updateUserPassword'])->name('admin.settings.users.password.update');
    Route::put('/admin/parametres/utilisateurs/role', [SettingsController::class, 'updateUserRole'])->name('admin.settings.users.role.update');
    Route::post('/admin/parametres/utilisateurs/{user}/permissions', [SettingsController::class, 'syncUserPermissions'])->name('admin.settings.users.permissions.sync');
    Route::post('/admin/parametres/permissions', [SettingsController::class, 'storePermission'])->name('admin.settings.permissions.store');
    Route::delete('/admin/parametres/permissions/{permission}', [SettingsController::class, 'destroyPermission'])->name('admin.settings.permissions.destroy');
    Route::post('/admin/parametres/roles/{roleSlug}/permissions', [SettingsController::class, 'syncRolePermissions'])->name('admin.settings.roles.permissions.sync');
    Route::post('/admin/parametres/roles', [SettingsController::class, 'storeRole'])->name('admin.settings.roles.store');
    Route::delete('/admin/parametres/roles/{customRole}', [SettingsController::class, 'destroyRole'])->name('admin.settings.roles.destroy');
    Route::post('/admin/parametres/fonctionnalites/{feature}/toggle', [SettingsController::class, 'toggleFeature'])->name('admin.settings.feature.toggle');
    Route::post('/admin/parametres/comptes/rh', [SettingsController::class, 'storeRhAccount'])->name('admin.settings.rh.store');
});

// Route accessible au PCA sans entité associée (évite la boucle middleware)
Route::middleware(['auth'])->get('/pca/en-attente', function () {
    if (!auth()->user()->isPca()) {
        return redirect()->route('login');
    }
    return view('pca.pending');
})->name('pca.pending');

Route::middleware(['auth', 'pca'])->prefix('pca')->name('pca.')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/', PcaDashboardController::class)->name('dashboard');
    Route::get('/statistiques', PcaStatistiqueController::class)->name('statistiques.index');
    Route::get('/comparaison', [\App\Http\Controllers\Pca\PcaAnalytiqueController::class, 'comparaison'])->name('comparaison.index');

    Route::get('/objectifs', [PcaObjectifController::class, 'index'])->name('objectifs.index');
    Route::get('/objectifs/creer', [PcaObjectifController::class, 'create'])->name('objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/objectifs', [PcaObjectifController::class, 'store'])->name('objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/objectifs/{objectif}/modifier', [PcaObjectifController::class, 'edit'])->name('objectifs.edit');
    Route::put('/objectifs/{objectif}', [PcaObjectifController::class, 'update'])->name('objectifs.update');
    Route::patch('/objectifs/{objectif}/soumettre', [PcaObjectifController::class, 'soumettre'])->name('objectifs.soumettre');
    Route::get('/objectifs/{objectif}', [PcaObjectifController::class, 'show'])->name('objectifs.show');
    Route::get('/objectifs/{objectif}/contrat', [PcaObjectifController::class, 'contrat'])->name('objectifs.contrat');
    Route::get('/objectifs/{objectif}/contrat/telecharger', [PcaObjectifController::class, 'contratDownload'])->name('objectifs.contrat.download');
    Route::post('/objectifs/{objectif}/progression', [PcaObjectifController::class, 'adjustProgress'])->name('objectifs.progress');
    Route::delete('/objectifs/{objectif}', [PcaObjectifController::class, 'destroy'])->name('objectifs.destroy');

    Route::get('/evaluations', [PcaEvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('/evaluations/creer', [PcaEvaluationController::class, 'create'])->name('evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/evaluations', [PcaEvaluationController::class, 'store'])->name('evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
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
    Route::get('/personnel/logout', fn () => redirect()->route('login'));
    Route::get('/personnel', PersonnelDashboardController::class)->name('personnel.dashboard');
    Route::get('/personnel/mon-espace', \App\Http\Controllers\Personnel\PersonnelMonEspaceController::class)->name('personnel.mon-espace');

    // ── Mes formations (Personnel) ────────────────────────────────────────────
    Route::get('/personnel/formations',               \App\Http\Controllers\Personnel\PersonnelFormationController::class)->name('personnel.formations.index');
    Route::get('/personnel/formations/{formation}/pdf', [\App\Http\Controllers\Personnel\PersonnelFormationController::class, 'pdf'])->name('personnel.formations.pdf');

    // ── Fiches d'objectifs reçues par le personnel ────────────────────────────
    // Accessible aux agents, secrétaires, et tout rôle sous le middleware 'personnel'.
    Route::get('/personnel/fiches/{fiche}',              [\App\Http\Controllers\Personnel\PersonnelFicheController::class, 'show'])->name('personnel.fiches.show');
    Route::patch('/personnel/fiches/{fiche}/statut',     [\App\Http\Controllers\Personnel\PersonnelFicheController::class, 'statut'])->name('personnel.fiches.statut');
    Route::patch('/personnel/fiches/{fiche}/avancement', [\App\Http\Controllers\Personnel\PersonnelFicheController::class, 'avancement'])->name('personnel.fiches.avancement');
    Route::get('/personnel/fiches/{fiche}/pdf',          [\App\Http\Controllers\Personnel\PersonnelFicheController::class, 'exportPdf'])->name('personnel.fiches.pdf');

    // ── Évaluations reçues par le personnel ───────────────────────────────────
    Route::get('/personnel/evaluations/{evaluation}',              [\App\Http\Controllers\Personnel\PersonnelEvaluationController::class, 'show'])->name('personnel.evaluations.show');
    Route::patch('/personnel/evaluations/{evaluation}/statut',     [\App\Http\Controllers\Personnel\PersonnelEvaluationController::class, 'statut'])->name('personnel.evaluations.statut');
    Route::post('/personnel/evaluations/{evaluation}/reclamer',   [\App\Http\Controllers\Personnel\PersonnelEvaluationController::class, 'reclamer'])->name('personnel.evaluations.reclamer');
    Route::get('/personnel/evaluations/{evaluation}/pdf',          [\App\Http\Controllers\Personnel\PersonnelEvaluationController::class, 'exportPdf'])->name('personnel.evaluations.pdf');

    // ── Statistiques (accès par permission) ───────────────────────────────────
    Route::get('/personnel/statistiques', \App\Http\Controllers\Personnel\PersonnelStatistiqueController::class)
        ->name('personnel.statistiques')
        ->middleware('can:statistiques.voir');

    // ── Tableaux Excel (accès par permission) ─────────────────────────────────
    Route::get('/personnel/tableaux',        [\App\Http\Controllers\Personnel\PersonnelTableauController::class, 'index'])->name('personnel.tableaux.index')->middleware('can:tableaux.voir');
    Route::get('/personnel/tableaux/export', [\App\Http\Controllers\Personnel\PersonnelTableauController::class, 'export'])->name('personnel.tableaux.export')->middleware('can:tableaux.voir');
});

// ── Espace Gerer : modules accessibles par permission individuelle ──────────────
Route::middleware(['auth'])->prefix('gerer')->name('gerer.')->group(function (): void {

    // Formations (formations.assigner)
    Route::middleware('can:formations.assigner')->group(function () {
        Route::get('/formations',                    [\App\Http\Controllers\Gerer\FormationGererController::class, 'index'])->name('formations.index');
        Route::get('/formations/creer',              [\App\Http\Controllers\Gerer\FormationGererController::class, 'create'])->name('formations.create');
        Route::post('/formations',                   [\App\Http\Controllers\Gerer\FormationGererController::class, 'store'])->name('formations.store');
        Route::get('/formations/{formation}/editer', [\App\Http\Controllers\Gerer\FormationGererController::class, 'edit'])->name('formations.edit');
        Route::put('/formations/{formation}',        [\App\Http\Controllers\Gerer\FormationGererController::class, 'update'])->name('formations.update');
        Route::delete('/formations/{formation}',     [\App\Http\Controllers\Gerer\FormationGererController::class, 'destroy'])->name('formations.destroy');
        Route::get('/formations/{formation}/pdf',    [\App\Http\Controllers\Gerer\FormationGererController::class, 'pdf'])->name('formations.pdf');
    });

    // Personnel (agents.voir)
    Route::middleware('can:agents.voir')->group(function () {
        Route::get('/personnel', [\App\Http\Controllers\Gerer\PersonnelGererController::class, 'index'])->name('personnel.index');
    });

    // Structures (structures.voir)
    Route::middleware('can:structures.voir')->group(function () {
        Route::get('/structures', [\App\Http\Controllers\Gerer\StructureGererController::class, 'index'])->name('structures.index');
    });

    // Alertes (admin.alertes)
    Route::middleware('can:admin.alertes')->group(function () {
        Route::get('/alertes',                   [\App\Http\Controllers\Gerer\AlerteGererController::class, 'index'])->name('alertes.index');
        Route::post('/alertes',                  [\App\Http\Controllers\Gerer\AlerteGererController::class, 'store'])->name('alertes.store');
        Route::delete('/alertes/{alerte}',       [\App\Http\Controllers\Gerer\AlerteGererController::class, 'destroy'])->name('alertes.destroy');
        Route::patch('/alertes/{alerte}/statut', [\App\Http\Controllers\Gerer\AlerteGererController::class, 'updateStatut'])->name('alertes.statut');
    });

    // Journal d'activité (admin.activites)
    Route::middleware('can:admin.activites')->group(function () {
        Route::get('/activites', [\App\Http\Controllers\Gerer\ActiviteGererController::class, 'index'])->name('activites.index');
    });

    // Toutes les évaluations réseau (evaluations.voir-reseau)
    Route::middleware('can:evaluations.voir-reseau')->group(function () {
        Route::get('/evaluations', [\App\Http\Controllers\Gerer\EvaluationGererController::class, 'index'])->name('evaluations.index');
    });
});

// Routes RH
Route::middleware(['auth', 'rh'])->prefix('rh')->name('rh.')->group(function (): void {
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/',          \App\Http\Controllers\Rh\RhDashboardController::class)->name('dashboard');
    Route::get('/comparaison', [\App\Http\Controllers\Rh\RhAnalytiqueController::class, 'comparaison'])->name('comparaison.index');

    // Formations (CRUD complet, RH uniquement)
    Route::get('/formations',                      [\App\Http\Controllers\Rh\RhFormationController::class, 'index'])->name('formations.index');
    Route::get('/formations/creer',                [\App\Http\Controllers\Rh\RhFormationController::class, 'create'])->name('formations.create');
    Route::post('/formations',                     [\App\Http\Controllers\Rh\RhFormationController::class, 'store'])->name('formations.store');
    Route::get('/formations/{formation}/editer',   [\App\Http\Controllers\Rh\RhFormationController::class, 'edit'])->name('formations.edit');
    Route::put('/formations/{formation}',          [\App\Http\Controllers\Rh\RhFormationController::class, 'update'])->name('formations.update');
    Route::delete('/formations/{formation}',       [\App\Http\Controllers\Rh\RhFormationController::class, 'destroy'])->name('formations.destroy');
    Route::get('/formations/{formation}/pdf',      [\App\Http\Controllers\Rh\RhFormationController::class, 'pdf'])->name('formations.pdf');

    // Évaluations (lecture seule + réclamations)
    Route::get('/evaluations/{evaluation}', [\App\Http\Controllers\Rh\RhEvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/reclamations',                                [\App\Http\Controllers\Rh\RhReclamationController::class, 'index'])->name('reclamations.index');
    Route::post('/reclamations/{evaluation}/repondre',         [\App\Http\Controllers\Rh\RhReclamationController::class, 'repondre'])->name('reclamations.repondre');

    // Structures du réseau (vue agrégée)
    Route::get('/structures',     \App\Http\Controllers\Rh\RhStructureController::class)->name('structures');
    Route::get('/structures/pdf', [\App\Http\Controllers\Rh\RhStructureController::class, 'pdf'])->name('structures.pdf');

    // Statistiques
    Route::get('/statistiques', \App\Http\Controllers\Rh\RhStatistiqueController::class)->name('statistiques');

    // Tableaux personnalisés
    Route::get('/tableaux',        [\App\Http\Controllers\Rh\RhTableauController::class, 'index'])->name('tableaux.index');
    Route::get('/tableaux/export', [\App\Http\Controllers\Rh\RhTableauController::class, 'export'])->name('tableaux.export');
});

// Shared routes — all authenticated users
Route::middleware('auth')->group(function () {
    Route::get('/alertes/non-lues',        [AlerteController::class, 'nonLues'])->name('alertes.non-lues');
    Route::post('/alertes/lire-tout',      [AlerteController::class, 'lireTout'])->name('alertes.lire-tout');
    Route::get('/formations/agent/{agent}', [\App\Http\Controllers\Rh\RhFormationController::class, 'pourAgent'])->name('formations.pour-agent');

    // Page de notifications (tous les rôles)
    Route::get('/mes-notifications',                       [\App\Http\Controllers\NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('/mes-notifications/{alerte}/marquer-lu',  [\App\Http\Controllers\NotificationsController::class, 'marquerLu'])->name('notifications.marquer-lu');
    Route::post('/mes-notifications/lire-tout',            [\App\Http\Controllers\NotificationsController::class, 'marquerToutLu'])->name('notifications.lire-tout');
});

// Routes DG
Route::middleware(['auth', 'dg'])->prefix('dg')->name('dg.')->group(function (): void {
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/', \App\Http\Controllers\Dg\DgDashboardController::class)->name('dashboard');
    Route::get('/comparaison', [\App\Http\Controllers\Dg\DgAnalytiqueController::class, 'comparaison'])->name('comparaison.index');
    // Objectifs du DG (reçus + assignés aux subordonnés) — spécifiques AVANT le wildcard {fiche}
    Route::get('/objectifs/creer',               [\App\Http\Controllers\Dg\DgObjectifController::class, 'create'])->name('objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/objectifs',                    [\App\Http\Controllers\Dg\DgObjectifController::class, 'store'])->name('objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/objectifs/{fiche}/pdf',         [\App\Http\Controllers\Dg\DgObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
    Route::get('/objectifs/{fiche}',             [\App\Http\Controllers\Dg\DgObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',              [\App\Http\Controllers\Dg\DgObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',          [\App\Http\Controllers\Dg\DgObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/contester',  [\App\Http\Controllers\Dg\DgObjectifController::class, 'contesterLigne'])->name('objectifs.lignes.contester');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/avancement', [\App\Http\Controllers\Dg\DgObjectifController::class, 'avancementLigne'])->name('objectifs.lignes.avancement');
    Route::delete('/objectifs/{fiche}',                    [\App\Http\Controllers\Dg\DgObjectifController::class, 'destroy'])->name('objectifs.destroy');

    // Évaluations reçues par le DG (de la PCA) — spécifiques AVANT le wildcard {evaluation}
    Route::get('/evaluations/{evaluation}/pdf',  [App\Http\Controllers\Dg\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::get('/evaluations/{evaluation}',      [App\Http\Controllers\Dg\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',  [App\Http\Controllers\Dg\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer', [App\Http\Controllers\Dg\EvaluationController::class, 'reclamer'])->name('evaluations.reclamer');

    // Évaluations données par le DG à ses subordonnés — spécifiques AVANT le wildcard
    Route::get('/subordonne-evaluations/creer',                    [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'create'])->name('sub-evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/subordonne-evaluations',                         [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'store'])->name('sub-evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonne-evaluations/{evaluation}/edit',        [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'edit'])->name('sub-evaluations.edit')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::put('/subordonne-evaluations/{evaluation}',              [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'update'])->name('sub-evaluations.update')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonne-evaluations/{evaluation}/pdf',         [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'exportPdf'])->name('sub-evaluations.pdf');
    Route::get('/subordonne-evaluations/{evaluation}',             [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'show'])->name('sub-evaluations.show');
    Route::patch('/subordonne-evaluations/{evaluation}/soumettre', [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'submit'])->name('sub-evaluations.submit');
    Route::delete('/subordonne-evaluations/{evaluation}',          [\App\Http\Controllers\Dg\DgSubEvaluationController::class, 'destroy'])->name('sub-evaluations.destroy');

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
    Route::get('/directions/{direction}/objectifs/creer',             [\App\Http\Controllers\Dg\DgDirectionController::class, 'createObjectif'])->name('directions.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/directions/objectifs',                              [\App\Http\Controllers\Dg\DgDirectionController::class, 'storeObjectif'])->name('directions.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/directions/objectifs/{fiche}',                       [\App\Http\Controllers\Dg\DgDirectionController::class, 'showObjectif'])->name('directions.objectifs.show');

    Route::delete('/directions/objectifs/{fiche}',                    [\App\Http\Controllers\Dg\DgDirectionController::class, 'destroyObjectif'])->name('directions.objectifs.destroy');
    Route::get('/directions/{direction}/evaluations/creer',           [\App\Http\Controllers\Dg\DgDirectionController::class, 'createEvaluation'])->name('directions.evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/directions/evaluations',                            [\App\Http\Controllers\Dg\DgDirectionController::class, 'storeEvaluation'])->name('directions.evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/directions/evaluations/{evaluation}',                [\App\Http\Controllers\Dg\DgDirectionController::class, 'showEvaluation'])->name('directions.evaluations.show');
    Route::get('/directions/evaluations/{evaluation}/pdf',            [\App\Http\Controllers\Dg\DgDirectionController::class, 'exportEvaluationPdf'])->name('directions.evaluations.pdf');
    Route::patch('/directions/evaluations/{evaluation}/soumettre',    [\App\Http\Controllers\Dg\DgDirectionController::class, 'submitEvaluation'])->name('directions.evaluations.submit');
    Route::delete('/directions/evaluations/{evaluation}',             [\App\Http\Controllers\Dg\DgDirectionController::class, 'destroyEvaluation'])->name('directions.evaluations.destroy');

    // Structures du réseau (vue agrégée)
    Route::get('/structures',     \App\Http\Controllers\Dg\DgStructureController::class)->name('structures');
    Route::get('/structures/pdf', [\App\Http\Controllers\Dg\DgStructureController::class, 'pdf'])->name('structures.pdf');

    // Statistiques
    Route::get('/statistiques', \App\Http\Controllers\Dg\DgStatistiqueController::class)->name('statistiques');
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
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/',                                           \App\Http\Controllers\Dga\DgaDashboardController::class)->name('dashboard');
    Route::get('/mon-dossier',                                \App\Http\Controllers\Dga\MonEspaceController::class)->name('mon-espace');
    Route::get('/ma-direction',                               [\App\Http\Controllers\Dga\DgaDirectionController::class, 'index'])->name('direction');

    // Évaluations reçues
    Route::get('/evaluations/{evaluation}',                   [\App\Http\Controllers\Dga\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',          [\App\Http\Controllers\Dga\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer',         [\App\Http\Controllers\Dga\EvaluationController::class, 'reclamer'])->name('evaluations.reclamer');
    Route::get('/evaluations/{evaluation}/pdf',               [\App\Http\Controllers\Dga\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::post('/evaluations/{evaluation}/commentaire',      [\App\Http\Controllers\Dga\EvaluationController::class, 'commentaire'])->name('evaluations.commentaire');

    // Fiches objectifs reçues
    Route::get('/objectifs/{fiche}',                                              [\App\Http\Controllers\Dga\ObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',                                    [\App\Http\Controllers\Dga\ObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',                                [\App\Http\Controllers\Dga\ObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::get('/objectifs/{fiche}/pdf',                                          [\App\Http\Controllers\Dga\ObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/avancement',                 [\App\Http\Controllers\Dga\ObjectifController::class, 'avancementLigne'])->name('objectifs.lignes.avancement');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/contester',                  [\App\Http\Controllers\Dga\ObjectifController::class, 'contesterLigne'])->name('objectifs.lignes.contester');

    // Mes subordonnés (Directeurs Techniques + secrétaire)
    Route::get('/subordonnes',                                      [\App\Http\Controllers\Dga\DgaSubordonnesController::class, 'index'])->name('subordonnes.index');
    Route::get('/subordonnes/{user}',                               [\App\Http\Controllers\Dga\DgaSubordonnesController::class, 'show'])->name('subordonnes.show');

    // Fiches d'objectifs assignées par le DGA à ses subordonnés — spécifiques AVANT le wildcard
    Route::get('/sub-objectifs/creer',               [\App\Http\Controllers\Dga\DgaSubObjectifController::class, 'create'])->name('sub-objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/sub-objectifs',                    [\App\Http\Controllers\Dga\DgaSubObjectifController::class, 'store'])->name('sub-objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/sub-objectifs/{fiche}',             [\App\Http\Controllers\Dga\DgaSubObjectifController::class, 'show'])->name('sub-objectifs.show');

    Route::delete('/sub-objectifs/{fiche}',          [\App\Http\Controllers\Dga\DgaSubObjectifController::class, 'destroy'])->name('sub-objectifs.destroy');

    // Évaluations des subordonnés (DGA → DTs / secrétaire)
    Route::get('/subordonne-evaluations/creer',                     [\App\Http\Controllers\Dga\DgaSubEvaluationController::class, 'create'])->name('sub-evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/subordonne-evaluations',                          [\App\Http\Controllers\Dga\DgaSubEvaluationController::class, 'store'])->name('sub-evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonne-evaluations/{evaluation}',              [\App\Http\Controllers\Dga\DgaSubEvaluationController::class, 'show'])->name('sub-evaluations.show');
    Route::get('/subordonne-evaluations/{evaluation}/pdf',          [\App\Http\Controllers\Dga\DgaSubEvaluationController::class, 'exportPdf'])->name('sub-evaluations.pdf');
    Route::patch('/subordonne-evaluations/{evaluation}/soumettre',  [\App\Http\Controllers\Dga\DgaSubEvaluationController::class, 'submit'])->name('sub-evaluations.submit');
    Route::delete('/subordonne-evaluations/{evaluation}',           [\App\Http\Controllers\Dga\DgaSubEvaluationController::class, 'destroy'])->name('sub-evaluations.destroy');

    // Notes du réseau (subordonnés directs + tous les agents des délégations)
    Route::get('/notes-reseau',              [\App\Http\Controllers\Dga\DgaNotesReseauController::class, 'index'])->name('notes-reseau.index');
    Route::get('/notes-reseau/{evaluation}', [\App\Http\Controllers\Dga\DgaNotesReseauController::class, 'show'])->name('notes-reseau.show');

    // Structures (vue hiérarchique par DT avec onglets)
    Route::get('/structures', [\App\Http\Controllers\Dga\DgaStructuresController::class, 'index'])->name('structures.index');

    // Réseau (lecture seule — délégations, caisses, agences, guichets)
    Route::prefix('reseau')->name('reseau.')->group(function (): void {
        Route::get('/delegations',              [\App\Http\Controllers\Dga\DgaReseauController::class, 'delegations'])->name('delegations');
        Route::get('/delegations/{delegation}', [\App\Http\Controllers\Dga\DgaReseauController::class, 'delegation'])->name('delegations.show');
        Route::get('/caisses',                  [\App\Http\Controllers\Dga\DgaReseauController::class, 'caisses'])->name('caisses');
        Route::get('/caisses/{caisse}',         [\App\Http\Controllers\Dga\DgaReseauController::class, 'caisse'])->name('caisses.show');
        Route::get('/agences',                  [\App\Http\Controllers\Dga\DgaReseauController::class, 'agences'])->name('agences');
        Route::get('/agences/{agence}',         [\App\Http\Controllers\Dga\DgaReseauController::class, 'agence'])->name('agences.show');
        Route::get('/guichets',                 [\App\Http\Controllers\Dga\DgaReseauController::class, 'guichets'])->name('guichets');
        Route::get('/guichets/{guichet}',       [\App\Http\Controllers\Dga\DgaReseauController::class, 'guichet'])->name('guichets.show');
    });

    // ── Mes formations (DGA) ──────────────────────────────────────────────────
    Route::get('/formations',                    \App\Http\Controllers\Dga\DgaFormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf',    [\App\Http\Controllers\Dga\DgaFormationController::class, 'pdf'])->name('formations.pdf');
});

// Espace Subordonnés (Assistante_Dg, Conseillers_Dg) — mêmes contrôleurs que DGA
Route::middleware(['auth', 'subordonne'])->prefix('mon-espace')->name('subordonne.')->group(function (): void {
    Route::post('/logout',  [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/',         \App\Http\Controllers\Dga\MonEspaceController::class)->name('mon-espace');
    Route::get('/evaluations/{evaluation}',              [\App\Http\Controllers\Dga\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',     [\App\Http\Controllers\Dga\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer',    [\App\Http\Controllers\Dga\EvaluationController::class, 'reclamer'])->name('evaluations.reclamer');
    Route::get('/evaluations/{evaluation}/pdf',          [\App\Http\Controllers\Dga\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::post('/evaluations/{evaluation}/commentaire', [\App\Http\Controllers\Dga\EvaluationController::class, 'commentaire'])->name('evaluations.commentaire');
    Route::get('/objectifs/{fiche}',                                         [\App\Http\Controllers\Dga\ObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',                               [\App\Http\Controllers\Dga\ObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',                           [\App\Http\Controllers\Dga\ObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::get('/objectifs/{fiche}/pdf',                                     [\App\Http\Controllers\Dga\ObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/avancement',            [\App\Http\Controllers\Dga\ObjectifController::class, 'avancementLigne'])->name('objectifs.lignes.avancement');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/contester',             [\App\Http\Controllers\Dga\ObjectifController::class, 'contesterLigne'])->name('objectifs.lignes.contester');
});

// ── Espace Assistante DG ──────────────────────────────────────────────────────
Route::middleware(['auth', 'subordonne'])->prefix('assistante')->name('assistante.')->group(function (): void {
    Route::get('/secretaire',                                                        [\App\Http\Controllers\Assistante\AssistanteController::class, 'secretaire'])->name('secretaire');
    Route::get('/secretaire/evaluations/creer',                                      [\App\Http\Controllers\Assistante\AssistanteController::class, 'createEval'])->name('secretaire.evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/secretaire/evaluations',                                           [\App\Http\Controllers\Assistante\AssistanteController::class, 'storeEval'])->name('secretaire.evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/secretaire/evaluations/{evaluation}',                               [\App\Http\Controllers\Assistante\AssistanteController::class, 'showEval'])->name('secretaire.evaluations.show');
    Route::patch('/secretaire/evaluations/{evaluation}/soumettre',                   [\App\Http\Controllers\Assistante\AssistanteController::class, 'submitEval'])->name('secretaire.evaluations.submit');
    Route::delete('/secretaire/evaluations/{evaluation}',                            [\App\Http\Controllers\Assistante\AssistanteController::class, 'destroyEval'])->name('secretaire.evaluations.destroy');
    Route::get('/secretaire/objectifs/creer',                                        [\App\Http\Controllers\Assistante\AssistanteController::class, 'createObjectif'])->name('secretaire.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/secretaire/objectifs',                                             [\App\Http\Controllers\Assistante\AssistanteController::class, 'storeObjectif'])->name('secretaire.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/secretaire/objectifs/{fiche}',                                      [\App\Http\Controllers\Assistante\AssistanteController::class, 'showObjectif'])->name('secretaire.objectifs.show');
    Route::delete('/secretaire/objectifs/{fiche}',                                   [\App\Http\Controllers\Assistante\AssistanteController::class, 'destroyObjectif'])->name('secretaire.objectifs.destroy');
});

// Routes accessibles au Directeur sans structure associée (évite la boucle middleware)
Route::middleware(['auth'])->get('/espace-directeur/non-configure', function () {
    $role = auth()->user()->role ?? '';
    if (! in_array($role, ['Directeur_Direction', 'Directeur_Caisse', 'Directeur_Technique'], true)) {
        return redirect()->route('login');
    }
    return view('directeur.pending');
})->name('directeur.pending');

// Déconnexion directeur depuis la page d'attente (contourne le middleware directeur_espace)
Route::middleware(['auth'])->post('/espace-directeur/non-configure/deconnecter', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('directeur.pending.logout');

// ── Espace Directeur de Direction ─────────────────────────────────────────────
Route::middleware(['auth', 'directeur_espace'])->prefix('espace-directeur')->name('directeur.')->group(function (): void {
    Route::post('/logout',    [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/',           \App\Http\Controllers\Directeur\DirecteurDashboardController::class)->name('dashboard');
    Route::get('/mon-espace', \App\Http\Controllers\Directeur\DirecteurMonEspaceController::class)->name('mon-espace');

    // Évaluations reçues (direction = évalué) + créées (chef de service = évalué)
    Route::get('/evaluations/creer',                    [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'create'])->name('evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/evaluations',                          [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'store'])->name('evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations/{evaluation}',              [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/evaluations/{evaluation}/pdf',          [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::patch('/evaluations/{evaluation}/statut',     [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer',    [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'reclamer'])->name('evaluations.reclamer');
    Route::patch('/evaluations/{evaluation}/soumettre',  [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'submit'])->name('evaluations.submit');
    Route::delete('/evaluations/{evaluation}',           [\App\Http\Controllers\Directeur\DirecteurEvaluationController::class, 'destroy'])->name('evaluations.destroy');

    // Objectifs reçus
    Route::get('/objectifs/{fiche}',                                    [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',                         [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',                     [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::get('/objectifs/{fiche}/pdf',                               [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/avancement',      [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'avancementLigne'])->name('objectifs.lignes.avancement');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/contester',       [\App\Http\Controllers\Directeur\DirecteurObjectifController::class, 'contesterLigne'])->name('objectifs.lignes.contester');

    // ── Subordonnés ───────────────────────────────────────────────────────────
    Route::get('/subordonnes',                    [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'index'])->name('subordonnes');
    Route::get('/subordonnes/chefs',              [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'indexChefs'])->name('subordonnes.chefs');
    Route::get('/subordonnes/directeurs',         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'indexDirecteurs'])->name('subordonnes.directeurs');
    Route::get('/subordonnes/services/{service}', [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showService'])->name('subordonnes.service');
    Route::get('/subordonnes/secretaire',        [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showSecretaire'])->name('subordonnes.secretaire');

    // Évaluations secrétaire
    Route::get('/subordonnes/secretaire/evaluations/creer',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createSecretaireEval'])->name('subordonnes.secretaire.evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/subordonnes/secretaire/evaluations',                              [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeSecretaireEval'])->name('subordonnes.secretaire.evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonnes/secretaire/evaluations/{evaluation}',                  [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showSecretaireEval'])->name('subordonnes.secretaire.evaluations.show');
    Route::patch('/subordonnes/secretaire/evaluations/{evaluation}/soumettre',      [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'submitSecretaireEval'])->name('subordonnes.secretaire.evaluations.submit');
    Route::delete('/subordonnes/secretaire/evaluations/{evaluation}',               [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroySecretaireEval'])->name('subordonnes.secretaire.evaluations.destroy');

    // Objectifs services
    Route::get('/subordonnes/services/{service}/objectifs/creer',                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createServiceObjectif'])->name('subordonnes.service.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/subordonnes/services/objectifs',                                  [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeServiceObjectif'])->name('subordonnes.service.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/services/objectifs/{fiche}',                           [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showServiceObjectif'])->name('subordonnes.service.objectifs.show');
    Route::delete('/subordonnes/services/objectifs/{fiche}',                        [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroyServiceObjectif'])->name('subordonnes.service.objectifs.destroy');

    // Objectifs secrétaire
    Route::get('/subordonnes/secretaire/objectifs/creer',                           [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/subordonnes/secretaire/objectifs',                                [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/secretaire/objectifs/{fiche}',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.show');
    Route::delete('/subordonnes/secretaire/objectifs/{fiche}',                      [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroySecretaireObjectif'])->name('subordonnes.secretaire.objectifs.destroy');

    // Agences (Directeur_Caisse uniquement)
    Route::get('/subordonnes/agences/{agence}',                                     [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showAgence'])->name('subordonnes.agence');
    Route::get('/subordonnes/agences/{agence}/objectifs/creer',                     [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createAgenceObjectif'])->name('subordonnes.agence.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/subordonnes/agences/objectifs',                                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeAgenceObjectif'])->name('subordonnes.agence.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/agences/objectifs/{fiche}',                            [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showAgenceObjectif'])->name('subordonnes.agence.objectifs.show');
    Route::delete('/subordonnes/agences/objectifs/{fiche}',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroyAgenceObjectif'])->name('subordonnes.agence.objectifs.destroy');

    // Caisses (Directeur_Technique uniquement)
    Route::get('/subordonnes/caisses/{caisse}',                                     [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showCaisse'])->name('subordonnes.caisse');
    Route::get('/subordonnes/caisses/{caisse}/objectifs/creer',                     [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createCaisseObjectif'])->name('subordonnes.caisse.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/subordonnes/caisses/objectifs',                                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeCaisseObjectif'])->name('subordonnes.caisse.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/caisses/objectifs/{fiche}',                            [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showCaisseObjectif'])->name('subordonnes.caisse.objectifs.show');
    Route::delete('/subordonnes/caisses/objectifs/{fiche}',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroyCaisseObjectif'])->name('subordonnes.caisse.objectifs.destroy');

    // ── Personnel ─────────────────────────────────────────────────────────────
    Route::get('/personnel/export',              [\App\Http\Controllers\Directeur\DirecteurPersonnelController::class, 'export'])->name('personnel.export');
    Route::get('/personnel',                     [\App\Http\Controllers\Directeur\DirecteurPersonnelController::class, 'index'])->name('personnel');

    // ── Mes formations (Directeur) ────────────────────────────────────────────
    Route::get('/formations',                      \App\Http\Controllers\Directeur\DirecteurFormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf',      [\App\Http\Controllers\Directeur\DirecteurFormationController::class, 'pdf'])->name('formations.pdf');
});

// Route accessible au Chef sans structure associée (évite la boucle middleware)
Route::middleware(['auth'])->get('/chef/non-configure', function () {
    $role = auth()->user()->role ?? '';
    if (! in_array($role, ['Chef_Service', 'Chef_Agence', 'Chef_Guichet'], true)) {
        return redirect()->route('login');
    }
    return view('chef.pending');
})->name('chef.pending');

// Déconnexion chef depuis la page d'attente (contourne le middleware chef)
Route::middleware(['auth'])->post('/chef/non-configure/deconnecter', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('chef.pending.logout');

// ── Espace Chef (Chef_Service, Chef_Agence, Chef_Guichet) ─────────────────────
// Les trois types de chefs partagent le même espace.
// ChefEntity::resolve() distingue le type au moment de l'exécution.
Route::middleware(['auth', 'chef'])->prefix('chef')->name('chef.')->group(function () {
    // Déconnexion
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));

    // Tableau de bord (landing page) + mon espace détaillé
    Route::get('/',           \App\Http\Controllers\Chef\ChefDashboardController::class)->name('dashboard');
    Route::get('/mon-espace', \App\Http\Controllers\Chef\ChefMonEspaceController::class)->name('mon-espace');
    Route::get('/mon-equipe', \App\Http\Controllers\Chef\ChefEquipeController::class)->name('equipe');

    // ── Évaluations créées par le chef pour ses agents ────────────────────────
    Route::get('/evaluations/creer',                       [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'create'])->name('evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/evaluations',                             [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'store'])->name('evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations/{evaluation}/modifier',        [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'edit'])->name('evaluations.edit')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::put('/evaluations/{evaluation}',                 [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'update'])->name('evaluations.update')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations/{evaluation}',                 [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',        [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer',       [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'reclamer'])->name('evaluations.reclamer');
    Route::post('/evaluations/{evaluation}/soumettre',      [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'submit'])->name('evaluations.submit');
    Route::delete('/evaluations/{evaluation}',              [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'destroy'])->name('evaluations.destroy');

    // ── Objectifs assignés par le chef à ses agents ───────────────────────────
    Route::get('/objectifs/assigner',                       [\App\Http\Controllers\Chef\ChefObjectifController::class, 'create'])->name('objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/objectifs',                               [\App\Http\Controllers\Chef\ChefObjectifController::class, 'store'])->name('objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/objectifs/{fiche}',                        [\App\Http\Controllers\Chef\ChefObjectifController::class, 'show'])->name('objectifs.show');
    Route::delete('/objectifs/{fiche}',                     [\App\Http\Controllers\Chef\ChefObjectifController::class, 'destroy'])->name('objectifs.destroy');

    // ── Fiches d'objectifs REÇUES par le chef (assignées par le directeur) ────
    // Distinct des routes ci-dessus qui gèrent les fiches que le chef ASSIGNE.
    // Préfixe /mes-fiches pour éviter toute ambiguïté de routes avec {fiche}.
    Route::get('/mes-fiches/{fiche}',              [\App\Http\Controllers\Chef\ChefReceivedFicheController::class, 'show'])->name('mes-fiches.show');
    Route::patch('/mes-fiches/{fiche}/statut',                        [\App\Http\Controllers\Chef\ChefReceivedFicheController::class, 'statut'])->name('mes-fiches.statut');
    Route::patch('/mes-fiches/{fiche}/avancement',                   [\App\Http\Controllers\Chef\ChefReceivedFicheController::class, 'avancement'])->name('mes-fiches.avancement');
    Route::get('/mes-fiches/{fiche}/pdf',                             [\App\Http\Controllers\Chef\ChefReceivedFicheController::class, 'exportPdf'])->name('mes-fiches.pdf');
    Route::patch('/mes-fiches/{fiche}/lignes/{ligne}/avancement',    [\App\Http\Controllers\Chef\ChefReceivedFicheController::class, 'avancementLigne'])->name('mes-fiches.lignes.avancement');
    Route::patch('/mes-fiches/{fiche}/lignes/{ligne}/contester',     [\App\Http\Controllers\Chef\ChefReceivedFicheController::class, 'contesterLigne'])->name('mes-fiches.lignes.contester');

    // ── PDF évaluation reçue par le chef ──────────────────────────────────────
    // Le show de l'évaluation reçue passe par chef.evaluations.show (dual mode).
    // On ajoute seulement la route PDF manquante.
    Route::get('/evaluations/{evaluation}/pdf',    [\App\Http\Controllers\Chef\ChefEvaluationController::class, 'exportPdf'])->name('evaluations.pdf');

    // ── Mes formations (Chef) ─────────────────────────────────────────────────
    Route::get('/formations',               \App\Http\Controllers\Chef\ChefFormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf', [\App\Http\Controllers\Chef\ChefFormationController::class, 'pdf'])->name('formations.pdf');
});
