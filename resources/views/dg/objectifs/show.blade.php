@extends('layouts.dg')

@section('title', 'Fiche d\'objectifs')

@section('content')
<div class="max-w-2xl mx-auto mt-12 bg-white rounded-3xl shadow-xl p-10 border border-slate-100">
    <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center justify-center h-14 w-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white text-3xl">
            <i class="fas fa-bullseye"></i>
        </div>
        <div>
            <h1 class="text-2xl font-black text-slate-900">Fiche d'objectifs</h1>
            <div class="text-sm text-slate-400">DÃ©tail et validation de la fiche</div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <div class="text-xs text-slate-400 font-bold uppercase">Titre</div>
            <div class="text-base font-semibold text-slate-800">{{ $fiche->titre }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 font-bold uppercase">AnnÃ©e</div>
            <div class="text-base font-semibold text-slate-800">{{ $fiche->annee }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 font-bold uppercase">Date</div>
            <div class="text-base text-slate-700">{{ $fiche->date }}</div>
        </div>
        <div>
            <div class="text-xs text-slate-400 font-bold uppercase">Ã‰chÃ©ance</div>
            <div class="text-base text-slate-700">{{ $fiche->date_echeance }}</div>
        </div>
        <div class="col-span-2">
            <div class="text-xs text-slate-400 font-bold uppercase">Statut</div>
            <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold
                @if($fiche->statut=='acceptee') bg-emerald-100 text-emerald-700
                @elseif($fiche->statut=='refusee') bg-red-100 text-red-700
                @else bg-gray-100 text-gray-700 @endif">
                {{ ucfirst($fiche->statut) }}
            </span>
        </div>
    </div>


    <div class="mb-8">
        <div class="text-xs text-slate-400 font-bold uppercase mb-2">Objectifs</div>
        <ul class="space-y-2">
            @foreach($fiche->objectifs as $objectif)
                <li class="flex items-start gap-2">
                    <span class="mt-1 text-emerald-500"><i class="fas fa-check-circle"></i></span>
                    <span class="text-slate-800">{{ $objectif->description }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mb-8">
        <div class="text-xs text-slate-400 font-bold uppercase mb-2">Avancement global</div>
        <div class="flex items-center gap-4">
            <form method="POST" action="{{ route('dg.objectifs.avancement', $fiche) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="avancement_percentage" value="{{ max(0, $fiche->avancement_percentage - 5) }}">
                <button type="submit" @disabled($fiche->avancement_percentage <= 0) class="rounded-lg bg-slate-200 px-4 py-2 font-bold text-slate-700 shadow hover:bg-slate-300 disabled:cursor-not-allowed disabled:opacity-50">-5</button>
            </form>

            <span class="min-w-[64px] text-center text-lg font-bold text-emerald-700">{{ $fiche->avancement_percentage }}%</span>

            <form method="POST" action="{{ route('dg.objectifs.avancement', $fiche) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="avancement_percentage" value="{{ min(100, $fiche->avancement_percentage + 5) }}">
                <button type="submit" @disabled($fiche->avancement_percentage >= 100) class="rounded-lg bg-emerald-600 px-4 py-2 font-bold text-white shadow hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">+5</button>
            </form>
        </div>
    </div>

    @if($fiche->statut === 'en_attente')
    <form method="POST" action="{{ route('dg.objectifs.statut', $fiche) }}" class="flex gap-4">
        @csrf
        @method('PATCH')
        <button name="statut" value="acceptee" class="flex-1 px-4 py-2 rounded-lg bg-emerald-600 text-white font-bold hover:bg-emerald-700 shadow">Accepter</button>
        <button name="statut" value="refusee" class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 shadow">Refuser</button>
    </form>
    @endif

    <a href="{{ route('dg.mon-espace') }}" class="inline-block mt-8 text-emerald-700 font-bold hover:underline">&larr; Retour Ã  la liste</a>
</div>
@endsection
