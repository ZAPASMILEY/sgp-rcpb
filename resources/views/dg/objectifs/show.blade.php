@extends('layouts.dg')

@section('title', 'Fiche d\'objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Fiche d'objectifs</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $fiche->titre }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Annee {{ $fiche->annee }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dg.objectifs.pdf', $fiche) }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-file-pdf mr-2"></i>Telecharger PDF
                    </a>
                    <a href="{{ route('dg.mon-espace') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="admin-panel px-6 py-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date d'assignation</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Echeance</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Avancement</p>
                    @php
                        $avancement = (int) ($fiche->avancement_percentage ?? 0);
                        $avancementColor = $avancement >= 80 ? 'bg-emerald-500' : ($avancement >= 50 ? 'bg-sky-500' : ($avancement >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                    @endphp
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $avancement }}<span class="text-sm font-semibold text-slate-500">%</span></p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full {{ $avancementColor }}" style="width: {{ $avancement }}%"></div>
                    </div>
                    {{-- Formulaire de mise à jour de l'avancement (multiples de 5) --}}
                    <form method="POST" action="{{ route('dg.objectifs.avancement', $fiche) }}" class="mt-3 flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="avancement_percentage"
                                class="flex-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-1 focus:ring-emerald-400"
                                onchange="this.form.submit()">
                            @for ($p = 0; $p <= 100; $p += 5)
                                <option value="{{ $p }}" @selected($avancement === $p)>{{ $p }}%</option>
                            @endfor
                        </select>
                    </form>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                    @php
                        $statClass = match ($fiche->statut ?? 'en_attente') {
                            'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                            default    => 'border-amber-200 bg-amber-50 text-amber-700',
                        };
                        $statLabel = match ($fiche->statut ?? 'en_attente') {
                            'acceptee' => 'Acceptee',
                            'refusee'  => 'Refusee',
                            default    => 'En attente',
                        };
                    @endphp
                    <div class="mt-2">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statClass }}">
                            {{ $statLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        <section class="admin-panel px-6 py-6 lg:px-8">
            <h2 class="text-lg font-black text-slate-900">Objectifs assignes</h2>
            <div class="mt-4 space-y-3">
                @foreach($fiche->objectifs as $objectif)
                    <div class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4">
                        <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                            <i class="fas fa-check text-[10px]"></i>
                        </div>
                        <p class="text-sm text-slate-700">{{ $objectif->description }}</p>
                    </div>
                @endforeach
            </div>
        </section>

    </div>
</div>
@endsection
