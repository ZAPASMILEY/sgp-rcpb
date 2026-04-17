{{-- Dossier complet avec onglets : Objectifs | Evaluations --}}
{{-- Variables attendues : $tab, $fiches, $fichesStats, $evaluations, $evaluationsStats, $filters --}}

@if (empty($currentSubordonneId ?? null))
    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
        <i class="fas fa-user-slash text-3xl text-slate-300"></i>
        <p class="mt-3 text-sm font-semibold text-slate-400">Aucun compte configure pour ce role.</p>
        <p class="mt-1 text-xs text-slate-400">Contactez l'administrateur pour creer le compte.</p>
    </div>
@else
    <div class="admin-panel px-6 py-6">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="inline-flex gap-1 rounded-2xl border border-slate-200 bg-slate-100/70 p-1">
                <a href="{{ request()->url() }}?tab=objectifs"
                   class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                       {{ $tab === 'objectifs'
                           ? 'border border-slate-200 bg-white text-emerald-700 shadow-sm'
                           : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fas fa-bullseye text-xs"></i>
                    Objectifs
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                        {{ $tab === 'objectifs' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                        {{ $fichesStats['total'] }}
                    </span>
                </a>
                <a href="{{ request()->url() }}?tab=evaluations"
                   class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-bold transition
                       {{ $tab === 'evaluations'
                           ? 'border border-slate-200 bg-white text-cyan-700 shadow-sm'
                           : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fas fa-star-half-stroke text-xs"></i>
                    Evaluations
                    <span class="rounded-full px-2 py-0.5 text-[11px] font-black
                        {{ $tab === 'evaluations' ? 'bg-cyan-100 text-cyan-700' : 'bg-slate-200 text-slate-600' }}">
                        {{ $evaluationsStats['total'] }}
                    </span>
                </a>
            </div>

            @if ($tab === 'objectifs')
                <a href="{{ route('dg.objectifs.create', ['subordonne_id' => $currentSubordonneId]) }}"
                   class="inline-flex items-center rounded-2xl bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-emerald-600">
                    <i class="fas fa-plus mr-2 text-xs"></i> Nouvelle fiche
                </a>
            @elseif ($tab === 'evaluations')
                <a href="{{ route('dg.sub-evaluations.create', ['subordonne_id' => $currentSubordonneId]) }}"
                   class="inline-flex items-center rounded-2xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-cyan-700">
                    <i class="fas fa-plus mr-2 text-xs"></i> Nouvelle evaluation
                </a>
            @endif
        </div>

        @if ($tab === 'objectifs')
            @include('dg.subordonnes._tab_objectifs')
        @else
            @include('dg.subordonnes._tab_evaluations')
        @endif
    </div>
@endif
