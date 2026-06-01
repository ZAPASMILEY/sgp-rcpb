@extends('layouts.dg')

@section('title', 'Tableaux personnalisés | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    {{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
    <div class="relative overflow-hidden px-6 py-8 lg:px-10"
         style="background:linear-gradient(135deg,#164e63 0%,#0e7490 50%,#0891b2 100%)">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white shadow ring-1 ring-white/20">
                <i class="fas fa-file-excel"></i>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-cyan-200">DG · RCPB</p>
                <h1 class="mt-0.5 text-2xl font-black text-white">Tableaux personnalisés</h1>
                <p class="mt-0.5 text-sm text-cyan-100/75">Configurez et exportez vos propres rapports Excel</p>
            </div>
        </div>
    </div>

    @include('shared.tableaux._panel')

</div>
@endsection
