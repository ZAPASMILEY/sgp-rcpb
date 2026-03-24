@extends('layouts.app')

@section('title', 'Faîtière | '.config('app.name', 'SGP-RCPB'))

@push('head')
<style>
/* RESET */
.app-content-header { display: none !important; }
.app-content { background: #ffffff !important; padding: 0 !important; }
.app-content > .container-fluid { padding: 0 !important; max-width: 100% !important; }
.app-main { background: #ffffff !important; }

/* BACKGROUND */
.ft-shell {
    min-height: 100vh;
    background:
        radial-gradient(circle at 20% 18%, rgba(34,197,94,0.08), transparent 25%),
        radial-gradient(circle at 80% 20%, rgba(34,197,94,0.05), transparent 20%),
        linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%);
    animation: fadeIn 0.6s ease;
}

/* CONTAINER */
.ft-stage {
    width: min(1200px, 95%);
    margin: auto;
    padding: 2rem 0;
}

/* CARD MODERNE */
.ft-card {
    background: linear-gradient(145deg, rgba(255,255,255,0.85), rgba(255,255,255,0.65));
    border: 1px solid rgba(34,197,94,0.15);
    border-radius: 1.25rem;
    backdrop-filter: blur(18px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    transition: all 0.25s ease;
}
.ft-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
}

/* TEXT */
.ft-heading {
    font-size: clamp(2.2rem, 3vw, 3rem);
    font-weight: 900;
}
.ft-muted {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
    font-weight: 700;
}

/* BADGE */
.ft-badge {
    padding: 0.4rem 0.8rem;
    border-radius: 999px;
    background: rgba(34,197,94,0.1);
    border: 1px solid rgba(34,197,94,0.2);
    color: #16a34a;
    font-size: 0.7rem;
    font-weight: 700;
}

/* BUTTONS */
.ft-btn {
    border-radius: 0.8rem;
    padding: 0.75rem 1.4rem;
    font-size: 0.85rem;
    font-weight: 700;
    transition: all 0.25s ease;
}

.ft-btn-primary {
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: white;
    box-shadow: 0 4px 14px rgba(34,197,94,0.35);
}
.ft-btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(34,197,94,0.45);
}

.ft-btn-soft {
    background: white;
    border: 1px solid rgba(34,197,94,0.2);
    color: #16a34a;
}
.ft-btn-soft:hover {
    background: #f0fdf4;
}

.ft-btn-danger {
    background: #fef2f2;
    color: #dc2626;
}
.ft-btn-danger:hover {
    background: #fee2e2;
}

/* STATS */
.ft-stat-value {
    font-size: 3rem;
    font-weight: 900;
    background: linear-gradient(135deg, #111827, #16a34a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* GRID */
.ft-quick-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px,1fr));
    gap: 1rem;
}

/* TABLE */
.ft-table-wrap {
    background: white;
    border-radius: 1rem;
    overflow: hidden;
}
.ft-table thead {
    background: #f9fafb;
}
.ft-table th {
    padding: 1rem;
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #6b7280;
}
.ft-table td {
    padding: 1rem;
}
.ft-table tbody tr {
    transition: 0.2s;
}
.ft-table tbody tr:hover {
    background: rgba(34,197,94,0.05);
    transform: scale(1.01);
}

/* ANIMATION */
@keyframes fadeIn {
    from {opacity:0; transform:translateY(10px);}
    to {opacity:1; transform:translateY(0);}
}
</style>
@endpush

@section('content')
<div class="ft-shell">
<div class="ft-stage space-y-6">

@if ($entite)

