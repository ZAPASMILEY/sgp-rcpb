@extends($layout)

@section('title', 'Mon Espace | '.config('app.name', 'SGP-RCPB'))

{{--
    Classes Tailwind utilisées dynamiquement via $themeEval / $themeFiche.
    Listées ici pour que le compilateur ne les purge pas.

    text-emerald-700  bg-emerald-100  border-emerald-300  ring-emerald-100  hover:bg-emerald-100  hover:text-emerald-600
    text-violet-700   bg-violet-100   border-violet-300   ring-violet-100   hover:bg-violet-100   hover:text-violet-600
    text-blue-700     bg-blue-100     border-blue-300     ring-blue-100     hover:bg-blue-100     hover:text-blue-600
    text-cyan-700     bg-cyan-100     border-cyan-300     ring-cyan-100     hover:bg-cyan-100     hover:text-cyan-600
    text-indigo-700   bg-indigo-100   border-indigo-300   ring-indigo-100   hover:bg-indigo-100   hover:text-indigo-600
    text-teal-700     bg-teal-100
    focus:border-emerald-300  focus:ring-emerald-100
    focus:border-violet-300   focus:ring-violet-100
    focus:border-blue-300     focus:ring-blue-100
    focus:border-cyan-300     focus:ring-cyan-100
    focus:border-indigo-300   focus:ring-indigo-100
--}}

@section('content')
@php
// ── Classes de thème pour les tabs ────────────────────────────────────────────
$evalTabActive = match ($themeEval) {
    'violet' => 'border border-slate-200 bg-white text-violet-700 shadow-sm',
    'blue'   => 'border border-slate-200 bg-white text-blue-700 shadow-sm',
    'cyan'   => 'border border-slate-200 bg-white text-cyan-700 shadow-sm',
    default  => 'border border-slate-200 bg-white text-emerald-700 shadow-sm',
};
$evalTabBadge = match ($themeEval) {
    'violet' => 'bg-violet-100 text-violet-700',
    'blue'   => 'bg-blue-100 text-blue-700',
    'cyan'   => 'bg-cyan-100 text-cyan-700',
    default  => 'bg-emerald-100 text-emerald-700',
};
$ficheTabActive = match ($themeFiche) {
    'violet' => 'border border-slate-200 bg-white text-violet-700 shadow-sm',
    'blue'   => 'border border-slate-200 bg-white text-blue-700 shadow-sm',
    'indigo' => 'border border-slate-200 bg-white text-indigo-700 shadow-sm',
    'teal'   => 'border border-slate-200 bg-white text-teal-700 shadow-sm',
    default  => 'border border-slate-200 bg-white text-emerald-700 shadow-sm',
};
$ficheTabBadge = match ($themeFiche) {
    'violet' => 'bg-violet-100 text-violet-700',
    'blue'   => 'bg-blue-100 text-blue-700',
    'indigo' => 'bg-indigo-100 text-indigo-700',
    'teal'   => 'bg-teal-100 text-teal-700',
    default  => 'bg-emerald-100 text-emerald-700',
};
$filterFocus = match ($themeEval) {
    'violet' => 'focus:border-violet-300 focus:ring-4 focus:ring-violet-100',
    'blue'   => 'focus:border-blue-300 focus:ring-4 focus:ring-blue-100',
    'cyan'   => 'focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100',
    default  => 'focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100',
};
$evalBtnHover = match ($themeEval) {
    'violet' => 'hover:bg-violet-100 hover:text-violet-600',
    'blue'   => 'hover:bg-blue-100 hover:text-blue-600',
    'cyan'   => 'hover:bg-cyan-100 hover:text-cyan-600',
    default  => 'hover:bg-emerald-100 hover:text-emerald-600',
};
$ficheBtnHover = match ($themeFiche) {
    'violet' => 'hover:bg-violet-100 hover:text-violet-600',
    'blue'   => 'hover:bg-blue-100 hover:text-blue-600',
    'indigo' => 'hover:bg-indigo-100 hover:text-indigo-600',
    'teal'   => 'hover:bg-teal-100 hover:text-teal-700',
    default  => 'hover:bg-emerald-100 hover:text-emerald-600',
};
@endphp

