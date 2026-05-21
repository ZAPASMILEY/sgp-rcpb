@extends('layouts.directeur')

@section('title', 'Personnel | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Espace Directeur / Personnel</p>
                    <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">Personnel</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $ctx->getNom() }}</p>
                </div>
                <a href="{{ route('directeur.personnel.export', request()->query()) }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs font-black text-emerald-700 shadow-sm transition hover:bg-emerald-100">
                    <i class="fas fa-file-csv"></i> Exporter CSV
                </a>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>
        @endif

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Total</p>
                <p class="mt-1 text-2xl font-black text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600">Évalués</p>
                <p class="mt-1 text-2xl font-black text-emerald-900">{{ $stats['evalues'] }}</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-amber-50/70 px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-600">Non évalués</p>
                <p class="mt-1 text-2xl font-black text-amber-900">{{ $stats['total'] - $stats['evalues'] }}</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600">Note moyenne</p>
                <p class="mt-1 text-2xl font-black text-blue-900">
                    {{ $stats['note_moy'] !== null ? number_format($stats['note_moy'], 2, ',', ' ').' /10' : '—' }}
                </p>
            </div>
        </div>

        {{-- Filtres --}}
        <section class="admin-panel px-6 py-5 lg:px-8">
            <form method="GET" action="{{ route('directeur.personnel') }}" class="flex flex-wrap items-end gap-3">

                <div class="space-y-1">
                    <label class="text-xs font-black uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Nom, prénom, fonction…"
                           class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-100 w-52">
                </div>

                @if ($ctx->hasCaisses() && $caisses->isNotEmpty())
                <div class="space-y-1">
                    <label class="text-xs font-black uppercase tracking-[0.15em] text-slate-500">Caisse</label>
                    <select name="caisse" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-100 w-44">
                        <option value="">Toutes les caisses</option>
                        @foreach ($caisses as $css)
                            <option value="{{ $css->id }}" @selected((int)$filterCaisse === $css->id)>{{ $css->nom }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if ($services->isNotEmpty())
                <div class="space-y-1">
                    <label class="text-xs font-black uppercase tracking-[0.15em] text-slate-500">Service</label>
                    <select name="service" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-100 w-44">
                        <option value="">Tous les services</option>
                        @foreach ($services as $svc)
                            <option value="{{ $svc->id }}" @selected((int)$filterService === $svc->id)>{{ $svc->nom }}</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="space-y-1">
                    <label class="text-xs font-black uppercase tracking-[0.15em] text-slate-500">Sexe</label>
                    <select name="sexe" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-100 w-36">
                        <option value="">Tous</option>
                        <option value="homme" @selected($sexe === 'homme')>Homme</option>
                        <option value="femme" @selected($sexe === 'femme')>Femme</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-black uppercase tracking-[0.15em] text-slate-500">Fonction</label>
                    <select name="fonction" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-100 w-52">
                        <option value="">Toutes</option>
                        @foreach ($fonctions as $key => $label)
                            <option value="{{ $key }}" @selected($fonction === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-black uppercase tracking-[0.15em] text-slate-500">Statut éval.</label>
                    <select name="statut" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-100 w-40">
                        <option value="">Tous</option>
                        <option value="valide"    @selected($statut === 'valide')>Validée</option>
                        <option value="soumis"    @selected($statut === 'soumis')>Soumise</option>
                        <option value="brouillon" @selected($statut === 'brouillon')>Brouillon</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-black uppercase tracking-[0.15em] text-slate-500">Trier par</label>
                    <select name="sort" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-300 focus:ring-2 focus:ring-blue-100 w-44">
                        <option value="nom"       @selected($sortBy === 'nom')>Nom A→Z</option>
                        <option value="service"   @selected($sortBy === 'service')>Structure</option>
                        <option value="fonction"  @selected($sortBy === 'fonction')>Fonction</option>
                        <option value="note_desc" @selected($sortBy === 'note_desc')>Meilleure note</option>
                        <option value="note_asc"  @selected($sortBy === 'note_asc')>Note la plus basse</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white transition hover:bg-slate-800">
                        <i class="fas fa-filter text-[10px]"></i> Filtrer
                    </button>
                    <a href="{{ route('directeur.personnel') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 transition hover:border-slate-300">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </section>

        {{-- Liste --}}
        <section class="admin-panel overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 lg:px-8">
                <p class="text-sm font-black text-slate-700">{{ $agents->count() }} agent(s) affiché(s)</p>
                <a href="{{ route('directeur.personnel.export', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700">
                    <i class="fas fa-download text-[10px]"></i> Télécharger
                </a>
            </div>

            @if ($agents->isEmpty())
                <div class="px-6 py-12 text-center lg:px-8">
                    <i class="fas fa-users text-3xl text-slate-200"></i>
                    <p class="mt-3 text-sm font-semibold text-slate-400">Aucun agent trouvé pour les critères sélectionnés</p>
                    <a href="{{ route('directeur.personnel') }}" class="mt-3 inline-flex text-xs font-bold text-blue-600 hover:underline">Réinitialiser les filtres</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/80">
                                <th class="px-5 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Agent</th>
                                <th class="px-5 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Rôle</th>
                                <th class="px-5 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Structure</th>
                                <th class="px-5 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Dernière note</th>
                                <th class="px-5 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Mention</th>
                                <th class="px-5 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($agents as $item)
                                @php
                                    $agent  = $item['agent'];
                                    $note   = $item['note'];
                                    $mention = $item['mention'];
                                    $noteClass = $note !== null ? match(true) {
                                        $note >= 8.5 => 'bg-emerald-100 text-emerald-700',
                                        $note >= 7   => 'bg-sky-100 text-sky-700',
                                        $note >= 5   => 'bg-amber-100 text-amber-700',
                                        default      => 'bg-rose-100 text-rose-700',
                                    } : null;
                                    $mentionClass = match($mention) {
                                        'Excellent'   => 'bg-emerald-100 text-emerald-700',
                                        'Bien'        => 'bg-sky-100 text-sky-700',
                                        'Passable'    => 'bg-amber-100 text-amber-700',
                                        'Insuffisant' => 'bg-rose-100 text-rose-700',
                                        default       => '',
                                    };
                                    $statutClass = match($item['statut']) {
                                        'valide'    => 'bg-emerald-100 text-emerald-700',
                                        'soumis'    => 'bg-amber-100 text-amber-700',
                                        'refuse'    => 'bg-rose-100 text-rose-700',
                                        'brouillon' => 'bg-slate-100 text-slate-600',
                                        default     => '',
                                    };
                                    $statutLabel = match($item['statut']) {
                                        'valide'    => 'Validée',
                                        'soumis'    => 'Soumise',
                                        'refuse'    => 'Refusée',
                                        'brouillon' => 'Brouillon',
                                        default     => '—',
                                    };
                                    // Libellé de structure : service > caisse > —
                                    $structure     = $item['service']?->nom ?? $item['caisse']?->nom ?? '—';
                                    $structureType = $item['service'] ? 'service' : ($item['caisse'] ? 'caisse' : null);
                                    $structureColor = match($structureType) {
                                        'service' => 'bg-indigo-50 text-indigo-700',
                                        'caisse'  => 'bg-violet-50 text-violet-700',
                                        default   => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-black text-blue-700">
                                                {{ strtoupper(substr($agent->prenom ?? $agent->nom ?? 'A', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-black text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                                @if ($agent->email)
                                                    <p class="text-xs text-slate-400">{{ $agent->email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">{{ $agent->role ?: '—' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $structureColor }}">
                                            {{ $structure }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($note !== null)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $noteClass }}">
                                                {{ number_format($note, 2, ',', ' ') }}/10
                                            </span>
                                        @else
                                            <span class="text-[11px] text-slate-300">Non évalué</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($mention)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $mentionClass }}">{{ $mention }}</span>
                                        @else
                                            <span class="text-[11px] text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if ($item['statut'])
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statutClass }}">{{ $statutLabel }}</span>
                                        @else
                                            <span class="text-[11px] text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
