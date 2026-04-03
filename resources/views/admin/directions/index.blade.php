@extends('layouts.app')

@section('title', 'Délégation Technique | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans">
    <div class="max-w-[1600px] mx-auto space-y-8">

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Délégations Techniques</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Référentiel</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-xs font-bold text-cyan-500 uppercase tracking-widest">Pilotage technique</span>
                </div>
            </div>
        </div>

        {{-- Contenu à configurer --}}

    </div>
</div>
@endsection