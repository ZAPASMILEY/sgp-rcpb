@extends('layouts.dga')
@section('title', $agence->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div>
                <a href="{{ route('dga.reseau.agences') }}" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-violet-600">
                    <i class="fas fa-arrow-left"></i> Agences
                </a>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $agence->nom }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $agence->caisse?->nom ?? '' }}</p>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chef d'agence</p>
                <p class="mt-1 font-bold text-slate-800">{{ $agence->chef_nom ?? '—' }}</p>
                <p class="text-xs text-slate-400">{{ $agence->chef_telephone ?? '' }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Secrétaire</p>
                <p class="mt-1 font-bold text-slate-800">{{ $agence->secretaire_nom ?? '—' }}</p>
                <p class="text-xs text-slate-400">{{ $agence->secretaire_telephone ?? '' }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Guichets</p>
                <p class="mt-1 text-3xl font-black text-blue-700">{{ $agence->guichets->count() }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Note moyenne</p>
                @if($noteStats['moyenne'] !== null)
                    @php $c = $noteStats['moyenne'] >= 8.5 ? 'text-emerald-600' : ($noteStats['moyenne'] >= 7 ? 'text-blue-600' : ($noteStats['moyenne'] >= 5 ? 'text-amber-600' : 'text-rose-600')); @endphp
                    <p class="mt-1 text-3xl font-black {{ $c }}">{{ number_format($noteStats['moyenne'], 2) }}</p>
                    <p class="text-xs text-slate-400">sur {{ $noteStats['total'] }} évaluation(s)</p>
                @else
                    <p class="mt-1 text-xl font-bold text-slate-300">—</p>
                    <p class="text-xs text-slate-300">Aucune évaluation</p>
                @endif
            </div>
        </div>

        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">Guichets de l'agence</h2>
            </div>
            @if($agence->guichets->isEmpty())
                <p class="px-6 py-10 text-sm text-slate-400 text-center">Aucun guichet rattaché.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-xs font-black uppercase tracking-[0.12em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Guichet</th>
                                <th class="px-4 py-3">Chef de guichet</th>
                                <th class="px-4 py-3">Téléphone</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($agence->guichets as $guichet)
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ $guichet->nom }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $guichet->chef_nom ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $guichet->chef_telephone ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('dga.reseau.guichets.show', $guichet) }}" class="ent-btn ent-btn-soft py-1 px-3 text-xs">
                                            <i class="fas fa-eye mr-1"></i>Voir
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
