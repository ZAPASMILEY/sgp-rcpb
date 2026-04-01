@extends('layouts.app')

@section('title', 'Faîtière | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans selection:bg-cyan-100">
    <div class="max-w-[1600px] mx-auto space-y-8">
        
        {{-- Notification de statut --}}
        @if (session('status'))
            <div id="status-message" class="flex items-center gap-4 p-4 bg-white border border-emerald-100 rounded-2xl shadow-xl shadow-emerald-100/50 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="h-10 w-10 bg-emerald-500 rounded-xl flex items-center justify-center text-white">
                    <i class="fas fa-check"></i>
                </div>
                <p class="font-bold text-slate-700 text-sm uppercase tracking-tight">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 5000);</script>
        @endif

        @if ($entite)
            {{-- HEADER & ACTIONS --}}
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">Gestion de la Faîtière</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="px-3 py-1 bg-cyan-50 text-cyan-600 text-[10px] font-black uppercase rounded-full border border-cyan-100 tracking-widest">Siège Principal</span>
                        <span class="text-slate-400 text-sm font-medium"><i class="fas fa-map-marker-alt mr-1"></i> {{ $entite->ville }}, {{ $entite->region }}</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.entites.edit', $entite) }}" class="h-12 px-6 bg-white border border-slate-200 text-slate-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center gap-2 hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-edit text-cyan-500"></i> Modifier
                    </a>
                    <a href="{{ route('admin.entites.show', $entite) }}" class="h-12 px-6 bg-white border border-slate-200 text-slate-600 rounded-2xl text-xs font-black uppercase tracking-widest flex items-center gap-2 hover:bg-slate-50 transition-all shadow-sm">
                        <i class="fas fa-eye text-slate-400"></i> Fiche Complète
                    </a>
                    <form method="POST" action="{{ route('admin.entites.reset') }}" class="inline">
                        @csrf
                        <button class="h-12 w-12 bg-rose-50 text-rose-500 rounded-2xl border border-rose-100 hover:bg-rose-100 transition-all flex items-center justify-center shadow-sm" title="Réinitialiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </form>
                </div>
            </div>

            {{-- SECTION KPI (Style Hotel Booking) --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @php
                    $kpis = [
                        [
                            'label' => 'Directions', 
                            'val' => $stats['directions'], 
                            'icon' => 'fas fa-sitemap', 
                            'grad' => 'from-cyan-400 to-blue-500', 
                            'href' => route('admin.entites.directions.index'),
                            'add_href' => route('admin.entites.directions.create')
                        ],
                        [
                            'label' => 'Services', 
                            'val' => $stats['services'], 
                            'icon' => 'fas fa-layer-group', 
                            'grad' => 'from-emerald-400 to-teal-500', 
                            'href' => route('admin.services.index'),
                            'add_href' => route('admin.services.create')
                        ],
                        [
                            'label' => 'Secrétaires',
                            'val' => $stats['secretaires'],
                            'icon' => 'fas fa-user-tie',
                            'grad' => 'from-fuchsia-400 to-pink-500',
                            'href' => route('admin.entites.secretaires.index'),
                            'add_href' => route('admin.entites.secretaires.index')
                        ],
                        [
                            'label' => 'Agents', 
                            'val' => $stats['agents'], 
                            'icon' => 'fas fa-users', 
                            'grad' => 'from-orange-400 to-amber-500', 
                            'href' => route('admin.agents.index'),
                            'add_href' => route('admin.agents.create')
                        ],
                    ];
                @endphp

                @foreach($kpis as $k)
                <div class="relative overflow-hidden rounded-[32px] p-8 text-white shadow-xl shadow-slate-200/50 bg-gradient-to-br {{ $k['grad'] }} group hover:scale-[1.02] transition-transform duration-300">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-5xl font-black tracking-tighter">{{ $k['val'] }}</p>
                            <p class="text-sm font-bold opacity-80 mt-1 uppercase tracking-[0.2em]">{{ $k['label'] }}</p>
                        </div>
                        <div class="bg-white/20 h-14 w-14 rounded-2xl flex items-center justify-center backdrop-blur-md">
                            <i class="{{ $k['icon'] }} text-2xl"></i>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-3">
                        <a href="{{ $k['href'] }}" class="flex-1 py-3 bg-white/20 rounded-xl text-[10px] font-black uppercase tracking-widest text-center backdrop-blur-sm hover:bg-white/30 transition-all">Consulter</a>
                        <a href="{{ $k['add_href'] }}" class="h-10 w-10 bg-white text-slate-800 rounded-xl flex items-center justify-center hover:scale-110 transition-transform shadow-lg"><i class="fas fa-plus"></i></a>
                    </div>
                </div>
                @endforeach
            </div>

            <section class="bg-white rounded-[32px] p-6 lg:p-8 shadow-sm border border-slate-100" id="tab-panels-entite">
                <div class="flex flex-wrap gap-3 border-b border-slate-100 pb-5">
                    <button type="button" data-entite-tab-trigger="directions" class="entite-tab-trigger inline-flex items-center gap-2 rounded-2xl border border-cyan-100 bg-cyan-50 px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-cyan-600">
                        <i class="fas fa-sitemap"></i>
                        <span>Directions</span>
                    </button>
                    <button type="button" data-entite-tab-trigger="services" class="entite-tab-trigger inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                        <i class="fas fa-layer-group"></i>
                        <span>Services</span>
                    </button>
                    <button type="button" id="tab-secretaires" data-entite-tab-trigger="secretaires" class="entite-tab-trigger inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                        <i class="fas fa-user-tie"></i>
                        <span>Secrétaires</span>
                    </button>
                    <button type="button" data-entite-tab-trigger="agents" class="entite-tab-trigger inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                        <i class="fas fa-users"></i>
                        <span>Agents</span>
                    </button>
                </div>

                <div class="mt-6">
                    <div data-entite-tab-panel="directions">
                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                            @forelse($directions as $direction)
                                <article class="rounded-[28px] border border-slate-100 bg-slate-50 p-6 shadow-sm">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-cyan-500">Direction</p>
                                            <h3 class="mt-2 text-lg font-black text-slate-800">{{ $direction->nom }}</h3>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-[10px] font-black uppercase tracking-widest text-slate-500 shadow-sm">
                                            {{ $direction->services_count }} service(s)
                                        </span>
                                    </div>
                                    <p class="mt-4 text-sm font-semibold text-slate-500">
                                        {{ trim($direction->directeur_prenom.' '.$direction->directeur_nom) ?: 'Directeur non renseigné' }}
                                    </p>
                                    <div class="mt-5 flex gap-3">
                                        <a href="{{ route('admin.entites.directions.index') }}" class="ent-btn ent-btn-soft flex-1 justify-center">Voir liste</a>
                                        <a href="{{ route('admin.entites.directions.create') }}" class="ent-btn ent-btn-primary justify-center px-4">Ajouter</a>
                                    </div>
                                </article>
                            @empty
                                <p class="text-sm text-slate-400">Aucune direction enregistrée.</p>
                            @endforelse
                        </div>
                    </div>

                    <div data-entite-tab-panel="services" class="hidden">
                        @include('admin.entites.partials.services')
                    </div>

                    <div data-entite-tab-panel="secretaires" class="hidden">
                        @include('admin.entites.partials.secretaires', ['directions' => $allDirections])
                    </div>

                    <div data-entite-tab-panel="agents" class="hidden">
                        <div class="flex items-center justify-between mb-5">
                            <h2 class="text-xl font-black text-slate-800">Liste des agents</h2>
                            <a href="{{ route('admin.agents.create') }}" data-open-create-modal data-modal-title="Ajouter un agent" class="ent-btn ent-btn-primary">Ajouter un agent</a>
                        </div>
                        @if($agents->isEmpty())
                            <p class="text-sm text-slate-400">Aucun agent trouvé.</p>
                        @else
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                @foreach($agents as $agent)
                                    <article class="rounded-[24px] border border-slate-100 bg-slate-50 p-5">
                                        <p class="text-lg font-black text-slate-800">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                        <p class="mt-2 text-sm font-medium text-slate-500">{{ $agent->fonction ?: 'Fonction non renseignée' }}</p>
                                        <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-slate-400">{{ $agent->service?->nom ?? 'Sans service' }}</p>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-12 gap-8">
                {{-- COLONNE GAUCHE : INFOS & DIRIGEANTS (8/12) --}}
                <div class="col-span-12 lg:col-span-8 space-y-8">
                    <div class="bg-white rounded-[32px] p-8 lg:p-12 shadow-sm border border-slate-100 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-12 opacity-[0.03] text-9xl pointer-events-none">
                            <i class="fas fa-building"></i>
                        </div>
                        
                        <div class="relative z-10">
                            <h3 class="text-xs font-black text-cyan-500 uppercase tracking-[0.3em] mb-4 text-center lg:text-left">Organisation de Haut Niveau</h3>
                            <h2 class="text-4xl font-black text-slate-800 tracking-tight mb-6 text-center lg:text-left">Structure de la Faîtière</h2>
                            <p class="text-slate-500 leading-relaxed mb-10 text-center lg:text-left">La faîtière assure le pilotage stratégique de l'ensemble du réseau RCPB. Elle centralise les décisions administratives et la gestion du personnel cadre.</p>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                @php
                                    $dirigeants = [
                                        ['label' => 'Directrice Générale', 'nom' => $entite->directrice_generale_prenom . ' ' . $entite->directrice_generale_nom, 'icon' => 'fa-user-tie'],
                                        ['label' => 'DGA', 'nom' => $entite->dga_prenom . ' ' . $entite->dga_nom, 'icon' => 'fa-user-shield'],
                                        ['label' => 'PCA', 'nom' => $entite->pca_prenom . ' ' . $entite->pca_nom, 'icon' => 'fa-landmark']
                                    ];
                                @endphp
                                @foreach($dirigeants as $d)
                                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 flex flex-col items-center text-center group hover:bg-white hover:shadow-xl hover:shadow-slate-100 transition-all duration-300">
                                    <div class="h-12 w-12 bg-white rounded-2xl flex items-center justify-center text-cyan-500 mb-4 shadow-sm group-hover:bg-cyan-500 group-hover:text-white transition-all">
                                        <i class="fas {{ $d['icon'] }}"></i>
                                    </div>
                                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">{{ $d['label'] }}</span>
                                    <span class="text-sm font-black text-slate-800 tracking-tight line-clamp-1">{{ $d['nom'] }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- COLONNE DROITE : STATUT & LOGS (4/12) --}}
                <div class="col-span-12 lg:col-span-4 space-y-8">
                    <div class="bg-slate-900 rounded-[32px] p-8 text-white shadow-xl shadow-slate-200">
                        <h3 class="text-lg font-black italic mb-6">Informations Siège</h3>
                        <div class="space-y-6">
                            <div class="flex gap-4 items-start">
                                <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                    <i class="fas fa-phone text-cyan-400"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest opacity-50">Contact Principal</p>
                                    <p class="text-sm font-bold mt-1 text-cyan-100">+226 25 XX XX XX</p>
                                </div>
                            </div>
                            <div class="flex gap-4 items-start">
                                <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                    <i class="fas fa-calendar-alt text-emerald-400"></i>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-widest opacity-50">Dernière Mise à jour</p>
                                    <p class="text-sm font-bold mt-1 text-emerald-100">{{ $entite->updated_at->format('d M Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-10 p-6 bg-white/5 rounded-3xl border border-white/10">
                            <p class="text-xs leading-relaxed opacity-70 italic text-center">"Assurer la pérennité et la solidarité du réseau à travers une gouvernance rigoureuse."</p>
                        </div>
                    </div>
                </div>
            </div>

        @else
            {{-- EMPTY STATE --}}
            <div class="bg-white rounded-[40px] p-20 text-center border border-slate-100 shadow-2xl">
                <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8">
                    <i class="fas fa-building text-5xl text-slate-200"></i>
                </div>
                <h2 class="text-3xl font-black text-slate-800 tracking-tight uppercase mb-4 italic">Aucune faîtière enregistrée</h2>
                <p class="text-slate-400 max-w-md mx-auto mb-10">Vous devez configurer l'entité de tête (Faîtière) pour commencer à gérer les directions et le personnel.</p>
                <a href="{{ route('admin.entites.create') }}" class="inline-flex h-14 px-12 bg-cyan-500 text-white rounded-2xl items-center text-xs font-black uppercase tracking-widest shadow-xl shadow-cyan-200 hover:bg-cyan-600 transition-all">
                    Créer la faîtière maintenant
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const triggers = document.querySelectorAll('[data-entite-tab-trigger]');
    const panels = document.querySelectorAll('[data-entite-tab-panel]');

    function activateTab(tabName) {
        triggers.forEach((trigger) => {
            const isActive = trigger.getAttribute('data-entite-tab-trigger') === tabName;
            trigger.classList.toggle('bg-cyan-50', isActive);
            trigger.classList.toggle('border-cyan-100', isActive);
            trigger.classList.toggle('text-cyan-600', isActive);
            trigger.classList.toggle('bg-white', !isActive);
            trigger.classList.toggle('border-slate-200', !isActive);
            trigger.classList.toggle('text-slate-500', !isActive);
        });

        panels.forEach((panel) => {
            panel.classList.toggle('hidden', panel.getAttribute('data-entite-tab-panel') !== tabName);
        });
    }

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', function () {
            activateTab(this.getAttribute('data-entite-tab-trigger'));
        });
    });

    if (window.location.hash === '#tab-secretaires') {
        activateTab('secretaires');
        document.getElementById('tab-panels-entite')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        activateTab('directions');
    }
});
</script>
@endpush
