@extends('layouts.directeur')

@section('title', 'Personnel de ma direction | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace Directeur / Personnel</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Personnel de ma direction</h1>
                    <p class="mt-2 text-sm text-slate-600">{{ $direction->nom }}</p>
                </div>
            </div>
        </header>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Total agents</p>
                <p class="mt-1 text-2xl font-black text-slate-900">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600">Évalués</p>
                <p class="mt-1 text-2xl font-black text-emerald-900">{{ $stats['evalues'] }}</p>
            </div>
            <div class="rounded-2xl border border-blue-100 bg-blue-50/70 px-4 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600">Note moyenne</p>
                <p class="mt-1 text-2xl font-black text-blue-900">
                    {{ $stats['note_moy'] !== null ? number_format($stats['note_moy'], 2, ',', ' ').' /10' : '—' }}
                </p>
            </div>
        </div>

        {{-- Filtres & tri --}}
        <section class="admin-panel px-6 py-5 lg:px-8">
            <form method="GET" action="{{ route('directeur.personnel') }}" class="flex flex-wrap items-end gap-3">
                <div class="space-y-1">
                    <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Recherche</label>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Nom, prénom, fonction…"
                           class="ent-input w-56">
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Service</label>
                    <select name="service" class="ent-select w-48">
                        <option value="">Tous les services</option>
                        @foreach ($services as $svc)
                            <option value="{{ $svc->id }}" @selected((int)$filterService === $svc->id)>
                                {{ $svc->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Trier par</label>
                    <select name="sort" class="ent-select w-44">
                        <option value="nom"       @selected($sortBy === 'nom')>Nom A→Z</option>
                        <option value="service"   @selected($sortBy === 'service')>Service</option>
                        <option value="fonction"  @selected($sortBy === 'fonction')>Fonction</option>
                        <option value="note_desc" @selected($sortBy === 'note_desc')>Meilleure note</option>
                        <option value="note_asc"  @selected($sortBy === 'note_asc')>Note la plus basse</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="ent-btn ent-btn-primary">
                        <i class="fas fa-filter mr-1"></i> Filtrer
                    </button>
                    <a href="{{ route('directeur.personnel') }}" class="ent-btn ent-btn-soft">Réinitialiser</a>
                </div>
            </form>
        </section>

        {{-- Liste du personnel --}}
        <section class="admin-panel px-6 py-6 lg:px-8">
            @if ($agents->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-10 text-center text-sm text-slate-400">
                    Aucun agent trouvé pour les critères sélectionnés.
                </div>
            @else
                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Agent</th>
                                <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Fonction</th>
                                <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Service</th>
                                <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Dernière note</th>
                                <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Mention</th>
                                <th class="px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Statut éval.</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                        'Excellent' => 'bg-emerald-100 text-emerald-700',
                                        'Bien'      => 'bg-sky-100 text-sky-700',
                                        'Passable'  => 'bg-amber-100 text-amber-700',
                                        'Insuffisant' => 'bg-rose-100 text-rose-700',
                                        default     => '',
                                    };
                                    $statut = $item['statut'];
                                    $statutClass = match($statut) {
                                        'valide' => 'bg-emerald-100 text-emerald-700',
                                        'soumis' => 'bg-amber-100 text-amber-700',
                                        'refuse' => 'bg-rose-100 text-rose-700',
                                        'brouillon' => 'bg-slate-100 text-slate-600',
                                        default  => '',
                                    };
                                    $statutLabel = match($statut) {
                                        'valide' => 'Validée', 'soumis' => 'Soumise', 'refuse' => 'Refusée',
                                        'brouillon' => 'Brouillon', default => '—',
                                    };
                                @endphp
                                <tr class="border-b border-slate-50 hover:bg-slate-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-black text-blue-700">
                                                {{ strtoupper(substr($agent->prenom ?? $agent->nom, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-900">{{ $agent->prenom }} {{ $agent->nom }}</p>
                                                @if ($agent->email)
                                                    <p class="text-xs text-slate-400">{{ $agent->email }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $agent->fonction ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                                            {{ $item['service']?->nom ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($note !== null)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-black {{ $noteClass }}">
                                                {{ number_format($note, 2, ',', ' ') }}/10
                                            </span>
                                        @else
                                            <span class="text-slate-300 text-xs">Non évalué</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($mention)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $mentionClass }}">{{ $mention }}</span>
                                        @else
                                            <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($statut)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statutClass }}">{{ $statutLabel }}</span>
                                        @else
                                            <span class="text-slate-300 text-xs">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-slate-400">{{ $agents->count() }} agent(s) affiché(s)</p>
            @endif
        </section>

    </div>
</div>
@endsection
