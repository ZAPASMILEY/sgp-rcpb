<div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    <a href="{{ route('admin.entites.directions.index') }}" class="rounded-[1.6rem] bg-gradient-to-br from-emerald-500 to-emerald-400 p-5 text-white shadow-[0_18px_38px_rgba(16,185,129,0.25)] transition hover:-translate-y-1">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/75">Total directions</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $totalDirections }}</p>
            </div>
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20">
                <i class="fas fa-sitemap text-lg"></i>
            </span>
        </div>
        <p class="mt-5 text-sm font-medium text-white/90">Structures de pilotage</p>
    </a>

    <a href="{{ route('admin.services.index') }}" class="rounded-[1.6rem] bg-gradient-to-br from-sky-500 to-cyan-400 p-5 text-white shadow-[0_18px_38px_rgba(14,165,233,0.24)] transition hover:-translate-y-1">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/75">Total services</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $totalServices }}</p>
            </div>
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20">
                <i class="fas fa-layer-group text-lg"></i>
            </span>
        </div>
        <p class="mt-5 text-sm font-medium text-white/90">Unites de travail</p>
    </a>

    <a href="{{ route('admin.agents.index') }}" class="rounded-[1.6rem] bg-gradient-to-br from-violet-500 to-fuchsia-500 p-5 text-white shadow-[0_18px_38px_rgba(139,92,246,0.24)] transition hover:-translate-y-1">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/75">Total agents</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $totalAgents }}</p>
            </div>
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20">
                <i class="fas fa-users text-lg"></i>
            </span>
        </div>
        <p class="mt-5 text-sm font-medium text-white/90">Personnel actif du systeme</p>
    </a>

    <a href="{{ route('admin.delegations-techniques.secretaires.index') }}" class="rounded-[1.6rem] bg-gradient-to-br from-orange-500 to-amber-400 p-5 text-white shadow-[0_18px_38px_rgba(249,115,22,0.24)] transition hover:-translate-y-1">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/75">Total secretaires</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $totalSecretaires }}</p>
            </div>
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20">
                <i class="fas fa-user-tie text-lg"></i>
            </span>
        </div>
        <p class="mt-5 text-sm font-medium text-white/90">Appui administratif et secretariat</p>
    </a>
</div>
