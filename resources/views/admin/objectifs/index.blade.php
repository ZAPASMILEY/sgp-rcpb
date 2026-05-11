@extends('layouts.app')
@section('title', 'Objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 via-slate-700 to-slate-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-300">Administration · Pilotage</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">Objectifs</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white ring-1 ring-white/20">
                        {{ $objectifs->total() }} objectif(s)
                    </span>
                    @if ($filters['search'])
                        <span class="inline-flex items-center rounded-full bg-amber-400/20 px-3 py-1 text-xs font-bold text-amber-200 ring-1 ring-amber-400/30">
                            <i class="fas fa-filter mr-1 text-[10px]"></i> Filtres actifs
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <a href="{{ route('admin.objectifs.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-plus text-[10px]"></i> Ajouter
                </a>
                <a href="{{ route('admin.objectifs.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-rotate-right text-[10px]"></i> Réinitialiser
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5">

        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check"></i>{{ session('status') }}
            </div>
        @endif

        {{-- Search --}}
        <form method="GET" action="{{ route('admin.objectifs.index') }}"
              class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
            <div class="flex-1 min-w-48">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                        <i class="fas fa-search text-sm"></i>
                    </span>
                    <input name="search" type="text" value="{{ $filters['search'] }}"
                           placeholder="Cible, commentaire, échéance..."
                           class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-slate-300 focus:ring-4 focus:ring-slate-100">
                </div>
            </div>
            <button type="submit"
                    class="inline-flex items-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                <i class="fas fa-filter mr-2 text-xs"></i> Filtrer
            </button>
        </form>

        {{-- Table --}}
        <div class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50/80">
                        <tr class="border-b border-slate-200 text-slate-500">
                            <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">#</th>
                            <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Date</th>
                            <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Échéance</th>
                            <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Type</th>
                            <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Cible</th>
                            <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Avancement</th>
                            <th class="px-4 py-4 text-xs font-black uppercase tracking-[0.16em]">Commentaire</th>
                            <th class="px-4 py-4 text-center text-xs font-black uppercase tracking-[0.16em]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($objectifs as $objectif)
                            @php
                                $assignable = $objectif->assignable;
                                $progressValue = (int) $objectif->avancement_percentage;
                                $isExpired = \Carbon\Carbon::parse($objectif->date_echeance)->isBefore(today());
                                $isLocked = (bool) ($objectif->is_evaluation_locked ?? false);
                                $typeLabel = $assignable instanceof \App\Models\Entite ? 'Entité' : (
                                    $assignable instanceof \App\Models\Direction ? 'Direction' : (
                                        $assignable instanceof \App\Models\Service ? 'Service' : (
                                            $assignable instanceof \App\Models\Agent ? 'Agent' : '-'
                                        )
                                    )
                                );
                                $cibleLabel = $assignable instanceof \App\Models\Agent
                                    ? trim($assignable->prenom.' '.$assignable->nom)
                                    : ($assignable?->nom ?? '-');
                                $progressColor = $progressValue > 50 ? 'bg-emerald-500' : ($progressValue > 0 ? 'bg-amber-400' : 'bg-slate-200');
                            @endphp
                            <tr class="align-top hover:bg-slate-50/60">
                                <td class="px-4 py-4 font-black text-slate-900">{{ ($objectifs->firstItem() ?? 1) + $loop->index }}</td>
                                <td class="px-4 py-4 whitespace-nowrap font-semibold text-slate-700">{{ $objectif->date }}</td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <p class="font-semibold {{ $isExpired ? 'text-rose-600' : 'text-slate-700' }}">{{ $objectif->date_echeance }}</p>
                                    @if ($isExpired)<p class="mt-0.5 text-[10px] font-bold text-rose-500">Dépassée</p>@endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-black text-slate-600">{{ $typeLabel }}</span>
                                </td>
                                <td class="px-4 py-4 font-black text-slate-900">{{ $cibleLabel }}</td>
                                <td class="px-4 py-4">
                                    <div class="min-w-[160px]">
                                        <div class="mb-1.5 flex items-center gap-2">
                                            @if (!$isLocked)
                                                <form method="POST" action="{{ route('admin.objectifs.progress', $objectif) }}">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="down">
                                                    <button type="submit" @disabled($isExpired)
                                                            class="flex h-7 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                                                        -10%
                                                    </button>
                                                </form>
                                            @endif
                                            <span class="rounded-full border {{ $progressValue > 50 ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }} px-2.5 py-0.5 text-sm font-black">{{ $progressValue }}%</span>
                                            @if ($isLocked)
                                                <span class="rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">Verrouillé</span>
                                            @endif
                                            @if (!$isLocked)
                                                <form method="POST" action="{{ route('admin.objectifs.progress', $objectif) }}">
                                                    @csrf
                                                    <input type="hidden" name="direction" value="up">
                                                    <button type="submit" @disabled($isExpired)
                                                            class="flex h-7 w-10 items-center justify-center rounded-lg bg-slate-800 text-xs font-bold text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-40">
                                                        +10%
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full {{ $progressColor }}" style="width: {{ $progressValue }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-slate-500">{{ \Illuminate\Support\Str::limit($objectif->commentaire, 80) }}</td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('admin.objectifs.show', $objectif) }}"
                                           class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                                           title="Voir">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                        <a href="{{ route('admin.objectifs.edit', $objectif) }}"
                                           class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-blue-100 hover:text-blue-600"
                                           title="Modifier">
                                            <i class="fas fa-pen text-xs"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.objectifs.destroy', $objectif) }}"
                                              onsubmit="return confirm('Supprimer cet objectif ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition hover:bg-rose-100 hover:text-rose-600"
                                                    title="Supprimer">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <div class="mx-auto max-w-sm rounded-[24px] border border-dashed border-slate-200 bg-slate-50 px-6 py-8">
                                        <i class="fas fa-inbox text-2xl text-slate-300"></i>
                                        <p class="mt-2 text-sm font-black text-slate-700">Aucun objectif enregistré</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($objectifs->hasPages())
            <div class="border-t border-slate-200 pt-4">
                {{ $objectifs->links() }}
            </div>
        @endif

        </div>
    </div>
</div>
@endsection
