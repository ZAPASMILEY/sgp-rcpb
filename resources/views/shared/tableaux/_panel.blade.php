{{--
    Partial partagé : configuration + aperçu des tableaux Excel personnalisés.
    Variables attendues :
      $indexRoute, $exportRoute,
      $annees, $delegations, $caisses,
      $hasParams, $payload
--}}
@php
    use App\Http\Controllers\Shared\TableauBaseController;
    $currentType = request('type_analyse', 'distribution_notes');
    $typeGroups  = [
        'Distribution des notes' => [
            'distribution_notes'           => ['icon' => 'fa-chart-bar',    'desc' => 'Vue globale — tous les agents'],
            'rapport_notes_par_delegation' => ['icon' => 'fa-sitemap',      'desc' => 'Un tableau par délégation'],
            'rapport_notes_par_caisse'     => ['icon' => 'fa-piggy-bank',   'desc' => 'Un tableau par caisse'],
        ],
        'Tableaux croisés' => [
            'notes_par_poste'              => ['icon' => 'fa-table-columns', 'desc' => 'Notes 5–10 + manquantes par poste'],
            'notes_par_entite'             => ['icon' => 'fa-layer-group',   'desc' => 'Répartition % des notes par entité'],
        ],
        'Effectifs' => [
            'effectif_par_sexe'            => ['icon' => 'fa-venus-mars',   'desc' => 'Répartition H / F'],
            'effectif_par_fonction'        => ['icon' => 'fa-id-badge',     'desc' => 'Répartition par poste'],
        ],
    ];
@endphp

