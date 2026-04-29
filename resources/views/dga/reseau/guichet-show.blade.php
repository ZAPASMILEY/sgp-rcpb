@extends('layouts.dga')
@section('title', $guichet->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
    <div class="w-full flex flex-col gap-6">

        <header class="admin-panel px-6 py-6 lg:px-8">
            <div>
                <a href="{{ route('dga.reseau.guichets') }}" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-slate-400 hover:text-violet-600">
                    <i class="fas fa-arrow-left"></i> Guichets
                </a>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">{{ $guichet->nom }}</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $guichet->agence?->nom ?? '' }}
                    @if($guichet->agence?->caisse) — {{ $guichet->agence->caisse->nom }} @endif
                </p>
            </div>
        </header>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Chef de guichet</p>
                <p class="mt-1 font-bold text-slate-800">{{ $guichet->chef_nom ?? '—' }}</p>
                <p class="text-xs text-slate-400">{{ $guichet->chef_email ?? '' }}</p>
            </div>
            <div class="admin-panel px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Téléphone</p>
                <p class="mt-1 font-bold text-slate-800">{{ $guichet->chef_telephone ?? '—' }}</p>
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
