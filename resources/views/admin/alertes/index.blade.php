@extends('layouts.app')

@section('title', 'Alertes | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">

        @if (session('status'))
            <div id="alerte-status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('alerte-status-message')?.remove(), 3000);</script>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Alertes</h1>
                    <p class="mt-1 text-sm text-slate-400">Suivi des alertes de sécurité et des alertes personnalisées du système.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="document.getElementById('modal-create-alerte').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="fas fa-plus text-xs"></i> Nouvelle alerte
                    </button>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-bell text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $counts['toutes'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Total Alertes</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-rose-400 to-pink-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-shield-halved text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $totalSecurite }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Sécurité</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-blue-400 to-indigo-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-pen-to-square text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $totalPersonnalisees }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Personnalisées</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-circle-exclamation text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $totalCritiques }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Critiques / Hautes</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-green-400 to-emerald-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-circle-check text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $totalResolues }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Résolues</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-red-500 to-rose-600 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20">
                        <i class="fas fa-calendar-day text-sm"></i>
                    </span>
                    <span class="text-3xl font-black">{{ $tentativesAujourdhui }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Aujourd'hui</p>
            </div>
        </div>

        {{-- Chart: 7 derniers jours --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-lg font-black tracking-tight text-slate-900">Évolution des alertes — 7 derniers jours</h2>
            <div id="chart-alertes-7j" style="min-height: 260px;"></div>
        </div>

        {{-- Tabs --}}
        <div class="flex flex-wrap gap-2">
            @foreach ([
                'toutes'         => ['label' => 'Toutes',          'icon' => 'fas fa-list'],
                'securite'       => ['label' => 'Sécurité',        'icon' => 'fas fa-shield-halved'],
                'personnalisees' => ['label' => 'Personnalisées',  'icon' => 'fas fa-pen-to-square'],
                'critiques'      => ['label' => 'Critiques',       'icon' => 'fas fa-circle-exclamation'],
            ] as $key => $meta)
                <a href="{{ route('admin.alertes.index', ['tab' => $key]) }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold transition
                          {{ $tab === $key ? 'bg-blue-500 text-white shadow-sm' : 'bg-white text-slate-500 hover:bg-slate-50' }}">
                    <i class="{{ $meta['icon'] }} text-xs"></i>
                    {{ $meta['label'] }}
                    <span class="ml-1 inline-flex min-w-[22px] items-center justify-center rounded-full {{ $tab === $key ? 'bg-white/20' : 'bg-slate-100' }} px-1.5 py-0.5 text-[10px] font-black">
                        {{ $counts[$key] }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Search + Table --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-6">
                <label for="alerte-search" class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                <div class="relative mt-1.5">
                    <input
                        id="alerte-search"
                        type="text"
                        placeholder="Rechercher par titre, type, priorité, IP, auteur..."
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400"
                        autocomplete="off"
                    >
                    <div id="alerte-suggestions" class="absolute left-0 right-0 top-full z-20 mt-1 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">#</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Type</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Priorité</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Titre</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Message</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Adresse IP</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Statut</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Auteur</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">Date</th>
                            <th class="px-3 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $alerte)
                            <tr class="border-b border-slate-50 transition hover:bg-slate-50"
                                data-search-content="{{ strtolower(trim($alerte['titre'].' '.$alerte['type'].' '.$alerte['priorite'].' '.($alerte['ip_address'] ?? '').' '.$alerte['auteur'].' '.$alerte['statut'])) }}">
                                <td class="whitespace-nowrap px-3 py-3">{{ $loop->iteration }}</td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    @if ($alerte['type'] === 'securite')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-bold text-rose-600">
                                            <i class="fas fa-shield-halved text-[9px]"></i> Sécurité
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-bold text-blue-600">
                                            <i class="fas fa-pen-to-square text-[9px]"></i> Personnalisée
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    @php
                                        $prioriteColors = [
                                            'critique' => 'bg-red-100 text-red-700',
                                            'haute'    => 'bg-orange-100 text-orange-700',
                                            'moyenne'  => 'bg-amber-100 text-amber-700',
                                            'basse'    => 'bg-slate-100 text-slate-600',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $prioriteColors[$alerte['priorite']] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($alerte['priorite']) }}
                                    </span>
                                </td>
                                <td class="max-w-[200px] truncate px-3 py-3 font-semibold text-slate-800">{{ $alerte['titre'] }}</td>
                                <td class="max-w-[180px] truncate px-3 py-3 text-slate-500">{{ Str::limit($alerte['message'] ?? '-', 40) }}</td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    @if ($alerte['ip_address'])
                                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600">
                                            <i class="fas fa-network-wired text-[9px] text-slate-400"></i>
                                            {{ $alerte['ip_address'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    @php
                                        $statutColors = [
                                            'active'  => 'bg-emerald-100 text-emerald-700',
                                            'resolue' => 'bg-sky-100 text-sky-700',
                                            'ignoree' => 'bg-slate-100 text-slate-500',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold {{ $statutColors[$alerte['statut']] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ ucfirst($alerte['statut']) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3">{{ $alerte['auteur'] }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-slate-500">{{ optional($alerte['date'])->format('d/m/Y H:i') }}</td>
                                <td class="whitespace-nowrap px-3 py-3 text-right">
                                    @if ($alerte['type'] === 'personnalisee')
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Résoudre --}}
                                            @if ($alerte['statut'] === 'active')
                                                <form method="POST" action="{{ route('admin.alertes.statut', $alerte['id']) }}" class="inline-flex">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="statut" value="resolue">
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-500" title="Résoudre">
                                                        <i class="fas fa-check text-xs"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.alertes.statut', $alerte['id']) }}" class="inline-flex">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="statut" value="ignoree">
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-amber-50 hover:text-amber-500" title="Ignorer">
                                                        <i class="fas fa-eye-slash text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.alertes.destroy', $alerte['id']) }}" onsubmit="return confirm('Supprimer cette alerte ?');" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition hover:bg-rose-50 hover:text-rose-500" title="Supprimer">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-300">Auto</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-10 text-center text-sm text-slate-400">
                                    Aucune alerte trouvée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal Créer Alerte --}}
<div id="modal-create-alerte" class="fixed inset-0 z-50 hidden">
    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" onclick="document.getElementById('modal-create-alerte').classList.add('hidden')"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900">Nouvelle alerte</h2>
                <button onclick="document.getElementById('modal-create-alerte').classList.add('hidden')" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.alertes.store') }}">
                @csrf
                <input type="hidden" name="type" value="personnalisee">
                <div class="space-y-4">
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Titre *</label>
                        <input type="text" name="titre" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Titre de l'alerte">
                    </div>
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Message</label>
                        <textarea name="message" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Description détaillée (optionnel)"></textarea>
                    </div>
                    <div>
                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Priorité *</label>
                        <select name="priorite" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="basse">Basse</option>
                            <option value="moyenne" selected>Moyenne</option>
                            <option value="haute">Haute</option>
                            <option value="critique">Critique</option>
                        </select>
                    </div>
                </div>
                <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50/50 px-4 py-3">
                    <label class="flex cursor-pointer items-center gap-3">
                        <input type="checkbox" name="diffuser_email" value="1" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <span class="text-sm font-bold text-slate-700">Diffuser par email</span>
                            <p class="text-[11px] text-slate-400">Envoyer cette alerte par mail à tous les utilisateurs (agents, chefs, directeurs...)</p>
                        </div>
                    </label>
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-create-alerte').classList.add('hidden')" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        Annuler
                    </button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                        <i class="fas fa-plus mr-1 text-xs"></i> Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Chart: Alertes 7 jours ---
    if (document.querySelector('#chart-alertes-7j')) {
        new ApexCharts(document.querySelector('#chart-alertes-7j'), {
            chart: { type: 'area', height: 260, fontFamily: 'Inter, sans-serif', toolbar: { show: false } },
            series: [
                { name: 'Sécurité', data: @json($chartSecurite) },
                { name: 'Personnalisées', data: @json($chartPersonnalisees) },
            ],
            xaxis: {
                categories: @json($chartCategories),
                labels: { style: { fontSize: '10px', fontWeight: 700, colors: '#94a3b8' } },
            },
            yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600, colors: '#94a3b8' } }, min: 0 },
            colors: ['#f43f5e', '#6366f1'],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            legend: { position: 'top', fontSize: '12px', fontWeight: 700, labels: { colors: '#64748b' } },
            tooltip: { y: { formatter: function (val) { return val + ' alerte(s)'; } } },
        }).render();
    }

    // --- Search with suggestions ---
    var searchInput = document.getElementById('alerte-search');
    var suggestionsBox = document.getElementById('alerte-suggestions');
    if (!searchInput || !suggestionsBox) return;

    var rows = Array.from(document.querySelectorAll('tr[data-search-content]'));
    var pool = new Set();
    rows.forEach(function (row) {
        var cells = row.querySelectorAll('td');
        if (cells.length < 9) return;
        [cells[3], cells[4], cells[5], cells[7]].forEach(function (cell) {
            var txt = (cell.innerText || '').replace(/\s+/g, ' ').trim();
            if (txt.length >= 2 && txt !== '-' && txt !== 'Auto') pool.add(txt);
        });
    });
    var terms = Array.from(pool);

    function hideSuggestions() {
        suggestionsBox.innerHTML = '';
        suggestionsBox.classList.add('hidden');
    }

    function filterRows(query) {
        var q = query.trim().toLowerCase();
        rows.forEach(function (row) {
            row.style.display = q === '' || row.dataset.searchContent.indexOf(q) !== -1 ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', function () {
        var q = searchInput.value.trim().toLowerCase();
        filterRows(q);
        if (q.length < 1) { hideSuggestions(); return; }
        var matches = terms.filter(function (t) { return t.toLowerCase().indexOf(q) !== -1; }).slice(0, 6);
        if (matches.length === 0) { hideSuggestions(); return; }
        suggestionsBox.innerHTML = matches.map(function (m) {
            return '<button type="button" class="flex w-full items-center px-3 py-2 text-left text-sm text-slate-600 hover:bg-slate-50">' + m + '</button>';
        }).join('');
        suggestionsBox.classList.remove('hidden');
        suggestionsBox.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                searchInput.value = btn.textContent;
                filterRows(btn.textContent.toLowerCase());
                hideSuggestions();
            });
        });
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) hideSuggestions();
    });
});
</script>
@endpush
