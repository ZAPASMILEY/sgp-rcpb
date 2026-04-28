@extends('layouts.app')

@section('title', 'Guichets | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="guichet-status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('guichet-status-message')?.remove(), 3000);</script>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Index des guichets</h1>
                    <p class="mt-1 text-sm text-slate-400">Gestion des guichets du réseau RCPB.</p>
                </div>
                <a href="{{ route('admin.guichets.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700">
                    <i class="fas fa-plus text-xs text-emerald-300"></i> Ajouter un guichet
                </a>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-{{ 1 + $stats['par_delegation']->count() }}">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20"><i class="fas fa-window-maximize"></i></span>
                    <span class="text-3xl font-black">{{ $stats['total'] }}</span>
                </div>
                <p class="mt-3 text-sm font-bold">Total Guichets</p>
            </div>
            @foreach ($stats['par_delegation'] as $i => $delegation)
                @php $gradients = ['from-violet-500 to-purple-600', 'from-blue-500 to-indigo-600', 'from-amber-400 to-orange-500']; @endphp
                <div class="rounded-2xl bg-gradient-to-br {{ $gradients[$i % count($gradients)] }} p-5 text-white shadow-sm">
                    <div class="flex items-start justify-between">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20"><i class="fas fa-map-marker-alt"></i></span>
                        <span class="text-3xl font-black">{{ $delegation->guichets_count }}</span>
                    </div>
                    <p class="mt-3 text-sm font-bold">{{ $delegation->region }}</p>
                </div>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="mb-6">
                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Recherche</label>
                <input id="guichet-search" type="text" placeholder="Rechercher un guichet, un chef ou une agence..." class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-700">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-3 text-[11px] font-bold uppercase text-slate-400">Guichet</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase text-slate-400">Chef de guichet</th>
                            <th class="px-3 py-3 text-[11px] font-bold uppercase text-slate-400">Agence</th>
                            <th class="px-3 py-3 text-right text-[11px] font-bold uppercase text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guichets as $guichet)
                            <tr class="border-b border-slate-50 hover:bg-slate-50 transition" 
                                data-search-content="{{ strtolower($guichet->nom . ' ' . $guichet->chef?->nom . ' ' . $guichet->agence?->nom) }}">
                                <td class="px-3 py-3 font-semibold text-slate-800">{{ $guichet->nom }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700">{{ $guichet->chef?->nom ?? 'Non assigné' }} {{ $guichet->chef?->prenom }}</span>
                                        <span class="text-xs text-slate-400">{{ $guichet->chef?->email }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <p class="font-semibold">{{ $guichet->agence?->nom ?? '-' }}</p>
                                    <p class="text-xs text-slate-400">{{ $guichet->agence?->delegationTechnique?->region }}</p>
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.guichets.edit', $guichet) }}" class="text-blue-500 hover:text-blue-700"><i class="fas fa-pen"></i></a>
                                        <form action="{{ route('admin.guichets.destroy', $guichet) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-10 text-center text-slate-400">Aucun guichet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $guichets->links() }}</div>
        </div>
    </div>
</div>
@endsection