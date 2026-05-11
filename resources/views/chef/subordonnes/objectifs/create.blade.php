{{--
    ──────────────────────────────────────────────────────────────────────────
    chef/subordonnes/objectifs/create.blade.php
    Formulaire d'assignation d'objectifs à un agent
    ──────────────────────────────────────────────────────────────────────────

    Variables reçues de ChefObjectifController::create() :
      $ctx           — ChefEntity
      $agents        — Collection des agents subordonnés
      $selectedAgent — Agent pré-sélectionné (ou null)
    ──────────────────────────────────────────────────────────────────────────
--}}
@extends('layouts.chef')

@section('title', 'Assigner des objectifs | ' . config('app.name', 'SGP-RCPB'))

@section('content')
<div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full flex-col gap-6">

        {{-- ── En-tête ─────────────────────────────────────────────────────── --}}
        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">
                        Espace Chef / {{ $ctx->getTypeLabel() }} {{ $ctx->getNom() }}
                    </p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">
                        Assigner des objectifs
                    </h1>
                    <p class="mt-2 text-sm text-slate-600">
                        Créez une fiche d'objectifs pour un agent de votre équipe.
                        L'agent devra l'accepter ou la refuser.
                    </p>
                </div>
                <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>
        </header>

        {{-- ── Formulaire ──────────────────────────────────────────────────── --}}
        <section class="admin-panel px-6 py-6 lg:px-8">

            {{-- Erreur globale --}}
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('chef.objectifs.store') }}" class="space-y-8">
                @csrf

                {{-- ── Section 1 : Sélection de l'agent et titre ──────────── --}}
                <section class="space-y-6">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">1. Agent et fiche</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Sélectionnez l'agent destinataire et donnez un titre à la fiche.
                        </p>
                    </div>

                    {{-- Sélection de l'agent --}}
                    @if ($selectedAgent)
                        {{-- Pré-sélectionné : affichage en lecture seule --}}
                        <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                            <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Agent destinataire</p>
                            <p class="mt-2 text-base font-black text-slate-900">
                                {{ trim($selectedAgent->prenom . ' ' . $selectedAgent->nom) }}
                            </p>
                            <p class="mt-1 text-sm text-slate-500">{{ $selectedAgent->fonction ?? 'Agent' }}</p>
                            <input type="hidden" name="agent_id" value="{{ $selectedAgent->id }}">
                        </div>
                    @else
                        <div class="space-y-2">
                            <label for="agent_id" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                Agent destinataire
                            </label>
                            <select id="agent_id" name="agent_id" class="ent-select" required>
                                <option value="">— Sélectionner un agent —</option>
                                @foreach ($agents as $ag)
                                    <option value="{{ $ag->id }}" @selected(old('agent_id') == $ag->id)>
                                        {{ trim($ag->prenom . ' ' . $ag->nom) }} — {{ $ag->fonction ?? 'Agent' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('agent_id')
                                <p class="text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div class="grid gap-5 md:grid-cols-2">
                        {{-- Titre de la fiche --}}
                        <div class="space-y-2">
                            <label for="titre" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                Titre de la fiche d'objectifs
                            </label>
                            <input id="titre" name="titre" type="text"
                                   value="{{ old('titre') }}"
                                   class="ent-input" placeholder="Ex : Objectifs Semestre 1 — 2026" required>
                            @error('titre')
                                <p class="text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Date d'échéance --}}
                        <div class="space-y-2">
                            <label for="date_echeance" class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">
                                Date d'échéance
                            </label>
                            <input id="date_echeance" name="date_echeance" type="date"
                                   value="{{ old('date_echeance') }}"
                                   class="ent-input" required>
                            @error('date_echeance')
                                <p class="text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- ── Section 2 : Objectifs ────────────────────────────────── --}}
                <section class="space-y-5 border-t border-slate-200 pt-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">2. Objectifs</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Ajoutez les objectifs à atteindre. Chaque ligne correspond à un objectif distinct.
                            </p>
                        </div>
                        <button id="add-objectif-row" type="button" class="ent-btn ent-btn-soft">
                            <i class="fas fa-plus mr-2"></i>Ajouter un objectif
                        </button>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-slate-200">
                        <table class="min-w-full text-sm text-slate-700">
                            <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                                <tr>
                                    <th class="px-4 py-3 text-left">#</th>
                                    <th class="px-4 py-3 text-left">Description de l'objectif</th>
                                    <th class="px-4 py-3 text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody id="objectifs-rows">
                                {{-- Les lignes sont injectées par JavaScript --}}
                            </tbody>
                        </table>
                    </div>

                    @error('objectifs')
                        <p class="text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </section>

                {{-- Boutons de soumission --}}
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft">Annuler</a>
                    <button type="submit" class="ent-btn ent-btn-primary">
                        <i class="fas fa-paper-plane mr-2"></i>Créer et envoyer à l'agent
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody     = document.getElementById('objectifs-rows');
    const addBtn    = document.getElementById('add-objectif-row');
    let   rowIndex  = 0;

    // Récupère les anciennes valeurs après erreur de validation
    const oldObjectifs = @json(old('objectifs', [['description' => '']]));

    // Échappe les caractères HTML dangereux
    function escapeHtml(v) {
        return String(v ?? '')
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    /**
     * Crée une ligne de tableau pour un objectif.
     * @param {object} row   — {description: string}
     * @param {number} idx   — index global pour les noms des inputs
     */
    function makeRow(row, idx) {
        const tr = document.createElement('tr');
        tr.className = 'border-t border-slate-200';
        tr.innerHTML = `
            <td class="px-4 py-3 text-slate-400 text-xs font-bold">${idx + 1}</td>
            <td class="px-4 py-3">
                <input type="text"
                       name="objectifs[${idx}][description]"
                       value="${escapeHtml(row.description ?? '')}"
                       class="ent-input"
                       placeholder="Décrivez l'objectif à atteindre..."
                       required>
            </td>
            <td class="px-4 py-3">
                <button type="button" class="ent-btn ent-btn-soft text-xs" data-remove-row>
                    Supprimer
                </button>
            </td>
        `;
        tr.querySelector('[data-remove-row]').addEventListener('click', () => {
            tr.remove();
            // Garantit au minimum une ligne vide
            if (tbody.children.length === 0) {
                addRow({ description: '' });
            }
            renumberRows();
        });
        return tr;
    }

    /** Ajoute une ligne et incrémente le compteur d'index. */
    function addRow(row) {
        tbody.appendChild(makeRow(row || {}, rowIndex));
        rowIndex++;
    }

    /** Met à jour les numéros de ligne (colonne #) après suppression. */
    function renumberRows() {
        Array.from(tbody.rows).forEach((tr, i) => {
            const numCell = tr.cells[0];
            if (numCell) numCell.textContent = String(i + 1);
        });
    }

    // Bouton "Ajouter un objectif"
    addBtn.addEventListener('click', () => addRow({}));

    // Initialisation : peuple avec les anciennes valeurs (ou une ligne vide)
    const initialRows = Array.isArray(oldObjectifs) && oldObjectifs.length
        ? oldObjectifs
        : [{ description: '' }];
    initialRows.forEach(r => addRow(r));
});
</script>
@endpush
