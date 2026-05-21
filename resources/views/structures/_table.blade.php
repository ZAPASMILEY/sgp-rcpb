{{-- Shared structures table partial --}}
{{-- Expected variables: $structures (Collection of structure objects) --}}

@php
    function structureMentionLabel(float|null $note): string {
        if ($note === null) return '—';
        if ($note >= 8.5) return 'Excellent';
        if ($note >= 7)   return 'Bien';
        if ($note >= 5)   return 'Passable';
        return 'Insuffisant';
    }
    function structureMentionColor(float|null $note): string {
        if ($note === null) return 'bg-slate-100 text-slate-500';
        if ($note >= 8.5) return 'bg-emerald-100 text-emerald-700';
        if ($note >= 7)   return 'bg-sky-100 text-sky-700';
        if ($note >= 5)   return 'bg-amber-100 text-amber-700';
        return 'bg-rose-100 text-rose-700';
    }
    function structureBarColor(float|null $note): string {
        if ($note === null) return 'bg-slate-200';
        if ($note >= 8.5) return 'bg-emerald-500';
        if ($note >= 7)   return 'bg-sky-500';
        if ($note >= 5)   return 'bg-amber-500';
        return 'bg-rose-500';
    }
@endphp

<div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                <th class="px-5 py-3.5">Structure</th>
                <th class="px-4 py-3.5">Type</th>
                <th class="px-4 py-3.5 text-center">Agents</th>
                <th class="px-4 py-3.5 text-center">Évaluations</th>
                <th class="px-4 py-3.5 min-w-[160px]">Note moy. /10</th>
                <th class="px-4 py-3.5 text-center">Mention</th>
                <th class="px-4 py-3.5">Distribution mentions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($structures as $s)
                @php
                    $note    = $s->note_moyenne;
                    $pct     = $note !== null ? min(100, ($note / 10) * 100) : 0;
                    $barCls  = structureBarColor($note);
                    $mentLbl = structureMentionLabel($note);
                    $mentCls = structureMentionColor($note);
                @endphp
                <tr class="transition hover:bg-slate-50/70">
                    {{-- Structure name --}}
                    <td class="px-5 py-3.5">
                        <span class="font-semibold text-slate-800">{{ $s->nom }}</span>
                    </td>

                    {{-- Type badge --}}
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                            <i class="fas fa-building text-[10px]"></i>
                            {{ $s->type }}
                        </span>
                    </td>

                    {{-- Agents count --}}
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                            {{ $s->nb_agents }}
                        </span>
                    </td>

                    {{-- Evaluations count --}}
                    <td class="px-4 py-3.5 text-center">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                            {{ $s->nb_evaluations }}
                        </span>
                    </td>

                    {{-- Note moyenne avec barre de progression --}}
                    <td class="px-4 py-3.5">
                        @if ($notesVisibles ?? true)
                            @if($note !== null)
                                <div class="flex items-center gap-2">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                                        <div class="{{ $barCls }} h-full rounded-full transition-all"
                                             style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span class="shrink-0 text-xs font-bold text-slate-700">{{ number_format($note, 2) }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400">—</span>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-600 border border-amber-200">
                                <i class="fas fa-clock text-[9px]"></i> En exercice
                            </span>
                        @endif
                    </td>

                    {{-- Mention badge --}}
                    <td class="px-4 py-3.5 text-center">
                        @if ($notesVisibles ?? true)
                            <span class="inline-block rounded-full px-2.5 py-1 text-[11px] font-bold {{ $mentCls }}">
                                {{ $mentLbl }}
                            </span>
                        @else
                            <span class="text-[11px] text-slate-300">—</span>
                        @endif
                    </td>

                    {{-- Distribution badges --}}
                    <td class="px-4 py-3.5">
                        @if ($notesVisibles ?? true)
                        <div class="flex flex-wrap items-center gap-1">
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700"
                                  title="Excellent (≥8.5)">
                                <i class="fas fa-star text-[8px]"></i>
                                {{ $s->nb_excellent }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-bold text-sky-700"
                                  title="Bien (7–8.5)">
                                <i class="fas fa-thumbs-up text-[8px]"></i>
                                {{ $s->nb_bien }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700"
                                  title="Passable (5–7)">
                                <i class="fas fa-minus text-[8px]"></i>
                                {{ $s->nb_passable }}
                            </span>
                            <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-700"
                                  title="Insuffisant (<5)">
                                <i class="fas fa-arrow-down text-[8px]"></i>
                                {{ $s->nb_insuffisant }}
                            </span>
                        </div>
                        @else
                            <span class="text-[10px] text-slate-300">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-400">
                        <i class="fas fa-building mb-3 text-3xl opacity-30"></i>
                        <p class="font-medium">Aucune structure trouvée</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
