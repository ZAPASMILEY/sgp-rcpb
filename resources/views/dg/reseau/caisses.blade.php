@extends('layouts.dg')
@section('title', 'Caisses | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="mx-auto max-w-6xl flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DG / Réseau RCPB</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Caisses</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $caisses->total() }} caisse(s) dans le réseau.</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 shadow-sm">
                    <i class="fas fa-building-columns text-xl"></i>
                </div>
            </div>
        </header>

        {{-- Filtres --}}
        <form method="GET" class="admin-panel px-5 py-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nom, directeur, quartier…" class="ent-input w-full">
                </div>
                @if ($delegations->isNotEmpty())
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Délégation</label>
                    <select name="delegation" class="ent-input">
                        <option value="">Toutes</option>
                        @foreach ($delegations as $d)
                            <option value="{{ $d->id }}" {{ $delegId == $d->id ? 'selected' : '' }}>{{ $d->region }} – {{ $d->ville }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button type="submit" class="ent-btn ent-btn-primary"><i class="fas fa-filter mr-2"></i>Filtrer</button>
                @if ($search || $delegId)
                    <a href="{{ route('dg.caisses') }}" class="ent-btn ent-btn-soft">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- Liste --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Liste des caisses</h2>
            </div>
            @if ($caisses->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucune caisse enregistrée.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Nom / Quartier</th>
                                <th class="px-4 py-3">Délégation</th>
                                <th class="px-4 py-3">Directeur</th>
                                <th class="px-4 py-3">Ouverture</th>
                                <th class="px-4 py-3 text-center">Agences</th>
                                <th class="px-4 py-3 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($caisses as $caisse)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-slate-900">{{ $caisse->nom }}</p>
                                        <p class="text-xs text-slate-400">{{ $caisse->quartier ?? '—' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">
                                        {{ $caisse->delegationTechnique?->region ?? '—' }}
                                        <span class="block text-xs text-slate-400">{{ $caisse->delegationTechnique?->ville }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold">{{ $caisse->directeur_prenom }} {{ $caisse->directeur_nom }}</p>
                                        <p class="text-xs text-slate-400">{{ $caisse->directeur_email }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $caisse->annee_ouverture ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-black text-sky-700">{{ $caisse->agences_count }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('dg.caisses.show', $caisse) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                                            <i class="fas fa-eye mr-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($caisses->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $caisses->withQueryString()->links() }}</div>
                @endif
            @endif
        </section>

    </div>
</div>
@endsection
