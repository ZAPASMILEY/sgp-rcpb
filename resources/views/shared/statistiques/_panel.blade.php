{{--
    Partial partagé : filtres + tableau statistiques personnel.
    Variables attendues (passées par le contrôleur) :
      $routeName, $type, $annees, $anneeSelectionnee,
      $delegations, $caisses, $agents, $s1, $s2
--}}
<div class="px-4 pt-6 lg:px-8">

    {{-- ── Onglets périmètre ───────────────────────────────────────────────── --}}
    <div class="mb-4 flex flex-wrap items-center gap-1 rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-100 w-fit">
        @php $currentType = request('type', ''); @endphp
        @foreach([
            ''         => ['label' => 'Tous',                            'icon' => 'fa-users'],
            'siege'    => ['label' => 'FCPB (Siège)',                    'icon' => 'fa-landmark'],
            'faitiere' => ['label' => 'Faitière (Siège + Délégations)',  'icon' => 'fa-building'],
            'rcpb'     => ['label' => 'RCPB (Caisses + Agences + Guichets)', 'icon' => 'fa-piggy-bank'],
        ] as $val => $opt)
        <a href="{{ route($routeName, array_merge(request()->except(['type','caisse_id','page']), $val !== '' ? ['type' => $val] : [])) }}"
           class="rounded-lg px-4 py-2 text-sm font-bold transition whitespace-nowrap
               {{ $currentType === $val ? 'text-white shadow' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}"
           @if($currentType === $val) style="background:#0891b2" @endif>
            <i class="fas {{ $opt['icon'] }} mr-1.5 text-xs"></i>
            {{ $opt['label'] }}
        </a>
        @endforeach
    </div>

    {{-- ── Filtres ─────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route($routeName) }}"
          class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">

        @if(request('type')) <input type="hidden" name="type" value="{{ request('type') }}"> @endif

        {{-- Année --}}
        <div class="flex flex-col gap-1 min-w-[130px]">
            <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Année</label>
            <select name="annee_id"
                    class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 focus:border-cyan-400 focus:ring-0">
                @foreach($annees as $a)
                    <option value="{{ $a->id }}" @selected($anneeSelectionnee?->id === $a->id)>
                        {{ $a->annee }}{{ $a->statut === 'ouvert' ? ' ★' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Délégation (masquée en mode Siège) --}}
        @if($type !== 'siege')
        <div class="flex flex-col gap-1 min-w-[180px]">
            <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Délégation</label>
            <select name="delegation_id"
                    class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 focus:border-cyan-400 focus:ring-0">
                <option value="">Toutes les délégations</option>
                @foreach($delegations as $d)
                    <option value="{{ $d->id }}" @selected(request('delegation_id') == $d->id)>
                        {{ $d->region }} – {{ $d->ville }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Caisse (masquée en mode Faitière ou Siège) --}}
        @if(! in_array($type, ['faitiere', 'siege']))
        <div class="flex flex-col gap-1 min-w-[160px]">
            <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Caisse</label>
            <select name="caisse_id"
                    class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 focus:border-cyan-400 focus:ring-0">
                <option value="">Toutes les caisses</option>
                @foreach($caisses as $c)
                    <option value="{{ $c->id }}" @selected(request('caisse_id') == $c->id)>
                        {{ $c->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Recherche --}}
        <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
            <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Recherche</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Matricule, nom, prénom…"
                       class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-2 text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:border-cyan-400 focus:ring-0">
            </div>
        </div>

        <button type="submit"
                class="rounded-lg px-5 py-2 text-sm font-bold text-white transition"
                style="background:#0891b2" onmouseover="this.style.background='#0e7490'" onmouseout="this.style.background='#0891b2'">
            <i class="fas fa-filter mr-1"></i> Filtrer
        </button>
        <a href="{{ route($routeName, request('type') ? ['type' => request('type')] : []) }}"
           class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
            Réinitialiser
        </a>
    </form>

    {{-- ── Pas d'année ─────────────────────────────────────────────────────── --}}
    @if(! $anneeSelectionnee)
    <div class="rounded-2xl bg-white border border-dashed border-slate-200 px-8 py-16 text-center">
        <i class="fas fa-calendar-times text-slate-200 text-5xl mb-4 block"></i>
        <p class="text-sm font-semibold text-slate-400">Aucune année d'exercice configurée.</p>
    </div>

    @else
    {{-- ── Cartes stats rapides ─────────────────────────────────────────────── --}}
    @php
        $totalAgents = $agents->count();
        $allEvals    = fn($a) => $a->evaluations->merge($a->evaluationsPersonnel)->merge($a->directedDirection?->evaluations ?? collect())->merge($a->directedCaisse?->evaluations ?? collect())->merge($a->directedDelegation?->evaluations ?? collect())->merge($a->ledAgence?->evaluations ?? collect())->merge($a->ledService?->evaluations ?? collect())->merge($a->ledGuichet?->evaluations ?? collect());
        // On utilise == (pas ===) pour éviter les écarts string/int selon le driver PDO.
        // On filtre sur statut='valide' plutôt que note_finale !== null : c'est le critère officiel.
        $evalS1      = fn ($e) => (int)$e->semestre_id === (int)($s1?->id) && $e->statut === 'valide';
        $evalS2      = fn ($e) => (int)$e->semestre_id === (int)($s2?->id) && $e->statut === 'valide';
        $avecS1      = $s1 ? $agents->filter(fn ($a) => $allEvals($a)->contains($evalS1))->count() : 0;
        $avecS2      = $s2 ? $agents->filter(fn ($a) => $allEvals($a)->contains($evalS2))->count() : 0;
        $moyS1       = $s1 ? $agents->map(fn ($a) => optional($allEvals($a)->first($evalS1))->note_finale)->filter()->avg() : null;
        $moyS2       = $s2 ? $agents->map(fn ($a) => optional($allEvals($a)->first($evalS2))->note_finale)->filter()->avg() : null;
    @endphp
    <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Agents</p>
            <p class="mt-1 text-3xl font-black text-slate-900">{{ $totalAgents }}</p>
        </div>
        <div class="rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Évalués S1</p>
            <p class="mt-1 text-3xl font-black text-cyan-700">{{ $avecS1 }}</p>
            <p class="text-[11px] text-slate-400">Moy. {{ $moyS1 ? number_format($moyS1, 2) : '—' }}</p>
        </div>
        <div class="rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Évalués S2</p>
            <p class="mt-1 text-3xl font-black text-cyan-700">{{ $avecS2 }}</p>
            <p class="text-[11px] text-slate-400">Moy. {{ $moyS2 ? number_format($moyS2, 2) : '—' }}</p>
        </div>
        <div class="rounded-2xl bg-white px-5 py-4 shadow-sm ring-1 ring-slate-100">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Année</p>
            <p class="mt-1 text-3xl font-black text-slate-900">{{ $anneeSelectionnee->annee }}</p>
            <span class="inline-block rounded-full px-2 py-0.5 text-[10px] font-bold
                {{ $anneeSelectionnee->statut === 'ouvert' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                {{ $anneeSelectionnee->statut === 'ouvert' ? 'Ouverte' : 'Clôturée' }}
            </span>
        </div>
    </div>

    {{-- ── Tableau principal ────────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
        @if($agents->isEmpty())
            <div class="px-8 py-16 text-center">
                <i class="fas fa-user-slash text-slate-200 text-5xl mb-4 block"></i>
                <p class="text-sm font-semibold text-slate-400">Aucun agent trouvé pour ces critères.</p>
            </div>
        @else
        {{-- Conteneur scrollable : header fixe, ~10 lignes visibles (~480 px), défilement molette --}}
        <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10">
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Matricule</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Nom et Prénom</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Sexe</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Fonction</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Grade</th>
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">
                            @if($type === 'siege') Direction
                            @elseif($type === 'faitiere') Structure
                            @else Délégation
                            @endif
                        </th>
                        @if(! in_array($type, ['faitiere', 'siege']))
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Caisse</th>
                        @endif
                        <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Prise de fonction</th>
                        <th class="px-4 py-3 text-center text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Note S1</th>
                        <th class="px-4 py-3 text-center text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Note S2</th>
                        <th class="px-4 py-3 text-center text-[11px] font-black uppercase tracking-wide text-slate-500 whitespace-nowrap">Note Annuelle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($agents as $agent)
                    @php
                        $evals  = $agent->evaluations->merge($agent->evaluationsPersonnel)->merge($agent->directedDirection?->evaluations ?? collect())->merge($agent->directedCaisse?->evaluations ?? collect())->merge($agent->directedDelegation?->evaluations ?? collect())->merge($agent->ledAgence?->evaluations ?? collect())->merge($agent->ledService?->evaluations ?? collect())->merge($agent->ledGuichet?->evaluations ?? collect());
                        $evalS1 = $s1 ? $evals->firstWhere('semestre_id', $s1->id) : null;
                        $evalS2 = $s2 ? $evals->firstWhere('semestre_id', $s2->id) : null;
                        $grade  = $evalS1?->identification?->grade ?? $evalS2?->identification?->grade ?? null;
                        $noteS1 = ($evalS1 && $evalS1->note_finale !== null) ? (float)$evalS1->note_finale : null;
                        $noteS2 = ($evalS2 && $evalS2->note_finale !== null) ? (float)$evalS2->note_finale : null;
                        $noteAn = ($noteS1 !== null && $noteS2 !== null) ? ($noteS1 + $noteS2) / 2 : ($noteS1 ?? $noteS2);

                        $structure  = $agent->delegationTechnique
                            ? $agent->delegationTechnique->region . ' – ' . $agent->delegationTechnique->ville
                            : 'FCPB';
                        $delegation = $agent->delegationTechnique
                            ? $agent->delegationTechnique->region . ' – ' . $agent->delegationTechnique->ville
                            : ($agent->caisse?->delegationTechnique
                                ? $agent->caisse->delegationTechnique->region . ' – ' . $agent->caisse->delegationTechnique->ville
                                : null);
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-slate-600 whitespace-nowrap">{{ $agent->matricule ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <p class="font-semibold text-slate-800">{{ $agent->prenom }} {{ $agent->nom }}</p>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $sexeRaw = strtolower($agent->sexe ?? '');
                                $isMasc  = in_array($sexeRaw, ['homme', 'masculin', 'm']);
                                $isFem   = in_array($sexeRaw, ['femme', 'féminin', 'feminin', 'f']);
                            @endphp
                            @if($isMasc)
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700">
                                    <i class="fas fa-mars text-[9px]"></i> H
                                </span>
                            @elseif($isFem)
                                <span class="inline-flex items-center gap-1 rounded-full bg-pink-50 px-2 py-0.5 text-[11px] font-bold text-pink-700">
                                    <i class="fas fa-venus text-[9px]"></i> F
                                </span>
                            @else
                                <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>
                        @php
                            $fonctionLabel = in_array($agent->role, ['Agent', 'Conseiller DG'])
                                ? ($agent->poste ?? '—')
                                : ($agent->poste ?? $agent->role ?? '—');
                        @endphp
                        <td class="px-4 py-3 text-xs text-slate-700 max-w-[180px] truncate" title="{{ $fonctionLabel }}">{{ $fonctionLabel }}</td>
                        <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">{{ $grade ?? '—' }}</td>

                        {{-- Colonne Direction / Structure / Délégation --}}
                        @if($type === 'siege')
                        <td class="px-4 py-3 text-xs text-slate-700 whitespace-nowrap max-w-[200px] truncate" title="{{ $agent->direction?->nom ?? '—' }}">
                            {{ $agent->direction?->nom ?? '—' }}
                        </td>
                        @elseif($type === 'faitiere')
                        <td class="px-4 py-3 text-xs whitespace-nowrap">
                            @if($structure === 'FCPB')
                                <span class="inline-block rounded-lg bg-cyan-50 px-2.5 py-0.5 text-[11px] font-bold text-cyan-700">FCPB</span>
                            @else
                                <span class="text-slate-600" title="{{ $structure }}">{{ $structure }}</span>
                            @endif
                        </td>
                        @else
                        <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap max-w-[160px] truncate" title="{{ $delegation ?? '' }}">
                            {{ $delegation ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap max-w-[140px] truncate" title="{{ $agent->caisse?->nom ?? '' }}">
                            {{ $agent->caisse?->nom ?? '—' }}
                        </td>
                        @endif

                        <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                            {{ $agent->date_debut_fonction?->format('d/m/Y') ?? '—' }}
                        </td>

                        @foreach([[$noteS1, 'center'], [$noteS2, 'center'], [$noteAn, 'black']] as [$note, $weight])
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($note !== null)
                                @php
                                    $cls = $note >= 14
                                        ? ($weight === 'black' ? 'bg-emerald-600 text-white' : 'bg-emerald-100 text-emerald-800')
                                        : ($note >= 10
                                            ? ($weight === 'black' ? 'bg-amber-500 text-white'   : 'bg-amber-100 text-amber-800')
                                            : ($weight === 'black' ? 'bg-red-600 text-white'      : 'bg-red-100 text-red-700'));
                                    $fw = $weight === 'black' ? 'font-black' : 'font-bold';
                                @endphp
                                <span class="inline-block rounded-lg px-2.5 py-0.5 text-xs {{ $fw }} {{ $cls }}">
                                    {{ number_format($note, 2) }}
                                </span>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">
            {{ $agents->count() }} agent{{ $agents->count() > 1 ? 's' : '' }} affiché{{ $agents->count() > 1 ? 's' : '' }}
        </div>
        @endif
    </div>
    @endif

</div>
