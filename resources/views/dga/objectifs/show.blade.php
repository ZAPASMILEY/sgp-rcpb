@extends('layouts.dga')
@section('title', $fiche->titre.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $statut  = $fiche->statut ?? 'en_attente';
    $sc = match($statut) {
        'acceptee'  => ['label'=>'Acceptée',   'bg'=>'bg-indigo-100','text'=>'text-indigo-700','dot'=>'bg-indigo-500','border'=>'border-indigo-200'],
        'refusee'   => ['label'=>'Refusée',    'bg'=>'bg-rose-100',  'text'=>'text-rose-700',  'dot'=>'bg-rose-500',  'border'=>'border-rose-200'],
        default     => ['label'=>'En attente', 'bg'=>'bg-amber-100', 'text'=>'text-amber-700', 'dot'=>'bg-amber-400', 'border'=>'border-amber-200'],
    };
    $avancement    = (int) ($fiche->avancement_percentage ?? 0);
    $progressColor = $avancement >= 75 ? 'bg-emerald-500' : ($avancement >= 40 ? 'bg-sky-500' : ($avancement > 0 ? 'bg-amber-400' : 'bg-slate-200'));
    $echeance      = $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance) : null;
    $expired       = $echeance && $echeance->isPast();
    $isPending     = $statut === 'en_attente' || $statut === 'soumis';
@endphp
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-200">Espace DGA · Mes objectifs</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">{{ $fiche->titre }}</h1>
                <p class="mt-0.5 text-sm text-indigo-100/80">Année {{  $fiche->annee ?? '—'}}</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white ring-1 ring-white/20">
                    <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
                    {{ $sc['label'] }}
                </span>
                <a href="{{ route('dga.mon-espace') }}?tab=objectifs"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Mon espace
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
        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
                <i class="fas fa-circle-exclamation"></i>{{ session('error') }}
            </div>
        @endif

        {{-- Validation banner --}}
        @if ($isPending)
        <div class="flex flex-col gap-4 rounded-[24px] border-2 border-amber-200 bg-amber-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                    <i class="fas fa-hourglass-half text-xl"></i>
                </div>
                <div>
                    <p class="font-black text-amber-900">Validation requise</p>
                    <p class="mt-0.5 text-sm text-amber-700">Examinez ces objectifs avant de donner votre réponse.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-3">
                <form action="{{ route('dga.objectifs.statut', $fiche) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="refuser">
                    <button type="submit"
                            onclick="return confirm('Refuser et demander une correction ?')"
                            class="inline-flex items-center gap-2 rounded-xl border-2 border-rose-200 bg-white px-5 py-2.5 text-sm font-black text-rose-600 transition hover:bg-rose-50">
                        <i class="fas fa-times text-xs"></i> Refuser
                    </button>
                </form>
                <form action="{{ route('dga.objectifs.statut', $fiche) }}" method="POST">
                    @csrf @method('PATCH')
                    <input type="hidden" name="action" value="accepter">
                    <button type="submit"
                            onclick="return confirm('Valider ce contrat d\'objectifs pour cette période ?')"
                            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-black text-white shadow-md shadow-indigo-200 transition hover:bg-indigo-700">
                        <i class="fas fa-check text-xs"></i> Accepter la fiche
                    </button>
                </form>
            </div>
        </div>
        @endif

        {{-- 4 KPI cards --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
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
                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Avancement</p>
                <p class="mt-2 text-2xl font-black text-slate-900">{{ $avancement }}<span class="text-sm font-bold text-slate-400">%</span></p>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full {{ $progressColor }}" style="width: {{ $avancement }}%"></div>
                </div>
                @if ($statut === 'acceptee')
                    <form method="POST" action="{{ route('dga.objectifs.avancement', $fiche) }}" class="mt-2">
                        @csrf @method('PATCH')
                        <select name="avancement_percentage" onchange="this.form.submit()"
                                class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-indigo-400 cursor-pointer">
                            @for ($p = 0; $p <= 100; $p += 5)
                                <option value="{{ $p }}" @selected($avancement === $p)>{{ $p }}%</option>
                            @endfor
                        </select>
                    </form>
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

        {{-- Objectifs list --}}
        <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <p class="text-sm font-black text-slate-800">
                    Objectifs assignés <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $fiche->objectifs->count() }}</span>
                </p>
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                    <i class="fas fa-bullseye"></i>
                </span>
            </div>
            <div class="divide-y divide-slate-50 px-6 py-2">
                @forelse ($fiche->objectifs as $index => $objectif)
                    <div class="flex items-start gap-4 py-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-sm font-black text-indigo-600 ring-1 ring-indigo-100">
                            {{ $index + 1 }}
                        </div>
                        <p class="flex-1 pt-1 text-sm leading-relaxed text-slate-700">{{ $objectif->description }}</p>
                    </div>
                @empty
                    <div class="py-10 text-center">
                        <i class="fas fa-inbox text-2xl text-slate-200"></i>
                        <p class="mt-2 text-sm text-slate-400">Aucun objectif défini dans cette fiche.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] bg-white px-6 py-4 shadow-sm ring-1 ring-slate-100">
            <a href="{{ route('dga.mon-espace') }}?tab=objectifs"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 shadow-sm transition hover:border-slate-300">
                <i class="fas fa-arrow-left text-xs"></i> Mon espace
            </a>
            <a href="{{ route('dga.objectifs.pdf', $fiche) }}"
               class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700">
                <i class="fas fa-file-pdf text-xs"></i> Télécharger PDF
            </a>
        </div>

        </div>
    </div>
</div>
@endsection
