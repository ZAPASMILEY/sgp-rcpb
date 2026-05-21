@extends('layouts.chef')
@section('title', $fiche->titre . ' | ' . config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $statut        = $fiche->statut ?? 'en_attente';
    $sc = match($statut) {
        'acceptee'  => ['label'=>'Acceptée',   'bg'=>'bg-emerald-100', 'text'=>'text-emerald-700', 'dot'=>'bg-emerald-500', 'border'=>'border-emerald-200'],
        'refusee'   => ['label'=>'Refusée',    'bg'=>'bg-rose-100',    'text'=>'text-rose-700',    'dot'=>'bg-rose-500',    'border'=>'border-rose-200'],
        'contesté'  => ['label'=>'Contestée',  'bg'=>'bg-orange-100',  'text'=>'text-orange-700',  'dot'=>'bg-orange-500',  'border'=>'border-orange-200'],
        default     => ['label'=>'En attente', 'bg'=>'bg-amber-100',   'text'=>'text-amber-700',   'dot'=>'bg-amber-400',   'border'=>'border-amber-200'],
    };
    $avancement    = (int) ($fiche->avancement_percentage ?? 0);
    $progressColor = $avancement >= 75 ? 'bg-emerald-500' : ($avancement >= 40 ? 'bg-sky-500' : ($avancement > 0 ? 'bg-amber-400' : 'bg-slate-200'));
    $echeance      = $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance) : null;
    $expired       = $echeance && $echeance->isPast();
    $isPending     = $statut === 'en_attente';
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-[#008751] via-[#006837] to-[#006837] px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-green-200">
                    Espace Chef · {{ $ctx->getRoleLabel() }} · Mes objectifs reçus
                </p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">{{ $fiche->titre }}</h1>
                <p class="mt-0.5 text-sm text-green-100/80">Année {{ $fiche->annee?->annee ?? $fiche->annee_id }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white ring-1 ring-white/20">
                    <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
                    {{ $sc['label'] }}
                </span>
                <a href="{{ route('chef.mon-espace') }}?tab=objectifs"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Mon espace
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5">

        {{-- Messages flash --}}
        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-circle-check"></i> {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Contesté banner --}}
        @if ($statut === 'contesté')
        <div class="flex items-center gap-4 rounded-[24px] border-2 border-orange-200 bg-orange-50 px-6 py-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">
                <i class="fas fa-flag text-lg"></i>
            </div>
            <div>
                <p class="font-black text-orange-900">Fiche contestée</p>
                <p class="mt-0.5 text-sm text-orange-700">Vous avez contesté un ou plusieurs objectifs. Votre supérieur a été notifié.</p>
            </div>
        </div>
        @endif

        {{-- Bandeau accepter / refuser --}}
        @if ($isPending)
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-amber-200 bg-amber-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-hourglass-half text-xl"></i>
                </div>
                <div>
                    <p class="font-black text-amber-900">Validation requise</p>
                    <p class="mt-0.5 text-sm text-amber-700">Examinez ces objectifs assignés par votre supérieur avant de valider.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('chef.mes-fiches.statut', $fiche) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="refuser">
                    <button type="submit"
                            onclick="return confirm('Refuser cette fiche d\'objectifs ?')"
                            class="inline-flex items-center gap-2 rounded-xl border-2 border-rose-200 bg-white px-5 py-2.5 text-sm font-black text-rose-600 transition hover:bg-rose-50">
                        <i class="fas fa-times text-xs"></i> Refuser
                    </button>
                </form>
                <form action="{{ route('chef.mes-fiches.statut', $fiche) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="accepter">
                    <button type="submit"
                            onclick="return confirm('Accepter ce contrat d\'objectifs ?')"
                            class="inline-flex items-center gap-2 rounded-xl bg-[#008751] px-5 py-2.5 text-sm font-black text-white shadow-md transition hover:bg-[#006837]">
                        <i class="fas fa-check text-xs"></i> Accepter la fiche
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- 5 KPI cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">

            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Assignée le</p>
                <p class="mt-2 text-lg font-black text-slate-900">
                    {{ $fiche->date ? \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') : '—' }}
                </p>
            </div>

            <div class="rounded-[20px] border {{ $expired ? 'border-rose-200 bg-rose-50' : 'border-slate-100 bg-white' }} px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Échéance</p>
                <p class="mt-2 text-lg font-black {{ $expired ? 'text-rose-600' : 'text-slate-900' }}">
                    {{ $echeance ? $echeance->format('d/m/Y') : '—' }}
                </p>
                @if ($expired)<p class="mt-0.5 text-[10px] font-bold text-rose-500">Dépassée</p>@endif
            </div>

            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Avancement global</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $avancement }}<span class="text-sm font-bold text-slate-400">%</span></p>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full transition-all {{ $progressColor }}" style="width: {{ $avancement }}%"></div>
                </div>
                <p class="mt-1 text-[10px] text-slate-400">Calculé automatiquement</p>
            </div>

            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Date de validation</p>
                @if ($fiche->date_validation)
                    <p class="mt-2 text-lg font-black text-emerald-700">
                        {{ \Carbon\Carbon::parse($fiche->date_validation)->format('d/m/Y') }}
                    </p>
                    <p class="mt-0.5 text-[10px] font-bold text-emerald-500"><i class="fas fa-circle-check mr-1"></i>Validée</p>
                @else
                    <p class="mt-2 text-lg font-black text-slate-400">—</p>
                    <p class="mt-0.5 text-[10px] text-slate-400">Non encore validée</p>
                @endif
            </div>

            <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</p>
                <div class="mt-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full border {{ $sc['border'] }} {{ $sc['bg'] }} px-3 py-1 text-xs font-black {{ $sc['text'] }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>{{ $sc['label'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Liste des objectifs --}}
        <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <p class="text-sm font-black text-slate-800">
                    Objectifs assignés
                    <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">
                        {{ $fiche->objectifs->count() }}
                    </span>
                </p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#008751]/10 text-[#008751]">
                    <i class="fas fa-bullseye"></i>
                </span>
            </div>
            <div class="divide-y divide-slate-50 px-6 py-2">
                @foreach ($fiche->objectifs as $index => $objectif)
                    @php
                        $contested  = ($objectif->statut ?? 'normal') === 'contesté';
                        $ligneAv    = (int) ($objectif->avancement_percentage ?? 0);
                        $ligneColor = $ligneAv >= 75 ? 'bg-emerald-500' : ($ligneAv >= 40 ? 'bg-sky-500' : ($ligneAv > 0 ? 'bg-amber-400' : 'bg-slate-200'));
                    @endphp
                    <div class="flex items-start gap-4 py-4 rounded-xl {{ $contested ? 'bg-rose-50 -mx-2 px-2' : '' }}">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-sm font-black ring-1
                            {{ $contested ? 'bg-rose-100 text-rose-600 ring-rose-200' : 'bg-[#008751]/10 text-[#008751] ring-[#008751]/20' }}">
                            {{ $index + 1 }}
                        </div>

                        <div class="flex-1 min-w-0 pt-1">
                            <p class="text-sm leading-relaxed {{ $contested ? 'text-rose-700 font-semibold' : 'text-slate-700' }}">{{ $objectif->description }}</p>

                            @if ($contested)
                                <p class="mt-1 text-[10px] font-bold text-rose-500"><i class="fas fa-flag mr-1"></i>Contesté</p>
                            @else
                                <div class="mt-2 flex items-center gap-2">
                                    <div class="flex-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full transition-all {{ $ligneColor }}" style="width: {{ $ligneAv }}%"></div>
                                    </div>
                                    <span class="shrink-0 text-[11px] font-black text-slate-600 w-8 text-right">{{ $ligneAv }}%</span>
                                </div>
                                @if ($statut === 'acceptee')
                                    <form method="POST" action="{{ route('chef.mes-fiches.lignes.avancement', [$fiche, $objectif]) }}" class="mt-2">
                                        @csrf @method('PATCH')
                                        <select name="avancement_percentage" onchange="this.form.submit()"
                                                class="w-full max-w-[160px] rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-[#008751] cursor-pointer">
                                            @for ($p = 0; $p <= 100; $p += 5)
                                                <option value="{{ $p }}" @selected($ligneAv === $p)>{{ $p }}%</option>
                                            @endfor
                                        </select>
                                    </form>
                                @endif
                            @endif
                        </div>

                        @if ($statut !== 'acceptee' && ! $contested)
                            <form method="POST" action="{{ route('chef.mes-fiches.lignes.contester', [$fiche, $objectif]) }}" class="shrink-0 pt-1">
                                @csrf @method('PATCH')
                                <button type="submit"
                                        onclick="return confirm('Contester cet objectif ?')"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-black text-orange-600 transition hover:bg-orange-100">
                                    <i class="fas fa-flag text-[10px]"></i> Contester
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Footer (retour + PDF) --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] bg-white px-6 py-4 shadow-sm ring-1 ring-slate-100">
            <a href="{{ route('chef.mon-espace') }}?tab=objectifs"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 shadow-sm transition hover:border-slate-300">
                <i class="fas fa-arrow-left text-xs"></i> Mon espace
            </a>
            <a href="{{ route('chef.mes-fiches.pdf', $fiche) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-[#008751] px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-[#006837]">
                <i class="fas fa-file-pdf text-xs"></i> Télécharger PDF
            </a>
        </div>

        </div>
    </div>
</div>
@endsection
