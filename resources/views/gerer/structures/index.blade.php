@extends($layout)

@section('title', 'Structures | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- Hero --}}
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
                ['id'=>'delegations', 'icon'=>'fas fa-map-marker-alt', 'color'=>'sky',    'label'=>'Délégations', 'value'=>$delegations->count()],
                ['id'=>'caisses',     'icon'=>'fas fa-landmark',        'color'=>'indigo', 'label'=>'Caisses',     'value'=>$caisses->count()],
                ['id'=>'agences',     'icon'=>'fas fa-building',         'color'=>'violet', 'label'=>'Agences',     'value'=>$agences->count()],
                ['id'=>'guichets',    'icon'=>'fas fa-cash-register',    'color'=>'amber',  'label'=>'Guichets',    'value'=>$guichets->count()],
                ['id'=>'directions',  'icon'=>'fas fa-sitemap',           'color'=>'teal',   'label'=>'Directions',  'value'=>$directions->count()],
            ] as $kpi)
            <button type="button" onclick="switchTab('{{ $kpi['id'] }}')"
                    data-kpi="{{ $kpi['id'] }}"
                    class="kpi-card rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-100 text-left transition hover:shadow-md hover:ring-slate-200 focus:outline-none">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $kpi['color'] }}-50 text-{{ $kpi['color'] }}-500">
                        <i class="{{ $kpi['icon'] }} text-sm"></i>
                    </span>
                    <span class="text-3xl font-black text-slate-800">{{ $kpi['value'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold text-slate-500">{{ $kpi['label'] }}</p>
            </button>
            @endforeach
        </div>

        {{-- Onglets --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">

            {{-- Barre d'onglets --}}
            <div class="flex overflow-x-auto border-b border-slate-100 bg-slate-50/60 px-2 pt-2 gap-1">
                @foreach([
                    ['id'=>'delegations', 'icon'=>'fas fa-map-marker-alt', 'color'=>'sky',    'label'=>'Délégations',  'count'=>$delegations->count()],
                    ['id'=>'caisses',     'icon'=>'fas fa-landmark',        'color'=>'indigo', 'label'=>'Caisses',      'count'=>$caisses->count()],
                    ['id'=>'agences',     'icon'=>'fas fa-building',         'color'=>'violet', 'label'=>'Agences',      'count'=>$agences->count()],
                    ['id'=>'guichets',    'icon'=>'fas fa-cash-register',    'color'=>'amber',  'label'=>'Guichets',     'count'=>$guichets->count()],
                    ['id'=>'directions',  'icon'=>'fas fa-sitemap',           'color'=>'teal',   'label'=>'Directions',   'count'=>$directions->count()],
                ] as $tab)
                <button type="button"
                        id="tab-btn-{{ $tab['id'] }}"
                        onclick="switchTab('{{ $tab['id'] }}')"
                        class="tab-btn flex shrink-0 items-center gap-2 rounded-t-xl px-4 py-2.5 text-xs font-black uppercase tracking-wide transition whitespace-nowrap border-b-2">
                    <i class="{{ $tab['icon'] }} text-{{ $tab['color'] }}-500"></i>
                    {{ $tab['label'] }}
                    <span class="ml-1 rounded-full px-1.5 py-0.5 text-[10px] font-black tab-badge-{{ $tab['id'] }}">{{ $tab['count'] }}</span>
                </button>
                @endforeach
            </div>

            {{-- Panneau : Délégations --}}
            <div id="tab-delegations" class="tab-panel">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Région</th>
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Ville</th>
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Caisses</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($delegations as $d)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $d->region }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $d->ville }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-100 text-[11px] font-black text-sky-700">{{ $d->caisses_count }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-5 py-10 text-center text-sm text-slate-400">Aucune délégation.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Panneau : Caisses --}}
            <div id="tab-caisses" class="tab-panel hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Nom</th>
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Délégation</th>
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Directeur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($caisses as $c)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $c->nom }}</td>
                                <td class="px-5 py-3 text-xs text-slate-600">{{ $c->delegationTechnique?->region }} – {{ $c->delegationTechnique?->ville }}</td>
                                <td class="px-5 py-3 text-xs text-slate-600">
                                    @if($c->directeur)
                                        {{ $c->directeur->prenom }} {{ $c->directeur->nom }}
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-5 py-10 text-center text-sm text-slate-400">Aucune caisse.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Panneau : Agences --}}
            <div id="tab-agences" class="tab-panel hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Nom</th>
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Caisse</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($agences as $a)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $a->nom }}</td>
                                <td class="px-5 py-3 text-xs text-slate-600">{{ $a->caisse?->nom ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-5 py-10 text-center text-sm text-slate-400">Aucune agence.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Panneau : Guichets --}}
            <div id="tab-guichets" class="tab-panel hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Nom</th>
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Agence</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($guichets as $g)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $g->nom }}</td>
                                <td class="px-5 py-3 text-xs text-slate-600">{{ $g->agence?->nom ?? '—' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-5 py-10 text-center text-sm text-slate-400">Aucun guichet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Panneau : Directions --}}
            <div id="tab-directions" class="tab-panel hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Nom</th>
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Directeur</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($directions as $dir)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $dir->nom }}</td>
                                <td class="px-5 py-3 text-slate-600">
                                    @if($dir->directeur)
                                        {{ $dir->directeur->prenom }} {{ $dir->directeur->nom }}
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-5 py-10 text-center text-sm text-slate-400">Aucune direction.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- fin card onglets --}}
    </div>
