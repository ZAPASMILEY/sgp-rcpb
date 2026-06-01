@extends('layouts.rh')

@section('title', 'Réclamations & Refus | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
<div class="w-full flex flex-col gap-6">

    {{-- En-tête --}}
    <header class="admin-panel px-6 py-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace RH / Réclamations</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-950">
                    <i class="fas fa-triangle-exclamation mr-2 text-amber-500"></i> Refus &amp; Réclamations
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $evaluations->count() }} réclamation(s) au total
                    @if($enAttente > 0)
                        — <span class="font-semibold text-amber-600">{{ $enAttente }} en attente de traitement</span>
                    @else
                        — <span class="font-semibold text-emerald-600">Tout traité</span>
                    @endif
                </p>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <a href="{{ route('rh.dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                    <i class="fas fa-arrow-left text-xs"></i> Tableau de bord
                </a>
            </div>
        </div>
    </header>

    {{-- Flash --}}
    @if(session('status'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
        <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
    </div>
    @endif

    @if($evaluations->isEmpty())
    {{-- État vide --}}
    <div class="admin-panel flex flex-col items-center justify-center gap-4 px-6 py-16 text-center">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50">
            <i class="fas fa-check-circle text-2xl text-emerald-500"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-slate-800">Aucune réclamation</h3>
            <p class="mt-1 text-sm text-slate-500">Aucune évaluation refusée ou réclamée pour le moment.</p>
        </div>
    </div>

    @else
    {{-- Table des refus --}}
    <div class="admin-panel overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-4">
            <h2 class="text-sm font-black uppercase tracking-[0.15em] text-slate-500">Évaluations refusées</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.15em] text-slate-400">Évalué</th>
                        <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.15em] text-slate-400">Évaluateur</th>
                        <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.15em] text-slate-400">Motif du refus</th>
                        <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.15em] text-slate-400">Réclamation</th>
                        <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.15em] text-slate-400">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-black uppercase tracking-[0.15em] text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($evaluations as $evaluation)
                    @php
                        $nom = $evaluation->identification?->nom_prenom
                            ?? (($evaluation->evaluable?->prenom ?? '') . ' ' . ($evaluation->evaluable?->nom ?? ''))
                            ?: ($evaluation->evaluable?->nom ?? '—');
                        $nom = trim($nom) ?: '—';

                        $statutReclam = $evaluation->statut_reclamation;
                        $badgeClass = match($statutReclam) {
                            'en_attente' => 'bg-amber-100 text-amber-700 border-amber-200',
                            'maintenu'   => 'bg-red-100 text-red-700 border-red-200',
                            'rouvert'    => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            default      => 'bg-slate-100 text-slate-500 border-slate-200',
                        };
                        $statutLabel = match($statutReclam) {
                            'en_attente' => 'En attente',
                            'maintenu'   => 'Maintenu',
                            'rouvert'    => 'Réglé',
                            default      => '—',
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-slate-800">{{ $nom }}</div>
                            @if($evaluation->identification?->grade)
                            <div class="text-xs text-slate-400">{{ $evaluation->identification->grade }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-600">
                            {{ $evaluation->evaluateur?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            @if($evaluation->motif_refus)
                            <p class="text-slate-700 line-clamp-3">{{ $evaluation->motif_refus }}</p>
                            @else
                            <span class="text-slate-400 italic">Aucun motif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 max-w-xs">
                            @if($evaluation->reclamation)
                            <p class="text-slate-700 line-clamp-3">{{ $evaluation->reclamation }}</p>
                            @else
                            <span class="text-slate-400 italic">Pas encore de réclamation</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                                @if($statutReclam === 'en_attente')
                                    <i class="fas fa-clock text-[10px]"></i>
                                @elseif($statutReclam === 'maintenu')
                                    <i class="fas fa-ban text-[10px]"></i>
                                @elseif($statutReclam === 'rouvert')
                                    <i class="fas fa-check-circle text-[10px]"></i>
                                @endif
                                {{ $statutLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                {{-- Voir l'évaluation --}}
                                <a href="{{ route('rh.evaluations.show', $evaluation) }}"
                                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:text-slate-900">
                                    <i class="fas fa-eye text-[10px]"></i> Voir
                                </a>

                                {{-- Répondre si en_attente ou sans statut --}}
                                @if(in_array($statutReclam, ['en_attente', null]))
                                <button type="button"
                                        onclick="openRepondreModal({{ $evaluation->id }}, '{{ addslashes($nom) }}')"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 shadow-sm transition hover:bg-amber-100">
                                    <i class="fas fa-reply text-[10px]"></i> Répondre
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
</div>

{{-- Modal Répondre --}}
<div id="repondreModal" class="fixed inset-0 z-50 hidden items-center justify-center">
    <div class="absolute inset-0 bg-black/50" onclick="closeRepondreModal()"></div>
    <div class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl mx-4">
        <h3 class="text-lg font-black text-slate-900">Répondre à la réclamation</h3>
        <p id="repondreNom" class="mt-1 text-sm text-slate-500"></p>

        <form id="repondreForm" method="POST" class="mt-5 flex flex-col gap-4">
            @csrf
            <div class="flex flex-col gap-3">
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-amber-300 hover:bg-amber-50">
                    <input type="radio" name="reponse" value="maintenu" class="mt-0.5 accent-amber-500" required>
                    <div>
                        <p class="font-semibold text-slate-800">Maintenir le refus</p>
                        <p class="text-xs text-slate-500">Le refus est confirmé, l'évaluation reste refusée.</p>
                    </div>
                </label>
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 transition hover:border-blue-300 hover:bg-blue-50">
                    <input type="radio" name="reponse" value="rouvert" class="mt-0.5 accent-blue-500">
                    <div>
                        <p class="font-semibold text-slate-800">Rouvrir l'évaluation</p>
                        <p class="text-xs text-slate-500">L'évaluation passe en statut <strong>« À réviser »</strong> — l'évaluateur pourra la corriger et la resoumettre.</p>
                    </div>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeRepondreModal()"
                        class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300">
                    Annuler
                </button>
                <button type="submit"
                        class="rounded-2xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-700">
                    Confirmer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRepondreModal(id, nom) {
    const modal = document.getElementById('repondreModal');
    document.getElementById('repondreNom').textContent = 'Évalué : ' + nom;
    document.getElementById('repondreForm').action = '/rh/reclamations/' + id + '/repondre';
    // Reset radio
    document.querySelectorAll('#repondreForm input[type=radio]').forEach(r => r.checked = false);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeRepondreModal() {
    const modal = document.getElementById('repondreModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeRepondreModal(); });
</script>
@endsection
