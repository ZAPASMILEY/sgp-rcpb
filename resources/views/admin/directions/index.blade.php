@extends('layouts.app')

@section('title', 'Directions | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Directions fonctionnelles')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 3000);</script>
        @endif

        {{-- En-tête --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">Directions fonctionnelles</h1>
                <p class="mt-0.5 text-xs text-slate-400">DRH, DAF, DTIC et autres directions de la faîtière</p>
            </div>
            <a href="{{ route('admin.directions.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fas fa-plus"></i> Ajouter une direction
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Directions</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ $stats['directions'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Agents</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ $stats['agents'] }}</p>
            </div>
            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Services</p>
                <p class="mt-1 text-3xl font-black text-slate-800">{{ $stats['services'] }}</p>
            </div>
        </div>

        {{-- Liste des directions --}}
        @if ($directions->isEmpty())
            <div class="rounded-2xl bg-white p-10 text-center shadow-sm">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100">
                    <i class="fas fa-building-columns text-2xl text-slate-300"></i>
                </div>
                <p class="mt-4 text-sm font-bold text-slate-600">Aucune direction fonctionnelle</p>
                <p class="mt-1 text-xs text-slate-400">Ajoutez la DRH, la DAF, la DTIC…</p>
                <a href="{{ route('admin.directions.create') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fas fa-plus"></i> Ajouter
                </a>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($directions as $dir)
                    <div class="flex flex-col rounded-2xl bg-white shadow-sm overflow-hidden">
                        {{-- Header carte --}}
                        <div class="flex items-center gap-3 border-b border-slate-100 p-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                                <i class="fas fa-building-columns"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-black text-slate-900">{{ $dir->nom }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    <span class="font-bold text-slate-600">{{ $dir->agents_count }}</span> agent{{ $dir->agents_count > 1 ? 's' : '' }}
                                    &nbsp;·&nbsp;
                                    <span class="font-bold text-slate-600">{{ $dir->services_count }}</span> service{{ $dir->services_count > 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>

                        {{-- Infos --}}
                        <div class="flex-1 space-y-2 p-4 text-sm">
                            <div class="flex items-start gap-2">
                                <span class="w-20 shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">Directeur</span>
                                @if ($dir->directeur)
                                    <span class="font-semibold text-slate-700">{{ $dir->directeur->prenom }} {{ $dir->directeur->nom }}</span>
                                @else
                                    <span class="text-slate-400 italic">Non désigné</span>
                                @endif
                            </div>
                            <div class="flex items-start gap-2">
                                <span class="w-20 shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">Secrétaire</span>
                                @if ($dir->secretaire)
                                    <span class="font-semibold text-slate-700">{{ $dir->secretaire->prenom }} {{ $dir->secretaire->nom }}</span>
                                @else
                                    <span class="text-slate-400 italic">Non désigné</span>
                                @endif
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 border-t border-slate-100 p-3">
                            <a href="{{ route('admin.directions.show', $dir) }}"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-200 transition">
                                <i class="fas fa-eye text-[10px]"></i> Voir
                            </a>
                            <a href="{{ route('admin.directions.edit', $dir) }}"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 transition">
                                <i class="fas fa-pen text-[10px]"></i> Modifier
                            </a>
                            <form method="POST" action="{{ route('admin.directions.destroy', $dir) }}"
                                  onsubmit="return confirm('Supprimer la direction « {{ $dir->nom }} » ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-1 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-red-500 hover:bg-red-50 transition">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
@endsection
