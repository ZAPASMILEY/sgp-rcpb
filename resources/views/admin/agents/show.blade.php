@extends('layouts.app')

@section('title', 'Agent | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Fiche agent</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $agent->prenom }} {{ $agent->nom }}</h1>
                        <p class="mt-2 text-sm text-slate-600">Informations detaillees de l'agent.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.agents.edit', $agent) }}" class="ent-btn ent-btn-primary">Modifier</a>
                        <a href="{{ route('admin.agents.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                    </div>
                </div>

                @if (session('status'))
                    <div data-auto-dismiss="4000" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="mt-8 grid gap-6 lg:grid-cols-[220px_1fr]">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                        @if ($agent->photo_path)
                            <img src="{{ Storage::url($agent->photo_path) }}" alt="Photo de {{ $agent->prenom }} {{ $agent->nom }}" class="h-48 w-full rounded-2xl object-cover ring-1 ring-slate-200">
                        @else
                            <div class="flex h-48 w-full items-center justify-center rounded-2xl bg-slate-100 text-4xl font-semibold uppercase tracking-[0.2em] text-slate-400 ring-1 ring-slate-200">
                                {{ strtoupper(substr($agent->prenom, 0, 1).substr($agent->nom, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Nom</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">{{ $agent->nom }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Prenom</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">{{ $agent->prenom }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Fonction</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">{{ $agent->fonction }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Service</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">{{ $agent->service?->nom ?? '-' }}</p>
                            @if ($agent->service?->direction)
                                <p class="mt-1 text-sm text-slate-500">{{ $agent->service->direction->nom }}</p>
                            @endif
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Numero</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">{{ $agent->numero_telephone }}</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Mail</p>
                            <p class="mt-2 text-lg font-semibold text-slate-950">{{ $agent->email }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection