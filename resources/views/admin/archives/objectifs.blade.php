@extends('layouts.app')

@section('title', 'Archives — Fiches d\'objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">

    {{-- En-tête --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Administration › Archives</p>
            <h1 class="mt-1 text-2xl font-black text-slate-900">Fiches d'objectifs archivées</h1>
            <p class="mt-1 text-sm text-slate-500">
                Ces fiches ont été archivées (non supprimées). Vous pouvez les restaurer ou les supprimer définitivement.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.archives.evaluations') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50">
                <i class="fas fa-clipboard-check"></i> Archives Évaluations
            </a>
            <a href="{{ route('admin.settings.edit', ['tab' => 'danger']) }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 shadow-sm hover:bg-slate-50">
                <i class="fas fa-arrow-left"></i> Paramètres
            </a>
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
            {{ $fiches->total() }} fiche(s) d'objectifs archivée(s) au total
        </span>
    </div>

    {{-- Recherche --}}
    <form method="GET" class="mb-4 flex gap-3">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Rechercher par titre…"
               class="w-full max-w-xs rounded-xl border border-slate-200 px-4 py-2 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        <button type="submit"
                class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            <i class="fas fa-search mr-1"></i> Filtrer
        </button>
        @if($search)
        <a href="{{ route('admin.archives.objectifs') }}"
           class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            Effacer
        </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        @if($fiches->isEmpty())
        <div class="py-16 text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <i class="fas fa-inbox text-2xl text-slate-400"></i>
            </div>
            <p class="text-sm font-semibold text-slate-500">Aucune fiche d'objectifs archivée</p>
            <p class="mt-1 text-xs text-slate-400">Les fiches archivées depuis les paramètres apparaîtront ici.</p>
        </div>
        @else
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-100 bg-slate-50 text-xs font-bold uppercase tracking-widest text-slate-400">
                <tr>
                    <th class="px-5 py-3">Titre</th>
                    <th class="px-5 py-3">Assigné à</th>
                    <th class="px-5 py-3">Statut</th>
                    <th class="px-5 py-3">Avancement</th>
                    <th class="px-5 py-3">Année</th>
                    <th class="px-5 py-3">Archivée le</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($fiches as $fiche)
                @php
                    $assignable = $fiche->assignable;
                    $assignLabel = match(true) {
                        $assignable instanceof \App\Models\User    => $assignable->name,
                        $assignable instanceof \App\Models\Agent   => trim(($assignable->prenom ?? '').' '.($assignable->nom ?? '')),
                        $assignable instanceof \App\Models\Direction => $assignable->nom,
                        $assignable instanceof \App\Models\Service => $assignable->nom,
                        default => class_basename($fiche->assignable_type).' #'.$fiche->assignable_id,
                    };
                    $statutColors = [
                        'brouillon'  => 'bg-slate-100 text-slate-600',
                        'en_attente' => 'bg-blue-100 text-blue-700',
                        'acceptee'   => 'bg-emerald-100 text-emerald-700',
                        'refusee'    => 'bg-rose-100 text-rose-700',
                        'contesté'   => 'bg-amber-100 text-amber-700',
                    ];
                    $statutLabels = [
                        'brouillon'  => 'Brouillon',
                        'en_attente' => 'En attente',
                        'acceptee'   => 'Acceptée',
                        'refusee'    => 'Refusée',
                        'contesté'   => 'Contestée',
                    ];
                    $pct = (int) $fiche->avancement_percentage;
                    $pctColor = $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-400' : 'bg-rose-400');
                @endphp
                <tr class="bg-amber-50/30 hover:bg-amber-50/60 transition-colors">
                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $fiche->titre }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $assignLabel }}</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                     {{ $statutColors[$fiche->statut] ?? 'bg-slate-100 text-slate-600' }}">
                            {{ $statutLabels[$fiche->statut] ?? $fiche->statut }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 w-20 rounded-full bg-slate-100">
                                <div class="h-1.5 rounded-full {{ $pctColor }}" style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-600">{{ $pct }}%</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-slate-500 text-xs">
                        {{ $fiche->annee?->libelle ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-slate-400 text-xs">
                        {{ \Carbon\Carbon::parse($fiche->deleted_at)->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Voir --}}
                            <a href="{{ route('admin.archives.objectifs.show', $fiche->id) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                <i class="fas fa-eye"></i> Voir
                            </a>
                            {{-- Restaurer --}}
                            <form method="POST"
                                  action="{{ route('admin.archives.objectifs.restore', $fiche->id) }}"
                                  onsubmit="return confirm('Restaurer cette fiche d\'objectifs ?')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                    <i class="fas fa-rotate-left"></i> Restaurer
                                </button>
                            </form>
                            {{-- Supprimer définitivement --}}
                            <form method="POST"
                                  action="{{ route('admin.archives.objectifs.force-delete', $fiche->id) }}"
                                  onsubmit="return confirm('Supprimer définitivement cette fiche ? Cette action est irréversible.')">
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

        @if($fiches->hasPages())
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $fiches->links() }}
        </div>
        @endif
        @endif
    </div>

</main>
@endsection
