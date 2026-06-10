{{-- Tab : Fiches d'objectifs des chefs de service du Directeur --}}
{{-- Variables : $fiches (paginated), $fichesStats, $filters, $serviceByChefUserId --}}

@php
$ficheCards = [
    ['label' => 'Total',      'value' => $fichesStats['total'],     'accent' => 'bg-slate-300',   'tone' => 'border-slate-100 bg-white'],
    ['label' => 'Acceptées',  'value' => $fichesStats['acceptees'], 'accent' => 'bg-emerald-500', 'tone' => 'border-emerald-100 bg-emerald-50/60'],
    ['label' => 'En attente', 'value' => $fichesStats['en_attente'],'accent' => 'bg-amber-400',   'tone' => 'border-amber-100 bg-amber-50/60'],
    ['label' => 'Refusées',   'value' => $fichesStats['refusees'],  'accent' => 'bg-rose-500',    'tone' => 'border-rose-100 bg-rose-50/60'],
];
$showServiceCol = empty($filters['serviceId']);
@endphp

{{-- ── Cartes stats ──────────────────────────────────────────────────────────── --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @foreach ($ficheCards as $card)
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
    <input type="hidden" name="tab" value="objectifs">
    @if(!empty($filters['serviceId']))
        <input type="hidden" name="service_id" value="{{ $filters['serviceId'] }}">
    @endif
    <i class="fas fa-filter text-xs text-slate-400 mr-1"></i>
    <div class="relative flex-1 min-w-44">
        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
            <i class="fas fa-search text-[10px]"></i>
        </span>
        <input name="search" type="text" value="{{ $filters['search'] }}" placeholder="Titre de fiche..."
               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs font-semibold text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
    </div>
    <select name="statut"
            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 outline-none transition focus:border-indigo-300 focus:ring-2 focus:ring-indigo-100">
        <option value="">Tous les statuts</option>
        <option value="en_attente" @selected($filters['statut'] === 'en_attente')>En attente</option>
        <option value="acceptee"   @selected($filters['statut'] === 'acceptee')>Acceptée</option>
        <option value="refusee"    @selected($filters['statut'] === 'refusee')>Refusée</option>
    </select>
    <button type="submit"
            class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-slate-700">
        Filtrer
    </button>
    @if ($filters['search'] || $filters['statut'])
    <a href="{{ request()->url() }}?tab=objectifs{{ !empty($filters['serviceId']) ? '&service_id='.$filters['serviceId'] : '' }}"
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
                    @if($showServiceCol)
                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Chef · Service</th>
                    @endif
                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Fiche</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Avancement</th>
                    <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</th>
                    <th class="px-5 py-3 text-center text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($fiches as $fiche)
                    @php
                        $statut = $fiche->statut ?? 'en_attente';
                        $statusCls = match ($statut) {
                            'acceptee'  => 'bg-emerald-100 text-emerald-700',
                            'refusee'   => 'bg-rose-100 text-rose-700',
                            'contesté'  => 'bg-orange-100 text-orange-700',
                            'brouillon' => 'bg-slate-100 text-slate-600',
                            default     => 'bg-amber-100 text-amber-700',
                        };
                        $dotCls = match ($statut) {
                            'acceptee'  => 'bg-emerald-500',
                            'refusee'   => 'bg-rose-500',
                            'contesté'  => 'bg-orange-400',
                            'brouillon' => 'bg-slate-400',
                            default     => 'bg-amber-400',
                        };
                        $statusLabel = match ($statut) {
                            'acceptee'  => 'Acceptée',
                            'refusee'   => 'Refusée',
                            'contesté'  => 'Contestée',
                            'brouillon' => 'Brouillon',
                            default     => 'En attente',
                        };
                        $progress = (int) ($fiche->avancement_percentage ?? 0);
                        $avBarCls = $progress >= 80 ? 'bg-emerald-500' : ($progress >= 50 ? 'bg-sky-500' : ($progress >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                        $avTxtCls = $progress >= 80 ? 'text-emerald-700' : ($progress >= 50 ? 'text-sky-700' : ($progress >= 25 ? 'text-amber-600' : 'text-slate-500'));
                        $objCount = $fiche->objectifs_count ?? 0;
                        $echeance = $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance) : null;
                        $expired  = $echeance && $echeance->isPast();
                        $chefUserId   = $fiche->assignable_id;
                        $serviceNom   = $serviceByChefUserId[$chefUserId] ?? null;
                        $chefUserName = $fiche->assignable?->name ?? '-';
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        @if($showServiceCol)
                        <td class="px-5 py-3.5">
                            <p class="font-black text-slate-800 text-xs">{{ $chefUserName }}</p>
                            @if($serviceNom)
                                <span class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-600">
                                    <i class="fas fa-sitemap text-[8px]"></i> {{ $serviceNom }}
                                </span>
                            @endif
                        </td>
                        @endif
                        {{-- Fiche --}}
                        <td class="px-5 py-3.5">
                            <p class="font-black text-slate-800">{{ $fiche->titre }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">
                                    <i class="fas fa-calendar-alt text-[9px]"></i>
                                    {{ $fiche->annee_value ?? \Carbon\Carbon::parse($fiche->date_echeance ?? $fiche->date)->year }}
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
                                    <div class="h-full rounded-full transition-all {{ $avBarCls }}" style="width:{{ $progress }}%"></div>
                                </div>
                                <span class="text-sm font-black {{ $avTxtCls }}">{{ $progress }}%</span>
                            </div>
                        </td>
                        {{-- Statut --}}
                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-black {{ $statusCls }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $dotCls }}"></span>
                                {{ $statusLabel }}
                            </span>
                            @if ($statut === 'refusee' && $fiche->motif_refus)
                                <p class="mt-1 max-w-[220px] truncate text-[10px] italic text-rose-600" title="{{ $fiche->motif_refus }}">
                                    « {{ $fiche->motif_refus }} »
                                </p>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="px-5 py-3.5 text-center">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('directeur.subordonnes.service.objectifs.show', $fiche->id) }}"
                                   class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-blue-100 hover:text-blue-600"
                                   title="Voir la fiche">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                @if ($statut !== 'acceptee')
                                    <form method="POST" action="{{ route('directeur.subordonnes.service.objectifs.destroy', $fiche->id) }}"
                                          onsubmit="return confirm('Supprimer cette fiche ?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-100 hover:text-rose-600"
                                                title="Supprimer">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $showServiceCol ? 5 : 4 }}" class="px-4 py-14 text-center">
                            <div class="mx-auto max-w-xs">
                                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                                    <i class="fas fa-bullseye text-xl text-slate-300"></i>
                                </div>
                                <p class="text-sm font-black text-slate-700">Aucune fiche d'objectifs</p>
                                <p class="mt-1 text-xs text-slate-400">Créez une nouvelle fiche pour démarrer le suivi.</p>
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
