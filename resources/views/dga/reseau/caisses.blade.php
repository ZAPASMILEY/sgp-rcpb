@extends('layouts.dga')
@section('title', 'Caisses | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- En-tête de la page --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-6 right-16 h-20 w-20 rounded-full bg-white/5"></div>
            <p class="text-[11px] font-black uppercase tracking-[0.25em] text-emerald-200">Espace DGA · Réseau</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-white">Caisses Populaires</h1>
            <p class="mt-1 text-sm text-emerald-100/80">{{ $caisses->count() }} caisse(s) dans le réseau.</p>
            <div class="absolute right-6 top-1/2 -translate-y-1/2 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10">
                <i class="fas fa-landmark text-2xl text-white"></i>
            </div>
        </div>

        {{-- ── NOUVEAU : BANNIÈRE D'INFORMATION POUR LES NOTES MASQUÉES ── --}}
        @php 
            // On vérifie si au moins une caisse a des notes masquées pour afficher l'avertissement
            $unSeulNull = false;
            foreach($caisses as $c) {
                if (($caisseNotes[$c->id]['moyenne'] ?? null) === null) {
                    $unSeulNull = true;
                    break;
                }
            }
        @endphp

        @if($unSeulNull)
            <div class="flex items-start gap-4 rounded-xl border border-amber-100 bg-amber-50 p-4 text-amber-800 shadow-sm">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                    <i class="fas fa-clock-rotate-left text-sm"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold tracking-tight">Période d'évaluation en cours</h3>
                    <p class="mt-0.5 text-xs text-amber-700/90 leading-relaxed">
                        Les notes moyennes des caisses, agences et délégations sont temporairement masquées. Elles seront automatiquement calculées et publiées dès que la Direction Generale aura procédé à la <strong>clôture officielle du semestre</strong>.
                    </p>
                </div>
            </div>
        @endif
        {{-- ───────────────────────────────────────────────────────────── --}}

        {{-- Formulaire de filtre --}}
        <form method="GET" class="admin-panel px-5 py-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nom de la caisse…" class="ent-input w-full">
                </div>
                @if($delegations->isNotEmpty())
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Délégation</label>
                    <select name="delegation" class="ent-input">
                        <option value="">Toutes</option>
                        @foreach($delegations as $d)
                            <option value="{{ $d->id }}" {{ $delegId == $d->id ? 'selected' : '' }}>{{ $d->region }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button type="submit" class="ent-btn ent-btn-primary"><i class="fas fa-filter mr-2"></i>Filtrer</button>
                @if($search || $delegId)
                    <a href="{{ route('dga.reseau.caisses') }}" class="ent-btn ent-btn-soft">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- Liste des données --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Liste des caisses</h2>
            </div>
            @if($caisses->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucune caisse trouvée.</p>
                </div>
            @else
                <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3">Caisse</th>
                                <th class="px-4 py-3">Délégation</th>
                                <th class="px-4 py-3">Directeur</th>
                                <th class="px-4 py-3 text-center">Agences</th>
                                <th class="px-4 py-3 text-center">Note</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($caisses as $caisse)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ $caisse->nom }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $caisse->delegationTechnique?->region ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($caisse->directeur)
                                            <p class="font-semibold">{{ $caisse->directeur->prenom }} {{ $caisse->directeur->nom }}</p>
                                            <p class="text-xs text-slate-400">{{ $caisse->directeur->numero_telephone ?? '' }}</p>
                                        @else
                                            <p class="font-semibold text-slate-400">—</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ $caisse->agences_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php $cn = $caisseNotes[$caisse->id] ?? ['moyenne' => null, 'total' => 0]; @endphp
                                        @if($cn['moyenne'] !== null)
                                            @php $c = $cn['moyenne'] >= 8.5 ? 'bg-emerald-100 text-emerald-700' : ($cn['moyenne'] >= 7 ? 'bg-blue-100 text-blue-700' : ($cn['moyenne'] >= 5 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700')); @endphp
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $c }}">{{ number_format($cn['moyenne'], 2) }}</span>
                                        @else
                                            <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full" title="Masqué jusqu'à la clôture du semestre">En cours</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('dga.reseau.caisses.show', $caisse) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                                            <i class="fas fa-eye mr-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $caisses->count() }} résultat(s)</div>
            @endif
        </section>

    </div>
</div>
@endsection