<!-- HERO -->
<section class="ft-card p-8 text-center">
    <div class="flex justify-center gap-2 mb-4 flex-wrap">
        <span class="ft-badge">Siège Unique</span>
        <span class="ft-badge">{{ $entite->ville }}</span>
        <span class="ft-badge">{{ $entite->region }}</span>
    </div>

    <p class="ft-muted">Administration Centrale</p>

    <h1 class="ft-heading mt-2">
        <span class="bg-gradient-to-r from-gray-900 to-emerald-600 bg-clip-text text-transparent">
            La Faîtière
        </span>
    </h1>

    <p class="text-gray-600 mt-4">
        Pilotage global et centralisation des directions et agents
    </p>

    <div class="mt-6 flex justify-center gap-3 flex-wrap">
        <span class="bg-gray-50 px-3 py-1 rounded border text-xs">
            DG: <b>{{ $entite->directrice_generale_prenom }} {{ $entite->directrice_generale_nom }}</b>
        </span>
        <span class="bg-gray-50 px-3 py-1 rounded border text-xs">
            DGA: <b>{{ $entite->dga_prenom }} {{ $entite->dga_nom }}</b>
        </span>
        <span class="bg-gray-50 px-3 py-1 rounded border text-xs">
            PCA: <b>{{ $entite->pca_prenom }} {{ $entite->pca_nom }}</b>
        </span>
    </div>

    <div class="flex gap-3 justify-center mt-6 flex-wrap">
        <a href="{{ route('admin.entites.edit', $entite) }}" class="ft-btn ft-btn-primary">Modifier</a>
        <a href="{{ route('admin.entites.show', $entite) }}" class="ft-btn ft-btn-soft">Fiche complète</a>
        <form method="POST" action="{{ route('admin.entites.reset') }}">
            @csrf
            <button class="ft-btn ft-btn-danger">Réinitialiser</button>
        </form>
    </div>
</section>

<!-- STATS -->
<section class="ft-quick-grid">

<div class="ft-card p-6 flex flex-col">
    <p class="ft-muted">Directions</p>
    <h2 class="ft-stat-value">{{ $stats['directions'] }}</h2>
    <p class="text-xs text-gray-400 mt-1 mb-4">Directions de la faîtière</p>
    <div class="mt-auto flex gap-2 flex-wrap">
        <a href="{{ route('admin.entites.directions.create') }}"
           data-open-create-modal data-modal-title="Ajouter une direction de la faîtière"
           class="ft-btn ft-btn-primary flex-1 text-center text-xs py-2">
            <i class="fas fa-plus mr-1"></i> Ajouter
        </a>
        <a href="{{ route('admin.entites.directions.index') }}"
           class="ft-btn ft-btn-soft flex-1 text-center text-xs py-2">
            <i class="fas fa-list mr-1"></i> Voir tout
        </a>
    </div>
</div>

<div class="ft-card p-6 flex flex-col">
    <p class="ft-muted">Services</p>
    <h2 class="ft-stat-value">{{ $stats['services'] }}</h2>
    <p class="text-xs text-gray-400 mt-1 mb-4">Services de la faîtière</p>
    <div class="mt-auto flex gap-2 flex-wrap">
        <a href="{{ route('admin.services.create') }}"
           data-open-create-modal data-modal-title="Ajouter un service"
           class="ft-btn ft-btn-primary flex-1 text-center text-xs py-2">
            <i class="fas fa-plus mr-1"></i> Ajouter
        </a>
        <a href="{{ route('admin.services.index', ['source' => 'faitiere']) }}"
           class="ft-btn ft-btn-soft flex-1 text-center text-xs py-2">
            <i class="fas fa-list mr-1"></i> Voir tout
        </a>
    </div>
</div>

<div class="ft-card p-6 flex flex-col">
    <p class="ft-muted">Agents</p>
    <h2 class="ft-stat-value">{{ $stats['agents'] }}</h2>
    <p class="text-xs text-gray-400 mt-1 mb-4">Agents de la faîtière</p>
    <div class="mt-auto flex gap-2 flex-wrap">
        <a href="{{ route('admin.agents.create') }}"
           data-open-create-modal data-modal-title="Ajouter un agent"
           class="ft-btn ft-btn-primary flex-1 text-center text-xs py-2">
            <i class="fas fa-user-plus mr-1"></i> Ajouter
        </a>
        <a href="{{ route('admin.agents.index') }}"
           class="ft-btn ft-btn-soft flex-1 text-center text-xs py-2">
            <i class="fas fa-list mr-1"></i> Voir tout
        </a>
    </div>
</div>

</section>

<!-- TABLE -->
<div class="ft-card p-6">
<h2 class="text-xl font-bold mb-4">Directions</h2>

<div class="ft-table-wrap">
<table class="ft-table w-full">
<thead>
<tr>
<th>Direction</th>
<th>Directeur</th>
<th>Services</th>
<th>Score</th>
</tr>
</thead>
<tbody>
@forelse ($directions as $direction)
<tr>
<td class="font-bold">{{ $direction->nom }}</td>
<td>{{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}</td>
<td>{{ $direction->services_count }}</td>
<td>{{ $notesByType['directions'][$direction->id] ?? '-' }}</td>
</tr>
@empty
<tr><td colspan="4" class="text-center py-6">Aucune donnée</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>

@endif

</div>
</div>
@endsection