@extends($layout)

@section('title', 'Journal d\'activité | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    <div class="relative overflow-hidden px-6 py-8 lg:px-10"
         style="background:linear-gradient(135deg,#0c4a6e 0%,#0369a1 50%,#0284c7 100%)">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex items-center gap-4">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white shadow ring-1 ring-white/20">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-sky-200">Suivi · RCPB</p>
                <h1 class="mt-0.5 text-2xl font-black text-white">Journal d'activité</h1>
                <p class="mt-0.5 text-sm text-sky-100/75">Historique des actions effectuées dans le système</p>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">

        {{-- Filtres --}}
        <form method="GET" action="{{ route('gerer.activites.index') }}"
              class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">

            <div class="flex flex-col gap-1 flex-1 min-w-[200px]">
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Recherche</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Action, description, utilisateur…"
                           class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-8 pr-3 py-2 text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:border-sky-400 focus:ring-0">
                </div>
            </div>

            <div class="flex flex-col gap-1 min-w-[150px]">
                <label class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 focus:border-sky-400 focus:ring-0">
            </div>

            <button type="submit" class="rounded-lg px-5 py-2 text-sm font-bold text-white transition"
                    style="background:#0284c7" onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='#0284c7'">
                <i class="fas fa-filter mr-1"></i> Filtrer
            </button>
            <a href="{{ route('gerer.activites.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Réinitialiser
            </a>
        </form>

        {{-- Tableau --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            @if($activites->isEmpty())
                <div class="px-8 py-16 text-center">
                    <i class="fas fa-history text-slate-200 text-5xl mb-4 block"></i>
                    <p class="text-sm font-semibold text-slate-400">Aucune activité trouvée.</p>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Date</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Action</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($activites as $activite)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $activite->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-700">{{ $activite->user?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full bg-sky-50 px-2.5 py-0.5 text-[10px] font-bold text-sky-700">{{ $activite->action }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 max-w-[320px] truncate" title="{{ $activite->description }}">{{ $activite->description ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 flex items-center justify-between">
                <span class="text-xs text-slate-400">{{ $activites->total() }} entrée{{ $activites->total() > 1 ? 's' : '' }}</span>
                {{ $activites->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
