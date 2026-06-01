{{-- ── Navigation par onglets ──────────────────────────────────────────────── --}}
<div class="mb-6 flex flex-wrap items-center gap-4">
    <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
        <a href="{{ $monEspaceUrl }}?tab=evaluations"
           class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
               {{ $tab === 'evaluations' ? $evalTabActive : 'text-slate-500 hover:text-slate-800' }}">
            <i class="fas fa-star-half-stroke text-xs"></i>
            Mes évaluations
            <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                {{ $tab === 'evaluations' ? $evalTabBadge : 'bg-slate-200 text-slate-600' }}">
                {{ $evaluationsStats['total'] }}
            </span>
        </a>
        <a href="{{ $monEspaceUrl }}?tab=objectifs"
           class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
               {{ $tab === 'objectifs' ? $ficheTabActive : 'text-slate-500 hover:text-slate-800' }}">
            <i class="fas fa-bullseye text-xs"></i>
            Mes objectifs
            <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                {{ $tab === 'objectifs' ? $ficheTabBadge : 'bg-slate-200 text-slate-600' }}">
                {{ $fichesStats['total'] }}
            </span>
        </a>
    </div>
    {{-- Lien "Mon équipe" pour les chefs --}}
    @if (isset($ctx) && method_exists($ctx, 'getParentNom'))
        <a href="{{ route('chef.equipe') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-500 shadow-sm transition hover:border-blue-300 hover:text-blue-700">
            <i class="fas fa-users text-xs"></i> Mon équipe <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    @endif
</div>
