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
                <p class="mt-1 text-sm text-slate-500">{{ $formations->count() }} formation(s) enregistrée(s)</p>
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

    {{-- ══ SECTION EN ATTENTE DE VALIDATION ══ --}}
    @if($enAttenteCount > 0)
    <div class="overflow-hidden rounded-[24px] border-2 border-amber-200 bg-amber-50 shadow-sm">
        <div class="flex items-center justify-between gap-4 border-b border-amber-200 bg-amber-100/60 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-white">
                    <i class="fas fa-clock text-sm"></i>
                </div>
                <div>
                    <p class="font-black text-amber-900">Formations en attente de validation</p>
                    <p class="text-xs text-amber-700">{{ $enAttenteCount }} formation(s) soumise(s) par des agents</p>
                </div>
            </div>
        </div>
        <div class="divide-y divide-amber-100">
            @foreach($enAttente as $f)
            <div class="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-200 text-sm font-black text-amber-800">
                        {{ strtoupper(substr($f->agent->prenom ?? 'A', 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-900">{{ $f->theme }}</p>
                        <p class="text-xs text-slate-500">
                            {{ trim(($f->agent->prenom ?? '') . ' ' . ($f->agent->nom ?? '')) }}
                            · {{ $f->type_label ?? ucfirst($f->type) }}
                            · {{ $f->domaine_label }}
                            · {{ $f->date_debut->format('d/m/Y') }} – {{ $f->date_fin->format('d/m/Y') }}
                            · {{ $f->duree_heures }}h
                        </p>
                        @if($f->attestation_path)
                            <a href="{{ Storage::url($f->attestation_path) }}" target="_blank"
                               class="mt-1 inline-flex items-center gap-1 text-xs font-bold text-amber-700 hover:underline">
                                <i class="fas fa-paperclip text-[10px]"></i> Voir l'attestation
                            </a>
                        @endif
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    {{-- Valider --}}
                    <form method="POST" action="{{ route(($routePrefix ?? 'rh').'.formations.valider', $f) }}">
                        @csrf
                        <input type="hidden" name="decision" value="validee">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-700">
                            <i class="fas fa-check text-[10px]"></i> Valider
                        </button>
                    </form>
                    {{-- Refuser (modal) --}}
                    <button type="button"
                            onclick="document.getElementById('refus-modal-{{ $f->id }}').classList.remove('hidden')"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-white px-4 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50">
                        <i class="fas fa-times text-[10px]"></i> Refuser
                    </button>
                </div>
            </div>

            {{-- Modal refus --}}
            <div id="refus-modal-{{ $f->id }}"
                 class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
                <div class="w-full max-w-md rounded-[24px] bg-white p-6 shadow-2xl">
                    <p class="font-black text-slate-900">Motif du refus</p>
                    <p class="mt-0.5 text-sm text-slate-500">Formation : <strong>{{ $f->theme }}</strong></p>
                    <form method="POST" action="{{ route(($routePrefix ?? 'rh').'.formations.valider', $f) }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="decision" value="refusee">
                        <textarea name="motif_refus" rows="3" required
                                  placeholder="Expliquer le motif du refus…"
                                  class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-rose-400 focus:bg-white"></textarea>
                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button"
                                    onclick="document.getElementById('refus-modal-{{ $f->id }}').classList.add('hidden')"
                                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">
                                Annuler
                            </button>
                            <button type="submit"
                                    class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700">
                                Confirmer le refus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
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

    {{-- Table des formations validées --}}
    <div class="admin-panel overflow-hidden">
        <div class="border-b border-slate-100 px-6 py-3">
            <p class="text-xs font-black uppercase tracking-[0.15em] text-slate-400">Formations enregistrées</p>
        </div>
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
            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="min-w-full text-sm text-slate-700">
                    <thead class="bg-slate-50 text-[11px] font-black uppercase tracking-[0.14em] text-slate-400 sticky top-0 z-10">
                        <tr>
                            <th class="px-6 py-3 text-left">Agent</th>
                            <th class="px-4 py-3 text-left">Formation</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Domaine</th>
                            <th class="px-4 py-3 text-left">Période</th>
                            <th class="px-4 py-3 text-left">Durée</th>
                            <th class="px-4 py-3 text-left">Statut</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($formations as $f)
                            @php
                                $statutCls = match($f->statut ?? 'validee') {
                                    'en_attente' => 'bg-amber-100 text-amber-700',
                                    'validee'    => 'bg-emerald-100 text-emerald-700',
                                    'refusee'    => 'bg-rose-100 text-rose-700',
                                    default      => 'bg-slate-100 text-slate-500',
                                };
                                $statutLabel = match($f->statut ?? 'validee') {
                                    'en_attente' => 'En attente',
                                    'validee'    => 'Validée',
                                    'refusee'    => 'Refusée',
                                    default      => ucfirst($f->statut),
                                };
                            @endphp
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
                                            <p class="text-[11px] text-slate-400">{{ $f->agent->role_genree ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-800 max-w-[200px] truncate">{{ $f->theme }}</p>
                                    @if($f->attestation_path)
                                        <a href="{{ Storage::url($f->attestation_path) }}" target="_blank"
                                           class="mt-0.5 inline-flex items-center gap-1 text-[10px] font-bold text-amber-600 hover:underline">
                                            <i class="fas fa-paperclip text-[9px]"></i> Attestation
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold
                                        {{ ($f->type ?? 'interne') === 'externe' ? 'bg-violet-100 text-violet-700' : 'bg-sky-100 text-sky-700' }}">
                                        {{ ucfirst($f->type ?? 'interne') }}
                                    </span>
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
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $statutCls }}">
                                        {{ $statutLabel }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($f->statut !== 'en_attente')
                                        <a href="{{ route(($routePrefix ?? 'rh').'.formations.pdf', $f) }}"
                                           title="Télécharger PDF"
                                           class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-500 transition hover:bg-red-50 hover:text-red-600">
                                            <i class="fas fa-file-pdf text-xs"></i>
                                        </a>
                                        @endif
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
            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $formations->count() }} résultat(s)</div>
        @endif
    </div>

</div>
</div>
@endsection
