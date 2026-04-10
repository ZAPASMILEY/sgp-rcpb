@extends('layouts.pca')

@section('title', 'Tableau de bord PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $overviewCards = [
            [
                'label' => "Fiches d'objectifs DG",
                'value' => $nbFichesObjectifsDG ?? 0,
                'meta' => 'Total fiches assignées',
                'icon' => 'fas fa-bullseye',
                'valueClass' => 'text-emerald-500',
                'iconClass' => 'bg-emerald-50 text-emerald-500',
                'borderClass' => 'border-slate-100',
            ],
            [
                'label' => 'Évaluations DG',
                'value' => $nbEvaluationsDG ?? 0,
                'meta' => 'Total évaluations reçues',
                'icon' => 'fas fa-clipboard-check',
                'valueClass' => 'text-blue-500',
                'iconClass' => 'bg-blue-50 text-blue-500',
                'borderClass' => 'border-slate-100',
            ],
            [
                'label' => 'Fiches en attente',
                'value' => $nbFichesObjectifsAttente ?? 0,
                'meta' => 'En attente de validation',
                'icon' => 'fas fa-hourglass-half',
                'valueClass' => 'text-amber-500',
                'iconClass' => 'bg-amber-50 text-amber-500',
                'borderClass' => 'border-slate-100',
            ],
            [
                'label' => 'Fiches acceptées',
                'value' => $nbFichesObjectifsAcceptees ?? 0,
                'meta' => 'Validées par le DG',
                'icon' => 'fas fa-check-circle',
                'valueClass' => 'text-emerald-700',
                'iconClass' => 'bg-emerald-100 text-emerald-700',
                'borderClass' => 'border-emerald-100',
            ],
        ];
    @endphp


    <div class="relative z-10 -mt-8 bg-[linear-gradient(180deg,#f6f9ff_0%,#fbfdff_100%)] px-4 pb-6 pt-0 lg:px-8">
        <div class="mx-auto max-w-[1500px] space-y-4">
            <section class="rounded-[26px] border border-white bg-white/90 px-5 py-4 shadow-[0_18px_60px_-35px_rgba(148,163,184,0.6)] backdrop-blur">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-base font-black text-emerald-700">Tableau de bord</p>
                        <div class="mt-1 flex flex-wrap items-center gap-3">
                            <h1 class="text-3xl font-black tracking-tight text-slate-900">Pilotage administratif du Directeur Général</h1>
                        </div>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Synthèse du {{ now()->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[520px]">
                        @foreach ($overviewCards as $card)
                            <div class="rounded-2xl bg-slate-50 px-4 py-3 border {{ $card['borderClass'] }}">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">{{ $card['label'] }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="mt-1 text-xl font-black {{ $card['valueClass'] }}">{{ $card['value'] }}</span>
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl {{ $card['iconClass'] }}"><i class="{{ $card['icon'] }} text-base"></i></span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-[11px] font-bold text-slate-400">{{ $card['meta'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif


            <!-- Charts Row (graphiques) -->
            <section class="grid grid-cols-1 gap-4 lg:grid-cols-3 mt-8">
                <!-- Donut: Statut des fiches d'objectifs DG -->
                <div class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <h2 class="text-base font-black tracking-tight text-slate-900">Statut des fiches d'objectifs DG</h2>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Répartition acceptées / en attente / refusées</p>
                    <div id="chart-fiches-dg" class="mt-2"></div>
                </div>
                <!-- Bar: Evolution des évaluations DG -->
                <div class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <h2 class="text-base font-black tracking-tight text-slate-900">Évolution des évaluations DG</h2>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Par période</p>
                    <div id="chart-evaluations-dg" class="mt-2"></div>
                </div>
                <!-- Area: Alertes DG -->
                <div class="rounded-[26px] border border-rose-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <h2 class="text-base font-black tracking-tight text-rose-600">Alertes DG</h2>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">7 derniers jours</p>
                    <div id="chart-alertes-dg" class="mt-2"></div>
                </div>
            </section>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.7fr)_minmax(340px,0.9fr)] mt-8">
                <div class="space-y-4">
                    <!-- Dernières fiches d'objectifs DG -->
                    <section class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-black tracking-tight text-slate-900">Dernières fiches d'objectifs DG</h2>
                                <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Fiches les plus récentes</p>
                            </div>
                        </div>
                        <ul class="divide-y divide-slate-200">
                            @forelse($dernieresFichesDG ?? [] as $fiche)
                                <li class="px-4 py-3 flex items-center justify-between">
                                    <span>{{ $fiche->titre }} ({{ $fiche->annee }})</span>
                                    <a href="{{ route('dg.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft">Voir</a>
                                </li>
                            @empty
                                <li class="px-4 py-3 text-slate-400">Aucune fiche récente.</li>
                            @endforelse
                        </ul>
                    </section>

                    <!-- Dernières évaluations DG -->
                    <section class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-black tracking-tight text-slate-900">Dernières évaluations DG</h2>
                                <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Évaluations les plus récentes</p>
                            </div>
                        </div>
                        <ul class="divide-y divide-slate-200">
                            @forelse($dernieresEvaluationsDG ?? [] as $evaluation)
                                <li class="px-4 py-3 flex items-center justify-between">
                                    <span>Période : {{ $evaluation->date_debut->format('m/Y') }} - {{ $evaluation->date_fin->format('m/Y') }}</span>
                                    <a href="{{ route('dg.evaluations.show', $evaluation) }}" class="ent-btn ent-btn-soft">Voir</a>
                                </li>
                            @empty
                                <li class="px-4 py-3 text-slate-400">Aucune évaluation récente.</li>
                            @endforelse
                        </ul>
                    </section>
                </div>
                <!-- Alertes DG -->
                <section class="rounded-[26px] border border-rose-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <h2 class="text-base font-black tracking-tight text-rose-600">Alertes / Notifications DG</h2>
                    <ul class="list-disc pl-6 text-slate-700 mt-2">
                        @forelse($alertesDG ?? [] as $alerte)
                            <li>{{ $alerte }}</li>
                        @empty
                            <li class="text-slate-400">Aucune alerte en cours.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <!-- Optionnel : informations institutionnelles globales -->
            <!--
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-base font-semibold text-slate-800 mb-4">Informations institutionnelles</h2>
                <dl class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom</dt>
                        <dd class="mt-1 text-slate-900">{{ $entite->nom }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Ville</dt>
                        <dd class="mt-1 text-slate-900">{{ $entite->ville ?: '—' }}</dd>
                    </div>
                </dl>
            </section>
            -->
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur(trice) General(e)</dt>
                        <dd class="mt-1 text-slate-900">
                            {{ trim($entite->directrice_generale_prenom.' '.$entite->directrice_generale_nom) ?: '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Email DG</dt>
                        <dd class="mt-1 text-slate-900">{{ $entite->directrice_generale_email ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Secretariat</dt>
                        <dd class="mt-1 text-slate-900">{{ $entite->secretariat_telephone ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Directions rattachees</dt>
                        <dd class="mt-1 text-slate-900">{{ $directions->count() }}</dd>
                    </div>
                </dl>

                @if ($directions->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500 mb-2">Directions</p>
                        <ul class="space-y-1">
                            @foreach ($directions as $direction)
                                <li class="text-sm text-slate-700">
                                    <span class="font-medium">{{ $direction->nom }}</span>
                                    @if ($direction->directeur_nom)
                                        <span class="text-slate-400"> — {{ $direction->directeur_nom }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>


            @if ($objectifsPendingCount > 0)
                <div class="flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-5 my-4 shadow-sm">
                    <div class="flex items-center justify-center h-12 w-12 rounded-xl bg-amber-100 text-amber-500">
                        <i class="fas fa-hourglass-half text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-base font-bold text-amber-800 mb-1">
                            {{ $objectifsPendingCount }} objectif(s) en cours de réalisation
                        </div>
                        <div class="text-xs text-amber-700 mb-2">
                            Pour votre entité ou ses directeurs, certains objectifs sont toujours en attente de finalisation.
                        </div>
                        <a href="{{ route('pca.objectifs.index') }}" class="inline-block rounded bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow hover:bg-amber-600 transition">Voir les objectifs</a>
                    </div>
                </div>
            @endif



        </div>
    </div>
@endsection

@push('scripts')
<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Données dynamiques passées depuis le contrôleur (exemple)
    const fichesStats = @json($fichesStatsDG);
    const evalLabels = @json($evaluationsDGLabels ?? ['Jan','Fév','Mar']);
    const evalData = @json($evaluationsDGData ?? [1,2,1]);
    const alertesLabels = @json($alertesDGLabels ?? ['2026-04-01','2026-04-02','2026-04-03']);
    const alertesData = @json($alertesDGData ?? [0,1,0]);

    // Donut Statut fiches DG
    new ApexCharts(document.querySelector('#chart-fiches-dg'), {
        chart: { type: 'donut', height: 220 },
        labels: Object.keys(fichesStats),
        series: Object.values(fichesStats),
        colors: ['#10b981', '#f59e42', '#ef4444'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
    }).render();

    // Bar Evolution évaluations DG
    new ApexCharts(document.querySelector('#chart-evaluations-dg'), {
        chart: { type: 'bar', height: 220 },
        series: [{ name: 'Évaluations', data: evalData }],
        xaxis: { categories: evalLabels },
        colors: ['#6366f1'],
        dataLabels: { enabled: false }
    }).render();

    // Area Alertes DG
    new ApexCharts(document.querySelector('#chart-alertes-dg'), {
        chart: { type: 'area', height: 220, sparkline: { enabled: false } },
        series: [{ name: 'Alertes', data: alertesData }],
        xaxis: { categories: alertesLabels, labels: { rotate: -45 } },
        colors: ['#ef4444'],
        dataLabels: { enabled: false },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1 } }
    }).render();
});
</script>
@endpush

