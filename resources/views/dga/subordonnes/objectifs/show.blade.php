@extends('layouts.dga')
@section('title', 'Fiche d\'objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <a href="{{ route('dga.subordonnes.show', $subordonne).'?tab=objectifs' }}"
                       class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-violet-600">
                        <i class="fas fa-arrow-left"></i> Retour au dossier
                    </a>
                    <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $fiche->titre }}</h1>
                    <p class="mt-1 text-sm text-slate-500">Assignée à : <span class="font-semibold text-slate-700">{{ $subordonne?->name ?? '—' }}</span> · Année {{ $fiche->annee }}</p>
                </div>
                @if ($fiche->statut !== 'acceptee')
                    <form method="POST" action="{{ route('dga.sub-objectifs.destroy', $fiche) }}"
                          onsubmit="return confirm('Supprimer cette fiche d\'objectifs ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="ent-btn ent-btn-soft text-rose-600 hover:bg-rose-50">
                            <i class="fas fa-trash mr-1 text-xs"></i>Supprimer
                        </button>
                    </form>
                @endif
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- KPIs --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Date d'assignation</p>
                <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Échéance</p>
                <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Avancement</p>
                @php
                    $avancement    = (int) ($fiche->avancement_percentage ?? 0);
                    $avancementColor = $avancement >= 80 ? 'bg-emerald-500' : ($avancement >= 50 ? 'bg-sky-500' : ($avancement >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                @endphp
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $avancement }}<span class="text-sm font-semibold text-slate-500">%</span></p>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full {{ $avancementColor }}" style="width: {{ $avancement }}%"></div>
                </div>
                @if ($fiche->statut === 'acceptee')
                    <form method="POST" action="{{ route('dga.sub-objectifs.avancement', $fiche) }}" class="mt-3">
                        @csrf @method('PATCH')
                        <select name="avancement_percentage" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 focus:ring-1 focus:ring-violet-400 cursor-pointer">
                            @for ($p = 0; $p <= 100; $p += 5)
                                <option value="{{ $p }}" @selected($avancement === $p)>{{ $p }}%</option>
                            @endfor
                        </select>
                    </form>
                @endif
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Statut</p>
                @php
                    $statClass = match ($fiche->statut ?? 'en_attente') {
                        'acceptee' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'refusee'  => 'border-rose-200 bg-rose-50 text-rose-700',
                        default    => 'border-amber-200 bg-amber-50 text-amber-700',
                    };
                    $statLabel = match ($fiche->statut ?? 'en_attente') {
                        'acceptee' => 'Acceptée',
                        'refusee'  => 'Refusée',
                        default    => 'En attente',
                    };
                @endphp
                <div class="mt-2">
                    <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-black {{ $statClass }}">
                        <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
                        {{ $statLabel }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Liste des objectifs --}}
        <section class="admin-panel overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-black uppercase tracking-widest text-slate-700">
                    Objectifs assignés
                    <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $fiche->objectifs->count() }}</span>
                </h2>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($fiche->objectifs as $objectif)
                    <div class="flex items-start gap-4 px-6 py-4">
                        <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600 border border-violet-100">
                            <i class="fas fa-bullseye text-xs"></i>
                        </div>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $objectif->description }}</p>
                    </div>
                @empty
                    <p class="px-6 py-8 text-sm text-slate-400 text-center">Aucun objectif enregistré.</p>
                @endforelse
            </div>
        </section>

    </div>
</div>
@endsection
