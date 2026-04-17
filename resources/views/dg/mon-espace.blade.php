@extends('layouts.dg')

@section('title', 'Mon espace DG | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
        @if (session('status'))
            <div id="status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 3000);</script>
        @endif

        <!-- Header -->
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Mon espace DG</h1>
                    <p class="mt-1 flex items-center gap-1.5 text-sm text-slate-400">
                        <i class="fas fa-user-circle text-xs"></i>
                        Mes fiches et évaluations reçues du PCA
                    </p>
                </div>
            </div>
        </div>

        <!-- Cartes Objectifs & Evaluations -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <button id="tabObjectifs" type="button" class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-6 text-white shadow-sm flex flex-col items-center justify-center focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 mb-2">
                    <i class="fas fa-bullseye text-2xl"></i>
                </span>
                <span class="text-3xl font-black">{{ $fiches->count() }}</span>
                <span class="mt-2 text-lg font-bold">Objectifs</span>
            </button>
            <button id="tabEvaluations" type="button" class="rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 p-6 text-white shadow-sm flex flex-col items-center justify-center focus:outline-none focus:ring-2 focus:ring-blue-400">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 mb-2">
                    <i class="fas fa-clipboard-check text-2xl"></i>
                </span>
                <span class="text-3xl font-black">{{ $evaluations->count() }}</span>
                <span class="mt-2 text-lg font-bold">Évaluations</span>
            </button>
        </div>

        <!-- Contenu dynamique Objectifs / Evaluations -->

        <div id="objectifsPanel" class="rounded-2xl bg-white p-6 shadow-sm mt-6">
            <h2 class="text-base font-semibold text-slate-800 mb-4">Fiches d'objectifs reçues</h2>
            <div class="mb-4 flex gap-2">
                <input type="text" id="searchObjectifInput" placeholder="Rechercher un objectif..." class="ent-input flex-1" autocomplete="off" />
            </div>
            @if($fiches->isEmpty())
                <p class="text-slate-500">Aucune fiche d'objectifs trouvée.</p>
            @else
                <ul id="objectifsList" class="divide-y divide-slate-200">
                    @foreach($fiches as $fiche)
                        @php
                            $fStatut = $fiche->statut ?? 'en_attente';
                            $fBadge  = match($fStatut) {
                                'acceptee'  => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                'refusee'   => 'border-rose-200 bg-rose-50 text-rose-700',
                                default     => 'border-amber-200 bg-amber-50 text-amber-700',
                            };
                            $fLabel  = match($fStatut) {
                                'acceptee'  => 'Acceptée',
                                'refusee'   => 'Refusée',
                                default     => 'En attente',
                            };
                        @endphp
                        <li class="py-3 flex items-center justify-between objectif-item gap-3">
                            <div class="flex min-w-0 flex-1 items-center gap-3">
                                <span class="objectif-label truncate font-semibold text-slate-800">{{ $fiche->titre }} ({{ $fiche->annee }})</span>
                                <span class="shrink-0 inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $fBadge }}">
                                    {{ $fLabel }}
                                </span>
                            </div>
                            <div class="flex shrink-0 gap-2">
                                <a href="{{ route('dg.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft">Voir</a>
                                <form action="{{ route('dg.objectifs.destroy', $fiche) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette fiche d\'objectifs ? Cette action est irréversible.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ent-btn ent-btn-danger">
                                        <i class="fas fa-trash-alt"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div id="evaluationsPanel" class="rounded-2xl bg-white p-6 shadow-sm mt-6 hidden">
            <h2 class="text-base font-semibold text-slate-800 mb-4">Évaluations reçues</h2>
            <div class="mb-4 flex gap-2">
                <input type="text" id="searchEvaluationInput" placeholder="Rechercher une évaluation..." class="ent-input flex-1" autocomplete="off" />
            </div>
            @if($evaluations->isEmpty())
                <p class="text-slate-500">Aucune évaluation trouvée.</p>
            @else
                <ul id="evaluationsList" class="divide-y divide-slate-200">
                    @foreach($evaluations as $evaluation)
                        <li class="py-2 flex items-center justify-between evaluation-item">
                            <span class="evaluation-label">Période : {{ $evaluation->date_debut->format('m/Y') }} - {{ $evaluation->date_fin->format('m/Y') }}</span>
                            <a href="{{ route('dg.evaluations.show', $evaluation) }}" class="ent-btn ent-btn-soft">Voir</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <script>
            const tabObjectifs = document.getElementById('tabObjectifs');
            const tabEvaluations = document.getElementById('tabEvaluations');
            const objectifsPanel = document.getElementById('objectifsPanel');
            const evaluationsPanel = document.getElementById('evaluationsPanel');
            tabObjectifs.addEventListener('click', () => {
                objectifsPanel.classList.remove('hidden');
                evaluationsPanel.classList.add('hidden');
            });
            tabEvaluations.addEventListener('click', () => {
                objectifsPanel.classList.add('hidden');
                evaluationsPanel.classList.remove('hidden');
            });

            // Recherche instantanée Objectifs
            const searchObjectifInput = document.getElementById('searchObjectifInput');
            if (searchObjectifInput) {
                searchObjectifInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    document.querySelectorAll('#objectifsList .objectif-item').forEach(function(item) {
                        const label = item.querySelector('.objectif-label').textContent.toLowerCase();
                        item.style.display = label.includes(filter) ? '' : 'none';
                    });
                });
            }

            // Recherche instantanée Evaluations
            const searchEvaluationInput = document.getElementById('searchEvaluationInput');
            if (searchEvaluationInput) {
                searchEvaluationInput.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    document.querySelectorAll('#evaluationsList .evaluation-item').forEach(function(item) {
                        const label = item.querySelector('.evaluation-label').textContent.toLowerCase();
                        item.style.display = label.includes(filter) ? '' : 'none';
                    });
                });
            }
        </script>
    </div>
</div>
@endsection
