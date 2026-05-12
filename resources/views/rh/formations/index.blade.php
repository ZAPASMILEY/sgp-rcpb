@extends($layout ?? 'layouts.rh')

@section('title', 'Formations | SGP-RCPB')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="flex flex-col gap-6">

    {{-- En-tête --}}
    <header class="admin-panel px-6 py-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Ressources Humaines</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Formations</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $formations->total() }} formation(s) enregistrée(s)</p>
            </div>
            <a href="{{ route(($routePrefix ?? 'rh').'.formations.create') }}"
               class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fas fa-plus text-xs"></i> Nouvelle formation
            </a>
        </div>
    </header>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
        </div>
    @endif

    {{-- Filtres --}}
    <form method="GET" action="{{ route(($routePrefix ?? 'rh').'.formations.index') }}"
          class="admin-panel px-6 py-4 lg:px-8">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher (titre, agent…)"
                   class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">

            <select name="domaine" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                <option value="">Tous les domaines</option>
                @foreach($domaines as $key => $label)
                    <option value="{{ $key }}" @selected(request('domaine') === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="annee" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                <option value="">Toutes les années</option>
                @foreach($annees as $yr)
                    <option value="{{ $yr }}" @selected(request('annee') == $yr)>{{ $yr }}</option>
                @endforeach
            </select>

            <select name="agent_id" class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                <option value="">Tous les agents</option>
                @foreach($agents as $ag)
                    <option value="{{ $ag->id }}" @selected(request('agent_id') == $ag->id)>
                        {{ trim($ag->prenom . ' ' . $ag->nom) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                <i class="fas fa-filter text-[10px]"></i> Filtrer
            </button>
            <a href="{{ route(($routePrefix ?? 'rh').'.formations.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-50">
                <i class="fas fa-times text-[10px]"></i> Effacer
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="admin-panel overflow-hidden">
        @if($formations->isEmpty())
            <div class="px-6 py-16 text-center">
                <i class="fas fa-graduation-cap text-4xl text-slate-200"></i>
                <p class="mt-3 text-sm font-semibold text-slate-400">Aucune formation trouvée.</p>
                <a href="{{ route(($routePrefix ?? 'rh').'.formations.create') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black text-white transition hover:bg-emerald-700">
                    <i class="fas fa-plus text-[10px]"></i> Enregistrer une formation
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-slate-700">
                    <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400">
                        <tr>
                            <th class="px-6 py-3 text-left">Agent</th>
                            <th class="px-4 py-3 text-left">Formation</th>
                            <th class="px-4 py-3 text-left">Domaine</th>
                            <th class="px-4 py-3 text-left">Période</th>
                            <th class="px-4 py-3 text-left">Durée</th>
                            <th class="px-4 py-3 text-left">Saisi par</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($formations as $f)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 text-xs font-black">
                                            {{ strtoupper(substr($f->agent->prenom ?? 'A', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">
                                                {{ trim(($f->agent->prenom ?? '') . ' ' . ($f->agent->nom ?? '')) }}
                                            </p>
                                            <p class="text-[11px] text-slate-400">{{ $f->agent->fonction ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-800 max-w-[220px] truncate">{{ $f->titre }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-[11px] font-black text-blue-700">
                                        {{ $f->domaine_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-[12px] text-slate-600 whitespace-nowrap">
                                    {{ $f->date_debut->format('d/m/Y') }}<br>
                                    <span class="text-slate-400">→ {{ $f->date_fin->format('d/m/Y') }}</span>
                                </td>
                                <td class="px-4 py-4 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $f->duree_heures }} h
                                </td>
                                <td class="px-4 py-4 text-[12px] text-slate-500">
                                    {{ $f->createdBy?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route(($routePrefix ?? 'rh').'.formations.pdf', $f) }}"
                                           title="Télécharger PDF"
                                           class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 transition hover:bg-red-50 hover:text-red-600">
                                            <i class="fas fa-file-pdf text-xs"></i>
                                        </a>
                                        <a href="{{ route(($routePrefix ?? 'rh').'.formations.edit', $f) }}"
                                           title="Modifier"
                                           class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 transition hover:bg-blue-50 hover:text-blue-600">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route(($routePrefix ?? 'rh').'.formations.destroy', $f) }}"
                                              onsubmit="return confirm('Supprimer cette formation ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    title="Supprimer"
                                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 transition hover:bg-rose-50 hover:text-rose-600">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($formations->hasPages())
                <div class="border-t border-slate-100 px-6 py-4">
                    {{ $formations->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
</div>
@endsection
