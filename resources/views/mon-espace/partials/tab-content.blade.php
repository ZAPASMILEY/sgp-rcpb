{{-- ════════ TAB : ÉVALUATIONS ════════ --}}
@if ($tab === 'evaluations')

    {{-- ── Cartes stats ──────────────────────────────────────────────────────── --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($evalInnerCards as $card)
        @php
            $accentMap = [
                'border-slate-100 bg-white text-slate-900'          => 'bg-slate-300',
                'border-slate-100 bg-slate-50/80 text-slate-900'    => 'bg-slate-400',
                'border-amber-100 bg-amber-50/80 text-amber-900'    => 'bg-amber-400',
                'border-emerald-100 bg-emerald-50/80 text-emerald-900' => 'bg-emerald-500',
                'border-rose-100 bg-rose-50/80 text-rose-900'       => 'bg-rose-500',
                'border-purple-100 bg-purple-50/80 text-purple-900' => 'bg-purple-500',
            ];
            $accent = $accentMap[$card['tone']] ?? 'bg-slate-300';
        @endphp
        <div class="relative overflow-hidden rounded-2xl border shadow-sm {{ $card['tone'] }}">
            <div class="absolute inset-y-0 left-0 w-1 {{ $accent }}"></div>
            <div class="px-5 py-4 pl-6">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-55">{{ $card['label'] }}</p>
                <p class="mt-2 text-4xl font-black leading-none">{{ $card['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Filtre ────────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ $monEspaceUrl }}"
          class="mb-5 flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <input type="hidden" name="tab" value="evaluations">
        <i class="fas fa-filter text-xs text-slate-400 mr-1"></i>
        <select name="statut"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-violet-300 focus:ring-2 focus:ring-violet-100">
            <option value="">Tous les statuts</option>
            @if ($showBrouillonFilter)
                <option value="brouillon" @selected(($filters['statut'] ?? '') === 'brouillon')>Brouillon</option>
            @endif
            <option value="soumis"  @selected(($filters['statut'] ?? '') === 'soumis')>Soumise</option>
            <option value="valide"  @selected(($filters['statut'] ?? '') === 'valide')>Validée</option>
            @if (!$showBrouillonFilter)
                <option value="refuse" @selected(($filters['statut'] ?? '') === 'refuse')>Refusée</option>
            @endif
        </select>
        <button type="submit"
                class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
            Filtrer
        </button>
        @if (!empty($filters['statut']))
        <a href="{{ $monEspaceUrl }}?tab=evaluations"
           class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 transition hover:text-slate-800">
            <i class="fas fa-times text-[10px]"></i>
        </a>
        @endif
    </form>

    {{-- ── Tableau évaluations ────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Période</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Note</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Évaluateur</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($evaluations as $evaluation)
                        @php
                            $note       = (float) $evaluation->note_finale;
                            $mention    = $note >= 8.5 ? 'Excellent' : ($note >= 7 ? 'Bien' : ($note >= 5 ? 'Passable' : 'Insuffisant'));
                            $mentionCls = match ($mention) {
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
                            $statusLbl = match ($evaluation->statut) {
                                'valide'      => 'Validée',
                                'soumis'      => 'Soumise',
                                'refuse'      => 'Refusée',
                                'reclamation' => 'Réclamation',
                                'a_reviser'   => 'À réviser',
                                'brouillon'   => 'Brouillon',
                                default       => ucfirst((string) $evaluation->statut),
                            };
                            $identification = $evaluation->identification;
                            $anneeEval  = $identification?->date_evaluation?->format('Y') ?? $evaluation->date_debut->format('Y');
                            $sem        = trim((string) ($identification?->semestre ?? ''));
                            if ($sem === '') { $sem = $evaluation->date_debut->month <= 6 ? '1' : '2'; }
                            $noteVal    = number_format($note, 2, ',', ' ');
                            $notePct    = max(0, min(100, ($note / 10) * 100));
                            $noteBarCls = $notePct >= 85 ? 'bg-emerald-500' : ($notePct >= 70 ? 'bg-sky-500' : ($notePct >= 50 ? 'bg-amber-400' : 'bg-rose-400'));
                            $notePillCls = $notePct >= 85
                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
                                : ($notePct >= 70 ? 'bg-sky-50 text-sky-700 ring-1 ring-sky-200'
                                : ($notePct >= 50 ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
                                : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200'));
                        @endphp
                        <tr class="group hover:bg-slate-50/60 transition-colors">
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
                                <div class="inline-flex flex-col gap-1">
                                    <span class="inline-flex items-baseline gap-0.5 rounded-lg px-2.5 py-1 text-sm font-black {{ $notePillCls }}">
                                        {{ $noteVal }}<span class="text-[10px] font-bold opacity-60">/10</span>
                                    </span>
                                    <div class="h-1.5 w-20 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full {{ $noteBarCls }}" style="width:{{ $notePct }}%"></div>
                                    </div>
                                </div>
                                <p class="mt-1 text-[10px] font-bold {{ $mentionCls }}">{{ $mention }}</p>
                            </td>
                            {{-- Statut --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statusCls }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
                                    {{ $statusLbl }}
                                </span>
                            </td>
                            {{-- Évaluateur --}}
                            <td class="px-5 py-3.5 text-sm text-slate-600">
                                {{ $evaluation->evaluateur?->name ?? '—' }}
                            </td>
                            {{-- Actions --}}
                            <td class="px-5 py-3.5 text-center">
                                <div class="inline-flex items-center gap-1">
                                    @if ($evaluation->statut !== 'brouillon')
                                        <a href="{{ route($evalShowRoute, $evaluation) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-violet-100 hover:text-violet-600"
                                           title="Voir">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-300 px-2">En cours</span>
                                    @endif

                                    @if ($hasEvalActions && $evaluation->statut === 'soumis')
                                        <form method="POST" action="{{ route($evalStatutRoute, $evaluation) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="accepter">
                                            <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-100 hover:text-emerald-600"
                                                    title="Accepter" onclick="return confirm('Accepter cette évaluation ?')">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route($evalStatutRoute, $evaluation) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="refuser">
                                            <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500"
                                                    title="Refuser" onclick="return confirm('Refuser cette évaluation ?')">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </form>
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
                                    <p class="mt-1 text-xs text-slate-400">Vous n'avez pas encore d'évaluation enregistrée.</p>
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

{{-- ════════ TAB : OBJECTIFS ════════ --}}
@else

    {{-- ── Cartes stats ──────────────────────────────────────────────────────── --}}
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($ficheInnerCards as $card)
        @php
            $accentMap = [
                'border-slate-100 bg-white text-slate-900'             => 'bg-slate-300',
                'border-emerald-100 bg-emerald-50/80 text-emerald-900' => 'bg-emerald-500',
                'border-amber-100 bg-amber-50/80 text-amber-900'       => 'bg-amber-400',
                'border-rose-100 bg-rose-50/80 text-rose-900'          => 'bg-rose-500',
                'border-orange-100 bg-orange-50/80 text-orange-900'    => 'bg-orange-400',
            ];
            $accent = $accentMap[$card['tone']] ?? 'bg-slate-300';
        @endphp
        <div class="relative overflow-hidden rounded-2xl border shadow-sm {{ $card['tone'] }}">
            <div class="absolute inset-y-0 left-0 w-1 {{ $accent }}"></div>
            <div class="px-5 py-4 pl-6">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] opacity-55">{{ $card['label'] }}</p>
                <p class="mt-2 text-4xl font-black leading-none">{{ $card['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Filtre ────────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ $monEspaceUrl }}"
          class="mb-5 flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
        <input type="hidden" name="tab" value="objectifs">
        <i class="fas fa-filter text-xs text-slate-400 mr-1"></i>
        <div class="relative flex-1 min-w-44">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="fas fa-search text-[10px]"></i>
            </span>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Titre, année..."
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs font-semibold text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
        </div>
        <select name="statut"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
            <option value="">Tous les statuts</option>
            <option value="en_attente" @selected(($filters['statut'] ?? '') === 'en_attente')>En attente</option>
            <option value="acceptee"   @selected(($filters['statut'] ?? '') === 'acceptee')>Acceptée</option>
            <option value="refusee"    @selected(($filters['statut'] ?? '') === 'refusee')>Refusée</option>
        </select>
        <button type="submit"
                class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
            Filtrer
        </button>
        @if (!empty($filters['search']) || !empty($filters['statut']))
        <a href="{{ $monEspaceUrl }}?tab=objectifs"
           class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-500 transition hover:text-slate-800">
            <i class="fas fa-times text-[10px]"></i>
        </a>
        @endif
    </form>

    {{-- ── Tableau objectifs ──────────────────────────────────────────────────── --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80">
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Fiche</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Avancement</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</th>
                        <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($fiches as $fiche)
                        @php
                            $statCls = match ($fiche->statut ?? 'en_attente') {
                                'acceptee' => 'bg-emerald-100 text-emerald-700',
                                'refusee'  => 'bg-rose-100 text-rose-700',
                                'contesté' => 'bg-orange-100 text-orange-700',
                                'brouillon'=> 'bg-slate-100 text-slate-600',
                                default    => 'bg-amber-100 text-amber-700',
                            };
                            $dotCls = match ($fiche->statut ?? 'en_attente') {
                                'acceptee' => 'bg-emerald-500',
                                'refusee'  => 'bg-rose-500',
                                'contesté' => 'bg-orange-400',
                                'brouillon'=> 'bg-slate-400',
                                default    => 'bg-amber-400',
                            };
                            $statLbl = match ($fiche->statut ?? 'en_attente') {
                                'acceptee' => 'Acceptée',
                                'refusee'  => 'Refusée',
                                'contesté' => 'Contestée',
                                'brouillon'=> 'Brouillon',
                                default    => 'En attente',
                            };
                            $av      = (int) ($fiche->avancement_percentage ?? 0);
                            $avBarCls = $av >= 80 ? 'bg-emerald-500' : ($av >= 50 ? 'bg-sky-500' : ($av >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                            $avTxtCls = $av >= 80 ? 'text-emerald-700' : ($av >= 50 ? 'text-sky-700' : ($av >= 25 ? 'text-amber-600' : 'text-slate-500'));
                            $objCount = $fiche->objectifs_count ?? $fiche->objectifs?->count() ?? 0;
                            $echeance = $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance) : null;
                            $expired  = $echeance && $echeance->isPast();
                        @endphp
                        <tr class="group hover:bg-slate-50/60 transition-colors">
                            {{-- Fiche --}}
                            <td class="px-5 py-3.5">
                                <p class="font-black text-slate-800">{{ $fiche->titre }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">
                                        <i class="fas fa-calendar-alt text-[9px]"></i>
                                        {{ $fiche->annee?->annee ?? $fiche->annee_id }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-600">
                                        <i class="fas fa-bullseye text-[9px]"></i>
                                        {{ $objCount }} objectif{{ $objCount > 1 ? 's' : '' }}
                                    </span>
                                    @if ($echeance)
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-bold {{ $expired ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-500' }}">
                                            <i class="fas fa-clock text-[9px]"></i>
                                            Éch. {{ $echeance->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            {{-- Avancement --}}
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full transition-all {{ $avBarCls }}" style="width:{{ $av }}%"></div>
                                    </div>
                                    <span class="text-sm font-black {{ $avTxtCls }}">{{ $av }}%</span>
                                </div>
                            </td>
                            {{-- Statut --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statCls }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
                                    {{ $statLbl }}
                                </span>
                            </td>
                            {{-- Actions --}}
                            <td class="px-5 py-3.5 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route($ficheShowRoute, $fiche) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-indigo-100 hover:text-indigo-600"
                                       title="Voir la fiche">
                                        <i class="fas fa-eye text-xs"></i>
                                    </a>

                                    @if ($hasFicheActions && in_array($fiche->statut, ['en_attente', null]))
                                        <form method="POST" action="{{ route($ficheStatutRoute, $fiche) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="accepter">
                                            <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-100 hover:text-emerald-600"
                                                    title="Accepter" onclick="return confirm('Accepter cette fiche ?')">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route($ficheStatutRoute, $fiche) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="action" value="refuser">
                                            <button type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500"
                                                    title="Refuser" onclick="return confirm('Refuser cette fiche ?')">
                                                <i class="fas fa-times text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-14 text-center">
                                <div class="mx-auto max-w-xs">
                                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                        <i class="fas fa-bullseye text-xl text-slate-300"></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-700">Aucun objectif</p>
                                    <p class="mt-1 text-xs text-slate-400">Vous n'avez pas encore de fiche d'objectifs assignée.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($fiches->hasPages())
        <div class="mt-4 border-t border-slate-100 pt-4">
            {{ $fiches->links() }}
        </div>
    @endif

@endif
