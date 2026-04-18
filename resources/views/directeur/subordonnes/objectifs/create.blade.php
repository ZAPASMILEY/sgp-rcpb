@extends('layouts.directeur')

@section('title', 'Assigner des objectifs | '.config('app.name', 'SGP-RCPB'))

@php
    $oldObjectifs = is_array($oldObjectifs) && $oldObjectifs !== [] ? $oldObjectifs : [''];
@endphp

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace Directeur / Subordonnés</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Assigner une fiche d'objectifs</h1>
                        <p class="mt-2 text-sm text-slate-600">Cible : <span class="font-semibold">{{ $cibleLabel }}</span></p>
                    </div>
                    <a href="{{ $backRoute }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Cible --}}
                <div class="mt-6 rounded-2xl border border-cyan-100 bg-cyan-50/70 px-4 py-4">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-cyan-700">Assigné à</p>
                    <p class="mt-2 text-base font-black text-slate-900">{{ $cibleLabel }}</p>
                    @if ($service)
                        @php $chef = trim(($service->chef_prenom ?? '').' '.($service->chef_nom ?? '')); @endphp
                        @if ($chef)
                            <p class="mt-1 text-sm text-slate-500">Chef de service : {{ $chef }}</p>
                        @endif
                    @endif
                    @if ($secretaire)
                        <p class="mt-1 text-sm text-slate-500">{{ $secretaire->email }}</p>
                    @endif
                </div>

                <form method="POST" action="{{ route($storeRoute) }}" class="mt-6 grid gap-5">
                    @csrf
                    @if ($hiddenField)
                        <input type="hidden" name="{{ $hiddenField['name'] }}" value="{{ $hiddenField['value'] }}">
                    @endif

                    {{-- Dates --}}
                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Date</label>
                            <input type="text" value="{{ now()->format('d/m/Y') }}" readonly class="ent-input bg-slate-100 text-slate-600">
                        </div>
                        <div class="space-y-2">
                            <label for="date_echeance" class="text-sm font-semibold text-slate-700">Date d'échéance</label>
                            <input id="date_echeance" name="date_echeance" type="date"
                                   value="{{ old('date_echeance', \Carbon\Carbon::now()->addYear()->toDateString()) }}"
                                   required class="ent-input">
                        </div>
                    </div>

                    {{-- Titre --}}
                    <div class="space-y-2">
                        <label for="titre_fiche" class="text-sm font-semibold text-slate-700">Titre de la fiche d'objectifs</label>
                        <input type="text" id="titre_fiche" name="titre_fiche" value="{{ old('titre_fiche') }}"
                               required class="ent-input"
                               placeholder="Ex : Contrat d'objectifs {{ now()->year }} — {{ $cibleLabel }}">
                    </div>

                    {{-- Objectifs --}}
                    <div class="space-y-2">
                        <label class="text-sm font-semibold text-slate-700">Objectifs à assigner</label>
                        <div id="objectifs-container" class="space-y-2">
                            @foreach ($oldObjectifs as $objectif)
                                <div class="objectif-row flex items-center gap-2">
                                    <input type="text" name="objectifs[]" value="{{ $objectif }}"
                                           required class="ent-input flex-1"
                                           placeholder="Description de l'objectif">
                                    <button type="button"
                                            class="remove-objectif-btn flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"
                                            title="Supprimer" style="display:none;">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-objectif-btn"
                                class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-600">
                            <i class="fas fa-plus text-[10px]"></i> Ajouter un objectif
                        </button>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm mt-4">
                        Assigner la fiche
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('objectifs-container');
        var btn = document.getElementById('add-objectif-btn');
        if (!container || !btn) return;

        btn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'objectif-row flex items-center gap-2';
            row.innerHTML =
                '<input type="text" name="objectifs[]" required class="ent-input flex-1" placeholder="Description de l\'objectif">' +
                '<button type="button" class="remove-objectif-btn flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600" title="Supprimer"><i class="fas fa-trash text-xs"></i></button>';
            container.appendChild(row);
            row.querySelector('.remove-objectif-btn').addEventListener('click', function () {
                row.remove();
                updateRemoveButtons();
            });
            updateRemoveButtons();
        });

        function updateRemoveButtons() {
            var rows = container.querySelectorAll('.objectif-row');
            rows.forEach(function (row) {
                var btn = row.querySelector('.remove-objectif-btn');
                if (btn) btn.style.display = rows.length > 1 ? '' : 'none';
            });
        }

        updateRemoveButtons();
        container.querySelectorAll('.remove-objectif-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                btn.closest('.objectif-row').remove();
                updateRemoveButtons();
            });
        });
    });
    </script>
@endpush
