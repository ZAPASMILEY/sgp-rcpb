@extends('layouts.dg')

@section('title', 'Assigner une fiche d\'objectifs | '.config('app.name', 'SGP-RCPB'))

@php
    $resolvedSubordonneId = (int) old('subordonne_id', $selectedSubordonne['id'] ?? 0);
    $resolvedSubordonne = $subordonnes->firstWhere('id', $resolvedSubordonneId);
    $lockSubordonne = $subordonnes->count() === 1 || $selectedSubordonne !== null;
    $oldObjectifs = old('objectifs', ['']);

    if (! is_array($oldObjectifs) || $oldObjectifs === []) {
        $oldObjectifs = [''];
    }
@endphp

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Collaborateurs</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Assigner une fiche d'objectifs</h1>
                        <p class="mt-2 text-sm text-slate-600">Creez une fiche et rattachez-la directement au collaborateur concerne.</p>
                    </div>
                    <a href="{{ url()->previous() }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if ($subordonnes->isEmpty())
                    <div class="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-8 text-center">
                        <i class="fas fa-users-slash text-2xl text-slate-300"></i>
                        <p class="mt-2 text-sm font-black text-slate-700">Aucun collaborateur disponible</p>
                        <p class="mt-1 text-xs text-slate-500">Ajoutez d'abord un DGA, une assistante ou un conseiller pour continuer.</p>
                    </div>
                @else
                    <form method="POST" action="{{ route('dg.objectifs.store') }}" class="mt-6 grid gap-5">
                        @csrf

                        {{-- Cible --}}
                        @if ($lockSubordonne && $resolvedSubordonne)
                            <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                                <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Collaborateur cible</p>
                                <p class="mt-2 text-base font-black text-slate-900">{{ $resolvedSubordonne['nom'] }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $resolvedSubordonne['role_label'] ?? '' }} — La fiche sera assignee automatiquement a ce collaborateur.
                                </p>
                                <input type="hidden" name="subordonne_id" value="{{ $resolvedSubordonne['id'] }}">
                            </div>
                        @else
                            <div class="space-y-2">
                                <label for="subordonne_id" class="text-sm font-semibold text-slate-700">Collaborateur cible</label>
                                <select id="subordonne_id" name="subordonne_id" class="ent-select" required>
                                    <option value="">Selectionner un collaborateur</option>
                                    @foreach ($subordonnes as $sub)
                                        <option value="{{ $sub['id'] }}" @selected($resolvedSubordonneId === (int) $sub['id'])>
                                            {{ $sub['nom'] }}{{ filled($sub['role_label'] ?? null) ? ' ('.$sub['role_label'].')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Dates --}}
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="date" class="text-sm font-semibold text-slate-700">Date</label>
                                <input id="date" type="date" value="{{ now()->toDateString() }}" readonly class="ent-input bg-slate-100 text-slate-600">
                            </div>
                            <div class="space-y-2">
                                <label for="date_echeance" class="text-sm font-semibold text-slate-700">Date d'écheance</label>
                                <input id="date_echeance" name="date_echeance" type="date"
                                       value="{{ old('date_echeance', \Carbon\Carbon::now()->addYear()->toDateString()) }}"
                                       required class="ent-input">
                            </div>
                        </div>

                        {{-- Titre fiche --}}
                        <div class="space-y-2">
                            <label for="titre_fiche" class="text-sm font-semibold text-slate-700">Titre de la fiche d'objectifs</label>
                            <input type="text" id="titre_fiche" name="titre_fiche" value="{{ old('titre_fiche') }}"
                                   required class="ent-input"
                                   placeholder="Titre general de la fiche (ex : Contrat d'objectifs 2026)">
                        </div>

                        {{-- Objectifs --}}
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Objectifs a assigner</label>
                            <div id="objectifs-container" class="space-y-2">
                                @foreach ($oldObjectifs as $objectif)
                                    <div class="objectif-row flex items-center gap-2">
                                        <input type="text" name="objectifs[]" value="{{ $objectif }}"
                                               required class="ent-input flex-1"
                                               placeholder="Description de l'objectif">
                                        <button type="button"
                                                class="remove-objectif-btn flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"
                                                title="Supprimer cet objectif" style="display:none;">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="add-objectif-btn"
                                    class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600">
                                <i class="fas fa-plus text-[10px]"></i> Ajouter un objectif
                            </button>
                        </div>

                        <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm mt-4">
                            Assigner la fiche
                        </button>
                    </form>
                @endif
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('objectifs-container');
        var btn = document.getElementById('add-objectif-btn');
        if (!container || !btn) return;

        btn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'objectif-row flex items-center gap-2';
            row.innerHTML =
                '<input type="text" name="objectifs[]" required class="ent-input flex-1" placeholder="Description de l\'objectif">' +
                '<button type="button" class="remove-objectif-btn flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600" title="Supprimer cet objectif"><i class="fas fa-trash text-xs"></i></button>';
            container.appendChild(row);
            row.querySelector('.remove-objectif-btn').addEventListener('click', function () {
                row.remove();
                updateRemoveButtons();
            });
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            var rows = container.querySelectorAll('.objectif-row');
            rows.forEach(function (row) {
                var removeBtn = row.querySelector('.remove-objectif-btn');
                if (removeBtn) removeBtn.style.display = rows.length > 1 ? '' : 'none';
            });
        }

        updateRemoveButtons();
        container.querySelectorAll('.remove-objectif-btn').forEach(function (removeBtn) {
            removeBtn.addEventListener('click', function () {
                removeBtn.closest('.objectif-row').remove();
                updateRemoveButtons();
            });
        });
    });
    </script>
@endpush
