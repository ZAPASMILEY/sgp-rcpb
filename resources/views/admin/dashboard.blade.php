@extends('layouts.app')

@section('title', 'Dashboard | SGP-RCPB')

@push('head')
<style>
    .app-content-header { display: none !important; }
    .app-content { background: #ffffff !important; padding: 0 !important; }
    .app-content > .container-fluid { padding: 0 !important; max-width: 100% !important; }
    .app-main { background: #ffffff !important; }

    .db-page .text-white { color: #1f2937 !important; }
    .db-card {
        background: linear-gradient(180deg, rgba(240, 253, 250, 0.92), rgba(229, 250, 245, 0.90));
        border-radius: 1rem;
        color: #374151;
        border: 1px solid rgba(34, 197, 94, 0.18);
        box-shadow: 0 18px 30px rgba(34, 197, 94, 0.08);
    }
    .db-table-hdr { background: rgba(34, 197, 94, 0.12); }
    .db-row:hover { background: rgba(34, 197, 94, 0.06); }
    .db-list-item {
        background: rgba(255, 255, 255, 0.92);
        border-radius: 0.75rem;
        border: 1px solid rgba(34, 197, 94, 0.14);
    }
    .db-muted { color: #6b7280; font-size: 0.7rem; letter-spacing: 0.06em; text-transform: uppercase; font-weight: 700; }
    .db-dots { background: transparent; border: none; cursor: pointer; color: #6b7280; padding: 0.2rem; }
    .db-dots:hover { color: #16a34a; }
</style>
@endpush

@section('content')
<div style="background:linear-gradient(180deg,#ffffff 0%,#f0f9f4 50%,#f8fcfa 100%);min-height:100vh;color:#374151;" class="font-sans db-page">

    <div class="px-4 lg:px-6 py-5 space-y-5">

        {{-- 4 KPI Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Performance Globale - Semi-circle gauge --}}
            <div class="db-card p-5">
                <div class="flex items-center justify-between mb-1">
                    <p class="db-muted">Performance Globale</p>
                    <button class="db-dots"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg></button>
                </div>
                @php
                    $arcLen = 202.2;
                    $arcFilled = round($arcLen * min($performanceGlobale, 100) / 100, 1);
                @endphp
                <div class="relative flex flex-col items-center">
                    <svg viewBox="0 0 100 62" class="w-full max-w-[160px]">
                        <path d="M 5 55 A 50 50 0 1 1 95 55" fill="none" stroke="#d1fae5" stroke-width="9" stroke-linecap="round"/>
                        <path d="M 5 55 A 50 50 0 1 1 95 55" fill="none" stroke="#22c55e" stroke-width="9" stroke-linecap="round"
                            stroke-dasharray="{{ $arcFilled }} {{ $arcLen }}"/>
                    </svg>
                    <div style="margin-top:-2.8rem;" class="text-center">
                        <span class="text-2xl font-black text-white">{{ $performanceGlobale }}%</span>
                    </div>
                    <p class="text-emerald-600 text-xs font-semibold mt-0.5">
                        @if($performanceGlobale >= 80) Excellent @elseif($performanceGlobale >= 60) Bon @else En progression @endif
                    </p>
                    <p style="color:#6b7280;font-size:0.68rem;">(Exercice {{ date('Y') }})</p>
                </div>
            </div>

            {{-- Objectifs Atteints --}}
            <div class="db-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="db-muted">Objectifs Atteints</p>
                    <button class="db-dots"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg></button>
                </div>
                @php $objPct = $objectifsCount > 0 ? round($objectifsAtteints / $objectifsCount * 100) : 0; @endphp
                <div class="flex items-end gap-2 mt-2">
                    <span class="text-3xl font-black text-white">{{ $objectifsAtteints }}</span>
                    <span class="text-xl mb-0.5" style="color:#6b7280;">/ {{ $objectifsCount }}</span>
                </div>
                <div class="mt-4 h-2 rounded-full overflow-hidden" style="background:#d1fae5;">
                    <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $objPct }}%"></div>
                </div>
                <div class="mt-2 flex items-center justify-between">
                    <span style="color:#6b7280;font-size:0.72rem;">Objectifs Atteints</span>
                    <span class="text-emerald-600 font-bold text-xs">{{ $objPct }}%</span>
                </div>
            </div>

            {{-- Évaluations Terminées - Donut --}}
            <div class="db-card p-5">
                <div class="flex items-center justify-between mb-1">
                    <p class="db-muted">Évaluations Terminées</p>
                    <button class="db-dots"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg></button>
                </div>
                @php
                    $r = 38; $circumference = round(2 * M_PI * $r, 2);
                    $donutFilled = round($circumference * min($tauxCompletion, 100) / 100, 2);
                @endphp
                <div class="relative flex items-center justify-center mt-2">
                    <svg viewBox="0 0 96 96" class="w-28 h-28" style="transform:rotate(-90deg);">
                        <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#d1fae5" stroke-width="10"/>
                        <circle cx="48" cy="48" r="{{ $r }}" fill="none" stroke="#22c55e" stroke-width="10"
                            stroke-dasharray="{{ $donutFilled }} {{ $circumference }}" stroke-linecap="round"/>
                    </svg>
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                        <span class="text-2xl font-black text-white">{{ $tauxCompletion }}%</span>
                    </div>
                </div>
            </div>

            {{-- Effectif Total --}}
            <div class="db-card p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="db-muted">Effectif Total</p>
                    <button class="db-dots"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg></button>
                </div>
                <div class="mt-5">
                    <span class="text-4xl font-black text-white">{{ number_format($agentsCount, 0, ',', ' ') }}</span>
                    <p style="color:#6b7280;font-size:0.8rem;" class="mt-1">Agents</p>
                </div>
            </div>
        </div>

        {{-- Délégations Table --}}
        <div class="db-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-white">Délégations Techniques – Vue Détaillée</h2>
                <button class="db-dots"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg></button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="db-table-hdr py-2.5 px-3 text-left db-muted rounded-tl-lg" style="font-size:0.68rem;">Région</th>
                            <th class="db-table-hdr py-2.5 px-3 text-left db-muted" style="font-size:0.68rem;">Ville</th>
                            <th class="db-table-hdr py-2.5 px-3 text-left db-muted" style="font-size:0.68rem;">Directeur</th>
                            <th class="db-table-hdr py-2.5 px-3 text-left db-muted" style="font-size:0.68rem;">Services</th>
                            <th class="db-table-hdr py-2.5 px-3 text-left db-muted" style="font-size:0.68rem;">Statut</th>
                            <th class="db-table-hdr py-2.5 px-3 text-right db-muted rounded-tr-lg" style="font-size:0.68rem;">Performance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($delegations as $delegation)
                            @php $dir = $delegation->directions->first(); @endphp
                            <tr class="db-row" style="border-bottom:1px solid rgba(34,197,94,0.10);">
                                <td class="py-3 px-3 font-medium text-white">{{ $delegation->region }}</td>
                                <td class="py-3 px-3" style="color:#4b5563;">{{ $delegation->ville }}</td>
                                <td class="py-3 px-3" style="color:#4b5563;">
                                    {{ $dir ? $dir->directeur_prenom.' '.$dir->directeur_nom : '—' }}
                                </td>
                                <td class="py-3 px-3" style="color:#4b5563;">{{ $delegation->services_count }}</td>
                                <td class="py-3 px-3">
                                    <span class="flex items-center gap-1.5">
                                        <span style="width:0.5rem;height:0.5rem;border-radius:999px;background:#22c55e;display:inline-block;"></span>
                                        <span style="color:#4b5563;font-size:0.78rem;">Actif</span>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-bold" style="color:#16a34a;">
                                    {{ $delegation->performance !== null ? $delegation->performance.'%' : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center" style="color:#475569;">Aucune délégation configurée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Bottom 3 Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pb-6">

            {{-- Services Récents --}}
            <div class="db-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-white">Services Recents</h3>
                    <button class="db-dots"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg></button>
                </div>
                <div class="space-y-2.5">
                    @forelse($recentServices as $service)
                        <a href="{{ route('admin.services.show', $service) }}" class="flex items-center justify-between p-3 db-list-item hover:opacity-80 transition" style="text-decoration:none;">
                            <div class="flex items-center gap-3">
                                <div style="width:2.2rem;height:2.2rem;border-radius:0.65rem;background:rgba(34,197,94,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-white">{{ $service->direction?->delegationTechnique?->ville ?? '—' }}</p>
                                    <p class="flex items-center gap-1" style="font-size:0.65rem;color:#6b7280;">
                                        <span style="width:0.45rem;height:0.45rem;background:#22c55e;border-radius:999px;display:inline-block;"></span>
                                        {{ Str::limit($service->nom, 28) }}
                                    </p>
                                </div>
                            </div>
                            <svg class="w-4 h-4" style="color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @empty
                        <p class="text-center py-4" style="color:#475569;font-size:0.78rem;">Aucun service récent.</p>
                    @endforelse
                </div>
            </div>

            {{-- Secrétaires Identifiés --}}
            <div class="db-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-white">Secrétaires Identifiés</h3>
                    <button class="db-dots"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg></button>
                </div>
                <div class="space-y-2.5">
                    @forelse($secretaires as $sec)
                        <div class="flex items-center justify-between p-3 db-list-item">
                            <div class="flex items-center gap-3">
                                <div style="width:2.2rem;height:2.2rem;border-radius:0.65rem;background:rgba(34,197,94,0.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-white">{{ $sec->delegationTechnique?->ville ?? '—' }}</p>
                                    <p class="flex items-center gap-1" style="font-size:0.65rem;color:#6b7280;">
                                        <span style="width:0.45rem;height:0.45rem;background:#22c55e;border-radius:999px;display:inline-block;"></span>
                                        {{ $sec->secretaire_prenom }} {{ $sec->secretaire_nom }}
                                    </p>
                                </div>
                            </div>
                            <svg class="w-4 h-4" style="color:#4b5563;" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    @empty
                        <p class="text-center py-4" style="color:#475569;font-size:0.78rem;">Aucun secrétaire enregistré.</p>
                    @endforelse
                </div>
            </div>

            {{-- Progression Mensuelle --}}
            <div class="db-card p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-white">Progression Mensuelle</h3>
                    <button class="db-dots"><svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><circle cx="4" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="16" cy="10" r="1.5"/></svg></button>
                </div>
                <canvas id="progressionChart"></canvas>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        var ctx = document.getElementById('progressionChart');
        if (!ctx) { return; }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! $monthlyLabels !!},
                datasets: [{
                    data: {!! $monthlyValues !!},
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.12)',
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#22c55e',
                    pointBorderColor: '#ecfdf5',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#ecfdf5',
                        titleColor: '#4b5563',
                        bodyColor: '#16a34a',
                        borderColor: 'rgba(34,197,94,0.18)',
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: {
                        ticks: { color: '#6b7280', font: { size: 10 } },
                        grid: { color: 'rgba(34,197,94,0.08)' },
                        border: { display: false }
                    },
                    y: {
                        ticks: { color: '#6b7280', font: { size: 10 } },
                        grid: { color: 'rgba(34,197,94,0.08)' },
                        border: { display: false },
                        beginAtZero: true
                    }
                }
            }
        });
    })();
</script>
@endpush