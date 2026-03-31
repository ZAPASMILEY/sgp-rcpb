@extends('layouts.app')

@section('title', 'Tableau de bord | SGP-RCPB')
@section('content')
<div class="bg-gradient-to-br from-blue-50 to-white min-h-screen py-8 px-2 sm:px-6 lg:px-12">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-1 flex items-center gap-2">
                Bienvenue, Administrateur <span class="text-2xl">👋</span>
            </h1>
            <div class="text-slate-500 text-lg mb-2">Système de gestion des performances (SGP-RCPB)</div>
        </div>
        <livewire:admin.dashboard-kpi />
        <div class="flex flex-col lg:flex-row gap-6 mt-8">
            <!-- Activités du Réseau (Graphique) -->
            <div class="bg-white rounded-2xl shadow p-6 flex-1 min-w-0">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-semibold text-slate-700">Activités du Réseau</div>
                    <div class="flex gap-2">
                        <select class="ent-input text-xs"><option>Tous Rôles</option></select>
                        <select class="ent-input text-xs"><option>Toutes Entités</option></select>
                        <select class="ent-input text-xs"><option>Tous Statuts</option></select>
                    </div>
                </div>
                <div class="h-32 flex items-center justify-center text-slate-400">
                    <span>Graphique à intégrer ici</span>
                </div>
            </div>
            <!-- Gestion des Comptes (Tableau) -->
            <div class="bg-white rounded-2xl shadow p-6 flex-1 min-w-0">
                <div class="flex items-center justify-between mb-4">
                    <div class="font-semibold text-slate-700">Gestion des Comptes</div>
                    <div class="flex gap-2">
                        <select class="ent-input text-xs"><option>Tous Rôles</option></select>
                        <select class="ent-input text-xs"><option>Toutes Entités</option></select>
                        <select class="ent-input text-xs"><option>Tous Statuts</option></select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-slate-500">
                                <th class="px-2 py-1 text-left">Nom</th>
                                <th class="px-2 py-1 text-left">Poste</th>
                                <th class="px-2 py-1 text-left">Entité</th>
                                <th class="px-2 py-1 text-left">Statut</th>
                                <th class="px-2 py-1 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-2 py-1">Alice Ouedraogo</td>
                                <td class="px-2 py-1">Directrice Générale</td>
                                <td class="px-2 py-1">Siège Ouagadougou</td>
                                <td class="px-2 py-1"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Actif</span></td>
                                <td class="px-2 py-1 flex gap-1">
                                    <button class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></button>
                                    <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">DGA</td>
                                <td class="px-2 py-1">Directeur Général Adjoint</td>
                                <td class="px-2 py-1">Siège Babo-ubssa</td>
                                <td class="px-2 py-1"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Actif</span></td>
                                <td class="px-2 py-1 flex gap-1">
                                    <button class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></button>
                                    <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Karim SAVO</td>
                                <td class="px-2 py-1">Directeur</td>
                                <td class="px-2 py-1">Caisse Dioulasso</td>
                                <td class="px-2 py-1"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Actif</span></td>
                                <td class="px-2 py-1 flex gap-1">
                                    <button class="text-blue-500 hover:text-blue-700"><i class="fas fa-edit"></i></button>
                                    <button class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="flex flex-col lg:flex-row gap-6 mt-8">
            <!-- Alertes & Sécurité -->
            <div class="bg-white rounded-2xl shadow p-6 flex-1 min-w-0">
                <div class="font-semibold text-slate-700 mb-4">Alertes & Sécurité</div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-red-50 rounded-xl p-4 flex flex-col items-center">
                        <div class="text-2xl font-bold text-red-600">9</div>
                        <div class="text-xs text-slate-500">Comptes inactifs &gt; 30j</div>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-4 flex flex-col items-center">
                        <div class="text-2xl font-bold text-yellow-600">17</div>
                        <div class="text-xs text-slate-500">Échecs de Connexion</div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4 flex flex-col items-center">
                        <div class="text-2xl font-bold text-blue-600">2</div>
                        <div class="text-xs text-slate-500">Activités suspectes</div>
                    </div>
                    <div class="bg-blue-50 rounded-xl p-4 flex flex-col items-center">
                        <div class="text-2xl font-bold text-blue-600">2</div>
                        <div class="text-xs text-slate-500">Activités suspectes</div>
                    </div>
                </div>
            </div>
            <!-- Historique -->
            <div class="bg-white rounded-2xl shadow p-6 flex-1 min-w-0">
                <div class="font-semibold text-slate-700 mb-4">Historique</div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-slate-500">
                                <th class="px-2 py-1 text-left">Action</th>
                                <th class="px-2 py-1 text-left">Utilisateur</th>
                                <th class="px-2 py-1 text-left">Date</th>
                                <th class="px-2 py-1 text-left">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-2 py-1">Initialisation système</td>
                                <td class="px-2 py-1">Administrateur</td>
                                <td class="px-2 py-1">28/03/2026 03:38</td>
                                <td class="px-2 py-1"><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs">Clic</span></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Réinitialiser mot de passe</td>
                                <td class="px-2 py-1">Karim Savio</td>
                                <td class="px-2 py-1">26/03/2026 03:38</td>
                                <td class="px-2 py-1"><span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full text-xs">Octoret</span></td>
                            </tr>
                            <tr>
                                <td class="px-2 py-1">Modification d'entité</td>
                                <td class="px-2 py-1">Alice Ouedraogo</td>
                                <td class="px-2 py-1">27/03/2026 16:48</td>
                                <td class="px-2 py-1"><span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs">Formulaire</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection