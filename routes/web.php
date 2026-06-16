<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\PosteController;
use App\Http\Controllers\Admin\AgenceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AlerteController;
use App\Http\Controllers\Admin\AnneeController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CaisseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\DirectionController;
use App\Http\Controllers\Admin\EntiteController;
use App\Http\Controllers\Admin\GuichetController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\StatistiqueController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\Pca\PcaStatistiqueController;
use App\Http\Controllers\Pca\PcaSettingsController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('login.store');

// ── Changement de mot de passe obligatoire (first login) ─────────────────────
Route::middleware('auth')->group(function (): void {
    Route::get('/changer-mot-de-passe',  [ChangePasswordController::class, 'create'])->name('password.change');
    Route::post('/changer-mot-de-passe', [ChangePasswordController::class, 'store'])->name('password.change.update');
});

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
        // Services de la Direction Générale
        Route::post('/admin/direction-generale/services', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'storeService'])->name('admin.direction-generale.services.store');
        Route::delete('/admin/direction-generale/services/{service}', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'destroyService'])->name('admin.direction-generale.services.destroy');
        Route::patch('/admin/direction-generale/services/{service}/chef', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'updateChefService'])->name('admin.direction-generale.services.chef.update');
        Route::post('/admin/direction-generale/services/{service}/agents', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'storeAgent'])->name('admin.direction-generale.services.agents.store');
        Route::delete('/admin/direction-generale/services/{service}/agents/{agent}', [\App\Http\Controllers\Admin\DirectionGeneraleController::class, 'removeAgent'])->name('admin.direction-generale.services.agents.destroy');

        // ── Direction Générale Adjointe (DGA) ─────────────────────────────────
        Route::get('/admin/direction-dga', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'index'])->name('admin.direction-dga.index');
        Route::get('/admin/direction-dga/configurer-dga', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'configurerDga'])->name('admin.direction-dga.configurer');
        Route::post('/admin/direction-dga/configurer-dga', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'stockerDga'])->name('admin.direction-dga.stocker');
        Route::get('/admin/direction-dga/services/{service}/chef', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'editChefService'])->name('admin.direction-dga.services.chef.edit');
        Route::put('/admin/direction-dga/services/{service}/chef', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'updateChefService'])->name('admin.direction-dga.services.chef.update');
        Route::get('/admin/direction-dga/secretaire', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'editSecretaire'])->name('admin.direction-dga.secretaire.edit');
        Route::put('/admin/direction-dga/secretaire', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'updateSecretaire'])->name('admin.direction-dga.secretaire.update');
        Route::post('/admin/direction-dga/services', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'storeService'])->name('admin.direction-dga.services.store');
        Route::delete('/admin/direction-dga/services/{service}', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'destroyService'])->name('admin.direction-dga.services.destroy');
        Route::post('/admin/direction-dga/services/{service}/agents', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'storeAgent'])->name('admin.direction-dga.services.agents.store');
        Route::delete('/admin/direction-dga/services/{service}/agents/{agent}', [\App\Http\Controllers\Admin\DirectionDgaController::class, 'removeAgent'])->name('admin.direction-dga.services.agents.destroy');
    // Route pour enregistrer un secrétaire depuis la modale de la Faitière
    Route::post('/admin/secretaires', [EntiteController::class, 'storeSecretaire'])->name('admin.secretaires.store');
    Route::get('/admin/secretaires/{direction}', [EntiteController::class, 'showSecretaire'])->name('admin.secretaires.show');
    Route::get('/admin/secretaires/{direction}/modifier', [EntiteController::class, 'editSecretaire'])->name('admin.secretaires.edit');
    Route::delete('/admin/secretaires/{direction}', [EntiteController::class, 'destroySecretaire'])->name('admin.secretaires.destroy');
    Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
    Route::get('/admin/logout', fn () => redirect()->route('login'));
    Route::get('/admin', [DashboardController::class, 'admin'])->name('admin.dashboard');
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
    Route::get('/admin/delegations-techniques/{delegationTechnique}/caisses/creer', [DirectionController::class, 'createCaisse'])->name('admin.delegations-techniques.caisses.create');
    Route::post('/admin/delegations-techniques/caisses', [DirectionController::class, 'storeCaisse'])->name('admin.delegations-techniques.caisses.store');
    Route::post('/admin/delegations-techniques/agents', [DirectionController::class, 'storeDelegationAgent'])->name('admin.delegations-techniques.agents.store');
    Route::post('/admin/delegations-techniques/services', [DirectionController::class, 'storeDelegationService'])->name('admin.delegations-techniques.services.store');
    Route::get('/admin/delegations-techniques/{delegationTechnique}', [DirectionController::class, 'showDelegation'])->name('admin.delegations-techniques.show');
    Route::get('/admin/delegations-techniques/{delegationTechnique}/modifier', [DirectionController::class, 'editDelegation'])->name('admin.delegations-techniques.edit');
    Route::get('/admin/delegations-techniques/{delegationTechnique}/villes', [DirectionController::class, 'editVilles'])->name('admin.delegations-techniques.villes.edit');
    Route::put('/admin/delegations-techniques/{delegationTechnique}/villes', [DirectionController::class, 'updateVilles'])->name('admin.delegations-techniques.villes.update');
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
    Route::get('/admin/services/{service}/affecter-agent', [ServiceController::class, 'createAttachAgent'])->name('admin.services.attach-agent.create');
    Route::post('/admin/services/{service}/attach-agent', [ServiceController::class, 'attachAgent'])->name('admin.services.attach-agent');
    Route::delete('/admin/services/{service}/agents/{agent}', [ServiceController::class, 'detachAgent'])->name('admin.services.agents.destroy');

    // Gestion des comptes utilisateurs
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/creer', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/modifier', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::post('/admin/users/{user}/reset-to-default', [UserController::class, 'resetToDefault'])->name('admin.users.reset-to-default');
    Route::post('/admin/users/{user}/unblock', [UserController::class, 'unblock'])->name('admin.users.unblock');
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
    Route::get('/admin/agents/import', [AgentController::class, 'importForm'])->name('admin.agents.import');
    Route::post('/admin/agents/import', [AgentController::class, 'import'])->name('admin.agents.import.store');
    Route::get('/admin/agents/import/template', [AgentController::class, 'downloadTemplate'])->name('admin.agents.import.template');
    Route::get('/admin/agents/{agent}', [AgentController::class, 'show'])->name('admin.agents.show');
    Route::get('/admin/agents/{agent}/modifier', [AgentController::class, 'edit'])->name('admin.agents.edit');
    Route::put('/admin/agents/{agent}', [AgentController::class, 'update'])->name('admin.agents.update');
    Route::delete('/admin/agents/{agent}', [AgentController::class, 'destroy'])->name('admin.agents.destroy');
    Route::post('/admin/agents/{agent}/activer-compte', [AgentController::class, 'activateAccount'])->name('admin.agents.activate-account');
    Route::post('/admin/agents/{agent}/toggle-formation-valider', [AgentController::class, 'toggleFormationValider'])->name('admin.agents.toggle-formation-valider');
    Route::patch('/admin/agents/{agent}/poste', [AgentController::class, 'updatePoste'])->name('admin.agents.update-poste');

    Route::get('/admin/caisses', [CaisseController::class, 'index'])->name('admin.caisses.index');
    Route::get('/admin/caisses/{caisse}/affecter-service', [CaisseController::class, 'affecterService'])->name('admin.caisses.affecter-service');
    Route::get('/admin/caisses/{caisse}/affecter-agence', [CaisseController::class, 'affecterAgence'])->name('admin.caisses.affecter-agence');
     
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
    Route::post('/admin/parametres/roles/{roleSlug}/permissions', [SettingsController::class, 'syncRolePermissions'])->name('admin.settings.roles.permissions.sync');
    Route::post('/admin/parametres/roles', [SettingsController::class, 'storeRole'])->name('admin.settings.roles.store');
    Route::delete('/admin/parametres/roles/{customRole}', [SettingsController::class, 'destroyRole'])->name('admin.settings.roles.destroy');
    Route::post('/admin/parametres/fonctionnalites/{feature}/toggle', [SettingsController::class, 'toggleFeature'])->name('admin.settings.feature.toggle');
    Route::patch('/admin/parametres/fonctionnalites/{feature}/message', [SettingsController::class, 'updateFeatureMessage'])->name('admin.settings.feature.message');
    Route::post('/admin/parametres/comptes/rh', [SettingsController::class, 'storeRhAccount'])->name('admin.settings.rh.store');
    Route::post('/admin/parametres/purger/evaluations', [SettingsController::class, 'purgeEvaluations'])->name('admin.settings.purge.evaluations');
    Route::post('/admin/parametres/purger/objectifs', [SettingsController::class, 'purgeObjectifs'])->name('admin.settings.purge.objectifs');

});

