@extends('layouts.pca')

@section('title', 'Evaluation #'.$evaluation->id.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full flex flex-col gap-6">
            
            {{-- Entête --}}
            <header class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace PCA / Evaluation</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Evaluation #{{ $evaluation->id }}</h1>
                        <p class="mt-2 text-sm text-slate-600">{{ $cibleType }} : {{ $cibleLabel }}</p>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="{{ route('pca.evaluations.pdf', $evaluation) }}"
                           class="ent-btn ent-btn-soft">
                            <i class="fas fa-file-pdf mr-2"></i>Télécharger PDF
                        </a>
                        <a href="{{ route('pca.evaluations.index') }}" class="ent-btn ent-btn-soft">Retour</a>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div data-auto-dismiss="4000" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Résumé des notes --}}
            <section class="admin-panel px-6 py-6 lg:px-8">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Note finale</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ number_format((float) $evaluation->note_finale, 2, ',', ' ') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ ucfirst($evaluation->statut) }}</p>
                    </div>
                </div>
            </section>

            {{-- Criteres Objectifs --}}
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-lg font-black text-slate-900 border-b pb-2">Criteres objectifs</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($objectiveCriteria as $criterion)
                        <div class="p-4 border rounded-xl bg-white">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-bold text-slate-800">{{ $criterion->titre }}</h3>
                                <span class="text-xs font-bold px-2 py-1 bg-emerald-100 text-emerald-700 rounded-lg">Note: {{ number_format((float) $criterion->note_globale, 2) }}</span>
                            </div>
                            <table class="w-full text-sm">
                                @foreach ($criterion->sousCriteres as $sub)
                                    <tr class="border-t border-slate-50">
                                        <td class="py-2 text-slate-600">{{ $sub->libelle }}</td>
                                        <td class="py-2 text-right font-semibold">{{ number_format((float) $sub->note, 2) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Criteres Subjectifs --}}
            <section class="admin-panel px-6 py-6 lg:px-8">
                <h2 class="text-lg font-black text-slate-900 border-b pb-2">Criteres subjectifs</h2>
                <div class="mt-4 space-y-4">
                    @forelse ($subjectiveCriteria as $criterion)
                        <div class="p-4 border rounded-xl bg-white">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-bold text-slate-800">{{ $criterion->titre }}</h3>
                                <span class="text-xs font-bold px-2 py-1 bg-slate-100 text-slate-700 rounded-lg">Note: {{ number_format((float) $criterion->note_globale, 2) }}</span>
                            </div>
                            <table class="w-full text-sm">
                                @foreach ($criterion->sousCriteres as $sub)
                                    <tr class="border-t border-slate-50">
                                        <td class="py-2 text-slate-600">{{ $sub->libelle }}</td>
                                        <td class="py-2 text-right font-semibold">{{ number_format((float) $sub->note, 2) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    @empty
                        <div class="p-8 text-center border-2 border-dashed rounded-2xl text-slate-400">
                            Aucun critère subjectif enregistré pour cette évaluation.
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Actions --}}
            <section class="admin-panel px-6 py-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-6">
                    <a href="{{ route('pca.evaluations.pdf', $evaluation) }}" class="ent-btn ent-btn-soft">Exporter PDF</a>

                    @if ($evaluation->statut === 'brouillon')
                        <form method="POST" action="{{ route('pca.evaluations.submit', $evaluation) }}">
                            @csrf
                            <button type="submit" class="ent-btn ent-btn-primary">Soumettre au PCA</button>
                        </form>
                    @endif

                

                    @if ($evaluation->statut !== 'valide')
                        <form method="POST" action="{{ route('pca.evaluations.destroy', $evaluation) }}" onsubmit="return confirm('Supprimer cette évaluation ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ent-btn ent-btn-destructive">Supprimer</button>
                        </form>
                    @endif
                </div>
            </section>

        </div>
    </div>
@endsection