@if ($useHeroHeader)
{{-- ════════════════════════════════════════════════════════════════════════════
     LAYOUT HERO (Assistante_Dg / Conseillers_Dg)
     ════════════════════════════════════════════════════════════════════════════ --}}
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden px-6 py-8 lg:px-10" style="background:linear-gradient(135deg,#003d20 0%,#005c30 50%,#008751 100%)">
        <div class="pointer-events-none absolute inset-0 opacity-10">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/30 blur-3xl"></div>
            <div class="absolute -bottom-16 left-10 h-48 w-48 rounded-full bg-teal-300/40 blur-2xl"></div>
        </div>
        <div class="relative flex items-center gap-5">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-black text-white shadow-inner ring-2 ring-white/20">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.25em] text-white/70">{{ $headerSubtitle }} · RCPB</p>
                <h1 class="mt-0.5 text-2xl font-black tracking-tight text-white">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-white/60">Synthèse du {{ now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>
        {{-- Mini KPIs dans le hero --}}
        <div class="relative mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($outerStats as $m)
            <div class="flex items-center gap-3 rounded-xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur-sm">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/15 text-white text-sm">
                    <i class="{{ $m['icon'] }}"></i>
                </span>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.15em] text-white/70">{{ $m['label'] }}</p>
                    <p class="text-lg font-black text-white">{{ $m['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        {{-- Section assistante (Assistante_Dg uniquement) --}}
        @if ($user->role === 'Assistante_Dg')
            <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-5 shadow-sm lg:px-8">
                <p class="mb-4 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Mes subordonnés</p>
                <a href="{{ route('assistante.secretaire') }}"
                   class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm transition hover:border-indigo-300 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                            <i class="fas fa-user-tie"></i>
                        </span>
                        <div>
                            <p class="font-black text-slate-900">Ma secrétaire</p>
                            <p class="text-xs text-slate-500">Gérer les évaluations et objectifs</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-right text-xs text-slate-400"></i>
                </a>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="{{ $tabPanelClass }}">
            @include('mon-espace.partials.tab-nav')
            @include('mon-espace.partials.tab-content')
        </div>

    </div>
    </div>
</div>

@else
{{-- ════════════════════════════════════════════════════════════════════════════
     LAYOUT STANDARD (DG, DGA, Directeur, Chef, Personnel)
     ════════════════════════════════════════════════════════════════════════════ --}}
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="w-full flex flex-col gap-6">

    {{-- ── En-tête ──────────────────────────────────────────────────────────── --}}
    <header class="admin-panel px-6 py-6 lg:px-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $headerSubtitle }}</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $user->name }}</h1>
                {{-- Ligne de détail : ctx (Directeur/Chef), agent (Personnel), ou texte simple --}}
                @isset($ctx)
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $ctx->getTypeLabel() }} :
                        <span class="font-semibold text-blue-700">{{ $ctx->getNom() }}</span>
                        @if (method_exists($ctx, 'getParentNom') && $ctx->getParentNom())
                            <span class="text-slate-400"> — {{ $ctx->getParentNom() }}</span>
                        @endif
                    </p>
                @elseif (isset($agent) && $agent)
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $agent->role ?? $user->role }}
                        @if ($agent->service) · {{ $agent->service->nom }}
                        @elseif ($agent->agence) · {{ $agent->agence->nom }}
                        @endif
                    </p>
                @elseif (!empty($headerDetail))
                    <p class="mt-1 text-sm text-slate-500">{{ $headerDetail }}</p>
                @endif
            </div>
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $avatarClasses }} font-black text-xl shadow-sm">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <i class="fas fa-circle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif

    {{-- Grille KPI (DG, DGA, Directeur) --}}
    @if (!empty($outerStats))
        @php
            $accentKpi = [
                'border-emerald-100 bg-emerald-50/80 text-emerald-900' => 'bg-emerald-500',
                'border-teal-100 bg-teal-50/80 text-teal-900'          => 'bg-teal-500',
                'border-violet-100 bg-violet-50/80 text-violet-900'    => 'bg-violet-500',
                'border-amber-100 bg-amber-50/80 text-amber-900'       => 'bg-amber-400',
                'border-rose-100 bg-rose-50/80 text-rose-900'          => 'bg-rose-500',
                'border-sky-100 bg-sky-50/80 text-sky-900'             => 'bg-sky-500',
                'border border-blue-100 bg-blue-50 text-blue-900'      => 'bg-blue-500',
                'border border-emerald-100 bg-emerald-50 text-emerald-900' => 'bg-emerald-500',
                'border border-slate-200 bg-slate-50 text-slate-900'   => 'bg-slate-400',
                'border border-teal-100 bg-teal-50 text-teal-900'      => 'bg-teal-500',
                'border-slate-100 bg-white text-slate-900'             => 'bg-slate-300',
            ];
        @endphp
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ($outerStats as $kpi)
                @php $accent = $accentKpi[$kpi['tone']] ?? 'bg-slate-300'; @endphp
                <div class="relative overflow-hidden rounded-2xl border shadow-sm {{ $kpi['tone'] }}">
                    <div class="absolute inset-y-0 left-0 w-1 {{ $accent }}"></div>
                    <div class="px-5 py-4 pl-6">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-55 leading-tight">{{ $kpi['label'] }}</p>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg {{ $kpi['iw'] }} text-xs">
                                <i class="{{ $kpi['icon'] }}"></i>
                            </span>
                        </div>
                        <p class="mt-2 text-4xl font-black leading-none text-slate-900">{{ $kpi['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Informations personnelles (Personnel uniquement) --}}
    @if (isset($agent) && $agent)
        <div class="admin-panel px-6 py-5">
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
                        <p class="mt-1 font-semibold">{{ $agent->role }}</p>
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
    @elseif (isset($agent) && !$agent)
        {{-- Compte non lié à un dossier agent --}}
        <div class="rounded-[24px] border border-slate-100 bg-white px-6 py-12 text-center shadow-sm">
            <i class="fas fa-user-slash text-3xl text-slate-300"></i>
            <p class="mt-3 text-sm font-semibold text-slate-700">Aucun dossier agent associé à votre compte.</p>
            <p class="mt-1 text-xs text-slate-500">Contactez l'administrateur pour lier votre compte à un dossier agent.</p>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="{{ $tabPanelClass }}">
        @include('mon-espace.partials.tab-nav')
        @include('mon-espace.partials.tab-content')
    </div>

</div>
</div>
@endif

@endsection
