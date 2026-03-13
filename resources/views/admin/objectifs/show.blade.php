@extends('layouts.app')

@section('title', 'Objectif | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto flex max-w-4xl flex-col gap-6">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Fiche objectif</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Detail de l'objectif</h1>
                        <p class="mt-2 text-sm text-slate-600">Informations completes de l'objectif assigne.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.objectifs.edit', $objectif) }}" class="ent-btn ent-btn-primary">Modifier</a>
                        <a href="{{ route('admin.objectifs.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                    </div>
                </div>

                @if (session('status'))
                    <div data-auto-dismiss="4000" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @php
                    $assignable = $objectif->assignable;
                    $progressValue = (int) $objectif->avancement_percentage;
                    $deadline = \Carbon\Carbon::parse($objectif->date_echeance);
                    $today = today();
                    $isExpired = $deadline->isBefore($today);
                    $remainingLabel = $isExpired
                        ? 'Echu depuis '.$deadline->diffInDays($today).' jour'.($deadline->diffInDays($today) > 1 ? 's' : '')
                        : ($deadline->isSameDay($today)
                            ? 'Echeance aujourd\'hui'
                            : 'Il reste '.$today->diffInDays($deadline).' jour'.($today->diffInDays($deadline) > 1 ? 's' : ''));
                    $typeLabel = $assignable instanceof \App\Models\Entite ? 'Entite' : (
                        $assignable instanceof \App\Models\Direction ? 'Direction' : (
                            $assignable instanceof \App\Models\Service ? 'Service' : (
                                $assignable instanceof \App\Models\Agent ? 'Agent' : '-'
                            )
                        )
                    );
                    $cibleLabel = $assignable instanceof \App\Models\Agent
                        ? trim($assignable->prenom.' '.$assignable->nom)
                        : ($assignable?->nom ?? '-');
                    $progressBarClasses = $progressValue > 50
                        ? '[&::-webkit-progress-value]:bg-emerald-600 [&::-moz-progress-bar]:bg-emerald-600'
                        : '[&::-webkit-progress-value]:bg-rose-500 [&::-moz-progress-bar]:bg-rose-500';
                    $progressTextClasses = $progressValue > 50
                        ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
                        : 'text-rose-700 bg-rose-50 border-rose-200';
                @endphp

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Date</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $objectif->date }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Date d'echeance</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $objectif->date_echeance }}</p>
                        <p class="mt-2 text-sm font-medium {{ $isExpired ? 'text-rose-600' : 'text-emerald-600' }}">{{ $remainingLabel }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Type cible</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $typeLabel }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Cible</p>
                        <p class="mt-2 text-lg font-semibold text-slate-950">{{ $cibleLabel }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Avancement</p>
                        <progress max="100" value="{{ $objectif->avancement_percentage }}" class="mt-3 h-3 w-full overflow-hidden rounded-full [&::-webkit-progress-bar]:rounded-full [&::-webkit-progress-bar]:bg-slate-200 [&::-webkit-progress-value]:rounded-full [&::-moz-progress-bar]:rounded-full {{ $progressBarClasses }}"></progress>
                        <p class="mt-3 inline-flex rounded-full border px-3 py-1 text-lg font-semibold {{ $progressTextClasses }}">{{ $objectif->avancement_percentage }}%</p>
                        @if ($isExpired)
                            <p class="mt-3 text-sm font-medium text-rose-600">L'echeance est depassee. L'evolution de cet objectif est verrouillee.</p>
                        @endif
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Commentaire / objectif</p>
                        <p class="mt-3 whitespace-pre-line text-base text-slate-800">{{ $objectif->commentaire }}</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection