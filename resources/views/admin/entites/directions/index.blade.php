@extends('layouts.app')

@section('title', 'Directions | SGP-RCPB')

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans">
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <div class="max-w-[1600px] mx-auto space-y-8">

        {{-- HEADER : Titre & Action --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Directions Faîtière</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Administration</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-xs font-bold text-cyan-500 uppercase tracking-widest">Unités Centrales</span>
                </div>
            </div>
            
            <div class="flex items-center gap-4">
                {{-- Barre de recherche intégrée --}}
                <form method="GET" action="{{ route('admin.entites.directions.index') }}" class="flex items-center bg-white p-2 rounded-2xl shadow-sm border border-slate-100 min-w-[300px]">
                    <div class="flex-1 flex items-center px-3 gap-2">
                        <i class="fas fa-search text-slate-300 text-sm"></i>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="w-full bg-transparent border-none focus:ring-0 text-sm text-slate-600 placeholder-slate-300 font-medium" 
                               placeholder="Rechercher une direction...">
                    </div>
                    <button type="submit" class="bg-slate-50 text-slate-400 px-4 py-2 rounded-xl text-xs font-black uppercase hover:bg-slate-100 transition-all">Filtrer</button>
                </form>

                <a href="{{ route('admin.entites.directions.create') }}" data-open-modal data-title="Nouvelle direction faitière" 
                   class="h-12 px-6 bg-cyan-500 text-white rounded-2xl text-xs font-black uppercase tracking-widest flex items-center gap-2 hover:bg-cyan-600 transition-all shadow-lg shadow-cyan-100">
                    <i class="fas fa-plus"></i> Nouvelle Direction
                </a>
            </div>
        </div>

        <div class="flex justify-end">
            <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">{{ $directions->total() }} direction(s)</div>
        </div>

        {{-- TABLEAU MODERNISÉ --}}
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-slate-400 text-[11px] font-black uppercase tracking-[0.2em]">
                        <th class="px-6 pb-2 text-left">#</th>
                        <th class="px-6 pb-2 text-left">Détails Direction</th>
                        <th class="px-6 pb-2 text-left">Responsable</th>
                        <th class="px-6 pb-2 text-center">Services</th>
                        <th class="px-6 pb-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($directions as $direction)
                    <tr class="bg-white group hover:scale-[1.01] transition-all duration-200">
                        {{-- Ordre --}}
                        <td class="px-6 py-5 first:rounded-l-[24px] border-y border-l border-slate-50 shadow-sm shadow-slate-200/20">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-slate-50 text-slate-400 font-black text-xs group-hover:bg-cyan-500 group-hover:text-white transition-colors">
                                {{ $loop->iteration + ($directions->currentPage() - 1) * $directions->perPage() }}
                            </span>
                        </td>

                        {{-- Détails --}}
                        <td class="px-6 py-5 border-y border-slate-50 shadow-sm shadow-slate-200/20">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 bg-gradient-to-br from-slate-50 to-slate-100 rounded-2xl flex items-center justify-center text-cyan-600 font-black text-lg shadow-sm border border-white">
                                    {{ substr($direction->nom, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-black text-slate-800 text-base leading-tight truncate group-hover:text-cyan-600 transition-colors">{{ $direction->nom }}</p>
                                    <p class="text-[10px] text-slate-300 font-bold uppercase mt-1 tracking-widest">ID: #{{ $direction->id }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Responsable --}}
                        <td class="px-6 py-5 border-y border-slate-50 shadow-sm shadow-slate-200/20">
                            <p class="font-bold text-slate-700 text-sm italic">{{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}</p>
                            <p class="text-[11px] text-cyan-500 font-medium lowercase flex items-center gap-1 mt-1">
                                <i class="far fa-envelope opacity-50"></i> {{ $direction->directeur_email ?? 'n/a' }}
                            </p>
                        </td>

                        {{-- Services --}}
                        <td class="px-6 py-5 border-y border-slate-50 shadow-sm shadow-slate-200/20 text-center">
                            <a href="{{ route('admin.services.index', ['direction_id' => $direction->id, 'source' => 'faitiere']) }}" 
                               class="px-4 py-2 bg-slate-50 text-slate-500 text-[10px] font-black uppercase rounded-xl border border-slate-100 hover:bg-cyan-500 hover:text-white hover:border-cyan-500 transition-all">
                                {{ $direction->services_count ?? 0 }} Services
                            </a>
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-5 last:rounded-r-[24px] border-y border-r border-slate-50 shadow-sm shadow-slate-200/20 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.directions.show', $direction) }}" class="h-9 w-9 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-cyan-500 hover:border-cyan-200 transition-all shadow-sm">
                                    <i class="far fa-eye text-sm"></i>
                                </a>
                                <a href="{{ route('admin.directions.edit', $direction) }}" class="h-9 w-9 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-emerald-500 hover:border-emerald-200 transition-all shadow-sm">
                                    <i class="far fa-edit text-sm"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.directions.destroy', $direction) }}" class="inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="h-9 w-9 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:border-rose-200 transition-all shadow-sm">
                                        <i class="far fa-trash-alt text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center text-slate-200 text-3xl mb-4">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <p class="text-slate-400 font-bold italic">Aucune direction trouvée.</p>
                                <a href="{{ route('admin.entites.directions.create') }}" class="mt-4 text-cyan-500 text-xs font-black uppercase hover:underline">Créer la première direction</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-8 px-4">
            {{ $directions->links() }}
        </div>
    </div>
</div>
@endsection