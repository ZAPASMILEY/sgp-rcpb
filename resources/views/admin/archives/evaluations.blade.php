@extends($layout)

@section('title', 'Archives — Évaluations | '.config('app.name', 'SGP-RCPB'))

@section('content')
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">

    {{-- En-tête --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Administration › Archives</p>
            <h1 class="mt-1 text-2xl font-black text-slate-900">Évaluations archivées</h1>
            <p class="mt-1 text-sm text-slate-500">
                Ces évaluations ont été archivées (non supprimées). Vous pouvez les restaurer ou les supprimer définitivement.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.archives.objectifs') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50">
                <i class="fas fa-bullseye"></i> Archives Objectifs
            </a>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.settings.edit', ['tab' => 'danger']) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50">
                <i class="fas fa-arrow-left"></i> Paramètres
            </a>
            @endif
        </div>
    </div>

    @if(session('status'))
    <div class="mb-4 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
        <i class="fas fa-check-circle"></i> {{ session('status') }}
    </div>
    @endif

    {{-- Compteur --}}
    <div class="mb-4 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-3">
        <i class="fas fa-archive text-amber-500"></i>
        <span class="text-sm font-semibold text-amber-700">
            {{ $evaluations->total() }} évaluation(s) archivée(s) au total
        </span>
    </div>

    {{-- Recherche --}}
    <form method="GET" class="mb-4 flex gap-3">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Rechercher par nom, direction, service…"
               class="w-full max-w-xs rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100">
        <button type="submit"
                class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            <i class="fas fa-search mr-1"></i> Filtrer
        </button>
        @if($search)
        <a href="{{ route('admin.archives.evaluations') }}"
           class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            Effacer
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        @if($evaluations->isEmpty())
        <div class="py-16 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <i class="fas fa-inbox text-2xl text-slate-400"></i>
            </div>
            <p class="text-sm font-semibold text-slate-500">Aucune évaluation archivée</p>
            <p class="mt-1 text-xs text-slate-400">Les évaluations archivées depuis les paramètres apparaîtront ici.</p>
        </div>
        @else
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-100 bg-slate-50 text-xs font-bold uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="px-5 py-3">Évalué</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3">Note finale</th>
                    <th class="px-5 py-3">Période</th>
                    <th class="px-5 py-3">Archivée le</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($evaluations as $evaluation)
                @php
                    $evaluable = $evaluation->evaluable;
                    $label = match(true) {
                        $evaluable instanceof \App\Models\Agent     => trim(($evaluable->prenom ?? '').' '.($evaluable->nom ?? '')),
                        $evaluable instanceof \App\Models\Direction => $evaluation->evaluable_role === 'manager'
                                                                        ? ($evaluable->directeur_nom ?: 'Directeur')
                                                                        : $evaluable->nom,
                        $evaluable instanceof \App\Models\Service   => $evaluation->evaluable_role === 'manager'
                                                                        ? trim(($evaluable->chef_prenom ?? '').' '.($evaluable->chef_nom ?? '')) ?: 'Chef'
                                                                        : $evaluable->nom,
                        default => '—',
                    };
                    $typeLabel = match(true) {
                        $evaluable instanceof \App\Models\Agent     => 'Agent',
                        $evaluable instanceof \App\Models\Direction => $evaluation->evaluable_role === 'manager' ? 'Directeur' : 'Direction',
                        $evaluable instanceof \App\Models\Service   => $evaluation->evaluable_role === 'manager' ? 'Chef de service' : 'Service',
                        default => '—',
                    };
                    $statutColors = [
                        'brouillon'   => 'bg-slate-100 text-slate-600',
                        'soumis'      => 'bg-blue-100 text-blue-700',
                        'valide'      => 'bg-emerald-100 text-emerald-700',
                        'refuse'      => 'bg-rose-100 text-rose-700',
                        'reclamation' => 'bg-amber-100 text-amber-700',
                        'a_reviser'   => 'bg-orange-100 text-orange-700',
                    ];
                    $statutLabels = [
                        'brouillon'   => 'Brouillon',
                        'soumis'      => 'Soumis',
                        'valide'      => 'Validé',
                        'refuse'      => 'Refusé',
                        'reclamation' => 'Réclamation',
                        'a_reviser'   => 'À réviser',
                    ];
                @endphp
                <tr class="bg-amber-50/30 hover:bg-amber-50/60 transition-colors">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $label }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $typeLabel }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                     {{ $statutColors[$evaluation->statut] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $statutLabels[$evaluation->statut] ?? $evaluation->statut }}
                        </span>
                    </td>
                    <td class="px-5 py-3 font-bold text-slate-700">
                        {{ number_format((float)$evaluation->note_finale, 1) }} / 100
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs">
                        {{ \Carbon\Carbon::parse($evaluation->date_debut)->format('d/m/Y') }}
                        → {{ \Carbon\Carbon::parse($evaluation->date_fin)->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-3 text-slate-400 text-xs">
                        {{ \Carbon\Carbon::parse($evaluation->deleted_at)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Voir --}}
                            <a href="{{ route('admin.archives.evaluations.show', $evaluation->id) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            {{-- Restaurer --}}
                            <form method="POST"
                                  action="{{ route('admin.archives.evaluations.restore', $evaluation->id) }}"
                                  onsubmit="return confirm('Restaurer cette évaluation ?')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                    <i class="fas fa-rotate-left"></i> Restaurer
                                </button>
                            </form>
                            {{-- Supprimer définitivement --}}
                            <form method="POST"
                                  action="{{ route('admin.archives.evaluations.force-delete', $evaluation->id) }}"
                                  onsubmit="return confirm('Supprimer définitivement cette évaluation ? Cette action est irréversible.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($evaluations->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $evaluations->links() }}
        </div>
        @endif
        @endif
    </div>

</main>
@endsection