// Archives (soft-deleted) — accessible à tout utilisateur authentifié ayant la permission admin.archives
// Volontairement HORS du groupe 'admin' pour que DG, RH, etc. puissent y accéder.
Route::middleware(['auth', 'can:admin.archives'])->group(function () {
    Route::get('/admin/archives/evaluations', [SettingsController::class, 'archivesEvaluations'])->name('admin.archives.evaluations');
    Route::get('/admin/archives/evaluations/{id}', [SettingsController::class, 'showArchiveEvaluation'])->name('admin.archives.evaluations.show');
    Route::post('/admin/archives/evaluations/{id}/restaurer', [SettingsController::class, 'restoreEvaluation'])->name('admin.archives.evaluations.restore');
    Route::delete('/admin/archives/evaluations/{id}/supprimer-definitif', [SettingsController::class, 'forceDeleteEvaluation'])->name('admin.archives.evaluations.force-delete');

    Route::get('/admin/archives/objectifs', [SettingsController::class, 'archivesObjectifs'])->name('admin.archives.objectifs');
    Route::get('/admin/archives/objectifs/{id}', [SettingsController::class, 'showArchiveFiche'])->name('admin.archives.objectifs.show');
    Route::post('/admin/archives/objectifs/{id}/restaurer', [SettingsController::class, 'restoreFiche'])->name('admin.archives.objectifs.restore');
    Route::delete('/admin/archives/objectifs/{id}/supprimer-definitif', [SettingsController::class, 'forceDeleteFiche'])->name('admin.archives.objectifs.force-delete');
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
    Route::get('/', [DashboardController::class, 'pca'])->name('dashboard');
    Route::get('/statistiques', PcaStatistiqueController::class)->name('statistiques.index');
    Route::get('/comparaison', [\App\Http\Controllers\Pca\PcaAnalytiqueController::class, 'comparaison'])->name('comparaison.index');

    Route::get('/objectifs', [\App\Http\Controllers\FicheObjectifController::class, 'index'])->name('objectifs.index');
    Route::get('/objectifs/creer', [\App\Http\Controllers\FicheObjectifController::class, 'create'])->name('objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/objectifs', [\App\Http\Controllers\FicheObjectifController::class, 'store'])->name('objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/objectifs/{fiche}/modifier', [\App\Http\Controllers\FicheObjectifController::class, 'edit'])->name('objectifs.edit');
    Route::put('/objectifs/{fiche}', [\App\Http\Controllers\FicheObjectifController::class, 'update'])->name('objectifs.update');
    Route::patch('/objectifs/{fiche}/soumettre', [\App\Http\Controllers\FicheObjectifController::class, 'soumettre'])->name('objectifs.soumettre');
    Route::get('/objectifs/{fiche}', [\App\Http\Controllers\FicheObjectifController::class, 'show'])->name('objectifs.show');
    Route::get('/objectifs/{objectif}/contrat', [\App\Http\Controllers\FicheObjectifController::class, 'contrat'])->name('objectifs.contrat');
    Route::get('/objectifs/{objectif}/contrat/telecharger', [\App\Http\Controllers\FicheObjectifController::class, 'contratDownload'])->name('objectifs.contrat.download');
    Route::post('/objectifs/{objectif}/progression', [\App\Http\Controllers\FicheObjectifController::class, 'adjustProgress'])->name('objectifs.progress');
    Route::delete('/objectifs/{fiche}', [\App\Http\Controllers\FicheObjectifController::class, 'destroy'])->name('objectifs.destroy');

    Route::get('/evaluations', [\App\Http\Controllers\EvaluationController::class, 'index'])->name('evaluations.index');
    Route::get('/evaluations/creer', [\App\Http\Controllers\EvaluationController::class, 'create'])->name('evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/evaluations', [\App\Http\Controllers\EvaluationController::class, 'store'])->name('evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations/{evaluation}', [\App\Http\Controllers\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/evaluations/{evaluation}/modifier', [\App\Http\Controllers\EvaluationController::class, 'edit'])->name('evaluations.edit')->middleware(['feature:evaluations']);
    Route::put('/evaluations/{evaluation}', [\App\Http\Controllers\EvaluationController::class, 'update'])->name('evaluations.update')->middleware(['feature:evaluations']);
    Route::patch('/evaluations/{evaluation}/soumettre', [\App\Http\Controllers\EvaluationController::class, 'submit'])->name('evaluations.submit');
    Route::post('/evaluations/{evaluation}/valider', [\App\Http\Controllers\EvaluationController::class, 'approve'])->name('evaluations.approve');
    Route::get('/evaluations/{evaluation}/pdf', [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::delete('/evaluations/{evaluation}', [\App\Http\Controllers\EvaluationController::class, 'destroy'])->name('evaluations.destroy');

    Route::get('/parametres', [PcaSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/parametres/theme', [PcaSettingsController::class, 'updateTheme'])->name('settings.theme.update');
    Route::put('/parametres/mot-de-passe', [PcaSettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::delete('/parametres/compte', [PcaSettingsController::class, 'destroyAccount'])->name('settings.account.destroy');

    // Mes formations
    Route::get('/formations',                    FormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf',    [FormationController::class, 'pdf'])->name('formations.pdf');
});

Route::middleware(['auth', 'personnel'])->group(function (): void {
    Route::post('/personnel/logout', [AuthenticatedSessionController::class, 'destroy'])->name('personnel.logout');
    Route::get('/personnel/logout', fn () => redirect()->route('login'));
    Route::get('/personnel', [DashboardController::class, 'personnel'])->name('personnel.dashboard');
    Route::get('/personnel/mon-espace', \App\Http\Controllers\MonEspaceController::class)->name('personnel.mon-espace');

    // ── Mes formations (Personnel) ────────────────────────────────────────────
    Route::get('/personnel/formations',                    FormationController::class)->name('personnel.formations.index');
    Route::get('/personnel/formations/{formation}/pdf',    [FormationController::class, 'pdf'])->name('personnel.formations.pdf');

    // ── Fiches d'objectifs reçues par le personnel ────────────────────────────
    // Accessible aux agents, secrétaires, et tout rôle sous le middleware 'personnel'.
    Route::get('/personnel/fiches/{fiche}',              [\App\Http\Controllers\FicheObjectifController::class, 'show'])->name('personnel.fiches.show');
    Route::patch('/personnel/fiches/{fiche}/statut',     [\App\Http\Controllers\FicheObjectifController::class, 'statut'])->name('personnel.fiches.statut');
    Route::patch('/personnel/fiches/{fiche}/avancement', [\App\Http\Controllers\FicheObjectifController::class, 'avancement'])->name('personnel.fiches.avancement');
    Route::get('/personnel/fiches/{fiche}/pdf',          [\App\Http\Controllers\FicheObjectifController::class, 'exportPdf'])->name('personnel.fiches.pdf');
    Route::patch('/personnel/fiches/{fiche}/lignes/{ligne}/contester', [\App\Http\Controllers\FicheObjectifController::class, 'contesterLigne'])->name('personnel.fiches.lignes.contester');
    Route::patch('/personnel/fiches/{fiche}/lignes/{ligne}/avancement', [\App\Http\Controllers\FicheObjectifController::class, 'avancementLigne'])->name('personnel.fiches.lignes.avancement');

    // ── Évaluations reçues par le personnel ───────────────────────────────────
    Route::get('/personnel/evaluations/{evaluation}',              [\App\Http\Controllers\EvaluationController::class, 'show'])->name('personnel.evaluations.show');
    Route::patch('/personnel/evaluations/{evaluation}/statut',     [\App\Http\Controllers\EvaluationController::class, 'statut'])->name('personnel.evaluations.statut');
    Route::post('/personnel/evaluations/{evaluation}/reclamer',    [\App\Http\Controllers\EvaluationController::class, 'reclamer'])->name('personnel.evaluations.reclamer');
    Route::post('/personnel/evaluations/{evaluation}/commentaire', [\App\Http\Controllers\EvaluationController::class, 'commentaire'])->name('personnel.evaluations.commentaire');
    Route::get('/personnel/evaluations/{evaluation}/pdf',          [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('personnel.evaluations.pdf');

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

    // Formations — CRUD (formations.assigner)
    Route::middleware('can:formations.assigner')->group(function () {
        Route::get('/formations',                    [\App\Http\Controllers\Gerer\FormationGererController::class, 'index'])->name('formations.index');
        Route::get('/formations/creer',              [\App\Http\Controllers\Gerer\FormationGererController::class, 'create'])->name('formations.create');
        Route::post('/formations',                   [\App\Http\Controllers\Gerer\FormationGererController::class, 'store'])->name('formations.store');
        Route::get('/formations/{formation}/editer', [\App\Http\Controllers\Gerer\FormationGererController::class, 'edit'])->name('formations.edit');
        Route::put('/formations/{formation}',        [\App\Http\Controllers\Gerer\FormationGererController::class, 'update'])->name('formations.update');
        Route::delete('/formations/{formation}',     [\App\Http\Controllers\Gerer\FormationGererController::class, 'destroy'])->name('formations.destroy');
        Route::get('/formations/{formation}/pdf',    [\App\Http\Controllers\Gerer\FormationGererController::class, 'pdf'])->name('formations.pdf');
    });

    // Formations — Validation (formations.valider)
    Route::middleware('can:formations.valider')->group(function () {
        Route::get('/formations/validation',              [\App\Http\Controllers\Gerer\FormationGererController::class, 'validationIndex'])->name('formations.validation');
        Route::post('/formations/{formation}/valider',    [\App\Http\Controllers\Gerer\FormationGererController::class, 'valider'])->name('formations.valider');
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
    Route::get('/', [DashboardController::class, 'rh'])->name('dashboard');
    Route::get('/comparaison', [\App\Http\Controllers\Rh\RhAnalytiqueController::class, 'comparaison'])->name('comparaison.index');

    // Formations (CRUD complet, RH uniquement)
    Route::get('/formations',                      [\App\Http\Controllers\Rh\RhFormationController::class, 'index'])->name('formations.index');
    Route::get('/formations/creer',                [\App\Http\Controllers\Rh\RhFormationController::class, 'create'])->name('formations.create');
    Route::post('/formations',                     [\App\Http\Controllers\Rh\RhFormationController::class, 'store'])->name('formations.store');
    Route::get('/formations/{formation}/editer',   [\App\Http\Controllers\Rh\RhFormationController::class, 'edit'])->name('formations.edit');
    Route::put('/formations/{formation}',          [\App\Http\Controllers\Rh\RhFormationController::class, 'update'])->name('formations.update');
    Route::delete('/formations/{formation}',       [\App\Http\Controllers\Rh\RhFormationController::class, 'destroy'])->name('formations.destroy');
    Route::get('/formations/{formation}/pdf',      [\App\Http\Controllers\Rh\RhFormationController::class, 'pdf'])->name('formations.pdf');
    Route::post('/formations/{formation}/valider', [\App\Http\Controllers\Rh\RhFormationController::class, 'valider'])->name('formations.valider');

    // Évaluations (lecture seule + réclamations)
    Route::get('/evaluations/{evaluation}', [\App\Http\Controllers\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/reclamations',                                [\App\Http\Controllers\Rh\RhReclamationController::class, 'index'])->name('reclamations.index');
    Route::get('/reclamations/{evaluation}',                   [\App\Http\Controllers\Rh\RhReclamationController::class, 'show'])->name('reclamations.show');
    Route::post('/reclamations/{evaluation}/repondre',         [\App\Http\Controllers\Rh\RhReclamationController::class, 'repondre'])->name('reclamations.repondre');

    // Structures du réseau (vue agrégée)
    Route::get('/structures',     \App\Http\Controllers\Rh\RhStructureController::class)->name('structures');
    Route::get('/structures/pdf', [\App\Http\Controllers\Rh\RhStructureController::class, 'pdf'])->name('structures.pdf');

    // Statistiques — permission requise
    Route::middleware('can:statistiques.voir')->group(function () {
        Route::get('/statistiques', \App\Http\Controllers\Rh\RhStatistiqueController::class)->name('statistiques');
    });

    // Tableaux personnalisés — permission requise
    Route::middleware('can:tableaux.voir')->group(function () {
        Route::get('/tableaux',        [\App\Http\Controllers\Rh\RhTableauController::class, 'index'])->name('tableaux.index');
        Route::get('/tableaux/export', [\App\Http\Controllers\Rh\RhTableauController::class, 'export'])->name('tableaux.export');
    });
});

// Shared routes — all authenticated users
Route::middleware('auth')->group(function () {
    Route::get('/alertes/non-lues',        [AlerteController::class, 'nonLues'])->name('alertes.non-lues');
    Route::post('/alertes/lire-tout',      [AlerteController::class, 'lireTout'])->name('alertes.lire-tout');
    Route::post('/alertes/{alerte}/lire',  [AlerteController::class, 'lireUne'])->name('alertes.lire-une');
    Route::get('/formations/agent/{agent}', [\App\Http\Controllers\Rh\RhFormationController::class, 'pourAgent'])->name('formations.pour-agent');

    // ── Soumission d'une formation par l'agent lui-même (tous rôles) ─────────
    Route::get('/soumettre-formation',              [FormationController::class, 'create'])->name('formation.soumettre');
    Route::post('/soumettre-formation',             [FormationController::class, 'store'])->name('formation.store');
    Route::delete('/mes-formations/{formation}',    [FormationController::class, 'destroy'])->name('formation.destroy');

    // Page de notifications (tous les rôles)
    Route::get('/mes-notifications',                       [\App\Http\Controllers\NotificationsController::class, 'index'])->name('notifications.index');
    Route::post('/mes-notifications/{alerte}/marquer-lu',  [\App\Http\Controllers\NotificationsController::class, 'marquerLu'])->name('notifications.marquer-lu');
    Route::post('/mes-notifications/lire-tout',            [\App\Http\Controllers\NotificationsController::class, 'marquerToutLu'])->name('notifications.lire-tout');
    Route::delete('/mes-notifications',                    [\App\Http\Controllers\NotificationsController::class, 'supprimerTout'])->name('notifications.supprimer-tout');
});

// Routes DG
Route::middleware(['auth', 'dg'])->prefix('dg')->name('dg.')->group(function (): void {
    Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/', [DashboardController::class, 'dg'])->name('dashboard');
    Route::get('/comparaison', [\App\Http\Controllers\Dg\DgAnalytiqueController::class, 'comparaison'])->name('comparaison.index');
    // Objectifs du DG (reçus + assignés aux subordonnés) — spécifiques AVANT le wildcard {fiche}
    Route::get('/objectifs/creer',               [\App\Http\Controllers\FicheObjectifController::class, 'create'])->name('objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::post('/objectifs',                    [\App\Http\Controllers\FicheObjectifController::class, 'store'])->name('objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/objectifs/{fiche}/pdf',         [\App\Http\Controllers\FicheObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
    Route::get('/objectifs/{fiche}/modifier',    [\App\Http\Controllers\FicheObjectifController::class, 'edit'])->name('objectifs.edit')->middleware(['feature:objectifs']);
    Route::put('/objectifs/{fiche}',             [\App\Http\Controllers\FicheObjectifController::class, 'update'])->name('objectifs.update')->middleware(['feature:objectifs']);
    Route::patch('/objectifs/{fiche}/soumettre', [\App\Http\Controllers\FicheObjectifController::class, 'soumettre'])->name('objectifs.soumettre')->middleware(['feature:objectifs']);
    Route::get('/objectifs/{fiche}',             [\App\Http\Controllers\FicheObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',              [\App\Http\Controllers\FicheObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',          [\App\Http\Controllers\FicheObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/contester',  [\App\Http\Controllers\FicheObjectifController::class, 'contesterLigne'])->name('objectifs.lignes.contester');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/avancement', [\App\Http\Controllers\FicheObjectifController::class, 'avancementLigne'])->name('objectifs.lignes.avancement');
    Route::delete('/objectifs/{fiche}',                    [\App\Http\Controllers\FicheObjectifController::class, 'destroy'])->name('objectifs.destroy');

    // Évaluations reçues par le DG (de la PCA) — spécifiques AVANT le wildcard {evaluation}
    Route::get('/evaluations/{evaluation}/pdf',  [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::get('/evaluations/{evaluation}',      [\App\Http\Controllers\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',  [\App\Http\Controllers\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer', [\App\Http\Controllers\EvaluationController::class, 'reclamer'])->name('evaluations.reclamer');

    // Évaluations données par le DG à ses subordonnés — spécifiques AVANT le wildcard
    Route::get('/subordonne-evaluations',                          fn () => redirect()->route('dg.dashboard'))->name('sub-evaluations.index');
    Route::get('/subordonne-evaluations/creer',                    [\App\Http\Controllers\EvaluationController::class, 'create'])->name('sub-evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/subordonne-evaluations',                         [\App\Http\Controllers\EvaluationController::class, 'store'])->name('sub-evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonne-evaluations/{evaluation}/edit',        [\App\Http\Controllers\EvaluationController::class, 'edit'])->name('sub-evaluations.edit')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::put('/subordonne-evaluations/{evaluation}',              [\App\Http\Controllers\EvaluationController::class, 'update'])->name('sub-evaluations.update')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonne-evaluations/{evaluation}/pdf',         [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('sub-evaluations.pdf');
    Route::get('/subordonne-evaluations/{evaluation}',             [\App\Http\Controllers\EvaluationController::class, 'show'])->name('sub-evaluations.show');
    Route::patch('/subordonne-evaluations/{evaluation}/soumettre', [\App\Http\Controllers\EvaluationController::class, 'submit'])->name('sub-evaluations.submit');
    Route::delete('/subordonne-evaluations/{evaluation}',          [\App\Http\Controllers\EvaluationController::class, 'destroy'])->name('sub-evaluations.destroy');

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
    Route::get('/directions/objectifs',                               fn () => redirect()->route('dg.directions'))->name('directions.objectifs.index');
    Route::post('/directions/objectifs',                              [\App\Http\Controllers\Dg\DgDirectionController::class, 'storeObjectif'])->name('directions.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/directions/objectifs/{fiche}',                       [\App\Http\Controllers\Dg\DgDirectionController::class, 'showObjectif'])->name('directions.objectifs.show');
    Route::get('/directions/objectifs/{fiche}/modifier',              [\App\Http\Controllers\Dg\DgDirectionController::class, 'editObjectif'])->name('directions.objectifs.edit')->middleware(['feature:objectifs']);
    Route::patch('/directions/objectifs/{fiche}',                     [\App\Http\Controllers\Dg\DgDirectionController::class, 'updateObjectif'])->name('directions.objectifs.update')->middleware(['feature:objectifs']);
    Route::patch('/directions/objectifs/{fiche}/soumettre',           [\App\Http\Controllers\Dg\DgDirectionController::class, 'soumettreObjectif'])->name('directions.objectifs.soumettre')->middleware(['feature:objectifs']);
    Route::patch('/directions/objectifs/{fiche}/reouvrir',            [\App\Http\Controllers\Dg\DgDirectionController::class, 'reouvrirObjectif'])->name('directions.objectifs.reouvrir');

    Route::delete('/directions/objectifs/{fiche}',                    [\App\Http\Controllers\Dg\DgDirectionController::class, 'destroyObjectif'])->name('directions.objectifs.destroy');
    Route::get('/directions/{direction}/evaluations/creer',           [\App\Http\Controllers\EvaluationController::class, 'createForDirection'])->name('directions.evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/directions/evaluations',                             fn () => redirect()->route('dg.directions'))->name('directions.evaluations.index');
    Route::post('/directions/evaluations',                            [\App\Http\Controllers\EvaluationController::class, 'store'])->name('directions.evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/directions/evaluations/{evaluation}',                [\App\Http\Controllers\EvaluationController::class, 'show'])->name('directions.evaluations.show');
    Route::get('/directions/evaluations/{evaluation}/modifier',       [\App\Http\Controllers\EvaluationController::class, 'edit'])->name('directions.evaluations.edit')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::put('/directions/evaluations/{evaluation}',                [\App\Http\Controllers\EvaluationController::class, 'update'])->name('directions.evaluations.update')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/directions/evaluations/{evaluation}/pdf',            [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('directions.evaluations.pdf');
    Route::patch('/directions/evaluations/{evaluation}/soumettre',    [\App\Http\Controllers\EvaluationController::class, 'submit'])->name('directions.evaluations.submit');
    Route::delete('/directions/evaluations/{evaluation}',             [\App\Http\Controllers\EvaluationController::class, 'destroy'])->name('directions.evaluations.destroy');

    // Structures du réseau (vue agrégée)
    Route::get('/structures',     \App\Http\Controllers\Dg\DgStructureController::class)->name('structures');
    Route::get('/structures/pdf', [\App\Http\Controllers\Dg\DgStructureController::class, 'pdf'])->name('structures.pdf');

    // Statistiques
    Route::get('/statistiques', \App\Http\Controllers\Dg\DgStatistiqueController::class)->name('statistiques');

    // Tableaux Excel personnalisés — permission requise
    Route::middleware('can:tableaux.voir')->group(function () {
        Route::get('/tableaux',        [\App\Http\Controllers\Dg\DgTableauController::class, 'index'])->name('tableaux.index');
        Route::get('/tableaux/export', [\App\Http\Controllers\Dg\DgTableauController::class, 'export'])->name('tableaux.export');
    });

    // Mes formations
    Route::get('/formations',                    FormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf',    [FormationController::class, 'pdf'])->name('formations.pdf');
});

// DG - Enregistrer le commentaire de l'évalué
Route::middleware(['auth'])->group(function () {
    Route::post('/dg/evaluations/{evaluation}/commentaire', [\App\Http\Controllers\EvaluationController::class, 'commentaire'])->name('dg.evaluations.commentaire');
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
Route::middleware(['auth', 'dg'])->get('/dg/mon-espace', \App\Http\Controllers\MonEspaceController::class)->name('dg.mon-espace');

// Espace DGA (Directeur General Adjoint)
Route::middleware(['auth', 'dga_espace'])->prefix('espace-dga')->name('dga.')->group(function (): void {
    Route::post('/logout',                                    [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/', [DashboardController::class, 'dga'])->name('dashboard');
    Route::get('/mon-dossier',                                \App\Http\Controllers\MonEspaceController::class)->name('mon-espace');
    Route::get('/ma-direction',                               [\App\Http\Controllers\Dga\DgaDirectionController::class, 'index'])->name('direction');
    // Évaluations reçues
    Route::get('/evaluations/{evaluation}',                   [\App\Http\Controllers\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',          [\App\Http\Controllers\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer',         [\App\Http\Controllers\EvaluationController::class, 'reclamer'])->name('evaluations.reclamer');
    Route::get('/evaluations/{evaluation}/pdf',               [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::post('/evaluations/{evaluation}/commentaire',      [\App\Http\Controllers\EvaluationController::class, 'commentaire'])->name('evaluations.commentaire');

    // Fiches objectifs reçues
    Route::get('/objectifs/{fiche}',                                              [\App\Http\Controllers\FicheObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',                                    [\App\Http\Controllers\FicheObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',                                [\App\Http\Controllers\FicheObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::get('/objectifs/{fiche}/pdf',                                          [\App\Http\Controllers\FicheObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/avancement',                 [\App\Http\Controllers\FicheObjectifController::class, 'avancementLigne'])->name('objectifs.lignes.avancement');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/contester',                  [\App\Http\Controllers\FicheObjectifController::class, 'contesterLigne'])->name('objectifs.lignes.contester');

    // Mes subordonnés (Directeurs Techniques + secrétaire)
    Route::get('/subordonnes',                                      [\App\Http\Controllers\Dga\DgaSubordonnesController::class, 'index'])->name('subordonnes.index');
    Route::get('/subordonnes/{user}',                               [\App\Http\Controllers\Dga\DgaSubordonnesController::class, 'show'])->name('subordonnes.show');
    Route::get('/secretaire',                                       [\App\Http\Controllers\Dga\DgaSubordonnesController::class, 'secretaire'])->name('secretaire');

    // Chefs de Service (vue d'ensemble)
    Route::get('/chefs-service',                                    \App\Http\Controllers\Dga\ChefsServiceController::class)->name('chefs-service.index');

    // Fiches d'objectifs assignées par le DGA à ses subordonnés — spécifiques AVANT le wildcard
    Route::get('/sub-objectifs/creer',               [\App\Http\Controllers\FicheObjectifController::class, 'create'])->name('sub-objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/sub-objectifs',                     fn () => redirect()->route('dga.subordonnes.index'))->name('sub-objectifs.index');
    Route::post('/sub-objectifs',                    [\App\Http\Controllers\FicheObjectifController::class, 'store'])->name('sub-objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/sub-objectifs/{fiche}/modifier',    [\App\Http\Controllers\FicheObjectifController::class, 'edit'])->name('sub-objectifs.edit')->middleware(['feature:objectifs']);
    Route::put('/sub-objectifs/{fiche}',             [\App\Http\Controllers\FicheObjectifController::class, 'update'])->name('sub-objectifs.update')->middleware(['feature:objectifs']);
    Route::patch('/sub-objectifs/{fiche}/soumettre', [\App\Http\Controllers\FicheObjectifController::class, 'soumettre'])->name('sub-objectifs.soumettre')->middleware(['feature:objectifs']);
    Route::get('/sub-objectifs/{fiche}',             [\App\Http\Controllers\FicheObjectifController::class, 'show'])->name('sub-objectifs.show');
    Route::get('/sub-objectifs/{fiche}/pdf',         [\App\Http\Controllers\FicheObjectifController::class, 'exportPdf'])->name('sub-objectifs.pdf');
    Route::delete('/sub-objectifs/{fiche}',          [\App\Http\Controllers\FicheObjectifController::class, 'destroy'])->name('sub-objectifs.destroy');

    // Évaluations des subordonnés (DGA → DTs / secrétaire)
    Route::get('/subordonne-evaluations',                           fn () => redirect()->route('dga.subordonnes.index'))->name('sub-evaluations.index');
    Route::get('/subordonne-evaluations/creer',                     [\App\Http\Controllers\EvaluationController::class, 'create'])->name('sub-evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::post('/subordonne-evaluations',                          [\App\Http\Controllers\EvaluationController::class, 'store'])->name('sub-evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonne-evaluations/{evaluation}',              [\App\Http\Controllers\EvaluationController::class, 'show'])->name('sub-evaluations.show');
    Route::get('/subordonne-evaluations/{evaluation}/pdf',          [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('sub-evaluations.pdf');
    Route::get('/subordonne-evaluations/{evaluation}/modifier',     [\App\Http\Controllers\EvaluationController::class, 'edit'])->name('sub-evaluations.edit')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::put('/subordonne-evaluations/{evaluation}',              [\App\Http\Controllers\EvaluationController::class, 'update'])->name('sub-evaluations.update')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::patch('/subordonne-evaluations/{evaluation}/soumettre',  [\App\Http\Controllers\EvaluationController::class, 'submit'])->name('sub-evaluations.submit');
    Route::delete('/subordonne-evaluations/{evaluation}',           [\App\Http\Controllers\EvaluationController::class, 'destroy'])->name('sub-evaluations.destroy');

    // Notes du réseau (subordonnés directs + tous les agents des délégations)
    Route::get('/notes-reseau',              [\App\Http\Controllers\Dga\DgaNotesReseauController::class, 'index'])->name('notes-reseau.index');
    Route::get('/notes-reseau/{evaluation}', [\App\Http\Controllers\EvaluationController::class, 'show'])->name('notes-reseau.show');

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
    Route::get('/formations',                    FormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf',    [FormationController::class, 'pdf'])->name('formations.pdf');
});

// Espace Subordonnés (Assistante_Dg, Conseillers_Dg) — mêmes contrôleurs que DGA
Route::middleware(['auth', 'subordonne'])->prefix('mon-espace')->name('subordonne.')->group(function (): void {
    Route::post('/logout',  [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));
    Route::get('/',         \App\Http\Controllers\MonEspaceController::class)->name('mon-espace');
    Route::get('/evaluations/{evaluation}',              [\App\Http\Controllers\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',     [\App\Http\Controllers\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer',    [\App\Http\Controllers\EvaluationController::class, 'reclamer'])->name('evaluations.reclamer');
    Route::get('/evaluations/{evaluation}/pdf',          [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::post('/evaluations/{evaluation}/commentaire', [\App\Http\Controllers\EvaluationController::class, 'commentaire'])->name('evaluations.commentaire');
    Route::get('/objectifs/{fiche}',                                         [\App\Http\Controllers\FicheObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',                               [\App\Http\Controllers\FicheObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',                           [\App\Http\Controllers\FicheObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::get('/objectifs/{fiche}/pdf',                                     [\App\Http\Controllers\FicheObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/avancement',            [\App\Http\Controllers\FicheObjectifController::class, 'avancementLigne'])->name('objectifs.lignes.avancement');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/contester',             [\App\Http\Controllers\FicheObjectifController::class, 'contesterLigne'])->name('objectifs.lignes.contester');

    // Mes formations
    Route::get('/formations',                    FormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf',    [FormationController::class, 'pdf'])->name('formations.pdf');
});

// ── Espace Assistante DG ──────────────────────────────────────────────────────
Route::middleware(['auth', 'subordonne'])->prefix('assistante')->name('assistante.')->group(function (): void {
    Route::get('/secretaire',                                                        [\App\Http\Controllers\Assistante\AssistanteController::class, 'secretaire'])->name('secretaire');
    Route::get('/secretaire/evaluations/creer',                                      [\App\Http\Controllers\EvaluationController::class, 'create'])->name('secretaire.evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/secretaire/evaluations',                                            fn () => redirect()->route('assistante.secretaire'))->name('secretaire.evaluations.index');
    Route::post('/secretaire/evaluations',                                           [\App\Http\Controllers\EvaluationController::class, 'store'])->name('secretaire.evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/secretaire/evaluations/{evaluation}',                               [\App\Http\Controllers\EvaluationController::class, 'show'])->name('secretaire.evaluations.show');
    Route::get('/secretaire/evaluations/{evaluation}/modifier',                      [\App\Http\Controllers\EvaluationController::class, 'edit'])->name('secretaire.evaluations.edit')->middleware(['feature:evaluations']);
    Route::put('/secretaire/evaluations/{evaluation}',                               [\App\Http\Controllers\EvaluationController::class, 'update'])->name('secretaire.evaluations.update')->middleware(['feature:evaluations']);
    Route::get('/secretaire/evaluations/{evaluation}/pdf',                           [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('secretaire.evaluations.pdf');
    Route::patch('/secretaire/evaluations/{evaluation}/soumettre',                   [\App\Http\Controllers\EvaluationController::class, 'submit'])->name('secretaire.evaluations.submit');
    Route::delete('/secretaire/evaluations/{evaluation}',                            [\App\Http\Controllers\EvaluationController::class, 'destroy'])->name('secretaire.evaluations.destroy');
    Route::get('/secretaire/objectifs/creer',                                        [\App\Http\Controllers\Assistante\AssistanteController::class, 'createObjectif'])->name('secretaire.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/secretaire/objectifs',                                              fn () => redirect()->route('assistante.secretaire'))->name('secretaire.objectifs.index');
    Route::post('/secretaire/objectifs',                                             [\App\Http\Controllers\Assistante\AssistanteController::class, 'storeObjectif'])->name('secretaire.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/secretaire/objectifs/{fiche}/modifier',                             [\App\Http\Controllers\Assistante\AssistanteController::class, 'editObjectif'])->name('secretaire.objectifs.edit')->middleware(['feature:objectifs']);
    Route::put('/secretaire/objectifs/{fiche}',                                      [\App\Http\Controllers\Assistante\AssistanteController::class, 'updateObjectif'])->name('secretaire.objectifs.update')->middleware(['feature:objectifs']);
    Route::patch('/secretaire/objectifs/{fiche}/soumettre',                          [\App\Http\Controllers\Assistante\AssistanteController::class, 'soumettreObjectif'])->name('secretaire.objectifs.soumettre')->middleware(['feature:objectifs']);
    Route::get('/secretaire/objectifs/{fiche}',                                      [\App\Http\Controllers\Assistante\AssistanteController::class, 'showObjectif'])->name('secretaire.objectifs.show');
    Route::get('/secretaire/objectifs/{fiche}/pdf',                                  [\App\Http\Controllers\Assistante\AssistanteController::class, 'pdfObjectif'])->name('secretaire.objectifs.pdf');
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
    Route::get('/', [DashboardController::class, 'directeur'])->name('dashboard');
    Route::get('/mon-espace', \App\Http\Controllers\MonEspaceController::class)->name('mon-espace');

    // Évaluations reçues (direction = évalué) + créées (chef de service = évalué)
    Route::get('/evaluations/objectifs-entite',         [\App\Http\Controllers\EvaluationController::class, 'objectivesForEntity'])->name('evaluations.objectives-for-entity');
    Route::get('/evaluations/creer',                    [\App\Http\Controllers\EvaluationController::class, 'create'])->name('evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations',                            fn () => redirect()->route('directeur.mon-espace'))->name('evaluations.index');
    Route::post('/evaluations',                          [\App\Http\Controllers\EvaluationController::class, 'store'])->name('evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations/{evaluation}',              [\App\Http\Controllers\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::get('/evaluations/{evaluation}/pdf',          [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');
    Route::patch('/evaluations/{evaluation}/statut',     [\App\Http\Controllers\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer',    [\App\Http\Controllers\EvaluationController::class, 'reclamer'])->name('evaluations.reclamer');
    Route::post('/evaluations/{evaluation}/commentaire', [\App\Http\Controllers\EvaluationController::class, 'commentaire'])->name('evaluations.commentaire');
    Route::get('/evaluations/{evaluation}/modifier',     [\App\Http\Controllers\EvaluationController::class, 'edit'])->name('evaluations.edit')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::put('/evaluations/{evaluation}',              [\App\Http\Controllers\EvaluationController::class, 'update'])->name('evaluations.update')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::patch('/evaluations/{evaluation}/soumettre',  [\App\Http\Controllers\EvaluationController::class, 'submit'])->name('evaluations.submit');
    Route::delete('/evaluations/{evaluation}',           [\App\Http\Controllers\EvaluationController::class, 'destroy'])->name('evaluations.destroy');

    // Objectifs reçus
    Route::get('/objectifs/{fiche}',                                    [\App\Http\Controllers\FicheObjectifController::class, 'show'])->name('objectifs.show');
    Route::patch('/objectifs/{fiche}/statut',                         [\App\Http\Controllers\FicheObjectifController::class, 'statut'])->name('objectifs.statut');
    Route::patch('/objectifs/{fiche}/avancement',                     [\App\Http\Controllers\FicheObjectifController::class, 'avancement'])->name('objectifs.avancement');
    Route::get('/objectifs/{fiche}/pdf',                               [\App\Http\Controllers\FicheObjectifController::class, 'exportPdf'])->name('objectifs.pdf');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/avancement',      [\App\Http\Controllers\FicheObjectifController::class, 'avancementLigne'])->name('objectifs.lignes.avancement');
    Route::patch('/objectifs/{fiche}/lignes/{ligne}/contester',       [\App\Http\Controllers\FicheObjectifController::class, 'contesterLigne'])->name('objectifs.lignes.contester');

    // ── Subordonnés ───────────────────────────────────────────────────────────
    Route::get('/subordonnes',                    [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'index'])->name('subordonnes');
    Route::get('/subordonnes/chefs',              [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'indexChefs'])->name('subordonnes.chefs');
    Route::get('/subordonnes/agences',            [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'indexAgenceChefs'])->name('subordonnes.agences.chefs');
    Route::get('/subordonnes/directeurs',         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'indexDirecteurs'])->name('subordonnes.directeurs');
    Route::get('/subordonnes/services/{service}', [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showService'])->name('subordonnes.service');
    Route::get('/subordonnes/secretaire',        [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showSecretaire'])->name('subordonnes.secretaire');

    // Évaluations secrétaire
    Route::get('/subordonnes/secretaire/evaluations/creer',                         [\App\Http\Controllers\EvaluationController::class, 'create'])->name('subordonnes.secretaire.evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonnes/secretaire/evaluations',                               fn () => redirect()->route('directeur.subordonnes.secretaire'))->name('subordonnes.secretaire.evaluations.index');
    Route::post('/subordonnes/secretaire/evaluations',                              [\App\Http\Controllers\EvaluationController::class, 'store'])->name('subordonnes.secretaire.evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonnes/secretaire/evaluations/{evaluation}',                  [\App\Http\Controllers\EvaluationController::class, 'show'])->name('subordonnes.secretaire.evaluations.show');
    Route::get('/subordonnes/secretaire/evaluations/{evaluation}/modifier',         [\App\Http\Controllers\EvaluationController::class, 'edit'])->name('subordonnes.secretaire.evaluations.edit')->middleware(['feature:evaluations']);
    Route::put('/subordonnes/secretaire/evaluations/{evaluation}',                  [\App\Http\Controllers\EvaluationController::class, 'update'])->name('subordonnes.secretaire.evaluations.update')->middleware(['feature:evaluations']);
    Route::get('/subordonnes/secretaire/evaluations/{evaluation}/pdf',              [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('subordonnes.secretaire.evaluations.pdf');
    Route::patch('/subordonnes/secretaire/evaluations/{evaluation}/soumettre',      [\App\Http\Controllers\EvaluationController::class, 'submit'])->name('subordonnes.secretaire.evaluations.submit');
    Route::delete('/subordonnes/secretaire/evaluations/{evaluation}',               [\App\Http\Controllers\EvaluationController::class, 'destroy'])->name('subordonnes.secretaire.evaluations.destroy');

    // Objectifs services
    Route::get('/subordonnes/services/{service}/objectifs/creer',                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createServiceObjectif'])->name('subordonnes.service.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/services/objectifs',                                   fn () => redirect()->route('directeur.subordonnes'))->name('subordonnes.service.objectifs.index');
    Route::post('/subordonnes/services/objectifs',                                  [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeServiceObjectif'])->name('subordonnes.service.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/services/objectifs/{fiche}/modifier',                  [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'editServiceObjectif'])->name('subordonnes.service.objectifs.edit')->middleware(['feature:objectifs']);
    Route::put('/subordonnes/services/objectifs/{fiche}',                           [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'updateServiceObjectif'])->name('subordonnes.service.objectifs.update')->middleware(['feature:objectifs']);
    Route::get('/subordonnes/services/objectifs/{fiche}',                           [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showServiceObjectif'])->name('subordonnes.service.objectifs.show');
    Route::delete('/subordonnes/services/objectifs/{fiche}',                        [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroyServiceObjectif'])->name('subordonnes.service.objectifs.destroy');

    // Objectifs secrétaire
    Route::get('/subordonnes/secretaire/objectifs/creer',                           [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/secretaire/objectifs',                                 fn () => redirect()->route('directeur.subordonnes.secretaire'))->name('subordonnes.secretaire.objectifs.index');
    Route::post('/subordonnes/secretaire/objectifs',                                [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/secretaire/objectifs/{fiche}/modifier',               [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'editSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.edit')->middleware(['feature:objectifs']);
    Route::put('/subordonnes/secretaire/objectifs/{fiche}',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'updateSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.update')->middleware(['feature:objectifs']);
    Route::get('/subordonnes/secretaire/objectifs/{fiche}',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showSecretaireObjectif'])->name('subordonnes.secretaire.objectifs.show');
    Route::delete('/subordonnes/secretaire/objectifs/{fiche}',                      [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroySecretaireObjectif'])->name('subordonnes.secretaire.objectifs.destroy');

    // Agences (Directeur_Caisse uniquement)
    Route::get('/subordonnes/agences/{agence}',                                     [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showAgence'])->name('subordonnes.agence');
    Route::get('/subordonnes/agences/{agence}/objectifs/creer',                     [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createAgenceObjectif'])->name('subordonnes.agence.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/agences/objectifs',                                    fn () => redirect()->route('directeur.subordonnes'))->name('subordonnes.agence.objectifs.index');
    Route::post('/subordonnes/agences/objectifs',                                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeAgenceObjectif'])->name('subordonnes.agence.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/agences/objectifs/{fiche}/modifier',                  [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'editAgenceObjectif'])->name('subordonnes.agence.objectifs.edit')->middleware(['feature:objectifs']);
    Route::put('/subordonnes/agences/objectifs/{fiche}',                            [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'updateAgenceObjectif'])->name('subordonnes.agence.objectifs.update')->middleware(['feature:objectifs']);
    Route::get('/subordonnes/agences/objectifs/{fiche}',                            [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showAgenceObjectif'])->name('subordonnes.agence.objectifs.show');
    Route::delete('/subordonnes/agences/objectifs/{fiche}',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroyAgenceObjectif'])->name('subordonnes.agence.objectifs.destroy');

    // Caisses (Directeur_Technique uniquement)
    Route::get('/subordonnes/caisses/{caisse}',                                     [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showCaisse'])->name('subordonnes.caisse');
    Route::get('/subordonnes/caisses/{caisse}/objectifs/creer',                     [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'createCaisseObjectif'])->name('subordonnes.caisse.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/caisses/objectifs',                                    fn () => redirect()->route('directeur.subordonnes'))->name('subordonnes.caisse.objectifs.index');
    Route::post('/subordonnes/caisses/objectifs',                                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'storeCaisseObjectif'])->name('subordonnes.caisse.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/caisses/objectifs/{fiche}/modifier',                   [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'editCaisseObjectif'])->name('subordonnes.caisse.objectifs.edit')->middleware(['feature:objectifs']);
    Route::put('/subordonnes/caisses/objectifs/{fiche}',                            [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'updateCaisseObjectif'])->name('subordonnes.caisse.objectifs.update')->middleware(['feature:objectifs']);
    Route::get('/subordonnes/caisses/objectifs/{fiche}',                            [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'showCaisseObjectif'])->name('subordonnes.caisse.objectifs.show');
    Route::delete('/subordonnes/caisses/objectifs/{fiche}',                         [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'destroyCaisseObjectif'])->name('subordonnes.caisse.objectifs.destroy');
    Route::patch('/subordonnes/objectifs/{fiche}/soumettre',                        [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'soumettreObjectif'])->name('subordonnes.objectifs.soumettre')->middleware(['feature:objectifs']);
    Route::get('/subordonnes/objectifs/{fiche}/pdf',                                [\App\Http\Controllers\Directeur\DirecteurSubordonneController::class, 'exportObjectifPdf'])->name('subordonnes.objectifs.pdf');

    // ── Personnel ─────────────────────────────────────────────────────────────
    Route::get('/personnel/export',              [\App\Http\Controllers\Directeur\DirecteurPersonnelController::class, 'export'])->name('personnel.export');
    Route::get('/personnel',                     [\App\Http\Controllers\Directeur\DirecteurPersonnelController::class, 'index'])->name('personnel');

    // ── Mes formations (Directeur) ────────────────────────────────────────────
    Route::get('/formations',                      FormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf',      [FormationController::class, 'pdf'])->name('formations.pdf');
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
    Route::get('/', [DashboardController::class, 'chef'])->name('dashboard');
    Route::get('/mon-espace', \App\Http\Controllers\MonEspaceController::class)->name('mon-espace');
    Route::get('/mon-equipe',     \App\Http\Controllers\Chef\ChefEquipeController::class)->name('equipe');
    Route::get('/agents/{agent}', [\App\Http\Controllers\Chef\ChefEquipeController::class, 'showAgent'])->name('agent.show');
    Route::get('/mes-guichets',   \App\Http\Controllers\Chef\ChefGuichetsController::class)->name('guichets');

    // ── Évaluations créées par le chef pour ses agents ────────────────────────
    Route::get('/evaluations/objectifs-agent',             [\App\Http\Controllers\EvaluationController::class, 'objectivesForAgent'])->name('evaluations.objectives-for-agent');
    Route::get('/evaluations/creer',                       [\App\Http\Controllers\EvaluationController::class, 'create'])->name('evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations',                              fn () => redirect()->route('chef.mon-espace'))->name('evaluations.index');
    Route::post('/evaluations',                             [\App\Http\Controllers\EvaluationController::class, 'store'])->name('evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations/{evaluation}/modifier',        [\App\Http\Controllers\EvaluationController::class, 'edit'])->name('evaluations.edit')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::put('/evaluations/{evaluation}',                 [\App\Http\Controllers\EvaluationController::class, 'update'])->name('evaluations.update')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/evaluations/{evaluation}',                 [\App\Http\Controllers\EvaluationController::class, 'show'])->name('evaluations.show');
    Route::patch('/evaluations/{evaluation}/statut',        [\App\Http\Controllers\EvaluationController::class, 'statut'])->name('evaluations.statut');
    Route::post('/evaluations/{evaluation}/reclamer',       [\App\Http\Controllers\EvaluationController::class, 'reclamer'])->name('evaluations.reclamer');
    Route::post('/evaluations/{evaluation}/commentaire',    [\App\Http\Controllers\EvaluationController::class, 'commentaire'])->name('evaluations.commentaire');
    Route::patch('/evaluations/{evaluation}/soumettre',     [\App\Http\Controllers\EvaluationController::class, 'submit'])->name('evaluations.submit');
    Route::delete('/evaluations/{evaluation}',              [\App\Http\Controllers\EvaluationController::class, 'destroy'])->name('evaluations.destroy');

    // ── Objectifs Chef d'Agence → Chefs de Guichet ───────────────────────────
    Route::get('/subordonnes/guichets/{guichet}/objectifs/creer', [\App\Http\Controllers\FicheObjectifController::class, 'createGuichetObjectif'])->name('subordonnes.guichet.objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/subordonnes/guichets/objectifs',                fn () => redirect()->route('chef.guichets'))->name('subordonnes.guichet.objectifs.index');
    Route::post('/subordonnes/guichets/objectifs',               [\App\Http\Controllers\FicheObjectifController::class, 'storeGuichetObjectif'])->name('subordonnes.guichet.objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);

    // ── Évaluations Chef d'Agence → Chefs de Guichet ────────────────────────
    Route::get('/subordonnes/guichets/{guichet}/evaluations/creer', [\App\Http\Controllers\EvaluationController::class, 'createForGuichet'])->name('subordonnes.guichet.evaluations.create')->middleware(['feature:evaluations', 'periode.ouverte']);
    Route::get('/subordonnes/guichets/evaluations',                 fn () => redirect()->route('chef.guichets'))->name('subordonnes.guichet.evaluations.index');
    Route::post('/subordonnes/guichets/evaluations',                [\App\Http\Controllers\EvaluationController::class, 'store'])->name('subordonnes.guichet.evaluations.store')->middleware(['feature:evaluations', 'periode.ouverte']);

    // ── Objectifs assignés par le chef à ses agents ───────────────────────────
    Route::get('/objectifs/assigner',                       [\App\Http\Controllers\FicheObjectifController::class, 'create'])->name('objectifs.create')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/objectifs',                                fn () => redirect()->route('chef.equipe'))->name('objectifs.index');
    Route::post('/objectifs',                               [\App\Http\Controllers\FicheObjectifController::class, 'store'])->name('objectifs.store')->middleware(['feature:objectifs', 'annee.ouverte']);
    Route::get('/objectifs/{fiche}/modifier',               [\App\Http\Controllers\FicheObjectifController::class, 'edit'])->name('objectifs.edit')->middleware(['feature:objectifs']);
    Route::put('/objectifs/{fiche}',                        [\App\Http\Controllers\FicheObjectifController::class, 'update'])->name('objectifs.update')->middleware(['feature:objectifs']);
    Route::patch('/objectifs/{fiche}/soumettre',            [\App\Http\Controllers\FicheObjectifController::class, 'soumettre'])->name('objectifs.soumettre')->middleware(['feature:objectifs']);
    Route::get('/objectifs/{fiche}',                        [\App\Http\Controllers\FicheObjectifController::class, 'show'])->name('objectifs.show');
    Route::delete('/objectifs/{fiche}',                     [\App\Http\Controllers\FicheObjectifController::class, 'destroy'])->name('objectifs.destroy');

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
    Route::get('/evaluations/{evaluation}/pdf',    [\App\Http\Controllers\EvaluationController::class, 'exportPdf'])->name('evaluations.pdf');

    // ── Mes formations (Chef) ─────────────────────────────────────────────────
    Route::get('/formations',               FormationController::class)->name('formations.index');
    Route::get('/formations/{formation}/pdf', [FormationController::class, 'pdf'])->name('formations.pdf');
});
