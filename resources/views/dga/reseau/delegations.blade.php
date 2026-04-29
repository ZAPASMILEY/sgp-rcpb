@extends('layouts.dga')
@section('title', 'Délégations | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DGA / Réseau</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Délégations Techniques</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $delegations->total() }} délégation(s) dans le réseau.</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700 shadow-sm">
                    <i class="fas fa-map-marker-alt text-xl"></i>
                </div>
            </div>
        </header>

        {{-- Recherche --}}
        <form method="GET" class="admin-panel px-5 py-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Région, ville…" class="ent-input w-full">
                </div>
                <button type="submit" class="ent-btn ent-btn-primary"><i class="fas fa-filter mr-2"></i>Filtrer</button>
                @if($search)
                    <a href="{{ route('dga.reseau.delegations') }}" class="ent-btn ent-btn-soft">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- Liste --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Liste des délégations</h2>
            </div>
            @if ($delegations->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucune délégation enregistrée.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Région / Ville</th>
                                <th class="px-4 py-3">Directeur</th>
                                <th class="px-4 py-3 text-center">Caisses</th>
                                <th class="px-4 py-3 text-center">Agences</th>
                                <th class="px-4 py-3 text-center">Note</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($delegations as $delegation)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3">
                                        <p class="font-bold text-slate-900">{{ $delegation->region }}</p>
                                        <p class="text-xs text-slate-400">{{ $delegation->ville }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($delegation->directeur)
                                            <p class="font-semibold">{{ $delegation->directeur->prenom }} {{ $delegation->directeur->nom }}</p>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ $delegation->caisses_count }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ $delegation->agences_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php $dn = $delegationNotes[$delegation->id] ?? ['moyenne' => null, 'total' => 0]; @endphp
                                        @if($dn['moyenne'] !== null)
                                            @php $c = $dn['moyenne'] >= 8.5 ? 'bg-emerald-100 text-emerald-700' : ($dn['moyenne'] >= 7 ? 'bg-blue-100 text-blue-700' : ($dn['moyenne'] >= 5 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700')); @endphp
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $c }}">{{ number_format($dn['moyenne'], 2) }}</span>
                                        @else
                                            <span class="text-xs font-bold text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('dga.reseau.delegations.show', $delegation) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                                            <i class="fas fa-eye mr-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($delegations->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $delegations->withQueryString()->links() }}</div>
                @endif
            @endif
        </section>

    </div>
</div>
@endsection
