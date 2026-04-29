<?php

namespace App\Traits;

use App\Models\Entite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Helpers partagés pour résoudre l'entité faîtière et les utilisateurs
 * liés (DG, DGA, Assistante) via les colonnes FK de la table entites.
 */
trait ResolvesEntite
{
    /** Entité du DG connecté (via entites.dg_agent_id). */
    protected function getEntiteForDG(): ?Entite
    {
        $dg = Auth::user();
        return Entite::query()->where('dg_agent_id', $dg->agent_id)->first()
            ?? Entite::query()->latest()->first();
    }

    /** Entité du DGA connecté (via entites.dga_agent_id). */
    protected function getEntiteForDGA(): ?Entite
    {
        $dga = Auth::user();
        return Entite::query()->where('dga_agent_id', $dga->agent_id)->first()
            ?? Entite::query()->latest()->first();
    }

    /** Entité principale (fallback universel — dernier enregistrement). */
    protected function getEntite(): ?Entite
    {
        return Entite::query()->latest()->first();
    }

    /** Utilisateur DG pour une entité donnée. */
    protected function getDGUser(?Entite $entite = null): ?User
    {
        $entite ??= $this->getEntite();
        if (! $entite || ! $entite->dg_agent_id) {
            return null;
        }
        return User::query()->where('role', 'DG')->where('agent_id', $entite->dg_agent_id)->first();
    }

    /** Utilisateur DGA pour une entité donnée. */
    protected function getDGAUser(?Entite $entite = null): ?User
    {
        $entite ??= $this->getEntite();
        if (! $entite || ! $entite->dga_agent_id) {
            return null;
        }
        return User::query()->where('role', 'DGA')->where('agent_id', $entite->dga_agent_id)->first();
    }

    /** Utilisateur Assistante DG pour une entité donnée. */
    protected function getAssistanteUser(?Entite $entite = null): ?User
    {
        $entite ??= $this->getEntite();
        if (! $entite || ! $entite->assistante_agent_id) {
            return null;
        }
        return User::query()->where('role', 'Assistante_Dg')->where('agent_id', $entite->assistante_agent_id)->first();
    }

    /** Secrétaire du DGA (via entites.dga_secretaire_agent_id). */
    protected function getDgaSecretaireUser(?Entite $entite = null): ?User
    {
        $entite ??= $this->getEntite();
        if (! $entite || ! $entite->dga_secretaire_agent_id) {
            return null;
        }
        return User::query()->where('agent_id', $entite->dga_secretaire_agent_id)->first();
    }

    /**
     * Préfixe de route selon le rôle de l'utilisateur connecté.
     * DGA → 'dga'  |  Assistante_Dg / Conseillers_Dg → 'subordonne'
     */
    protected function espaceRoutePrefix(): string
    {
        return Auth::user()?->role === 'DGA' ? 'dga' : 'subordonne';
    }

    /**
     * Préfixe de vue selon le rôle de l'utilisateur connecté.
     * DGA → 'dga'  |  autres → 'subordonne'
     */
    protected function espaceViewPrefix(): string
    {
        return Auth::user()?->role === 'DGA' ? 'dga' : 'subordonne';
    }
}
