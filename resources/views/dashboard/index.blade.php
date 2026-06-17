@extends($layout)

@section('title', 'Tableau de bord | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

{{-- ══════════════════════════ HERO ══════════════════════════════════════ --}}
@if ($role === 'pca')

    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background:linear-gradient(135deg,#003d20 0%,#005c30 50%,#008751 100%)">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white text-2xl font-black shadow-inner ring-2 ring-white/20 backdrop-blur-sm">
                    R
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-white/70">Réseau des Caisses Populaires du Burkina</p>
                    <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ $entite->nom }}</h1>
                    <p class="mt-1 text-sm text-white/60">Pilotage PCA · Synthèse du {{ now()->translatedFormat('d F Y') }}</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-white/70">Année</span>
                <div class="relative" id="dd-anno-hero">
                    <button type="button"
                        class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm cursor-pointer transition hover:bg-white/20 focus:ring-2 focus:ring-white/30"
                        onclick="heroYearToggle()">
                        <span>{{ $annee }}</span>
                        <i class="fas fa-chevron-down text-xs opacity-60"></i>
                    </button>
                    <div id="dd-anno-hero-menu" class="hero-year-menu hidden absolute right-0 top-full mt-2 z-50 min-w-[7rem] rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden py-1">
                        @foreach ($anneesDisponibles as $yr)
                            <a href="{{ route('pca.dashboard', ['annee' => $yr]) }}"
                               class="flex items-center px-4 py-2.5 text-sm transition hover:bg-slate-50 {{ $yr === $annee ? 'font-bold text-green-700 bg-green-50/60' : 'text-slate-700' }}">
                                {{ $yr }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Fiches objectifs DG', 'value' => $totalFiches,                                                             'icon' => 'fas fa-clipboard-list'],
                ['label' => 'Évaluations DG',       'value' => $evalsTotal,                                                             'icon' => 'fas fa-star'],
                ['label' => 'Note moy. DG',         'value' => $noteMoyenne > 0 ? number_format($noteMoyenne, 2, ',', ' ').'/10' : '—', 'icon' => 'fas fa-chart-bar'],
                ['label' => 'Avancement DG',        'value' => $tauxAvancement.'%',                                                     'icon' => 'fas fa-gauge-high'],
            ] as $m)
            <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm"><i class="{{ $m['icon'] }}"></i></span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-white/70">{{ $m['label'] }}</p>
                    <p class="text-lg font-black text-white">{{ $m['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

@elseif ($role === 'dga')

    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background:linear-gradient(135deg,#003d20 0%,#005c30 50%,#008751 100%)">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white shadow-inner ring-2 ring-white/20">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-white/70">Réseau RCPB · Directeur Général Adjoint</p>
                    <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ auth()->user()->name }}</h1>
                    <p class="mt-1 text-sm text-white/60">Synthèse du {{ now()->translatedFormat('d F Y') }}</p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-white/70">Année</span>
                <div class="relative" id="dd-anno-hero">
                    <button type="button"
                        class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm cursor-pointer transition hover:bg-white/20"
                        onclick="heroYearToggle()">
                        <span>{{ $annee }}</span>
                        <i class="fas fa-chevron-down text-xs opacity-60"></i>
                    </button>
                    <div id="dd-anno-hero-menu" class="hero-year-menu hidden absolute right-0 top-full mt-2 z-50 min-w-[7rem] rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden py-1">
                        @foreach ($anneesDisponibles as $yr)
                            <a href="{{ route('dga.dashboard', ['annee' => $yr]) }}"
                               class="flex items-center px-4 py-2.5 text-sm transition hover:bg-slate-50 {{ $yr === $annee ? 'font-bold text-green-700 bg-green-50/60' : 'text-slate-700' }}">
                                {{ $yr }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('dga.mon-espace') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i class="fas fa-folder-open text-xs"></i> Mon espace
                </a>
            </div>
        </div>
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Délégations',        'value' => $reseauStats['delegations'], 'icon' => 'fas fa-map-marker-alt'],
                ['label' => 'Caisses',            'value' => $reseauStats['caisses'],     'icon' => 'fas fa-building-columns'],
                ['label' => 'Évaluations reçues', 'value' => $evalsRecStats['total'],     'icon' => 'fas fa-star'],
                ['label' => 'Note moy. réseau',   'value' => $noteReseau ? number_format($noteReseau, 2, ',', ' ').'/10' : '—', 'icon' => 'fas fa-chart-bar'],
            ] as $m)
            <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm"><i class="{{ $m['icon'] }}"></i></span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-white/70">{{ $m['label'] }}</p>
                    <p class="text-lg font-black text-white">{{ $m['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

@elseif ($role === 'personnel')

    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background:linear-gradient(135deg,#003d20 0%,#005c30 50%,#008751 100%)">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white shadow-inner ring-2 ring-white/20">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-white/70">
                        @if ($agent?->service)
                            {{ $agent->service->nom }} · Personnel
                        @elseif ($agent?->agence)
                            {{ $agent->agence->nom }} · Personnel
                        @else
                            Mon Espace · Personnel
                        @endif
                    </p>
                    <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-white/60">{{ $agent?->role ?? $user->role }} · Synthèse du {{ now()->translatedFormat('d F Y') }}</p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-white/70">Année</span>
                <div class="relative" id="dd-anno-hero">
                    <button type="button"
                        class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm cursor-pointer transition hover:bg-white/20"
                        onclick="heroYearToggle()">
                        <span>{{ $annee }}</span>
                        <i class="fas fa-chevron-down text-xs opacity-60"></i>
                    </button>
                    <div id="dd-anno-hero-menu" class="hero-year-menu hidden absolute right-0 top-full mt-2 z-50 min-w-[7rem] rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden py-1">
                        @foreach ($anneesDisponibles as $yr)
                            <a href="{{ route('personnel.dashboard', ['annee' => $yr]) }}"
                               class="flex items-center px-4 py-2.5 text-sm transition hover:bg-slate-50 {{ $yr === $annee ? 'font-bold text-green-700 bg-green-50/60' : 'text-slate-700' }}">
                                {{ $yr }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('personnel.mon-espace') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm transition hover:bg-white/20">
                    <i class="fas fa-folder-open text-xs"></i> Mon dossier
                </a>
            </div>
        </div>
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Évaluations',    'value' => $evalsRecStats['total'],  'icon' => 'fas fa-star'],
                ['label' => 'Validées',        'value' => $evalsRecStats['valide'], 'icon' => 'fas fa-check'],
                ['label' => 'Fiches obj.',     'value' => $fichesRecStats['total'], 'icon' => 'fas fa-clipboard-list'],
                ['label' => 'Avancement moy.', 'value' => $tauxAvancement.'%',      'icon' => 'fas fa-gauge-high'],
            ] as $m)
            <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm"><i class="{{ $m['icon'] }}"></i></span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-white/70">{{ $m['label'] }}</p>
                    <p class="text-lg font-black text-white">{{ $m['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

@else {{-- chef / directeur --}}

    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background:linear-gradient(135deg,#003d20 0%,#005c30 50%,#008751 100%)">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>
        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-5">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white shadow-inner ring-2 ring-white/20">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.25em] text-white/70">
                        @if ($role === 'chef')
                            {{ $ctx->getNom() }} · Pilotage
                        @else
                            {{ $direction->nom ?? 'Direction' }} · Pilotage
                        @endif
                    </p>
                    <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ $user->name }}</h1>
                    <p class="mt-1 text-sm text-white/60">
                        @if ($role === 'chef')
                            {{ match($user->role) { 'Chef_Service' => 'Chef de Service', 'Chef_Agence' => "Chef d'Agence", 'Chef_Guichet' => 'Chef de Guichet', default => $user->role } }}
                        @else
                            {{ match($user->role) { 'Directeur_Direction' => 'Directeur de Direction', 'Directeur_Technique' => 'Directeur Technique', 'Directeur_Caisse' => 'Directeur de Caisse', default => $user->role } }}
                        @endif
                        · Synthèse du {{ now()->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                <span class="text-[11px] font-black uppercase tracking-widest text-white/70">Année</span>
                <div class="relative" id="dd-anno-hero">
                    <button type="button"
                        class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-black text-white backdrop-blur-sm cursor-pointer transition hover:bg-white/20 focus:ring-2 focus:ring-white/30"
                        onclick="heroYearToggle()">
                        <span>{{ $annee }}</span>
                        <i class="fas fa-chevron-down text-xs opacity-60"></i>
                    </button>
                    <div id="dd-anno-hero-menu" class="hero-year-menu hidden absolute right-0 top-full mt-2 z-50 min-w-[7rem] rounded-xl bg-white shadow-xl ring-1 ring-black/10 overflow-hidden py-1">
                        @foreach ($anneesDisponibles as $yr)
                            <a href="{{ route($role.'.dashboard', ['annee' => $yr]) }}"
                               class="flex items-center px-4 py-2.5 text-sm transition hover:bg-slate-50 {{ $yr === $annee ? 'font-bold text-green-700 bg-green-50/60' : 'text-slate-700' }}">
                                {{ $yr }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @if ($role === 'chef')
                @php $heroKpis = [
                    ['label' => 'Fiches reçues',      'value' => $fichesRecStats['total'],  'icon' => 'fas fa-clipboard-list'],
                    ['label' => 'Évaluations reçues', 'value' => $evalsRecStats['total'],   'icon' => 'fas fa-star'],
                    ['label' => 'Agents suivis',       'value' => $agentsOverview->count(), 'icon' => 'fas fa-users'],
                    ['label' => 'Note moy. équipe',   'value' => $noteMoyenneEquipe > 0 ? number_format($noteMoyenneEquipe, 2, ',', ' ').'/10' : '—', 'icon' => 'fas fa-chart-bar'],
                ]; @endphp
            @else
                @php $heroKpis = [
                    ['label' => 'Fiches reçues',      'value' => $fichesRecStats['total'],  'icon' => 'fas fa-clipboard-list'],
                    ['label' => 'Évaluations reçues', 'value' => $evalsRecStats['total'],   'icon' => 'fas fa-star'],
                    ['label' => 'Avancement',          'value' => $tauxAvancement.'%',       'icon' => 'fas fa-gauge-high'],
                    ['label' => 'Note moy. équipe',   'value' => $noteMoyenneEquipe > 0 ? number_format($noteMoyenneEquipe, 2, ',', ' ').'/10' : '—', 'icon' => 'fas fa-chart-bar'],
                ]; @endphp
            @endif
            @foreach ($heroKpis as $m)
            <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm"><i class="{{ $m['icon'] }}"></i></span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-white/70">{{ $m['label'] }}</p>
                    <p class="text-lg font-black text-white">{{ $m['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

@endif

<script>
function heroYearToggle() {
    const menu = document.getElementById('dd-anno-hero-menu');
    if (menu) menu.classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('#dd-anno-hero')) {
        const m = document.getElementById('dd-anno-hero-menu');
        if (m) m.classList.add('hidden');
    }
});
</script>

{{-- ══════════════════════════ BODY ══════════════════════════════════════ --}}

@if ($role === 'personnel')

    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        @if (! $agent)
            <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-12 text-center shadow-sm">
                <i class="fas fa-user-slash text-3xl text-slate-300"></i>
                <p class="mt-3 text-sm font-semibold text-slate-700">Aucun dossier agent associé à votre compte.</p>
                <p class="mt-1 text-xs text-slate-500">Contactez l'administrateur pour lier votre compte à un dossier agent.</p>
            </div>
        @else

        @if ($evalsRecStats['total'] > 0 || $fichesRecStats['total'] > 0)
        <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Mes évaluations {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Répartition par statut</h2>
                <div id="chart-evals-donut" class="mt-3"></div>
            </div>
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Mes objectifs {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Statut des fiches</h2>
                <div id="chart-fiches-donut" class="mt-3"></div>
            </div>
        </div>
        @endif

        {{-- Informations personnelles --}}
        <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-5 shadow-sm">
            <p class="text-xs font-black uppercase tracking-[0.16em] text-slate-500 mb-3">Informations personnelles</p>
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4 text-sm text-slate-700">
                @if ($agent->prenom || $agent->nom)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Nom complet</p>
                        <p class="mt-1 font-semibold">{{ trim($agent->prenom.' '.$agent->nom) }}</p>
                    </div>
                @endif
                @if ($agent->role)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Rôle</p>
                        <p class="mt-1 font-semibold">{{ $agent->role_genree }}</p>
                    </div>
                @endif
                @if ($agent->service)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Service</p>
                        <p class="mt-1 font-semibold">{{ $agent->service->nom }}</p>
                    </div>
                @elseif ($agent->agence)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Agence</p>
                        <p class="mt-1 font-semibold">{{ $agent->agence->nom }}</p>
                    </div>
                @endif
                @if ($agent->email)
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Email</p>
                        <p class="mt-1 font-semibold">{{ $agent->email }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Évaluations récentes --}}
        <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Évaluations</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Mes dernières évaluations · {{ $annee }}</h2>
                </div>
                <a href="{{ route('personnel.mon-espace') }}?tab=evaluations"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-black text-slate-600 transition hover:border-cyan-300 hover:text-cyan-700">
                    Voir tout <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            @if ($evaluationsRecentes->isEmpty())
                <div class="px-6 py-10 text-center">
                    <i class="fas fa-clipboard text-2xl text-slate-200"></i>
                    <p class="mt-2 text-sm text-slate-400">Aucune évaluation pour {{ $annee }}</p>
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach ($evaluationsRecentes as $eval)
                        @php
                            $statClass = match ($eval->statut) {
                                'valide'      => 'bg-emerald-100 text-emerald-700',
                                'soumis'      => 'bg-amber-100 text-amber-700',
                                'refuse'      => 'bg-rose-100 text-rose-700',
                                'reclamation' => 'bg-orange-100 text-orange-700',
                                'a_reviser'   => 'bg-purple-100 text-purple-700',
                                default       => 'bg-slate-100 text-slate-600',
                            };
                            $statLabel = match ($eval->statut) {
                                'valide'      => 'Validée', 'soumis' => 'Soumise',
                                'refuse'      => 'Refusée', 'reclamation' => 'Réclamation',
                                'a_reviser'   => 'À réviser', 'brouillon' => 'Brouillon',
                                default       => ucfirst((string) $eval->statut),
                            };
                            $note = $eval->note_finale !== null ? number_format((float)$eval->note_finale, 2, ',', ' ').'/10' : null;
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50/60">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900">Période {{ $eval->date_debut->format('m/Y') }} – {{ $eval->date_fin->format('m/Y') }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">Par {{ $eval->evaluateur?->name ?? '—' }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($note)
                                    <span class="text-sm font-black text-emerald-700">{{ $note }}</span>
                                @endif
                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $statClass }}">{{ $statLabel }}</span>
                                @if ($eval->statut !== 'brouillon')
                                <a href="{{ route('personnel.evaluations.show', $eval) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700">
                                    <i class="fas fa-eye text-[10px]"></i> Voir
                                </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Fiches d'objectifs récentes --}}
        <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Objectifs</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Mes dernières fiches · {{ $annee }}</h2>
                </div>
                <a href="{{ route('personnel.mon-espace') }}?tab=objectifs"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-black text-slate-600 transition hover:border-emerald-300 hover:text-emerald-700">
                    Voir tout <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            @if ($fichesRecentes->isEmpty())
                <div class="px-6 py-10 text-center">
                    <i class="fas fa-bullseye text-2xl text-slate-200"></i>
                    <p class="mt-2 text-sm text-slate-400">Aucune fiche d'objectifs pour {{ $annee }}</p>
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach ($fichesRecentes as $fiche)
                        @php
                            $statClass = match ($fiche->statut ?? 'en_attente') {
                                'acceptee' => 'bg-emerald-100 text-emerald-700',
                                'refusee'  => 'bg-rose-100 text-rose-700',
                                'contesté' => 'bg-orange-100 text-orange-700',
                                default    => 'bg-amber-100 text-amber-700',
                            };
                            $statLabel = match ($fiche->statut ?? 'en_attente') {
                                'acceptee' => 'Acceptée', 'refusee' => 'Refusée', 'contesté' => 'Contestée', default => 'En attente',
                            };
                            $av = (int)($fiche->avancement_percentage ?? 0);
                            $avColor = $av >= 80 ? 'bg-emerald-500' : ($av >= 50 ? 'bg-sky-500' : ($av >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-4 transition hover:bg-slate-50/60">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900">{{ $fiche->titre }}</p>
                                <div class="mt-1.5 flex items-center gap-3">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $avColor }}" style="width:{{ $av }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500">{{ $av }}% · {{ $fiche->objectifs_count }} obj.</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $statClass }}">{{ $statLabel }}</span>
                                <a href="{{ route('personnel.fiches.show', $fiche) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-black text-slate-600 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700">
                                    <i class="fas fa-eye text-[10px]"></i> Voir
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @endif {{-- end $agent check --}}

    </div>
    </div>

@elseif ($role === 'pca')

    <div class="px-4 pt-6 lg:px-8">

        @if (session('status'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- KPI CARDS --}}
        @php
        $kpis = [
            ['label' => "Fiches d'objectifs", 'value' => $totalFiches,      'icon' => 'fas fa-clipboard-list', 'color' => 'bg-slate-700',   'light' => 'bg-slate-50 border-slate-200'],
            ['label' => 'Acceptées',           'value' => $fichesAcceptees,  'icon' => 'fas fa-circle-check',  'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-100'],
            ['label' => 'En attente',          'value' => $fichesEnAttente,  'icon' => 'fas fa-clock',         'color' => 'bg-amber-500',   'light' => 'bg-amber-50 border-amber-100'],
            ['label' => "Taux d'avancement",   'value' => $tauxAvancement.'%','icon' => 'fas fa-gauge-high',   'color' => 'bg-sky-600',     'light' => 'bg-sky-50 border-sky-100'],
            ['label' => 'Évaluations',         'value' => $evalsTotal,       'icon' => 'fas fa-star',          'color' => 'bg-indigo-600',  'light' => 'bg-indigo-50 border-indigo-100'],
            ['label' => 'Validées',            'value' => $evalsValidees,    'icon' => 'fas fa-check',         'color' => 'bg-teal-600',    'light' => 'bg-teal-50 border-teal-100'],
            ['label' => 'À traiter',           'value' => $evalsSoumises + $evalsBrouillon, 'icon' => 'fas fa-triangle-exclamation', 'color' => 'bg-rose-500', 'light' => 'bg-rose-50 border-rose-100'],
        ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ($kpis as $kpi)
                <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs"><i class="{{ $kpi['icon'] }}"></i></span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- CHARTS + PROFIL DG --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Santé des évaluations {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Répartition par statut</h2>
                <div id="chart-evals-donut" class="mt-3"></div>
            </div>
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Fiches d'objectifs {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Statut des fiches</h2>
                <div id="chart-fiches-donut" class="mt-3"></div>
            </div>
            <div class="rounded-[24px] border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">Profil institutionnel</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Directeur(trice) Général(e)</h2>
                <div class="mt-4 flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-xl font-black text-white shadow-lg shadow-emerald-200/60">
                        {{ $dgInitiale }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-base font-black text-slate-900">{{ $dgNom ?: 'Non renseigné' }}</p>
                        <span class="mt-1 inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-black text-emerald-700">Administration centrale</span>
                    </div>
                </div>
                <dl class="mt-4 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                        <dt class="font-black uppercase tracking-wider text-slate-400">Email</dt>
                        <dd class="mt-1 truncate font-semibold text-slate-700">{{ $entite->dg?->email ?: '—' }}</dd>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                        <dt class="font-black uppercase tracking-wider text-slate-400">Ville</dt>
                        <dd class="mt-1 font-semibold text-slate-700">{{ $entite->ville ?: '—' }}</dd>
                    </div>
                </dl>
                @if ($personnelCabinet->isNotEmpty())
                    <div class="mt-4 space-y-2">
                        @foreach ($personnelCabinet as $p)
                            <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $p['color'] }} text-sm"><i class="{{ $p['icon'] }}"></i></span>
                                <div class="min-w-0">
                                    <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $p['role'] }}</p>
                                    <p class="truncate text-sm font-bold text-slate-800">{{ trim($p['agent']->prenom . ' ' . $p['agent']->nom) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- DG SUIVI + FICHES RÉCENTES --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_340px]">
            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Directeur Général · {{ $annee }}</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Suivi des objectifs &amp; évaluations</h2>
                </div>
                <div class="p-6">
                    @if (!$dgUser)
                        <div class="py-8 text-center">
                            <i class="fas fa-user-slash text-2xl text-slate-200"></i>
                            <p class="mt-2 text-sm text-slate-400">Aucun Directeur Général assigné.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            @foreach ([
                                ['label' => 'Fiches DG',  'value' => $totalFiches,     'color' => 'bg-emerald-50 border-emerald-100',  'text' => 'text-emerald-700', 'icon' => 'fas fa-clipboard-list'],
                                ['label' => 'Acceptées',  'value' => $fichesAcceptees, 'color' => 'bg-teal-50 border-teal-100',        'text' => 'text-teal-700',    'icon' => 'fas fa-circle-check'],
                                ['label' => 'En attente', 'value' => $fichesEnAttente, 'color' => 'bg-amber-50 border-amber-100',      'text' => 'text-amber-700',   'icon' => 'fas fa-clock'],
                                ['label' => 'Avancement', 'value' => $tauxAvancement.'%', 'color' => 'bg-sky-50 border-sky-100',       'text' => 'text-sky-700',     'icon' => 'fas fa-gauge-high'],
                            ] as $s)
                            <div class="rounded-2xl border {{ $s['color'] }} px-4 py-3">
                                <div class="flex items-center gap-2"><i class="{{ $s['icon'] }} text-xs {{ $s['text'] }}"></i><p class="text-[10px] font-black uppercase tracking-wider text-slate-500">{{ $s['label'] }}</p></div>
                                <p class="mt-2 text-2xl font-black {{ $s['text'] }}">{{ $s['value'] }}</p>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                            @foreach ([
                                ['label' => 'Évaluations', 'value' => $evalsTotal,    'color' => 'bg-indigo-50 border-indigo-100',  'text' => 'text-indigo-700',  'icon' => 'fas fa-star'],
                                ['label' => 'Validées',     'value' => $evalsValidees, 'color' => 'bg-emerald-50 border-emerald-100','text' => 'text-emerald-700', 'icon' => 'fas fa-check'],
                                ['label' => 'Soumises',     'value' => $evalsSoumises, 'color' => 'bg-amber-50 border-amber-100',    'text' => 'text-amber-700',   'icon' => 'fas fa-paper-plane'],
                                ['label' => 'Note moy.',    'value' => $noteMoyenne > 0 ? number_format($noteMoyenne, 2, ',', ' ').'/10' : '—', 'color' => 'bg-violet-50 border-violet-100', 'text' => 'text-violet-700', 'icon' => 'fas fa-chart-bar'],
                            ] as $s)
                            <div class="rounded-2xl border {{ $s['color'] }} px-4 py-3">
                                <div class="flex items-center gap-2"><i class="{{ $s['icon'] }} text-xs {{ $s['text'] }}"></i><p class="text-[10px] font-black uppercase tracking-wider text-slate-500">{{ $s['label'] }}</p></div>
                                <p class="mt-2 text-xl font-black {{ $s['text'] }}">{{ $s['value'] }}</p>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">DG · Activité récente</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Fiches d'objectifs {{ $annee }}</h2>
                </div>
                @if ($fichesDGRecentes->isEmpty())
                    <div class="px-5 py-10 text-center">
                        <i class="fas fa-bullseye text-2xl text-slate-200"></i>
                        <p class="mt-2 text-sm text-slate-400">Aucune fiche pour {{ $annee }}</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($fichesDGRecentes as $fiche)
                            @php
                                $sc = match($fiche->statut) {
                                    'acceptee' => 'bg-emerald-100 text-emerald-700', 'en_attente' => 'bg-amber-100 text-amber-700',
                                    'refusee'  => 'bg-rose-100 text-rose-700',       'contesté'   => 'bg-orange-100 text-orange-700',
                                    default    => 'bg-slate-100 text-slate-500',
                                };
                                $sl = match($fiche->statut) {
                                    'acceptee' => 'Acceptée', 'en_attente' => 'En attente', 'refusee' => 'Refusée', 'contesté' => 'Contestée', default => ucfirst($fiche->statut),
                                };
                                $av = (int) ($fiche->avancement_percentage ?? 0);
                                $avBar = $av >= 80 ? 'bg-emerald-500' : ($av >= 50 ? 'bg-sky-500' : ($av >= 25 ? 'bg-amber-400' : 'bg-slate-200'));
                            @endphp
                            <div class="px-5 py-3 hover:bg-slate-50/60 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-800">{{ $fiche->titre }}</p>
                                        <div class="mt-0.5 flex items-center gap-2">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $sc }}">{{ $sl }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $av }}%</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('pca.objectifs.show', $fiche) }}"
                                       class="shrink-0 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </a>
                                </div>
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $avBar }}" style="width:{{ $av }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-slate-100 px-5 py-3">
                        <a href="{{ route('pca.objectifs.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Voir tous les objectifs →</a>
                    </div>
                @endif
            </div>
        </div>

        @if ($fichesEnAttente > 0)
            <div class="mt-5 flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-lg"><i class="fas fa-hourglass-half"></i></span>
                <div class="flex-1">
                    <p class="font-black text-amber-800">{{ $fichesEnAttente }} fiche(s) d'objectifs en attente de validation en {{ $annee }}</p>
                    <p class="text-xs text-amber-600">Ces fiches nécessitent votre examen.</p>
                </div>
                <a href="{{ route('pca.objectifs.index') }}"
                   class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-amber-600">Voir les fiches</a>
            </div>
        @endif

    </div>

@elseif ($role === 'dga')

    <div class="px-4 pt-6 lg:px-8">

        @if (session('status'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- Alerte agents sans évaluation --}}
        @if ($openAnnee && $agentsSansEval > 0)
            @php $dgaSansEvalUrl = request()->fullUrlWithQuery(['sans_eval' => $filters['sansEval'] ? null : 1]); @endphp
            <a href="{{ $dgaSansEvalUrl }}"
               class="mb-4 flex items-center gap-4 rounded-2xl border px-5 py-4 transition hover:shadow-md
                      {{ $filters['sansEval'] ? 'border-orange-400 bg-orange-100' : 'border-orange-200 bg-orange-50' }}">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-orange-800">{{ $agentsSansEval }} subordonné{{ $agentsSansEval > 1 ? 's' : '' }} sans évaluation validée — Année {{ $openAnnee->annee }}</p>
                    <p class="mt-0.5 text-xs text-orange-600">{{ $filters['sansEval'] ? 'Cliquez pour masquer la liste.' : 'Cliquez pour voir la liste.' }}</p>
                </div>
                <span class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-orange-500 px-2 text-xl font-black text-white shadow-sm">{{ $agentsSansEval }}</span>
            </a>
        @endif

        {{-- Liste des subordonnés DGA sans évaluation --}}
        @if ($filters['sansEval'] && $openAnnee)
            <div class="mb-4 rounded-2xl border border-orange-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-orange-100 bg-orange-50 px-6 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-black text-orange-800 shrink-0">Subordonnés sans évaluation validée — {{ $openAnnee->annee }}</p>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1 sm:w-56">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                            <input id="dga-se-search" type="text" placeholder="Rechercher…"
                                   class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pl-7 pr-3 text-xs font-semibold text-slate-700 outline-none focus:border-orange-300 focus:ring-2 focus:ring-orange-100">
                        </div>
                        <span id="dga-se-count" class="shrink-0 rounded-full bg-orange-200 px-2.5 py-0.5 text-xs font-black text-orange-800">{{ $listeSansEval->count() }}</span>
                        <a href="{{ request()->fullUrlWithQuery(['sans_eval' => null]) }}" class="shrink-0 text-xs font-bold text-orange-600 hover:underline">Fermer</a>
                    </div>
                </div>
                @if ($listeSansEval->isEmpty())
                    <div class="px-6 py-8 text-center text-sm text-slate-400">Tous les subordonnés ont une évaluation validée.</div>
                @else
                    <div class="overflow-x-auto">
                        <table id="dga-se-table" class="w-full text-sm">
                            <thead><tr class="border-b border-slate-100 bg-slate-50/70">
                                <th data-col="0" class="se-th cursor-pointer select-none px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Nom <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th data-col="1" class="se-th cursor-pointer select-none px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Prénom <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th data-col="2" class="se-th cursor-pointer select-none px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Rôle <i class="fas fa-sort ml-1 opacity-40"></i></th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($listeSansEval as $u)
                                <tr class="se-row hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $u->agent?->nom ?? $u->name }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $u->agent?->prenom ?? '—' }}</td>
                                    <td class="px-5 py-3 text-xs text-slate-500">{{ $u->role }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- Couverture évaluation --}}
        @if($openAnnee && $totalAgents > 0)
        @php $tauxCouv = $totalAgents > 0 ? round($agentsEvalues / $totalAgents * 100) : 0; @endphp
        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $agentsSansEval === 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}"><i class="fas fa-users-viewfinder"></i></div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Couverture réseau · {{ $openAnnee->annee }}</p>
                        <p class="text-sm font-black text-slate-900">Évaluation des agents</p>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ request()->fullUrlWithQuery(['sans_eval' => null]) }}" class="text-center hover:opacity-75 transition">
                        <p class="text-2xl font-black text-emerald-600">{{ $agentsEvalues }}</p>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Évalués</p>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sans_eval' => 1]) }}" class="text-center hover:opacity-75 transition">
                        <p class="text-2xl font-black {{ $agentsSansEval > 0 ? 'text-amber-500' : 'text-slate-300' }}">{{ $agentsSansEval }}</p>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Restants</p>
                    </a>
                    <div class="text-center"><p class="text-2xl font-black text-slate-700">{{ $totalAgents }}</p><p class="text-[10px] font-bold uppercase text-slate-400">Total</p></div>
                </div>
            </div>
            <div class="px-6 pb-4">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-1.5">
                    <span>Progression réseau</span>
                    <span class="{{ $tauxCouv === 100 ? 'text-emerald-600' : ($tauxCouv >= 50 ? 'text-amber-600' : 'text-rose-600') }}">{{ $tauxCouv }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full transition-all {{ $tauxCouv === 100 ? 'bg-emerald-500' : ($tauxCouv >= 50 ? 'bg-amber-400' : 'bg-rose-500') }}" style="width:{{ $tauxCouv }}%"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- KPI CARDS --}}
        @php
        $kpis = [
            ['label' => 'Fiches reçues',   'value' => $fichesRecStats['total'],       'icon' => 'fas fa-clipboard-list', 'color' => 'bg-slate-700',   'light' => 'bg-slate-50 border-slate-200'],
            ['label' => 'Acceptées',        'value' => $fichesRecStats['acceptees'],   'icon' => 'fas fa-circle-check',   'color' => 'bg-emerald-600', 'light' => 'bg-emerald-50 border-emerald-100'],
            ['label' => 'En attente',       'value' => $fichesRecStats['en_attente'],  'icon' => 'fas fa-clock',          'color' => 'bg-amber-500',   'light' => 'bg-amber-50 border-amber-100'],
            ['label' => 'Avancement moy.',  'value' => $tauxAvancement.'%',            'icon' => 'fas fa-gauge-high',     'color' => 'bg-sky-600',     'light' => 'bg-sky-50 border-sky-100'],
            ['label' => 'Évals. reçues',    'value' => $evalsRecStats['total'],        'icon' => 'fas fa-star',           'color' => 'bg-indigo-600',  'light' => 'bg-indigo-50 border-indigo-100'],
            ['label' => 'Validées',         'value' => $evalsRecStats['valide'],       'icon' => 'fas fa-check',          'color' => 'bg-teal-600',    'light' => 'bg-teal-50 border-teal-100'],
            ['label' => 'Évals. données',   'value' => $subStats['total'],             'icon' => 'fas fa-pen-to-square',  'color' => 'bg-rose-500',    'light' => 'bg-rose-50 border-rose-100'],
        ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ($kpis as $kpi)
                <div class="flex flex-col rounded-2xl border px-4 py-4 shadow-sm {{ $kpi['light'] }}">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[10px] font-black uppercase tracking-[0.13em] text-slate-500 leading-tight">{{ $kpi['label'] }}</p>
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['color'] }} text-white text-xs"><i class="{{ $kpi['icon'] }}"></i></span>
                    </div>
                    <p class="mt-3 text-3xl font-black text-slate-900">{{ $kpi['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- CHARTS + PILOTAGE --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Évaluations reçues {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Répartition par statut</h2>
                <div id="chart-evals-donut" class="mt-3"></div>
            </div>
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Fiches d'objectifs {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Statut des fiches reçues</h2>
                <div id="chart-fiches-donut" class="mt-3"></div>
            </div>
            <div class="rounded-[24px] border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">Pilotage équipe</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Évaluations données</h2>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach ([
                        ['label' => 'Total',      'value' => $subStats['total'],     'tone' => 'bg-slate-50 border-slate-100',     'text' => 'text-slate-700'],
                        ['label' => 'Validées',   'value' => $subStats['valide'],    'tone' => 'bg-emerald-50 border-emerald-100', 'text' => 'text-emerald-700'],
                        ['label' => 'Soumises',   'value' => $subStats['soumis'],    'tone' => 'bg-amber-50 border-amber-100',     'text' => 'text-amber-700'],
                        ['label' => 'Brouillons', 'value' => $subStats['brouillon'], 'tone' => 'bg-slate-50 border-slate-100',     'text' => 'text-slate-500'],
                    ] as $s)
                    <div class="rounded-xl border {{ $s['tone'] }} px-3 py-2.5">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $s['label'] }}</p>
                        <p class="mt-1 text-xl font-black {{ $s['text'] }}">{{ $s['value'] }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2 rounded-xl border border-slate-100 bg-slate-50 p-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Agences</p>
                        <p class="mt-1 text-xl font-black text-slate-700">{{ $reseauStats['agences'] }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">Guichets</p>
                        <p class="mt-1 text-xl font-black text-slate-700">{{ $reseauStats['guichets'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLE ÉVALUATIONS DONNÉES --}}
        <div class="mt-5 rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">DGA · Activité</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Évaluations données récentes</h2>
                </div>
                <form method="GET" action="{{ route('dga.dashboard') }}" class="flex items-center gap-2">
                    <input type="hidden" name="annee" value="{{ $annee }}">
                    <select name="statut" onchange="this.form.submit()"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 outline-none">
                        <option value="" {{ $filters['statut'] === '' ? 'selected' : '' }}>Tous statuts</option>
                        <option value="brouillon" {{ $filters['statut'] === 'brouillon' ? 'selected' : '' }}>Brouillons</option>
                        <option value="soumis"    {{ $filters['statut'] === 'soumis'    ? 'selected' : '' }}>Soumises</option>
                        <option value="valide"    {{ $filters['statut'] === 'valide'    ? 'selected' : '' }}>Validées</option>
                    </select>
                </form>
            </div>
            @if ($evaluations->isEmpty())
                <div class="px-6 py-10 text-center">
                    <i class="fas fa-star text-2xl text-slate-200"></i>
                    <p class="mt-2 text-sm text-slate-400">Aucune évaluation créée.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/70">
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Évalué</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Statut</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Note</th>
                                <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Date</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($evaluations as $eval)
                                @php
                                    $sc = match($eval->statut) { 'valide' => 'bg-emerald-100 text-emerald-700', 'soumis' => 'bg-amber-100 text-amber-700', 'brouillon' => 'bg-slate-100 text-slate-500', 'refuse' => 'bg-rose-100 text-rose-700', default => 'bg-slate-100 text-slate-500' };
                                    $sl = match($eval->statut) { 'valide' => 'Validée', 'soumis' => 'Soumise', 'brouillon' => 'Brouillon', 'refuse' => 'Refusée', default => ucfirst($eval->statut) };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3 font-semibold text-slate-800">
                                        {{ $eval->identification?->nom_prenom ?? '—' }}
                                        @if ($eval->identification?->emploi)
                                            <span class="block text-[10px] font-normal text-slate-400">{{ $eval->identification->emploi }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $sc }}">{{ $sl }}</span></td>
                                    <td class="px-5 py-3 font-black text-slate-700">{{ $eval->note_finale ? number_format($eval->note_finale, 2, ',', ' ').'/10' : '—' }}</td>
                                    <td class="px-5 py-3 text-slate-500">{{ $eval->date_debut?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <a href="{{ route('dga.sub-evaluations.show', $eval) }}"
                                           class="inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition">
                                            <i class="fas fa-eye text-[10px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-3">{{ $evaluations->links() }}</div>
            @endif
        </div>

        @if ($fichesRecStats['en_attente'] > 0)
            <div class="mt-5 flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-lg"><i class="fas fa-hourglass-half"></i></span>
                <div class="flex-1"><p class="font-black text-amber-800">{{ $fichesRecStats['en_attente'] }} fiche(s) d'objectifs en attente de votre validation</p></div>
                <a href="{{ route('dga.mon-espace') }}"
                   class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-amber-600">Mon espace</a>
            </div>
        @endif

    </div>

@else {{-- chef / directeur --}}

    <div class="px-4 pt-6 lg:px-8">

        @if (session('status'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- Alerte agents sans évaluation --}}
        @if ($openAnnee && $agentsSansEval > 0)
            @php $chefSansEvalUrl = request()->fullUrlWithQuery(['sans_eval' => (request()->boolean('sans_eval') ? null : 1)]); @endphp
            <a href="{{ $chefSansEvalUrl }}"
               class="mb-4 flex items-center gap-4 rounded-2xl border px-5 py-4 transition hover:shadow-md
                      {{ request()->boolean('sans_eval') ? 'border-orange-400 bg-orange-100' : 'border-orange-200 bg-orange-50' }}">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-orange-800">{{ $agentsSansEval }} agent{{ $agentsSansEval > 1 ? 's' : '' }} sans évaluation validée — Année {{ $openAnnee->annee }}</p>
                    <p class="mt-0.5 text-xs text-orange-600">{{ request()->boolean('sans_eval') ? 'Cliquez pour masquer la liste.' : 'Cliquez pour voir la liste.' }}</p>
                </div>
                <span class="flex h-10 min-w-[2.5rem] items-center justify-center rounded-xl bg-orange-500 px-2 text-xl font-black text-white shadow-sm">{{ $agentsSansEval }}</span>
            </a>
        @endif

        {{-- Liste des agents sans évaluation --}}
        @if (request()->boolean('sans_eval') && $openAnnee)
            <div class="mb-4 rounded-2xl border border-orange-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-orange-100 bg-orange-50 px-6 py-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-black text-orange-800 shrink-0">Agents sans évaluation validée — {{ $openAnnee->annee }}</p>
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1 sm:w-56">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                            <input id="chef-se-search" type="text" placeholder="Rechercher…"
                                   class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pl-7 pr-3 text-xs font-semibold text-slate-700 outline-none focus:border-orange-300 focus:ring-2 focus:ring-orange-100">
                        </div>
                        <span id="chef-se-count" class="shrink-0 rounded-full bg-orange-200 px-2.5 py-0.5 text-xs font-black text-orange-800">{{ $listeSansEval->count() }}</span>
                        <a href="{{ request()->fullUrlWithQuery(['sans_eval' => null]) }}" class="shrink-0 text-xs font-bold text-orange-600 hover:underline">Fermer</a>
                    </div>
                </div>
                @if ($listeSansEval->isEmpty())
                    <div class="px-6 py-8 text-center text-sm text-slate-400">Tous les agents ont une évaluation validée.</div>
                @else
                    <div class="overflow-x-auto">
                        <table id="chef-se-table" class="w-full text-sm">
                            <thead><tr class="border-b border-slate-100 bg-slate-50/70">
                                <th data-col="0" class="se-th cursor-pointer select-none px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Nom <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th data-col="1" class="se-th cursor-pointer select-none px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Prénom <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th data-col="2" class="se-th cursor-pointer select-none px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Matricule <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th data-col="3" class="se-th cursor-pointer select-none px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 hover:text-orange-600">Fonction <i class="fas fa-sort ml-1 opacity-40"></i></th>
                                <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Téléphone</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach ($listeSansEval as $a)
                                <tr class="se-row hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3 font-semibold text-slate-800">{{ $a->nom }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $a->prenom }}</td>
                                    <td class="px-5 py-3 text-xs text-slate-500">{{ $a->matricule ?? '—' }}</td>
                                    <td class="px-5 py-3 text-xs text-slate-500">{{ $a->role ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        @if ($a->numero_telephone)
                                            <a href="tel:{{ $a->numero_telephone }}"
                                               class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition">
                                                <i class="fas fa-phone text-[10px]"></i>{{ $a->numero_telephone }}
                                            </a>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- Couverture évaluation --}}
        @if($openAnnee && $totalAgents > 0)
        @php $tauxCouv = $totalAgents > 0 ? round($agentsEvalues / $totalAgents * 100) : 0; @endphp
        <div class="mb-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $agentsSansEval === 0 ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}"><i class="fas fa-users-viewfinder"></i></div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Couverture · {{ $openAnnee->annee }}</p>
                        <p class="text-sm font-black text-slate-900">Évaluation des agents</p>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <a href="{{ request()->fullUrlWithQuery(['sans_eval' => null]) }}" class="text-center hover:opacity-75 transition">
                        <p class="text-2xl font-black text-emerald-600">{{ $agentsEvalues }}</p>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Évalués</p>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sans_eval' => 1]) }}" class="text-center hover:opacity-75 transition">
                        <p class="text-2xl font-black {{ $agentsSansEval > 0 ? 'text-amber-500' : 'text-slate-300' }}">{{ $agentsSansEval }}</p>
                        <p class="text-[10px] font-bold uppercase text-slate-400">Restants</p>
                    </a>
                    <div class="text-center"><p class="text-2xl font-black text-slate-700">{{ $totalAgents }}</p><p class="text-[10px] font-bold uppercase text-slate-400">Total</p></div>
                </div>
            </div>
            <div class="px-6 pb-4">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-1.5">
                    <span>Progression</span>
                    <span class="{{ $tauxCouv === 100 ? 'text-emerald-600' : ($tauxCouv >= 50 ? 'text-amber-600' : 'text-rose-600') }}">{{ $tauxCouv }}%</span>
                </div>
                <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full transition-all {{ $tauxCouv === 100 ? 'bg-emerald-500' : ($tauxCouv >= 50 ? 'bg-amber-400' : 'bg-rose-500') }}" style="width:{{ $tauxCouv }}%"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- KPI CARDS --}}
        @php
        $kpis = [
            ['label' => 'Fiches reçues',   'value' => $fichesRecStats['total'],      'meta' => $role === 'chef' ? 'Objectifs assignés au chef'      : 'Objectifs assignés au directeur',  'icon' => 'fas fa-clipboard-list', 'valueClass' => 'text-slate-700',   'iconClass' => 'bg-slate-100 text-slate-600',    'href' => route($role.'.dashboard')],
            ['label' => 'Acceptées',       'value' => $fichesRecStats['acceptees'],  'meta' => 'Fiches objectifs acceptées',                                                                'icon' => 'fas fa-circle-check',   'valueClass' => 'text-emerald-600', 'iconClass' => 'bg-emerald-50 text-emerald-600', 'href' => route($role.'.dashboard')],
            ['label' => 'En attente',      'value' => $fichesRecStats['en_attente'], 'meta' => 'Fiches en cours de traitement',                                                             'icon' => 'fas fa-clock',          'valueClass' => 'text-amber-500',   'iconClass' => 'bg-amber-50 text-amber-500',     'href' => route($role.'.dashboard')],
            ['label' => 'Avancement moy.', 'value' => $tauxAvancement.'%',           'meta' => 'Taux moyen de réalisation',                                                                 'icon' => 'fas fa-gauge-high',     'valueClass' => 'text-sky-500',     'iconClass' => 'bg-sky-50 text-sky-500',         'href' => route($role.'.dashboard')],
            ['label' => 'Évals. reçues',   'value' => $evalsRecStats['total'],       'meta' => $role === 'chef' ? 'Évaluations reçues du directeur' : 'Évaluations reçues du DGA/DG',      'icon' => 'fas fa-star',           'valueClass' => 'text-indigo-500',  'iconClass' => 'bg-indigo-50 text-indigo-500',   'href' => route($role.'.dashboard')],
            ['label' => 'Validées',        'value' => $evalsRecStats['valide'],      'meta' => 'Évaluations acceptées et validées',                                                         'icon' => 'fas fa-check',          'valueClass' => 'text-teal-600',    'iconClass' => 'bg-teal-50 text-teal-600',       'href' => route($role.'.dashboard')],
            ['label' => 'Évals. données',  'value' => $evalsGivStats['total'],       'meta' => $role === 'chef' ? 'Évaluations données à vos agents' : 'Évaluations données aux chefs',    'icon' => 'fas fa-pen-to-square',  'valueClass' => 'text-rose-500',    'iconClass' => 'bg-rose-50 text-rose-500',       'href' => route($role.'.mon-espace')],
        ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-7">
            @foreach ($kpis as $kpi)
                <article class="rounded-[20px] border border-slate-100 bg-white px-4 py-3 shadow-[0_12px_30px_-24px_rgba(15,23,42,0.3)]">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 leading-tight">{{ $kpi['label'] }}</p>
                            <p class="mt-2 text-3xl font-black tracking-tight {{ $kpi['valueClass'] }}">{{ $kpi['value'] }}</p>
                            <p class="mt-1 line-clamp-1 text-[11px] font-bold text-slate-400">{{ $kpi['meta'] }}</p>
                        </div>
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ $kpi['iconClass'] }}"><i class="{{ $kpi['icon'] }} text-base"></i></div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <a href="{{ $kpi['href'] }}" class="inline-flex h-8 items-center rounded-xl bg-slate-50 px-3 text-[10px] font-black uppercase tracking-[0.14em] text-slate-700 transition hover:bg-slate-900 hover:text-white">Ouvrir</a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- CHARTS + ÉQUIPE --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-3">
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Évaluations reçues {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Répartition par statut</h2>
                <div id="chart-evals-donut" class="mt-3"></div>
            </div>
            <div class="rounded-[24px] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Fiches d'objectifs {{ $annee }}</p>
                <h2 class="mt-1 text-base font-black text-slate-900">Statut des fiches reçues</h2>
                <div id="chart-fiches-donut" class="mt-3"></div>
            </div>
            <div class="rounded-[24px] border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-600">
                    {{ $role === 'chef' ? 'Mon équipe' : 'Pilotage équipe' }} {{ $annee }}
                </p>
                <h2 class="mt-1 text-base font-black text-slate-900">Évaluations données</h2>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    @foreach ([
                        ['label' => 'Total',      'value' => $evalsGivStats['total'],     'tone' => 'bg-slate-50 border-slate-100',     'text' => 'text-slate-700'],
                        ['label' => 'Validées',   'value' => $evalsGivStats['valide'],    'tone' => 'bg-emerald-50 border-emerald-100', 'text' => 'text-emerald-700'],
                        ['label' => 'Soumises',   'value' => $evalsGivStats['soumis'],    'tone' => 'bg-amber-50 border-amber-100',     'text' => 'text-amber-700'],
                        ['label' => 'Brouillons', 'value' => $evalsGivStats['brouillon'], 'tone' => 'bg-slate-50 border-slate-100',     'text' => 'text-slate-500'],
                    ] as $s)
                    <div class="rounded-xl border {{ $s['tone'] }} px-3 py-2.5">
                        <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $s['label'] }}</p>
                        <p class="mt-1 text-xl font-black {{ $s['text'] }}">{{ $s['value'] }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 flex gap-2">
                    <a href="{{ route($role.'.mon-espace') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                        <i class="fas fa-folder-open text-[10px]"></i> Mon espace
                    </a>
                </div>
            </div>
        </div>

        {{-- TEAM + FICHES RÉCENTES --}}
        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_340px]">

            @if ($role === 'chef')
            {{-- Agents overview --}}
            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Mon équipe · {{ $annee }}</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Statut des agents</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($agentsOverview as $ao)
                        @php
                            $sc = match($ao['eval_statut']) {
                                'valide' => 'bg-emerald-100 text-emerald-700', 'soumis' => 'bg-amber-100 text-amber-700',
                                'brouillon' => 'bg-slate-100 text-slate-500',  'refuse' => 'bg-rose-100 text-rose-700',
                                'reclamation' => 'bg-orange-100 text-orange-700', default => 'bg-slate-100 text-slate-400',
                            };
                            $sl = match($ao['eval_statut']) {
                                'valide' => 'Validée', 'soumis' => 'Soumise', 'brouillon' => 'Brouillon',
                                'refuse' => 'Refusée', 'reclamation' => 'Réclamation', default => 'Non évalué',
                            };
                        @endphp
                        <div class="flex items-center justify-between gap-3 px-6 py-3 hover:bg-slate-50/60 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 font-black text-sm">
                                    {{ strtoupper(substr($ao['agent']->prenom ?? $ao['agent']->nom ?? 'A', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-800">{{ trim($ao['agent']->prenom.' '.$ao['agent']->nom) }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $ao['agent']->poste ?? $ao['agent']->role ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $sc }}">{{ $sl }}</span>
                                @if ($ao['eval_note'])
                                    <span class="text-sm font-black text-slate-700">{{ number_format($ao['eval_note'], 1) }}/10</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <i class="fas fa-users text-2xl text-slate-200"></i>
                            <p class="mt-2 text-sm text-slate-400">Aucun agent dans votre équipe.</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-slate-100 px-6 py-3">
                    <a href="{{ route('chef.mon-espace') }}?tab=agents" class="text-xs font-bold text-emerald-600 hover:underline">Voir tous les agents →</a>
                </div>
            </div>
            @else
            {{-- Services overview --}}
            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Mon équipe · {{ $annee }}</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Suivi des services</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($servicesOverview as $s)
                        @php
                            $es = $s['eval'];
                            $sc = match($es?->statut) { 'valide' => 'bg-emerald-100 text-emerald-700', 'soumis' => 'bg-amber-100 text-amber-700', default => 'bg-slate-100 text-slate-500' };
                            $sl = match($es?->statut) { 'valide' => 'Validée', 'soumis' => 'Soumise', default => 'Non évalué' };
                        @endphp
                        <div class="flex items-center justify-between gap-3 px-6 py-3 hover:bg-slate-50/60 transition">
                            <a href="{{ route('directeur.subordonnes.service', $s['service']) }}" class="flex items-center gap-3 min-w-0">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 text-sm"><i class="fas fa-sitemap"></i></span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-800 hover:text-emerald-600 transition">{{ $s['service']->nom }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $s['agents_count'] }} agent(s)</p>
                                </div>
                            </a>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $sc }}">{{ $sl }}</span>
                                @if ($es?->note_finale)
                                    <span class="text-sm font-black text-slate-700">{{ number_format($es->note_finale, 1) }}/10</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <i class="fas fa-sitemap text-2xl text-slate-200"></i>
                            <p class="mt-2 text-sm text-slate-400">Aucun service rattaché.</p>
                        </div>
                    @endforelse
                </div>
                <div class="border-t border-slate-100 px-6 py-3">
                    <a href="{{ route('directeur.mon-espace') }}" class="text-xs font-bold text-emerald-600 hover:underline">Voir tout mon espace →</a>
                </div>
            </div>
            @endif

            {{-- Fiches récentes reçues --}}
            <div class="rounded-[24px] border border-slate-100 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-4">
                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Activité récente</p>
                    <h2 class="mt-0.5 text-base font-black text-slate-900">Fiches {{ $role === 'chef' ? 'reçues' : "d'objectifs" }} {{ $annee }}</h2>
                </div>
                @if ($fichesRecentes->isEmpty())
                    <div class="px-5 py-10 text-center">
                        <i class="fas fa-bullseye text-2xl text-slate-200"></i>
                        <p class="mt-2 text-sm text-slate-400">Aucune fiche pour {{ $annee }}</p>
                    </div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($fichesRecentes as $fiche)
                            @php
                                $sc = match($fiche->statut) {
                                    'acceptee'   => 'bg-emerald-100 text-emerald-700', 'en_attente' => 'bg-amber-100 text-amber-700',
                                    'refusee'    => 'bg-rose-100 text-rose-700',       'contesté'   => 'bg-orange-100 text-orange-700',
                                    default      => 'bg-slate-100 text-slate-500',
                                };
                                $sl = match($fiche->statut) {
                                    'acceptee' => 'Acceptée', 'en_attente' => 'En attente', 'refusee' => 'Refusée', default => ucfirst((string)$fiche->statut),
                                };
                                $av    = (int) ($fiche->avancement_percentage ?? 0);
                                $avBar = $av >= 80 ? 'bg-emerald-500' : ($av >= 50 ? 'bg-sky-500' : ($av >= 25 ? 'bg-amber-400' : 'bg-slate-200'));
                            @endphp
                            <div class="px-5 py-3 hover:bg-slate-50/60 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-slate-800">{{ $fiche->titre }}</p>
                                        <div class="mt-0.5 flex items-center gap-2">
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold {{ $sc }}">{{ $sl }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $av }}%</span>
                                        </div>
                                    </div>
                                    <a href="{{ $role === 'chef' ? route('chef.mes-fiches.show', $fiche) : route('directeur.objectifs.show', $fiche) }}"
                                       class="shrink-0 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </a>
                                </div>
                                <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $avBar }}" style="width:{{ $av }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-slate-100 px-5 py-3">
                        <a href="{{ route($role.'.mon-espace') }}?tab=objectifs" class="text-xs font-bold text-emerald-600 hover:underline">Voir toutes les fiches →</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Alerte fiches en attente --}}
        @if ($fichesRecStats['en_attente'] > 0)
            <div class="mt-5 flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-4 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 text-lg"><i class="fas fa-hourglass-half"></i></span>
                <div class="flex-1">
                    <p class="font-black text-amber-800">{{ $fichesRecStats['en_attente'] }} fiche(s) d'objectifs en attente de votre validation</p>
                    @if ($role === 'directeur')
                        <p class="text-xs text-amber-600">Ces fiches nécessitent votre acceptation ou refus.</p>
                    @endif
                </div>
                <a href="{{ route($role.'.mon-espace') }}?tab=objectifs"
                   class="shrink-0 rounded-xl bg-amber-500 px-4 py-2 text-xs font-black text-white shadow-sm transition hover:bg-amber-600">Voir</a>
            </div>
        @endif

    </div>

@endif

</div>
@endsection

@push('scripts')
<script>
window._dashData = {
    evalsDonut:  {!! json_encode($evalsDonut) !!},
    fichesDonut: {!! json_encode($fichesDonut) !!},
};
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var d = window._dashData;
    function donutOptions(data) {
        return {
            chart: { type: 'donut', height: 200, fontFamily: 'Inter, sans-serif' },
            labels: data.labels, series: data.series, colors: data.colors,
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true,
                total: { show: true, label: 'Total', fontSize: '12px', fontWeight: 700,
                    color: '#475569', formatter: function(w) { return w.globals.seriesTotals.reduce(function(a,b){return a+b;},0); } }
            } } } },
            legend: { position: 'bottom', fontSize: '11px', fontWeight: 600 },
            dataLabels: { enabled: false }, stroke: { width: 2 },
        };
    }
    if (document.querySelector('#chart-evals-donut'))  new ApexCharts(document.querySelector('#chart-evals-donut'),  donutOptions(d.evalsDonut)).render();
    if (document.querySelector('#chart-fiches-donut')) new ApexCharts(document.querySelector('#chart-fiches-donut'), donutOptions(d.fichesDonut)).render();
});
</script>

