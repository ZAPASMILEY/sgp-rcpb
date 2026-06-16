@extends($layout)
@use('Illuminate\Support\Facades\Storage')

@section('title', 'Mes formations | SGP-RCPB')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="mx-auto max-w-6xl flex flex-col gap-6">

    {{-- En-tête --}}
    <header class="admin-panel px-6 py-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Formations</p>
                <h1 class="text-xl font-black tracking-tight text-slate-950">Mes formations</h1>
                <p class="mt-0.5 text-sm text-slate-500">Historique de vos formations et attestations</p>
            </div>
            <a href="{{ route('formation.soumettre') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fas fa-plus text-xs"></i> Soumettre une formation
            </a>
        </div>
    </header>

    {{-- Statistiques --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="admin-panel flex items-center gap-4 px-6 py-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                <i class="fas fa-graduation-cap text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</p>
                <p class="text-xs font-semibold text-slate-500">Formation{{ $stats['total'] > 1 ? 's' : '' }} au total</p>
            </div>
        </div>
        <div class="admin-panel flex items-center gap-4 px-6 py-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $stats['ce_mois'] }}</p>
                <p class="text-xs font-semibold text-slate-500">Ce mois-ci</p>
            </div>
        </div>
        <div class="admin-panel flex items-center gap-4 px-6 py-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ number_format($stats['heures']) }}</p>
                <p class="text-xs font-semibold text-slate-500">Heures de formation</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="admin-panel px-6 py-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            {{-- Recherche --}}
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400 mb-1">Recherche</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Titre de la formation…"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                </div>
            </div>

            {{-- Domaine --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400 mb-1">Domaine</label>
                <select name="domaine"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                    <option value="">Tous les domaines</option>
                    @foreach($domaines as $key => $label)
                        <option value="{{ $key }}" @selected(request('domaine') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Année --}}
            <div class="min-w-[120px]">
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400 mb-1">Année</label>
                <select name="annee"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                    <option value="">Toutes</option>
                    @foreach($annees as $a)
                        <option value="{{ $a }}" @selected((string)request('annee') === (string)$a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-emerald-700 transition">
                    <i class="fas fa-filter text-xs"></i> Filtrer
                </button>
                @if(request('search') || request('domaine') || request('annee'))
                    <a href="{{ url()->current() }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-500 hover:bg-slate-50 transition">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table des formations --}}
    <div class="admin-panel overflow-hidden">
        @if($formations->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 py-16 text-center text-slate-400">
                <i class="fas fa-graduation-cap text-4xl opacity-30"></i>
                <p class="text-sm font-semibold">Aucune formation trouvée</p>
                @if(request('search') || request('domaine') || request('annee'))
                    <p class="text-xs">Essayez d'élargir vos critères de recherche</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-400">Agent</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-400">Formation</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-400">Domaine</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-400">Période</th>
                            <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-[0.12em] text-slate-400">Durée</th>
                            <th class="px-5 py-3 text-center text-xs font-black uppercase tracking-[0.12em] text-slate-400">Statut</th>
                            <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-[0.12em] text-slate-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($formations as $formation)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                {{-- Agent --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-xs font-black text-emerald-700">
                                            {{ strtoupper(substr($formation->agent->prenom ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-800 text-sm">
                                                {{ trim(($formation->agent->prenom ?? '') . ' ' . ($formation->agent->nom ?? '')) }}
                                            </p>
                                            <p class="truncate text-xs text-slate-400">{{ $formation->agent->role_genree ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Titre --}}
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-slate-800 line-clamp-1">{{ $formation->theme }}</p>
                                </td>

                                {{-- Domaine --}}
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                                        {{ $formation->domaine_label }}
                                    </span>
                                </td>

                                {{-- Période --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <p class="text-sm text-slate-600">
                                        {{ $formation->date_debut->translatedFormat('d M Y') }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        → {{ $formation->date_fin->translatedFormat('d M Y') }}
                                    </p>
                                </td>

                                {{-- Durée --}}
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 text-sm font-black text-emerald-700">
                                        {{ $formation->duree_heures }}h
                                    </span>
                                </td>

                                {{-- Statut --}}
                                <td class="px-5 py-3.5 text-center">
                                    @php
                                        $statut = $formation->statut ?? 'validee';
                                        $statutCls = match($statut) {
                                            'en_attente' => 'bg-amber-100 text-amber-700',
                                            'validee'    => 'bg-emerald-100 text-emerald-700',
                                            'refusee'    => 'bg-rose-100 text-rose-700',
                                            default      => 'bg-slate-100 text-slate-500',
                                        };
                                        $statutLabel = match($statut) {
                                            'en_attente' => 'En attente',
                                            'validee'    => 'Validée',
                                            'refusee'    => 'Refusée',
                                            default      => ucfirst($statut),
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $statutCls }}">
                                        {{ $statutLabel }}
                                    </span>
                                    @if($statut === 'refusee' && $formation->motif_refus)
                                        <p class="mt-1 text-[10px] text-rose-500 italic line-clamp-2">{{ $formation->motif_refus }}</p>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="px-5 py-3.5 text-right">
                                    @if($statut === 'validee' && $formation->attestation_path)
                                        <a href="{{ Storage::url($formation->attestation_path) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-black text-rose-600 transition hover:bg-rose-100"
                                           title="Télécharger l'attestation">
                                            <i class="fas fa-file-pdf"></i>
                                            <span>Attestation</span>
                                        </a>
                                    @elseif($statut === 'en_attente')
                                        <form method="POST" action="{{ route('formation.destroy', $formation) }}"
                                              onsubmit="return confirm('Supprimer cette formation ? Elle sera retirée définitivement.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-black text-rose-600 transition hover:bg-rose-100">
                                                <i class="fas fa-trash text-[10px]"></i>
                                                <span>Retirer</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $formations->count() }} résultat(s)</div>
        @endif
    </div>

</div>
</div>
@endsection
