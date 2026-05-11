@extends('layouts.app')
@section('title', 'Modifier objectif | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 via-slate-700 to-slate-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-300">Administration · Modification</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Modifier l'objectif</h1>
                <p class="mt-0.5 text-sm text-slate-300/80">Mettez à jour la cible, l'échéance ou le commentaire.</p>
            </div>
            <a href="{{ route('admin.objectifs.show', $objectif) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20 self-start">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour
            </a>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5 max-w-2xl">

        @if ($errors->any())
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation"></i>{{ $errors->first() }}
            </div>
        @endif

        <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
            <div class="border-b border-slate-100 px-6 py-4">
                <p class="text-sm font-black text-slate-800">Modifier l'objectif</p>
                <p class="mt-0.5 text-xs text-slate-500">Objectif #{{ $objectif->id }}</p>
            </div>

            <form method="POST" action="{{ route('admin.objectifs.update', $objectif) }}" class="px-6 py-5 grid gap-5">
                @csrf
                @method('PUT')

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label for="date" class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Date</label>
                        <input id="date" type="date" value="{{ $objectif->date }}" readonly
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 cursor-not-allowed outline-none">
                    </div>
                    <div class="space-y-1.5">
                        <label for="date_echeance" class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Date d'échéance</label>
                        <input id="date_echeance" name="date_echeance" type="date"
                               value="{{ old('date_echeance', $objectif->date_echeance) }}" required
                               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-slate-300 focus:ring-4 focus:ring-slate-100">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <label class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Type de cible</label>
                        <input type="hidden" name="assignable_type" value="agent">
                        <div class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500 cursor-not-allowed">
                            Agent
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label for="assignable_id" class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Cible</label>
                        <select id="assignable_id" name="assignable_id" required
                                data-previous-target="{{ old('assignable_id', $objectif->assignable_id) }}"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-slate-300 focus:ring-4 focus:ring-slate-100 cursor-pointer">
                            <option value="">Sélectionner d'abord un type</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="commentaire" class="text-xs font-black uppercase tracking-[0.14em] text-slate-500">Commentaire / objectif</label>
                    <textarea id="commentaire" name="commentaire" rows="6" required
                              class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-100 resize-none">{{ old('commentaire', $objectif->commentaire) }}</textarea>
                </div>

                <div class="border-t border-slate-100 pt-4">
                    <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-800 px-6 py-3.5 text-sm font-black text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-floppy-disk text-xs"></i> Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var optionsByType = JSON.parse(document.getElementById('objectif-assignment-options').textContent || '{}');
        var typeSelect = document.getElementById('assignable_type');
        var targetSelect = document.getElementById('assignable_id');
        var previousTarget = targetSelect.dataset.previousTarget || '';

        function populateTargets() {
            var selectedType = typeSelect.value;
            var options = optionsByType[selectedType] || [];
            targetSelect.innerHTML = '';
            var defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = options.length > 0 ? 'Sélectionner une cible' : 'Aucune cible disponible';
            targetSelect.appendChild(defaultOption);
            options.forEach(function (item) {
                var option = document.createElement('option');
                option.value = String(item.id);
                option.textContent = item.label;
                if (previousTarget !== '' && String(item.id) === previousTarget) option.selected = true;
                targetSelect.appendChild(option);
            });
        }

        typeSelect.addEventListener('change', function () { previousTarget = ''; populateTargets(); });
        populateTargets();
    });
</script>
<script id="objectif-assignment-options" type="application/json">@json($assignmentOptions)</script>
@endpush
