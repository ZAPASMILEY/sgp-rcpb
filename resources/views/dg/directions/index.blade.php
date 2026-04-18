@extends('layouts.dg')

@section('title', 'Directions | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Directions</h1>
                    <p class="mt-2 text-sm text-slate-600">Gérez les évaluations et les fiches d'objectifs des directeurs de la faitière.</p>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <section class="admin-panel px-6 py-6 lg:px-8">
            @if ($directions->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-12 text-center">
                    <i class="fas fa-building text-2xl text-slate-300"></i>
                    <p class="mt-3 text-sm font-black text-slate-700">Aucune direction enregistrée</p>
                    <p class="mt-1 text-xs text-slate-500">Les directions de la faitière apparaîtront ici une fois créées par l'administrateur.</p>
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($directions as $direction)
                        @php
                            $directeurNom = trim($direction->directeur_prenom.' '.$direction->directeur_nom);
                        @endphp
                        <a href="{{ route('dg.directions.show', $direction) }}"
                           class="group flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                    <i class="fas fa-building text-base"></i>
                                </div>
                                <span class="ml-auto rounded-full bg-slate-100 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-slate-500">
                                    {{ $direction->services->count() }} service(s)
                                </span>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 group-hover:text-emerald-700">{{ $direction->nom }}</h3>
                                @if ($directeurNom !== '')
                                    <p class="mt-1 text-sm text-slate-500">{{ $directeurNom }}</p>
                                @else
                                    <p class="mt-1 text-sm text-slate-400 italic">Directeur non renseigné</p>
                                @endif
                                @if ($direction->directeur_email)
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $direction->directeur_email }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 border-t border-slate-100 pt-3">
                                <span class="text-xs text-slate-400">Voir les évaluations & objectifs</span>
                                <i class="fas fa-arrow-right ml-auto text-xs text-slate-300 transition group-hover:text-emerald-500"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
