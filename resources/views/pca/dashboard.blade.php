@extends('layouts.pca')

@section('title', 'Tableau de bord PCA | '.config('app.name', 'SGP-RCPB'))

@section('content')
    @php
        $overviewCards = [
            [
                'label' => "Fiches d'objectifs DG",
                'value' => $nbFichesObjectifsDG ?? 0,
                'meta' => 'Total fiches assignees',
                'icon' => 'fas fa-bullseye',
                'valueClass' => 'text-emerald-500',
                'iconClass' => 'bg-emerald-50 text-emerald-500',
                'borderClass' => 'border-slate-100',
            ],
            [
                'label' => 'Evaluations DG',
                'value' => $nbEvaluationsDG ?? 0,
                'meta' => 'Total evaluations recues',
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
                'label' => 'Fiches acceptees',
                'value' => $nbFichesObjectifsAcceptees ?? 0,
                'meta' => 'Validees par le DG',
                'icon' => 'fas fa-check-circle',
                'valueClass' => 'text-emerald-700',
                'iconClass' => 'bg-emerald-100 text-emerald-700',
                'borderClass' => 'border-emerald-100',
            ],
        ];

        $directeurGeneral = $entite->dg ? trim($entite->dg->prenom.' '.$entite->dg->nom) : '';
        $dgInitial = strtoupper(substr($directeurGeneral ?: 'D', 0, 1));
    @endphp

    <div class="relative z-10 -mt-8 bg-[linear-gradient(180deg,#f6f9ff_0%,#fbfdff_100%)] px-4 pb-6 pt-0 lg:px-8">
        <div class="mx-auto max-w-[1500px] space-y-4">
            <section class="rounded-[26px] border border-white bg-white/90 px-5 py-4 shadow-[0_18px_60px_-35px_rgba(148,163,184,0.6)] backdrop-blur">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                    <div>
                        <p class="text-base font-black text-emerald-700">Tableau de bord</p>
                        <div class="mt-1 flex flex-wrap items-center gap-3">
                            <h1 class="text-3xl font-black tracking-tight text-slate-900">Pilotage administratif du Directeur General</h1>
                        </div>
                        <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-slate-400">Synthese du {{ now()->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[520px]">
                        @foreach ($overviewCards as $card)
                            <div class="rounded-2xl border {{ $card['borderClass'] }} bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">{{ $card['label'] }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="mt-1 text-xl font-black {{ $card['valueClass'] }}">{{ $card['value'] }}</span>
                                    <span class="flex h-7 w-7 items-center justify-center rounded-xl {{ $card['iconClass'] }}">
                                        <i class="{{ $card['icon'] }} text-base"></i>
                                    </span>
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

            <section class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <h2 class="text-base font-black tracking-tight text-slate-900">Statut des fiches d'objectifs DG</h2>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Repartition acceptees / en attente / refusees</p>
                    <div id="chart-fiches-dg" class="mt-2"></div>
                </div>

                <div class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <h2 class="text-base font-black tracking-tight text-slate-900">Evolution des evaluations DG</h2>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">Par periode</p>
                    <div id="chart-evaluations-dg" class="mt-2"></div>
                </div>

                <div class="rounded-[26px] border border-rose-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <h2 class="text-base font-black tracking-tight text-rose-600">Alertes DG</h2>
                    <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">7 derniers jours</p>
                    <div id="chart-alertes-dg" class="mt-2"></div>
                </div>
            </section>

            <div class="mt-8 grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1.7fr)_minmax(340px,0.9fr)]">
                <div class="space-y-4">
                    <section class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                        <div class="mb-4">
                            <h2 class="text-xl font-black tracking-tight text-slate-900">Dernieres fiches d'objectifs DG</h2>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Fiches les plus recentes</p>
                        </div>
                        <ul class="divide-y divide-slate-200">
                            @forelse($dernieresFichesDG ?? [] as $fiche)
                                <li class="flex items-center justify-between px-4 py-3">
                                    <span>{{ $fiche->titre }} ({{ $fiche->annee }})</span>
                                    <a href="{{ route('pca.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft">Voir</a>
                                </li>
                            @empty
                                <li class="px-4 py-3 text-slate-400">Aucune fiche recente.</li>
                            @endforelse
                        </ul>
                    </section>

                    <section class="rounded-[26px] border border-slate-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                        <div class="mb-4">
                            <h2 class="text-xl font-black tracking-tight text-slate-900">Dernieres evaluations DG</h2>
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Evaluations les plus recentes</p>
                        </div>
                        <ul class="divide-y divide-slate-200">
                            @forelse($dernieresEvaluationsDG ?? [] as $evaluation)
                                <li class="flex items-center justify-between px-4 py-3">
                                    <span>Periode : {{ $evaluation->date_debut->format('m/Y') }} - {{ $evaluation->date_fin->format('m/Y') }}</span>
                                    <a href="{{ route('pca.evaluations.show', $evaluation) }}" class="ent-btn ent-btn-soft">Voir</a>
                                </li>
                            @empty
                                <li class="px-4 py-3 text-slate-400">Aucune evaluation recente.</li>
                            @endforelse
                        </ul>
                    </section>
                </div>

                <section class="rounded-[26px] border border-rose-100 bg-white p-5 shadow-[0_16px_40px_-30px_rgba(15,23,42,0.35)]">
                    <h2 class="text-base font-black tracking-tight text-rose-600">Alertes / Notifications DG</h2>
                    <ul class="mt-2 list-disc pl-6 text-slate-700">
                        @forelse($alertesDG ?? [] as $alerte)
                            <li>{{ $alerte }}</li>
                        @empty
                            <li class="text-slate-400">Aucune alerte en cours.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <section class="mt-8 rounded-[30px] border border-slate-200/80 bg-white p-6 shadow-[0_20px_50px_-34px_rgba(15,23,42,0.35)] lg:p-7">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-600">Profil institutionnel</p>
                        <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $entite->nom }}</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Informations de reference du Directeur General, du secretariat et des directions rattachees.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:min-w-[540px]">
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-700">DG</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">1</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Direction generale active</p>
                        </div>
                        <div class="rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-700">Directions</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ $directionsRattacheesCount }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Structures rattachees</p>
                        </div>
                        <div class="rounded-2xl border border-violet-100 bg-violet-50/70 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-violet-700">Services</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ $servicesRattachesCount }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Services des directions</p>
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50/80 px-4 py-4">
                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-amber-700">Agents</p>
                            <p class="mt-2 text-2xl font-black text-slate-900">{{ $agentsRattachesCount }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">Agents des services</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(320px,0.9fr)]">
                    <div class="rounded-[26px] border border-slate-200 bg-slate-50/80 p-5">
                        <div class="flex items-start gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-xl font-black text-white shadow-lg shadow-emerald-200/70">
                                {{ $dgInitial }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Directeur(trice) general(e)</p>
                                <h3 class="mt-2 text-xl font-black text-slate-900">{{ $directeurGeneral ?: 'Non renseigne' }}</h3>
                                <p class="mt-2 inline-flex items-center rounded-full bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.16em] text-emerald-700 shadow-sm">
                                    Administration centrale
                                </p>
                            </div>
                        </div>

                        <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                                <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Email DG</dt>
                                <dd class="mt-2 break-all text-sm font-bold text-slate-800">{{ $entite->dg?->email ?: 'Non renseigne' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                                <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Secretariat</dt>
                                <dd class="mt-2 text-sm font-bold text-slate-800">{{ $entite->secretariat_telephone ?: 'Non renseigne' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                                <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Ville</dt>
                                <dd class="mt-2 text-sm font-bold text-slate-800">{{ $entite->ville ?: 'Non renseignee' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-white bg-white px-4 py-3 shadow-sm">
                                <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Directions rattachees</dt>
                                <dd class="mt-2 text-sm font-bold text-slate-800">{{ $directionsRattacheesCount }}</dd>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 shadow-sm">
                                <dt class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Note faîtière</dt>
                                <dd class="mt-2 text-sm font-bold text-slate-300">— (à venir)</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-[26px] border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">Personnel rattache</p>
                                <h3 class="mt-1 text-lg font-black text-slate-900">Organisation du cabinet</h3>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                {{ $personnelRattacheCount }} total
                            </span>
                        </div>

                        @if ($personnelRattache->isNotEmpty())
                            <div class="mt-4 space-y-3">
                                @foreach ($personnelRattache as $personne)
                                    <div class="flex items-start justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-black text-slate-800">{{ $personne['fonction'] }}</p>
                                            <p class="mt-1 text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">
                                                Personnel
                                                <span class="normal-case tracking-normal text-slate-500">
                                                    {{ $personne['nom'] }}
                                                </span>
                                            </p>
                                        </div>
                                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-slate-400 shadow-sm">
                                            <i class="{{ $personne['icone'] }}"></i>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-4 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm font-semibold text-slate-400">
                                Aucun personnel rattache pour le moment.
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            @if ($objectifsPendingCount > 0)
                <div class="my-4 flex items-center gap-4 rounded-2xl border border-amber-200 bg-amber-50 px-6 py-5 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-500">
                        <i class="fas fa-hourglass-half text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="mb-1 text-base font-bold text-amber-800">
                            {{ $objectifsPendingCount }} objectif(s) en cours de realisation
                        </div>
                        <div class="mb-2 text-xs text-amber-700">
                            Pour votre entite ou ses directeurs, certains objectifs sont toujours en attente de finalisation.
                        </div>
                        <a href="{{ route('pca.objectifs.index') }}" class="inline-block rounded bg-amber-500 px-3 py-1 text-xs font-semibold text-white shadow transition hover:bg-amber-600">Voir les objectifs</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fichesStats = @json($fichesStatsDG);
    const evalLabels = @json($evaluationsDGLabels ?? ['Jan', 'Fev', 'Mar']);
    const evalData = @json($evaluationsDGData ?? [1, 2, 1]);
    const alertesLabels = @json($alertesDGLabels ?? ['2026-04-01', '2026-04-02', '2026-04-03']);
    const alertesData = @json($alertesDGData ?? [0, 1, 0]);

    new ApexCharts(document.querySelector('#chart-fiches-dg'), {
        chart: { type: 'donut', height: 220 },
        labels: Object.keys(fichesStats),
        series: Object.values(fichesStats),
        colors: ['#10b981', '#f59e42', '#ef4444'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true }
    }).render();

    new ApexCharts(document.querySelector('#chart-evaluations-dg'), {
        chart: { type: 'bar', height: 220 },
        series: [{ name: 'Evaluations', data: evalData }],
        xaxis: { categories: evalLabels },
        colors: ['#6366f1'],
        dataLabels: { enabled: false }
    }).render();

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
