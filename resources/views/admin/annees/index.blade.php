@extends('layouts.app')

@section('title', 'Années d\'exercice | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        {{-- Flash messages --}}
        @if (session('status'))
            <div id="flash-status" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('flash-status')?.remove(), 3500);</script>
        @endif

        @if (session('error'))
            <div id="flash-error" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-rose-100 bg-white px-5 py-4 shadow-2xl shadow-rose-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                    <i class="fas fa-xmark"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('error') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('flash-error')?.remove(), 4000);</script>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Années d'exercice</h1>
                    <p class="mt-1 text-sm text-slate-400">Gestion des années fiscales liées aux évaluations et aux objectifs.</p>
                </div>
                <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fas fa-plus text-xs"></i> Nouvelle année
                </button>
            </div>
        </div>

        {{-- KPI --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-5 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest opacity-80">Total</p>
                <p class="mt-1 text-4xl font-black">{{ $annees->count() }}</p>
                <p class="mt-1 text-xs opacity-70">années enregistrées</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-sky-400 to-blue-500 p-5 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest opacity-80">Ouvertes</p>
                <p class="mt-1 text-4xl font-black">{{ $annees->where('statut', 'ouvert')->count() }}</p>
                <p class="mt-1 text-xs opacity-70">en cours d'exercice</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-slate-400 to-slate-600 p-5 text-white shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest opacity-80">Clôturées</p>
                <p class="mt-1 text-4xl font-black">{{ $annees->where('statut', 'cloture')->count() }}</p>
                <p class="mt-1 text-xs opacity-70">archivées</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Année</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Semestres</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Évaluations</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Objectifs</th>
                            <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse ($annees as $annee)
                            @php
                                $isOuvert = $annee->statut === 'ouvert';
                                $hasData  = $annee->evaluations_count > 0 || $annee->objectifs_count > 0;
                                $s1 = $annee->semestres->firstWhere('numero', 1);
                                $s2 = $annee->semestres->firstWhere('numero', 2);
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-lg font-black text-slate-900">{{ $annee->annee }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($isOuvert)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Ouverte
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Clôturée
                                        </span>
                                    @endif
                                </td>

                                {{-- Semestres S1 / S2 --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @foreach ([1 => $s1, 2 => $s2] as $num => $sem)
                                            @php $semOuvert = $sem?->statut === 'ouvert'; @endphp
                                            <form method="POST" action="{{ route('admin.annees.semestres.toggle', [$annee, $num]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        title="{{ $semOuvert ? 'Clôturer S'.$num : 'Ouvrir S'.$num }}"
                                                        @if (!$isOuvert) disabled @endif
                                                        class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-bold transition
                                                            {{ $semOuvert
                                                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                                                : 'border-slate-200 bg-slate-50 text-slate-500 hover:bg-slate-100' }}
                                                            {{ !$isOuvert ? 'opacity-40 cursor-not-allowed' : '' }}">
                                                    @if ($semOuvert)
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                    @else
                                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                                    @endif
                                                    S{{ $num }}
                                                </button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="font-semibold text-slate-700">{{ $annee->evaluations_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-semibold text-slate-700">{{ $annee->objectifs_count }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Toggle statut année --}}
                                        <form method="POST" action="{{ route('admin.annees.toggle-statut', $annee) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-bold transition
                                                        {{ $isOuvert
                                                            ? 'border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100'
                                                            : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                                @if ($isOuvert)
                                                    <i class="fas fa-lock text-[10px]"></i> Clôturer
                                                @else
                                                    <i class="fas fa-lock-open text-[10px]"></i> Rouvrir
                                                @endif
                                            </button>
                                        </form>

                                        {{-- Delete --}}
                                        @if (!$hasData)
                                            <form method="POST" action="{{ route('admin.annees.destroy', $annee) }}"
                                                  onsubmit="return confirm('Supprimer l\'année {{ $annee->annee }} ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-600 transition hover:bg-rose-100">
                                                    <i class="fas fa-trash text-[10px]"></i> Supprimer
                                                </button>
                                            </form>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 text-xs text-slate-400"
                                                  title="Des données sont liées à cette année">
                                                <i class="fas fa-lock text-[10px]"></i> Protégée
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <i class="fas fa-calendar-xmark text-2xl"></i>
                                        </div>
                                        <p class="font-semibold text-slate-500">Aucune année enregistrée</p>
                                        <p class="text-xs text-slate-400">Créez la première année d'exercice.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- Modal : Créer une année --}}
<div id="modal-create" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-lg font-black text-slate-900">Nouvelle année</h2>
            <button onclick="document.getElementById('modal-create').classList.add('hidden')"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <i class="fas fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.annees.store') }}">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Année</label>
                <input type="number" name="annee"
                       min="{{ now()->year }}" max="{{ now()->year }}"
                       value="{{ now()->year }}"
                       readonly
                       class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-600 outline-none cursor-not-allowed"
                       required>
                @error('annee')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-slate-400">Seule l'année en cours ({{ now()->year }}) peut être créée. Le statut sera "Ouverte" par défaut.</p>
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modal-create').classList.add('hidden')"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Annuler
                </button>
                <button type="submit"
                        class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700">
                    Créer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.getElementById('modal-create').classList.add('hidden');
        }
    });

    @if ($errors->any())
        document.getElementById('modal-create').classList.remove('hidden');
    @endif
</script>
@endsection
