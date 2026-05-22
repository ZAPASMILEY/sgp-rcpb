<?php

namespace App\Helpers;

/**
 * Helper centralisant les sections de navigation conditionnelles.
 *
 * Chaque section n'apparaît QUE si l'utilisateur connecté possède
 * la permission correspondante (via rôle ou attribution individuelle).
 *
 * Usage dans n'importe quel layout :
 *   $menuSections = array_merge($menuSections, \App\Helpers\PermissionMenu::extraSections());
 */
class PermissionMenu
{
    public static function extraSections(): array
    {
        $user     = auth()->user();
        $sections = [];

        // ── Section Gestion ───────────────────────────────────────────────────
        $gestionItems = [];

        if ($user?->can('agents.voir')) {
            $gestionItems[] = [
                'route' => 'gerer.personnel.index',
                'icon'  => 'fas fa-users',
                'label' => 'Personnel',
            ];
        }

        if ($user?->can('structures.voir')) {
            $gestionItems[] = [
                'route' => 'gerer.structures.index',
                'icon'  => 'fas fa-sitemap',
                'label' => 'Structures',
            ];
        }

        if ($user?->can('formations.assigner')) {
            $gestionItems[] = [
                'route' => 'gerer.formations.index',
                'icon'  => 'fas fa-graduation-cap',
                'label' => 'Formations',
            ];
        }

        if ($user?->can('admin.alertes')) {
            $gestionItems[] = [
                'route' => 'gerer.alertes.index',
                'icon'  => 'fas fa-bell',
                'label' => 'Alertes',
            ];
        }

        if (! empty($gestionItems)) {
            $sections[] = ['title' => 'Gestion', 'items' => $gestionItems];
        }

        // ── Section Suivi ─────────────────────────────────────────────────────
        $suiviItems = [];

        if ($user?->can('evaluations.voir-reseau')) {
            $suiviItems[] = [
                'route' => 'gerer.evaluations.index',
                'icon'  => 'fas fa-clipboard-list',
                'label' => 'Toutes les évaluations',
            ];
        }

        if ($user?->can('admin.activites')) {
            $suiviItems[] = [
                'route' => 'gerer.activites.index',
                'icon'  => 'fas fa-history',
                'label' => 'Journal d\'activité',
            ];
        }

        if (! empty($suiviItems)) {
            $sections[] = ['title' => 'Suivi', 'items' => $suiviItems];
        }

        // ── Section Rapports ──────────────────────────────────────────────────
        // Pour le RH les liens stats/tableaux sont déjà dans le layout RH,
        // donc on ne les ajoute ici QUE pour les rôles non-RH.
        $rapportItems = [];

        if ($user?->can('statistiques.voir') && $user?->role !== 'RH') {
            $rapportItems[] = [
                'route' => 'personnel.statistiques',
                'icon'  => 'fas fa-chart-bar',
                'label' => 'Statistiques',
            ];
        }

        if ($user?->can('tableaux.voir') && $user?->role !== 'RH') {
            $rapportItems[] = [
                'route' => 'personnel.tableaux.index',
                'icon'  => 'fas fa-file-excel',
                'label' => 'Tableaux Excel',
            ];
        }

        if (! empty($rapportItems)) {
            $sections[] = ['title' => 'Rapports', 'items' => $rapportItems];
        }

        return $sections;
    }
}
