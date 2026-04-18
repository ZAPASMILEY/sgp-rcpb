@extends('layouts.dg')
@section('title', 'Guichets | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="mx-auto max-w-6xl flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Espace DG / Réseau RCPB</p>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Guichets</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $guichets->total() }} guichet(s) dans le réseau.</p>
                </div>
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-700 shadow-sm">
                    <i class="fas fa-cash-register text-xl"></i>
                </div>
            </div>
        </header>

        {{-- Filtres --}}
        <form method="GET" class="admin-panel px-5 py-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nom, chef de guichet…" class="ent-input w-full">
                </div>
                @if ($agences->isNotEmpty())
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Agence</label>
                    <select name="agence" class="ent-input">
                        <option value="">Toutes</option>
                        @foreach ($agences as $a)
                            <option value="{{ $a->id }}" {{ $agenceId == $a->id ? 'selected' : '' }}>{{ $a->nom }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button type="submit" class="ent-btn ent-btn-primary"><i class="fas fa-filter mr-2"></i>Filtrer</button>
                @if ($search || $agenceId)
                    <a href="{{ route('dg.guichets') }}" class="ent-btn ent-btn-soft">Réinitialiser</a>
                @endif
            </div>
        </form>

        {{-- Liste --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Liste des guichets</h2>
            </div>
            @if ($guichets->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucun guichet enregistré.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Guichet</th>
                                <th class="px-4 py-3">Agence</th>
                                <th class="px-4 py-3">Caisse</th>
                                <th class="px-4 py-3">Chef de guichet</th>
                                <th class="px-4 py-3">Téléphone</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($guichets as $guichet)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ $guichet->nom }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $guichet->agence?->nom ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $guichet->agence?->superviseurCaisse?->nom ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-semibold">{{ $guichet->chef_nom ?? '—' }}</p>
                                        <p class="text-xs text-slate-400">{{ $guichet->chef_email ?? '' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $guichet->chef_telephone ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($guichets->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $guichets->withQueryString()->links() }}</div>
                @endif
            @endif
        </section>

    </div>
</div>
@endsection
