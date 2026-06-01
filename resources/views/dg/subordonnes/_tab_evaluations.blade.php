{{-- Tab : Evaluations d'un subordonné du DG --}}
{{-- Variables : $evaluations (paginated), $evaluationsStats, $filters --}}

@php
$evalCards = [
    ['label' => 'Total',    'value' => $evaluationsStats['total'],    'accent' => 'bg-slate-300',   'tone' => 'border-slate-100 bg-white'],
    ['label' => 'Brouillons','value' => $evaluationsStats['brouillon'],'accent' => 'bg-slate-400',  'tone' => 'border-slate-100 bg-slate-50/80'],
    ['label' => 'Soumises', 'value' => $evaluationsStats['soumis'],   'accent' => 'bg-amber-400',   'tone' => 'border-amber-100 bg-amber-50/60'],
    ['label' => 'Validées', 'value' => $evaluationsStats['valide'],   'accent' => 'bg-emerald-500', 'tone' => 'border-emerald-100 bg-emerald-50/60'],
];
@endphp

{{-- ── Cartes stats ──────────────────────────────────────────────────────────── --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @foreach ($evalCards as $card)
    <div class="relative overflow-hidden rounded-2xl border shadow-sm {{ $card['tone'] }}">
        <div class="absolute inset-y-0 left-0 w-1 {{ $card['accent'] }}"></div>
        <div class="px-5 py-4 pl-6">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $card['label'] }}</p>
            <p class="mt-2 text-4xl font-black leading-none text-slate-900">{{ $card['value'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- ── Filtre ─────────────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ request()->url() }}"
      class="mb-5 flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
    <input type="hidden" name="tab" value="evaluations">
    <i class="fas fa-filter text-xs text-slate-400 mr-1"></i>
    <select name="statut"
            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-cyan-300 focus:ring-2 focus:ring-cyan-100">
        <option value="">Tous les statuts</option>
        <option value="brouillon" @selected($filters['statut'] === 'brouillon')>Brouillon</option>
        <option value="soumis"    @selected($filters['statut'] === 'soumis')>Soumise</option>
        <option value="valide"    @selected($filters['statut'] === 'valide')>Validée</option>
    </select>
    <button type="submit"
            class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
        Filtrer
    </button>
    @if ($filters['statut'])
    <a href="{{ request()->url() }}?tab=evaluations"
       class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-500 transition hover:text-slate-800">
        <i class="fas fa-times text-[10px]"></i>
    </a>
    @endif
</form>

{{-- ── Tableau ─────────────────────────────────────────────────────────────────── --}}
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/80">
                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Cible</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Période</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Note</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</th>
                    <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($evaluations as $evaluation)
                    @php
                        $note    = (float) $evaluation->note_finale;
                        $mention = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
                        $mentionTxtCls = match ($mention) {
                            'Excellent' => 'text-emerald-600',
                            'Bien'      => 'text-sky-600',
                            'Passable'  => 'text-amber-600',
                            default     => 'text-rose-600',
                        };
                        $statusCls = match ($evaluation->statut) {
                            'valide'      => 'bg-emerald-100 text-emerald-700',
                            'soumis'      => 'bg-amber-100 text-amber-700',
                            'refuse'      => 'bg-rose-100 text-rose-700',
                            'reclamation' => 'bg-orange-100 text-orange-700',
                            'a_reviser'   => 'bg-purple-100 text-purple-700',
                            default       => 'bg-slate-100 text-slate-600',
                        };
                        $dotCls = match ($evaluation->statut) {
                            'valide'      => 'bg-emerald-500',
                            'soumis'      => 'bg-amber-400',
                            'refuse'      => 'bg-rose-500',
                            'reclamation' => 'bg-orange-500',
                            'a_reviser'   => 'bg-purple-500',
                            default       => 'bg-slate-400',
                        };
                        $statusLabel = match ($evaluation->statut) {
                            'valide'      => 'Validée',
                            'soumis'      => 'Soumise',
                            'refuse'      => 'Refusée',
                            'reclamation' => 'Réclamation',
                            'a_reviser'   => 'À réviser',
                            'brouillon'   => 'Brouillon',
                            default       => ucfirst((string) $evaluation->statut),
                        };
                        $identification = $evaluation->identification;
                        $cibleLabel  = trim((string) ($identification?->nom_prenom ?? '')) ?: ($evaluation->evaluable?->name ?? '-');
                        $anneeEval   = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
                        $sem         = trim((string) ($identification?->semestre ?? ''));
                        if ($sem === '') { $sem = $evaluation->date_debut->month <= 6 ? '1' : '2'; }
                        $noteVal     = number_format($note, 2, ',', ' ');
                        $notePct     = max(0, min(100, ($note / 10) * 100));
                        $noteBarCls  = $notePct >= 85 ? 'bg-emerald-500' : ($notePct >= 70 ? 'bg-sky-500' : ($notePct >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                        $notePillCls = $notePct >= 85
                            ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                            : ($notePct >= 70 ? 'bg-sky-50 text-sky-700 ring-1 ring-sky-200'
                            : ($notePct >= 50 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
                            : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'));
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- Cible --}}
                        <td class="px-5 py-3.5">
                            <p class="font-black text-slate-800">{{ $cibleLabel }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                Évaluateur : {{ $evaluation->evaluateur?->name ?? '-' }}
                            </p>
                        </td>
                        {{-- Période --}}
                        <td class="px-5 py-3.5">
                            <div class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-700">
                                <i class="fas fa-calendar-alt text-[9px] text-slate-400"></i>
                                S{{ $sem }} / {{ $anneeEval }}
                            </div>
                            <p class="mt-1 text-[11px] text-slate-400">
                                {{ $evaluation->date_debut->format('m/Y') }} → {{ $evaluation->date_fin->format('m/Y') }}
                            </p>
                        </td>
                        {{-- Note --}}
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-baseline gap-0.5 rounded-lg px-2.5 py-1 text-sm font-black {{ $notePillCls }}">
                                {{ $noteVal }}<span class="text-[10px] font-bold opacity-60">/10</span>
                            </span>
                            <div class="mt-1.5 h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full {{ $noteBarCls }}" style="width:{{ $notePct }}%"></div>
                            </div>
                            <p class="mt-0.5 text-[10px] font-bold {{ $mentionTxtCls }}">{{ $mention }}</p>
                        </td>
                        {{-- Statut --}}
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statusCls }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
                                {{ $statusLabel }}
                            </span>
                        </td>
                        {{-- Actions --}}
                        <td class="px-5 py-3.5 text-center">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('dg.sub-evaluations.show', $evaluation) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-100 hover:text-blue-600"
                                   title="Voir">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                @if ($evaluation->statut !== 'brouillon')
                                    <a href="{{ route('dg.sub-evaluations.pdf', $evaluation) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-100 hover:text-rose-600"
                                       title="PDF" target="_blank">
                                        <i class="fas fa-file-pdf text-xs"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-14 text-center">
                            <div class="mx-auto max-w-xs">
                                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                    <i class="fas fa-clipboard-list text-xl text-slate-300"></i>
                                </div>
                                <p class="text-sm font-black text-slate-700">Aucune évaluation</p>
                                <p class="mt-1 text-xs text-slate-400">Aucune évaluation enregistrée pour ce collaborateur.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($evaluations->hasPages())
    <div class="mt-4 border-t border-slate-100 pt-4">
        {{ $evaluations->links() }}
    </div>
@endif