<div class="px-4 pt-6 lg:px-8">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- ── Panneau de configuration ─────────────────────────────────── --}}
        <div class="lg:col-span-1">
            <form method="GET" action="{{ route($indexRoute) }}" id="form-tableau">
                <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-black text-slate-800">
                            <i class="fas fa-sliders-h mr-2 text-blue-500"></i>Configuration du rapport
                        </h2>
                    </div>
                    <div class="divide-y divide-slate-50">

                        {{-- Type d'analyse --}}
                        <div class="px-5 py-4 space-y-1">
                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-500 mb-3">Type de rapport</p>
                            @foreach($typeGroups as $groupLabel => $items)
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 pt-2 pb-1">{{ $groupLabel }}</p>
                            @foreach($items as $val => $meta)
                            <label class="flex items-start gap-3 cursor-pointer rounded-xl p-3 transition
                                {{ $currentType === $val ? 'bg-blue-50 ring-1 ring-blue-200' : 'hover:bg-slate-50' }}">
                                <input type="radio" name="type_analyse" value="{{ $val }}"
                                       @checked($currentType === $val)
                                       class="mt-0.5 accent-blue-600"
                                       onchange="this.form.submit()">
                                <div>
                                    <p class="text-sm font-bold text-slate-700">
                                        <i class="fas {{ $meta['icon'] }} mr-1 text-blue-400 text-xs"></i>
                                        {{ TableauBaseController::TYPES[$val] }}
                                    </p>
                                    <p class="text-[11px] text-slate-400">{{ $meta['desc'] }}</p>
                                </div>
                            </label>
                            @endforeach
                            @endforeach
                        </div>

                        {{-- Année --}}
                        <div class="px-5 py-4">
                            <label class="block text-[11px] font-black uppercase tracking-wide text-slate-500 mb-2">Année</label>
                            <select name="annee_id"
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 focus:border-blue-400 focus:ring-0">
                                @foreach($annees as $a)
                                    <option value="{{ $a->id }}" @selected(request('annee_id') == $a->id || (!request('annee_id') && $a->statut === 'ouvert'))>
                                        {{ $a->annee }}{{ $a->statut === 'ouvert' ? ' ★' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Semestre (seulement pour les types notes) --}}
                        @if(str_contains($currentType, 'note') || $currentType === 'distribution_notes')
                        <div class="px-5 py-4">
                            <label class="block text-[11px] font-black uppercase tracking-wide text-slate-500 mb-2">Période</label>
                            @foreach(['1' => 'Semestre 1', '2' => 'Semestre 2', 'annuel' => 'Note annuelle (S1+S2÷2)'] as $val => $label)
                            <label class="flex items-center gap-2 py-1.5 cursor-pointer">
                                <input type="radio" name="semestre" value="{{ $val }}"
                                       @checked(request('semestre', '1') === $val)
                                       class="accent-blue-600">
                                <span class="text-sm text-slate-700">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @endif

                        {{-- Périmètre --}}
                        <div class="px-5 py-4">
                            <label class="block text-[11px] font-black uppercase tracking-wide text-slate-500 mb-2">Périmètre</label>
                            @foreach(['' => 'Tout le réseau', 'faitiere' => 'Faitière', 'rcpb' => 'RCPB'] as $val => $label)
                            <label class="flex items-center gap-2 py-1.5 cursor-pointer">
                                <input type="radio" name="scope" value="{{ $val }}"
                                       @checked(request('scope', '') === $val)
                                       class="accent-blue-600">
                                <span class="text-sm text-slate-700">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>

                        {{-- Filtres optionnels --}}
                        <div class="px-5 py-4 space-y-3">
                            <p class="text-[11px] font-black uppercase tracking-wide text-slate-500">Filtres optionnels</p>
                            <div>
                                <label class="text-xs font-semibold text-slate-500 mb-1 block">Délégation</label>
                                <select name="delegation_id"
                                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-0">
                                    <option value="">Toutes</option>
                                    @foreach($delegations as $d)
                                        <option value="{{ $d->id }}" @selected(request('delegation_id') == $d->id)>
                                            {{ $d->region }} – {{ $d->ville }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500 mb-1 block">Caisse</label>
                                <select name="caisse_id"
                                        class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-blue-400 focus:ring-0">
                                    <option value="">Toutes</option>
                                    @foreach($caisses as $c)
                                        <option value="{{ $c->id }}" @selected(request('caisse_id') == $c->id)>
                                            {{ $c->nom }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Boutons --}}
                    <div class="border-t border-slate-100 px-5 py-4 flex flex-col gap-2">
                        <button type="submit"
                                class="w-full rounded-xl py-2.5 text-sm font-bold text-white transition"
                                style="background:#1d4ed8"
                                onmouseover="this.style.background='#1e3a8a'"
                                onmouseout="this.style.background='#1d4ed8'">
                            <i class="fas fa-table mr-2"></i> Générer l'aperçu
                        </button>
                        @if($hasParams && $payload && $payload['groupes']->isNotEmpty())
                        <a href="{{ route($exportRoute, request()->query()) }}"
                           class="w-full rounded-xl py-2.5 text-sm font-bold text-center transition"
                           style="background:#16a34a;color:#fff"
                           onmouseover="this.style.background='#15803d'"
                           onmouseout="this.style.background='#16a34a'">
                            <i class="fas fa-file-excel mr-2"></i> Télécharger Excel (.xls)
                        </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- ── Aperçu ───────────────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-4">

            @if(! $hasParams || ! $payload)
            <div class="flex h-full min-h-[320px] items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                <div class="text-center px-8 py-16">
                    <i class="fas fa-file-excel text-slate-200 text-6xl mb-5 block"></i>
                    <p class="text-base font-bold text-slate-400">Configurez votre rapport</p>
                    <p class="text-xs text-slate-400 mt-1">Choisissez un type et cliquez sur <strong>Générer l'aperçu</strong></p>
                </div>
            </div>

            @else
            @php
                $pGroupes = $payload['groupes'];
                $pIsNotes = $payload['isNotes'];
                $pTitre   = $payload['titrePrincipal'];
            @endphp

            @if($pGroupes->isEmpty())
            <div class="rounded-2xl bg-white px-8 py-12 text-center shadow-sm ring-1 ring-slate-100">
                <i class="fas fa-inbox text-slate-200 text-4xl mb-4 block"></i>
                <p class="text-sm font-semibold text-slate-400">Aucune donnée pour ces critères.</p>
            </div>

            @else

            {{-- Barre titre --}}
            <div class="flex items-center justify-between gap-4 rounded-2xl bg-white px-5 py-3.5 shadow-sm ring-1 ring-slate-100">
                <div>
                    <p class="text-sm font-black text-slate-800">{{ $pTitre }}</p>
                    <p class="text-xs text-slate-400">{{ $pGroupes->count() }} tableau{{ $pGroupes->count() > 1 ? 'x' : '' }}</p>
                </div>
                <a href="{{ route($exportRoute, request()->query()) }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-white"
                   style="background:#16a34a"
                   onmouseover="this.style.background='#15803d'"
                   onmouseout="this.style.background='#16a34a'">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
            </div>

            {{-- Un carte par groupe --}}
            @foreach($pGroupes as $groupe)
            @php
                $rows   = $groupe['rows'] ?? collect();
                $isWide = isset($groupe['headers']);
            @endphp
            @if($rows->isEmpty()) @continue @endif

            <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                <div class="px-5 py-3 font-black text-sm text-white" style="background:#1D6F42">
                    @if(!$isWide){{ $pIsNotes ? 'NOTES ' : '' }}@endif{{ $groupe['nom'] }}
                    @if(!$isWide)
                    @php
                        $effectifRow = $rows->firstWhere('is_total', true);
                        $totalAgents = $effectifRow['nombre'] ?? $effectifRow['nb_raw'] ?? 0;
                    @endphp
                    <span class="ml-2 text-[11px] font-normal opacity-75">({{ $totalAgents }} agents)</span>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            @if($isWide)
                            <tr style="background:#4E9A56">
                                @foreach($groupe['headers'] as $hi => $col)
                                <th class="px-3 py-2.5 text-[10px] font-black uppercase tracking-wide text-white {{ $hi === 0 ? 'text-left' : 'text-center' }}">
                                    {{ $col }}
                                </th>
                                @endforeach
                            </tr>
                            @elseif($pIsNotes)
                            <tr style="background:#4E9A56">
                                <th class="px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-white">NOTE/10</th>
                                <th class="px-5 py-2.5 text-center text-[11px] font-black uppercase tracking-wide text-white">NOMBRE</th>
                                <th class="px-5 py-2.5 text-center text-[11px] font-black uppercase tracking-wide text-white">%</th>
                            </tr>
                            @else
                            @php $colonnes = $groupe['colonnes'] ?? ['Catégorie', 'Nombre', '%']; @endphp
                            <tr style="background:#4E9A56">
                                @foreach($colonnes as $col)
                                <th class="px-5 py-2.5 text-left text-[11px] font-black uppercase tracking-wide text-white">{{ $col }}</th>
                                @endforeach
                            </tr>
                            @endif
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($rows as $i => $row)
                            @php $isTotal = $row['is_total'] ?? false; @endphp

                            @if($isWide)
                            <tr class="{{ $isTotal ? '' : ($i % 2 === 0 ? 'bg-white' : 'bg-[#E8F5E9]') }}"
                                @if($isTotal) style="background:#1D6F42" @endif>
                                @foreach($row['cells'] ?? [] as $ci => $val)
                                <td class="px-3 py-2 text-xs {{ $isTotal ? 'font-black text-white' : 'text-slate-700' }} {{ $ci === 0 ? 'text-left font-semibold' : 'text-center' }}">
                                    {{ $val === '-' ? '—' : $val }}
                                </td>
                                @endforeach
                            </tr>

                            @elseif($isTotal)
                            <tr style="background:#1D6F42">
                                @if($pIsNotes)
                                <td class="px-5 py-2.5 text-xs font-black text-white">{{ $row['note'] ?? 'EFFECTIF' }}</td>
                                <td class="px-5 py-2.5 text-center text-xs font-black text-white">{{ $row['nombre'] ?? '' }}</td>
                                <td class="px-5 py-2.5 text-center text-xs font-black text-white">{{ $row['pct'] ?? '' }}</td>
                                @else
                                <td class="px-5 py-2.5 text-xs font-black text-white">{{ $row['col1'] ?? 'EFFECTIF' }}</td>
                                <td class="px-5 py-2.5 text-center text-xs font-black text-white">{{ $row['nombre'] ?? '' }}</td>
                                <td class="px-5 py-2.5 text-center text-xs font-black text-white">{{ $row['pct'] ?? '' }}</td>
                                @endif
                            </tr>
                            @else
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-[#E8F5E9]' }} hover:bg-emerald-50/60">
                                @if($pIsNotes)
                                <td class="px-5 py-2.5 text-xs font-semibold text-slate-700">{{ $row['note'] ?? '' }}</td>
                                <td class="px-5 py-2.5 text-center text-xs text-slate-700">
                                    @if(isset($row['nb_raw']) && $row['nb_raw'] > 0)
                                        <span class="font-bold">{{ $row['nombre'] }}</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-2.5 text-center text-xs text-slate-600">
                                    @if(isset($row['pct_raw']) && $row['pct_raw'] > 0)
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="h-1.5 w-14 rounded-full bg-slate-100 overflow-hidden">
                                                <div class="h-full rounded-full bg-emerald-500" style="width:{{ min($row['pct_raw'], 100) }}%"></div>
                                            </div>
                                            <span>{{ $row['pct'] }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-300">0,00%</span>
                                    @endif
                                </td>
                                @else
                                <td class="px-5 py-2.5 text-xs text-slate-700">{{ $row['col1'] ?? '' }}</td>
                                <td class="px-5 py-2.5 text-center text-xs font-bold text-slate-700">{{ $row['nombre'] ?? '' }}</td>
                                <td class="px-5 py-2.5 text-center text-xs text-slate-600">{{ $row['pct'] ?? '' }}</td>
                                @endif
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach

            @endif {{-- $pGroupes->isEmpty() --}}
            @endif {{-- $hasParams --}}

        </div>
    </div>
</div>