<script>
// ── Recherche + tri des tables "sans évaluation" ───────────────────────────
(function () {
    function initSETable(tableId, searchId, countId) {
        var table  = document.getElementById(tableId);
        var search = document.getElementById(searchId);
        var count  = document.getElementById(countId);
        if (!table || !search) return;
        var tbody   = table.querySelector('tbody');
        var sortCol = -1, sortAsc = true;

        function filterAndCount() {
            var q    = search.value.trim().toLowerCase();
            var rows = tbody.querySelectorAll('tr.se-row');
            var n    = 0;
            rows.forEach(function (tr) {
                var show = q === '' || tr.textContent.toLowerCase().includes(q);
                tr.classList.toggle('hidden', !show);
                if (show) n++;
            });
            if (count) count.textContent = n;
        }

        search.addEventListener('input', filterAndCount);

        table.querySelectorAll('th.se-th').forEach(function (th) {
            th.addEventListener('click', function () {
                var col = parseInt(th.dataset.col);
                if (sortCol === col) { sortAsc = !sortAsc; } else { sortCol = col; sortAsc = true; }

                table.querySelectorAll('th.se-th').forEach(function (h) {
                    var icon = h.querySelector('i');
                    if (!icon) return;
                    icon.className = h === th
                        ? (sortAsc ? 'fas fa-sort-up ml-1 text-orange-500' : 'fas fa-sort-down ml-1 text-orange-500')
                        : 'fas fa-sort ml-1 opacity-40';
                });

                var rows = Array.from(tbody.querySelectorAll('tr.se-row'));
                rows.sort(function (a, b) {
                    var va = a.cells[col] ? a.cells[col].textContent.trim().toLowerCase() : '';
                    var vb = b.cells[col] ? b.cells[col].textContent.trim().toLowerCase() : '';
                    return sortAsc ? va.localeCompare(vb, 'fr') : vb.localeCompare(va, 'fr');
                });
                rows.forEach(function (tr) { tbody.appendChild(tr); });
            });
        });
    }

    initSETable('dga-se-table',  'dga-se-search',  'dga-se-count');
    initSETable('chef-se-table', 'chef-se-search', 'chef-se-count');
})();
</script>
@endpush
