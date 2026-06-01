@extends('layouts.dga')
@section('title', 'Ma Secrétaire | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-10">

    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-violet-700 via-violet-600 to-purple-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-violet-300">Espace DGA · Mes Subordonnés</p>
            <h1 class="mt-1 text-2xl font-black text-white leading-tight">Ma Secrétaire</h1>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col items-center justify-center rounded-[24px] border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-amber-500">
                <i class="fas fa-user-slash text-2xl"></i>
            </div>
            <p class="mt-4 text-base font-black text-slate-700">Aucune secrétaire configurée</p>
            <p class="mt-1 text-sm text-slate-400">
                Aucune secrétaire n'est actuellement associée à votre direction.
            </p>
            <p class="mt-1 text-xs text-slate-300">
                Contactez l'administrateur pour configurer une secrétaire
                (champ <span class="font-mono">dga_secretaire_agent_id</span>).
            </p>
            <a href="{{ route('dga.subordonnes.index') }}"
               class="mt-6 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-600 shadow-sm transition hover:border-violet-300 hover:text-violet-700">
                <i class="fas fa-users text-xs"></i> Voir tous mes subordonnés
            </a>
        </div>
    </div>

</div>
@endsection