</div>

<script>
const TABS = ['delegations', 'caisses', 'agences', 'guichets', 'directions'];

const COLORS = {
    delegations: { active: 'border-sky-500 text-sky-700 bg-white',    badge: 'bg-sky-100 text-sky-700' },
    caisses:     { active: 'border-indigo-500 text-indigo-700 bg-white', badge: 'bg-indigo-100 text-indigo-700' },
    agences:     { active: 'border-violet-500 text-violet-700 bg-white', badge: 'bg-violet-100 text-violet-700' },
    guichets:    { active: 'border-amber-500 text-amber-700 bg-white',  badge: 'bg-amber-100 text-amber-700' },
    directions:  { active: 'border-teal-500 text-teal-700 bg-white',   badge: 'bg-teal-100 text-teal-700' },
};
const INACTIVE = 'border-transparent text-slate-500 bg-transparent hover:text-slate-700 hover:bg-white/60';

function switchTab(id) {
    TABS.forEach(t => {
        const panel = document.getElementById('tab-' + t);
        const btn   = document.getElementById('tab-btn-' + t);
        const badge = btn?.querySelector('.tab-badge-' + t);

        if (t === id) {
            panel?.classList.remove('hidden');
            btn?.classList.remove(...INACTIVE.split(' '));
            btn?.classList.add(...COLORS[t].active.split(' '));
            if (badge) { badge.classList.remove('bg-slate-100','text-slate-500'); badge.classList.add(...COLORS[t].badge.split(' ')); }
            // Mettre à jour le KPI card actif
            document.querySelectorAll('.kpi-card').forEach(k => k.classList.remove('ring-2','ring-offset-1'));
            document.querySelector('[data-kpi="'+t+'"]')?.classList.add('ring-2','ring-offset-1', 'ring-'+t+'-color');
        } else {
            panel?.classList.add('hidden');
            btn?.classList.add(...INACTIVE.split(' '));
            btn?.classList.remove(...COLORS[t].active.split(' '));
            if (badge) { badge.classList.add('bg-slate-100','text-slate-500'); badge.classList.remove(...COLORS[t].badge.split(' ')); }
        }
    });
    // Persister dans l'URL sans rechargement
    const url = new URL(window.location);
    url.searchParams.set('tab', id);
    history.replaceState(null, '', url);
}

// Initialisation : lire ?tab= ou défaut
const initTab = new URLSearchParams(window.location.search).get('tab');
switchTab(TABS.includes(initTab) ? initTab : 'delegations');
</script>
@endsection
