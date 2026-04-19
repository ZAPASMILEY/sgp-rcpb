@extends('layouts.dg')

@section('title', 'Fiche d\'objectifs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Espace DG / Fiche d'objectifs</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $fiche->titre }}</h1>
                    <p class="mt-2 text-sm text-slate-600">Année {{ $fiche->annee }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('dg.objectifs.pdf', $fiche) }}" class="ent-btn ent-btn-soft">
                        <i class="fas fa-file-pdf mr-2"></i>Télécharger PDF
                    </a>
                    <a href="{{ route('dg.mon-espace') }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>
            </div>
        </header>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm transition-all animate-pulse">
                <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- SECTION DE DÉCISION (ACCEPTER / REFUSER) --}}
        @if($fiche->statut === 'en_attente' || $fiche->statut === 'soumis')
            <section class="admin-panel p-6 border-2 border-amber-200 bg-amber-50 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 transition-all hover:scale-[1.01]">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-600 shadow-inner">
                        <i class="fas fa-question-circle text-xl animate-bounce"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-amber-900">Validation requise</h3>
                        <p class="text-sm text-amber-700">Veuillez examiner ces objectifs avant de valider votre fiche.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 w-full md:w-auto">
                    {{-- Bouton Refuser --}}
                    <form action="{{ route('dg.objectifs.statut', $fiche) }}" method="POST" class="flex-1 md:flex-none">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="statut" value="refusee">
                        <button type="submit" onclick="return confirm('Voulez-vous refuser ces objectifs et demander une correction ?')" 
                                class="w-full px-6 py-3 rounded-xl border-2 border-rose-200 bg-white text-rose-600 font-bold hover:bg-rose-50 transition shadow-sm">
                            Refuser / Corriger
                        </button>
                    </form>

                    {{-- Bouton Accepter --}}
                    <form action="{{ route('dg.objectifs.statut', $fiche) }}" method="POST" class="flex-1 md:flex-none">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="statut" value="acceptee">
                        <button type="submit" onclick="return confirm('En acceptant, vous validez votre contrat d\'objectifs pour cette période. Continuer ?')" 
                                class="w-full px-6 py-3 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-200">
                            Accepter la fiche
                        </button>
                    </form>
                </div>
            </section>
        @endif

        {{-- Remplissage des infos clés --}}
        <section class="admin-panel px-6 py-6 lg:px-8 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 shadow-sm hover:border-slate-300 transition-colors">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Date d'assignation</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') }}</p>
                </div>
                
                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 shadow-sm hover:border-slate-300 transition-colors">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Échéance</p>
                    <p class="mt-2 text-sm font-black text-slate-900">{{ \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Avancement</p>
                    @php
                        $avancement = (int) ($fiche->avancement_percentage ?? 0);
                        $avancementColor = $avancement >= 80 ? 'bg-emerald-500' : ($avancement >= 50 ? 'bg-sky-500' : ($avancement >= 25 ? 'bg-amber-400' : 'bg-slate-300'));
                    @endphp
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $avancement }}<span class="text-sm font-semibold text-slate-500">%</span></p>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full {{ $avancementColor }} transition-all duration-700" style="width: {{ $avancement }}%"></div>
                    </div>
                    
                    {{-- Formulaire de mise à jour (autorisé seulement si accepté) --}}
                    @if($fiche->statut === 'acceptee')
                    <form method="POST" action="{{ route('dg.objectifs.avancement', $fiche) }}" class="mt-3">
                        @csrf @method('PATCH')
                        <select name="avancement_percentage" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 focus:ring-1 focus:ring-emerald-400 cursor-pointer">
                            @for ($p = 0; $p <= 100; $p += 5)
                                <option value="{{ $p }}" @selected($avancement === $p)>{{ $p }}%</option>
                            @endfor
                        </select>
                    </form>
                    @endif
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Statut</p>
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
                        <span class="inline-flex items-center rounded-full border px-4 py-1 text-xs font-black uppercase tracking-wider {{ $statClass }}">
                            <span class="mr-1.5 h-1.5 w-1.5 rounded-full bg-current"></span>
                            {{ $statLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </section>

        {{-- Liste des Objectifs --}}
        <section class="admin-panel px-6 py-6 lg:px-8 shadow-md">
            <h2 class="text-lg font-black text-slate-900 border-b border-slate-100 pb-4 mb-4">Objectifs assignés</h2>
            <div class="space-y-4">
                @foreach($fiche->objectifs as $objectif)
                    <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white px-5 py-5 transition-all hover:border-emerald-200 hover:shadow-md hover:-translate-y-1">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm">
                            <i class="fas fa-bullseye text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-700 leading-relaxed">{{ $objectif->description }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

    </div>
</div>
@endsection