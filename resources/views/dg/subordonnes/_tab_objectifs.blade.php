{{-- Tab : Fiches d'objectifs d'un subordonné du DG --}}
{{-- Variables : $fiches (paginated), $fichesStats, $filters --}}

@php
$ficheCards = [
    [
        'label'    => 'Total',
        'value'    => $fichesStats['total'],
        'icon'     => 'fas fa-folder-open',
        'tone'     => 'border-slate-100 bg-white text-slate-900',
        'iconWrap' => 'bg-slate-100 text-slate-600',
    ],
    [
        'label'    => 'Acceptées',
        'value'    => $fichesStats['acceptees'],
        'icon'     => 'fas fa-circle-check',
        'tone'     => 'border-emerald-100 bg-emerald-50/80 text-emerald-900',
        'iconWrap' => 'bg-white text-emerald-600',
    ],
    [
        'label'    => 'En attente',
        'value'    => $fichesStats['en_attente'],
        'icon'     => 'fas fa-hourglass-half',
        'tone'     => 'border-amber-100 bg-amber-50/80 text-amber-900',
        'iconWrap' => 'bg-white text-amber-600',
    ],
    [
        'label'    => 'Refusées',
        'value'    => $fichesStats['refusees'],
        'icon'     => 'fas fa-ban',
        'tone'     => 'border-rose-100 bg-rose-50/80 text-rose-900',
        'iconWrap' => 'bg-white text-rose-600',
    ],
];
@endphp

{{-- Stats --}}
<div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
    @foreach ($ficheCards as $card)
        <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $card['tone'] }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] opacity-60">{{ $card['label'] }}</p>
                    <p class="mt-1 text-3xl font-black leading-none">{{ $card['value'] }}</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['iconWrap'] }}">
                    <i class="{{ $card['icon'] }}"></i>
                </span>
            </div>
        </div>
    @endforeach
</div>

{{-- Recherche + filtre --}}
<form method="GET" action="{{ request()->url() }}"
      class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
    <input type="hidden" name="tab" value="objectifs">
    <div class="flex-1 min-w-48">
        <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input name="search" type="text" value="{{ $filters['search'] }}" placeholder="Titre, année..."
                   class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100">
        </div>
    </div>
    <select name="statut"
            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100">
        <option value="">Tous les statuts</option>
        <option value="en_attente" @selected($filters['statut'] === 'en_attente')>En attente</option>
        <option value="acceptee"   @selected($filters['statut'] === 'acceptee')>Acceptée</option>
        <option value="refusee"    @selected($filters['statut'] === 'refusee')>Refusée</option>
    </select>
    <button type="submit"
            class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
        <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
    </button>
    <a href="{{ request()->url() }}?tab=objectifs"
       class="inline-flex items-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:text-slate-900">
        Effacer
    </a>
</form>

{{-- Tableau --}}
<div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm text-slate-700">
            <thead class="bg-slate-50/80">
                <tr class="border-b border-slate-200 text-slate-500">
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Fiche</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Période</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Objectifs</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Avancement</th>
                    <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Statut</th>
                    <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($fiches as $fiche)
                    @php
                        $statut = $fiche->statut ?? 'en_attente';
                        $statusClasses = match ($statut) {
                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                            default    => 'border-slate-200 bg-slate-100 text-slate-700',
                        };
                        $statusLabel = match ($statut) {
                            'acceptee' => 'Acceptée',
                            'refusee'  => 'Refusée',
                            default    => 'En attente',
                        };
                        $progress = (int) ($fiche->avancement_percentage ?? 0);
                        $progressColor = $progress >= 50
                            ? 'bg-emerald-500'
                            : ($progress > 0 ? 'bg-amber-400' : 'bg-rose-400');
                    @endphp
                    <tr class="align-top hover:bg-slate-50/60">
                        <td class="px-4 py-4 font-black text-slate-900">
                            {{ ($fiches->firstItem() ?? 1) + $loop->index }}
                        </td>
                        <td class="px-4 py-4">
                            <p class="text-sm font-black text-slate-900">{{ $fiche->titre }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Année {{ $fiche->annee }}</p>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                            <p class="font-semibold text-slate-700">
                                {{ $fiche->date ? \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') : '-' }}
                            </p>
                            <p class="mt-1 text-xs font-semibold text-slate-400">
                                Échéance {{ $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') : '-' }}
                            </p>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-black text-cyan-700">
                                {{ $fiche->objectifs_count ?? 0 }} objectif(s)
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="min-w-[130px]">
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">Progression</span>
                                    <span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-black text-slate-700">{{ $progress }}%</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full {{ $progressColor }}" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <a href="{{ route('dg.objectifs.show', $fiche->id) }}"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600"
                               title="Voir la fiche">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                <i class="fas fa-inbox text-2xl text-slate-300"></i>
                                <p class="mt-2 text-sm font-black text-slate-700">Aucune fiche d'objectifs</p>
                                <p class="mt-1 text-xs text-slate-500">Créez une nouvelle fiche pour démarrer le suivi.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($fiches->hasPages())
    <div class="mt-5 border-t border-slate-200 pt-4">
        {{ $fiches->links() }}
    </div>
@endif
