@extends($layout)

@section('title', 'Mes formations | SGP-RCPB')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="mx-auto max-w-6xl flex flex-col gap-6">

    {{-- En-tête --}}
    <header class="admin-panel px-6 py-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Formations</p>
                <h1 class="text-xl font-black tracking-tight text-slate-950">Mes formations</h1>
                <p class="mt-0.5 text-sm text-slate-500">Historique de vos formations et attestations</p>
            </div>
        </div>
    </header>

    {{-- Statistiques --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="admin-panel flex items-center gap-4 px-6 py-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                <i class="fas fa-graduation-cap text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</p>
                <p class="text-xs font-semibold text-slate-500">Formation{{ $stats['total'] > 1 ? 's' : '' }} au total</p>
            </div>
        </div>
        <div class="admin-panel flex items-center gap-4 px-6 py-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                <i class="fas fa-calendar-check text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ $stats['ce_mois'] }}</p>
                <p class="text-xs font-semibold text-slate-500">Ce mois-ci</p>
            </div>
        </div>
        <div class="admin-panel flex items-center gap-4 px-6 py-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-violet-600">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ number_format($stats['heures']) }}</p>
                <p class="text-xs font-semibold text-slate-500">Heures de formation</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="admin-panel px-6 py-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            {{-- Recherche --}}
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400 mb-1">Recherche</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Titre de la formation…"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                </div>
            </div>

            {{-- Domaine --}}
            <div class="min-w-[160px]">
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400 mb-1">Domaine</label>
                <select name="domaine"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                    <option value="">Tous les domaines</option>
                    @foreach($domaines as $key => $label)
                        <option value="{{ $key }}" @selected(request('domaine') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Année --}}
            <div class="min-w-[120px]">
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-400 mb-1">Année</label>
                <select name="annee"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                    <option value="">Toutes</option>
                    @foreach($annees as $a)
                        <option value="{{ $a }}" @selected((string)request('annee') === (string)$a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-black text-white shadow-sm hover:bg-emerald-700 transition">
                    <i class="fas fa-filter text-xs"></i> Filtrer
                </button>
                @if(request('search') || request('domaine') || request('annee'))
                    <a href="{{ url()->current() }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-500 hover:bg-slate-50 transition">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table des formations --}}
    <div class="admin-panel overflow-hidden">
        @if($formations->isEmpty())
            <div class="flex flex-col items-center justify-center gap-3 py-16 text-center text-slate-400">
                <i class="fas fa-graduation-cap text-4xl opacity-30"></i>
                <p class="text-sm font-semibold">Aucune formation trouvée</p>
                @if(request('search') || request('domaine') || request('annee'))
                    <p class="text-xs">Essayez d'élargir vos critères de recherche</p>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-400">Agent</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-400">Formation</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-400">Domaine</th>
                            <th class="px-5 py-3 text-left text-xs font-black uppercase tracking-[0.12em] text-slate-400">Période</th>
                            <th class="px-4 py-3 text-center text-xs font-black uppercase tracking-[0.12em] text-slate-400">Durée</th>
                            <th class="px-5 py-3 text-right text-xs font-black uppercase tracking-[0.12em] text-slate-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($formations as $formation)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                {{-- Agent --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-xs font-black text-emerald-700">
                                            {{ strtoupper(substr($formation->agent->prenom ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="truncate font-bold text-slate-800 text-sm">
                                                {{ trim(($formation->agent->prenom ?? '') . ' ' . ($formation->agent->nom ?? '')) }}
                                            </p>
                                            <p class="truncate text-xs text-slate-400">{{ $formation->agent->role ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Titre --}}
                                <td class="px-5 py-3.5">
                                    <p class="font-semibold text-slate-800 line-clamp-1">{{ $formation->titre }}</p>
                                </td>

                                {{-- Domaine --}}
                                <td class="px-5 py-3.5">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                                        {{ $formation->domaine_label }}
                                    </span>
                                </td>

                                {{-- Période --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <p class="text-sm text-slate-600">
                                        {{ $formation->date_debut->translatedFormat('d M Y') }}
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        → {{ $formation->date_fin->translatedFormat('d M Y') }}
                                    </p>
                                </td>

                                {{-- Durée --}}
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 text-sm font-black text-emerald-700">
                                        {{ $formation->duree_heures }}h
                                    </span>
                                </td>

                                {{-- PDF --}}
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route($pdfRoutePrefix . '.formations.pdf', $formation) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-black text-rose-600 transition hover:bg-rose-100"
                                       title="Télécharger l'attestation PDF">
                                        <i class="fas fa-file-pdf"></i>
                                        <span>Attestation</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($formations->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">
                    {{ $formations->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
</div>
@endsection
