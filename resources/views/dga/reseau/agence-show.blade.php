@extends('layouts.dga')
@section('title', $agence->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-6 right-16 h-20 w-20 rounded-full bg-white/5"></div>
            <a href="{{ route('dga.reseau.agences') }}" class="mb-3 inline-flex items-center gap-1.5 text-xs font-bold text-emerald-200 hover:text-white transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i> Agences
            </a>
            <h1 class="text-2xl font-black tracking-tight text-white">{{ $agence->nom }}</h1>
            <p class="mt-1 text-sm text-emerald-100/80">{{ $agence->caisse?->nom ?? '' }}</p>
            <div class="absolute right-6 top-1/2 -translate-y-1/2 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10">
                <i class="fas fa-building text-2xl text-white"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chef d'agence</p>
                <p class="mt-1 font-bold text-slate-800">
                    {{ $agence->chef ? $agence->chef->prenom.' '.$agence->chef->nom : '—' }}
                </p>
                <p class="text-xs text-slate-400">{{ $agence->chef?->numero_telephone ?? '' }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Secrétaire</p>
                <p class="mt-1 font-bold text-slate-800">
                    {{ $agence->secretaire ? $agence->secretaire->prenom.' '.$agence->secretaire->nom : '—' }}
                </p>
                <p class="text-xs text-slate-400">{{ $agence->secretaire?->numero_telephone ?? '' }}</p>
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
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $guichet->chef ? $guichet->chef->prenom.' '.$guichet->chef->nom : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $guichet->chef?->numero_telephone ?? '—' }}</td>
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
