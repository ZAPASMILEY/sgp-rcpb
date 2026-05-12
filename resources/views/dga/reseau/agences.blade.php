@extends('layouts.dga')
@section('title', 'Agences | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-6 right-16 h-20 w-20 rounded-full bg-white/5"></div>
            <p class="text-[11px] font-black uppercase tracking-[0.25em] text-emerald-200">Espace DGA · Réseau</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-white">Agences</h1>
            <p class="mt-1 text-sm text-emerald-100/80">{{ $agences->total() }} agence(s) dans le réseau.</p>
            <div class="absolute right-6 top-1/2 -translate-y-1/2 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10">
                <i class="fas fa-building text-2xl text-white"></i>
            </div>
        </div>

        <form method="GET" class="admin-panel px-5 py-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[180px]">
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nom de l'agence…" class="ent-input w-full">
                </div>
                @if($caisses->isNotEmpty())
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Caisse</label>
                    <select name="caisse" class="ent-input">
                        <option value="">Toutes</option>
                        @foreach($caisses as $c)
                            <option value="{{ $c->id }}" {{ $caisseId == $c->id ? 'selected' : '' }}>{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <button type="submit" class="ent-btn ent-btn-primary"><i class="fas fa-filter mr-2"></i>Filtrer</button>
                @if($search || $caisseId)
                    <a href="{{ route('dga.reseau.agences') }}" class="ent-btn ent-btn-soft">Réinitialiser</a>
                @endif
            </div>
        </form>

        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Liste des agences</h2>
            </div>
            @if($agences->isEmpty())
                <div class="px-6 py-16 text-center">
                    <i class="fas fa-inbox text-4xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucune agence trouvée.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Agence</th>
                                <th class="px-4 py-3">Caisse</th>
                                <th class="px-4 py-3">Chef d'agence</th>
                                <th class="px-4 py-3 text-center">Agents</th>
                                <th class="px-4 py-3 text-center">Guichets</th>
                                <th class="px-4 py-3 text-center">Note</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($agences as $agence)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ $agence->nom }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $agence->caisse?->nom ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if($agence->chef)
                                            <p class="font-semibold">{{ $agence->chef->prenom }} {{ $agence->chef->nom }}</p>
                                            <p class="text-xs text-slate-400">{{ $agence->chef->numero_telephone ?? '' }}</p>
                                        @else
                                            <p class="font-semibold text-slate-400">—</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ $agence->agents_count }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-700">{{ $agence->guichets_count }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @php $an = $agenceNotes[$agence->id] ?? ['moyenne' => null, 'total' => 0]; @endphp
                                        @if($an['moyenne'] !== null)
                                            @php $c = $an['moyenne'] >= 8.5 ? 'bg-emerald-100 text-emerald-700' : ($an['moyenne'] >= 7 ? 'bg-blue-100 text-blue-700' : ($an['moyenne'] >= 5 ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700')); @endphp
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-bold {{ $c }}">{{ number_format($an['moyenne'], 2) }}</span>
                                        @else
                                            <span class="text-xs font-bold text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('dga.reseau.agences.show', $agence) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                                            <i class="fas fa-eye mr-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($agences->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $agences->withQueryString()->links() }}</div>
                @endif
            @endif
        </section>

    </div>
</div>
@endsection
