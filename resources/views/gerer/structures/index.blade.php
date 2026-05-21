@extends($layout)

@section('title', 'Structures | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    <div class="relative overflow-hidden px-6 py-8 lg:px-10"
         style="background:linear-gradient(135deg,#0c4a6e 0%,#0369a1 50%,#0284c7 100%)">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white shadow ring-1 ring-white/20">
                <i class="fas fa-sitemap"></i>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-sky-200">Gestion · RCPB</p>
                <h1 class="mt-0.5 text-2xl font-black text-white">Structures</h1>
                <p class="mt-0.5 text-sm text-sky-100/75">Vue d'ensemble des structures du réseau</p>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8 space-y-6">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            @foreach([
                ['icon'=>'fas fa-map-marker-alt','color'=>'sky',   'label'=>'Délégations', 'value'=>$delegations->count()],
                ['icon'=>'fas fa-landmark',       'color'=>'indigo','label'=>'Caisses',     'value'=>$caisses->count()],
                ['icon'=>'fas fa-building',        'color'=>'violet','label'=>'Agences',     'value'=>$agences->count()],
                ['icon'=>'fas fa-cash-register',   'color'=>'amber', 'label'=>'Guichets',    'value'=>$guichets->count()],
                ['icon'=>'fas fa-sitemap',          'color'=>'teal',  'label'=>'Directions',  'value'=>$directions->count()],
            ] as $kpi)
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $kpi['color'] }}-50 text-{{ $kpi['color'] }}-500">
                        <i class="{{ $kpi['icon'] }} text-sm"></i>
                    </span>
                    <span class="text-3xl font-black text-slate-800">{{ $kpi['value'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold text-slate-500">{{ $kpi['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Délégations --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-black text-slate-800"><i class="fas fa-map-marker-alt mr-2 text-sky-500"></i>Délégations Techniques</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Région</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Ville</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Caisses</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($delegations as $d)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $d->region }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $d->ville }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-100 text-[11px] font-black text-sky-700">{{ $d->caisses_count }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-slate-400">Aucune délégation.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Directions --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-black text-slate-800"><i class="fas fa-sitemap mr-2 text-teal-500"></i>Directions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Nom</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Directeur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($directions as $dir)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $dir->nom }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($dir->directeur)
                                    {{ $dir->directeur->prenom }} {{ $dir->directeur->nom }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="2" class="px-4 py-8 text-center text-sm text-slate-400">Aucune direction.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Caisses --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-black text-slate-800"><i class="fas fa-landmark mr-2 text-indigo-500"></i>Caisses</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Nom</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Délégation</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Directeur</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($caisses as $c)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $c->nom }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $c->delegationTechnique?->region }} – {{ $c->delegationTechnique?->ville }}</td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                @if($c->directeur)
                                    {{ $c->directeur->prenom }} {{ $c->directeur->nom }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-slate-400">Aucune caisse.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
