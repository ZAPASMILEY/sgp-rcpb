@extends('layouts.dga')
@section('title', $guichet->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-600 px-6 py-8 lg:px-10">
            <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-white/5"></div>
            <div class="absolute -bottom-6 right-16 h-20 w-20 rounded-full bg-white/5"></div>
            <a href="{{ route('dga.reseau.guichets') }}" class="mb-3 inline-flex items-center gap-1.5 text-xs font-bold text-emerald-200 hover:text-white transition-colors">
                <i class="fas fa-arrow-left text-[10px]"></i> Guichets
            </a>
            <h1 class="text-2xl font-black tracking-tight text-white">{{ $guichet->nom }}</h1>
            <p class="mt-1 text-sm text-emerald-100/80">
                {{ $guichet->agence?->nom ?? '' }}
                @if($guichet->agence?->caisse) — {{ $guichet->agence->caisse->nom }} @endif
            </p>
            <div class="absolute right-6 top-1/2 -translate-y-1/2 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10">
                <i class="fas fa-cash-register text-2xl text-white"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chef de guichet</p>
                <p class="mt-1 font-bold text-slate-800">
                    {{ $guichet->chef ? $guichet->chef->prenom.' '.$guichet->chef->nom : '—' }}
                </p>
                <p class="text-xs text-slate-400">{{ $guichet->chef?->role ?? '' }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Téléphone</p>
                <p class="mt-1 font-bold text-slate-800">{{ $guichet->chef?->numero_telephone ?? '—' }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Agence parente</p>
                <p class="mt-1 font-bold text-slate-800">{{ $guichet->agence?->nom ?? '—' }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Note moyenne</p>
                @if($noteStats['moyenne'] !== null)
                    @php $c = $noteStats['moyenne'] >= 8.5 ? 'text-emerald-600' : ($noteStats['moyenne'] >= 7 ? 'text-blue-600' : ($noteStats['moyenne'] >= 5 ? 'text-amber-600' : 'text-rose-600')); @endphp
                    <p class="mt-1 text-3xl font-black {{ $c }}">{{ number_format($noteStats['moyenne'], 2) }}</p>
                    <p class="text-xs text-slate-400">sur {{ $noteStats['total'] }} évaluation(s)</p>
                @else
                    <p class="mt-1 text-xl font-bold text-slate-300">—</p>
                    <p class="text-xs text-slate-300">Aucune évaluation</p>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
