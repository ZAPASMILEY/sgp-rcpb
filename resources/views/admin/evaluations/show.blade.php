@extends('layouts.app')

@section('title', 'Evaluation #'.$evaluation->id.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full flex flex-col gap-6">
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pilotage</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluation #{{ $evaluation->id }}</h1>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ $cibleType }} : {{ $cibleLabel }}
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="{{ route('admin.evaluations.pdf', $evaluation) }}"
                           class="ent-btn ent-btn-soft">
                            <i class="fas fa-file-pdf mr-2"></i>Télécharger PDF
                        </a>
                        <a href="{{ route('admin.evaluations.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <section class="admin-panel px-6 py-6 lg:px-8">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Periode</p>
                        <p class="mt-2 text-sm text-slate-800">{{ $evaluation->date_debut->format('d/m/Y') }} - {{ $evaluation->date_fin->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note objectifs</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $evaluation->note_objectifs }}%</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note manuelle</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $evaluation->note_manuelle !== null ? $evaluation->note_manuelle.'%' : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note finale</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ $evaluation->note_finale }}%</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Mention</p>
                        @php
                            $mentionClass = match ($mention) {
                                'Excellent' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                                'Bien'      => 'text-sky-700 bg-sky-50 border-sky-200',
                                'Passable'  => 'text-amber-700 bg-amber-50 border-amber-200',
                                default     => 'text-rose-700 bg-rose-50 border-rose-200',
                            };
                        @endphp
                        <span class="mt-2 inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $mentionClass }}">
                            {{ $mention }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 border-t border-slate-200 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Evaluateur</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $evaluation->evaluateur->name ?? '-' }}</p>
                </div>

                <div class="mt-6 border-t border-slate-200 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Commentaire</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $evaluation->commentaire ?: 'Aucun commentaire.' }}</p>
                </div>

                <div class="mt-6 border-t border-slate-200 pt-6">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Objectifs de la periode</p>
                    <div class="mt-3 space-y-3">
                        @forelse ($objectifs as $objectif)
                            <article class="rounded-xl border border-slate-200 bg-white p-4">
                                <p class="text-sm text-slate-800">{{ \Illuminate\Support\Str::limit($objectif->commentaire, 180) }}</p>
                                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                                    <span>Date: {{ \Carbon\Carbon::parse($objectif->date)->format('d/m/Y') }}</span>
                                    <span>Avancement: {{ $objectif->avancement_percentage }}%</span>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-slate-500">Aucun objectif dans la periode selectionnee.</p>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="{{ route('admin.evaluations.pdf', $evaluation) }}" class="ent-btn ent-btn-soft">Exporter PDF</a>

                    @if ($evaluation->statut === 'brouillon')
                        <a href="{{ route('admin.evaluations.edit', $evaluation) }}" class="ent-btn ent-btn-soft">Modifier</a>
                    @endif

                    @if ($evaluation->statut === 'brouillon')
                        <form method="POST" action="{{ route('admin.evaluations.submit', $evaluation) }}">
                            @csrf
                            <button type="submit" class="ent-btn ent-btn-primary">Soumettre</button>
                        </form>
                    @endif

                    @if ($evaluation->statut === 'soumis')
                        <form method="POST" action="{{ route('admin.evaluations.approve', $evaluation) }}">
                            @csrf
                            <button type="submit" class="ent-btn ent-btn-primary">Valider</button>
                        </form>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
