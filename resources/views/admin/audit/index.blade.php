@extends('layouts.app')

@section('title', 'Historique d\'audit | '.config('app.name', 'SGP-RCPB'))

@section('content')
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">

    {{-- En-tête --}}
    <div class="mb-6">
        <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Administration</p>
        <h1 class="mt-1 text-2xl font-black text-slate-900">Historique d'audit</h1>
        <p class="mt-1 text-sm text-slate-500">Toutes les modifications sensibles enregistrées automatiquement.</p>
    </div>

    {{-- KPIs --}}
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        @foreach([
            ['label' => 'Total entrées',      'value' => $stats['total'],         'icon' => 'fa-list-check',      'color' => 'text-slate-600',   'bg' => 'bg-slate-50'],
            ['label' => 'Aujourd\'hui',        'value' => $stats['today'],         'icon' => 'fa-calendar-day',    'color' => 'text-blue-600',    'bg' => 'bg-blue-50'],
            ['label' => 'Changements statut',  'value' => $stats['statut_change'], 'icon' => 'fa-arrow-right-arrow-left', 'color' => 'text-violet-600', 'bg' => 'bg-violet-50'],
            ['label' => 'Suppressions',        'value' => $stats['deleted'],       'icon' => 'fa-trash',           'color' => 'text-rose-600',    'bg' => 'bg-rose-50'],
        ] as $kpi)
        <div class="flex items-center gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $kpi['bg'] }}">
                <i class="fas {{ $kpi['icon'] }} {{ $kpi['color'] }}"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-slate-900">{{ number_format($kpi['value']) }}</p>
                <p class="text-xs font-semibold text-slate-400">{{ $kpi['label'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filtres --}}
    <form method="GET" class="mb-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <input type="text" name="search" value="{{ $filters['search'] }}"
                   placeholder="Rechercher dans la description…"
                   class="col-span-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-100">

            <select name="action" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none">
                <option value="">Toutes les actions</option>
                @foreach(['created' => 'Créé', 'updated' => 'Modifié', 'deleted' => 'Supprimé', 'statut_change' => 'Statut changé'] as $val => $lbl)
                    <option value="{{ $val }}" {{ $filters['action'] === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>

            <select name="type" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none">
                <option value="">Tous les modèles</option>
                @foreach(['Evaluation' => 'Évaluation', 'User' => 'Compte', 'Agent' => 'Agent'] as $val => $lbl)
                    <option value="{{ $val }}" {{ $filters['type'] === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>

            <select name="user_id" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none">
                <option value="">Tous les utilisateurs</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ $filters['userId'] === $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <input type="date" name="date_from" value="{{ $filters['dateFrom'] }}"
                       class="flex-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none">
                <input type="date" name="date_to" value="{{ $filters['dateTo'] }}"
                       class="flex-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-400 focus:outline-none">
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                <i class="fas fa-filter mr-1.5 text-xs"></i>Filtrer
            </button>
            <a href="{{ route('admin.audit.index') }}" class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Réinitialiser
            </a>
        </div>
    </form>

    {{-- Tableau --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        @if($logs->isEmpty())
            <div class="px-6 py-16 text-center">
                <i class="fas fa-shield-halved text-3xl text-slate-200"></i>
                <p class="mt-3 text-sm font-semibold text-slate-400">Aucune entrée d'audit pour ces critères.</p>
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-100 bg-slate-50 text-xs font-black uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Utilisateur</th>
                        <th class="px-4 py-3 text-left">Modèle</th>
                        <th class="px-4 py-3 text-left">Action</th>
                        <th class="px-4 py-3 text-left">Description</th>
                        <th class="px-4 py-3 text-left">Détails</th>
                        <th class="px-4 py-3 text-left">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($logs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="whitespace-nowrap px-4 py-3 text-[11px] text-slate-400">
                            <span class="block font-semibold text-slate-600">{{ $log->created_at->format('d/m/Y') }}</span>
                            {{ $log->created_at->format('H:i:s') }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-800">{{ $log->user_name ?? '—' }}</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500">
                                {{ $log->auditableLabel() }} #{{ $log->auditable_id }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $log->actionBadgeClass() }}">
                                {{ $log->actionLabel() }}
                            </span>
                        </td>
                        <td class="max-w-xs px-4 py-3 text-slate-600">
                            {{ $log->description }}
                        </td>
                        <td class="px-4 py-3">
                            @if($log->old_values || $log->new_values)
                                <button type="button"
                                    onclick="toggleDiff({{ $log->id }})"
                                    class="text-[11px] font-bold text-emerald-600 hover:underline">
                                    Voir diff
                                </button>
                            @else
                                <span class="text-[11px] text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-[11px] font-mono text-slate-300">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                    {{-- Ligne diff (masquée par défaut) --}}
                    @if($log->old_values || $log->new_values)
                    <tr id="diff-{{ $log->id }}" class="hidden bg-slate-50">
                        <td colspan="7" class="px-6 py-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                @if($log->old_values)
                                <div>
                                    <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-rose-400">Avant</p>
                                    <div class="space-y-1">
                                        @foreach($log->old_values as $field => $val)
                                        <div class="flex items-start gap-2 rounded-lg bg-rose-50 px-3 py-1.5">
                                            <span class="min-w-[120px] text-[11px] font-bold text-rose-500">{{ $field }}</span>
                                            <span class="text-[11px] text-rose-700 break-all">{{ $val ?? 'null' }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if($log->new_values)
                                <div>
                                    <p class="mb-2 text-[10px] font-black uppercase tracking-widest text-emerald-400">Après</p>
                                    <div class="space-y-1">
                                        @foreach($log->new_values as $field => $val)
                                        <div class="flex items-start gap-2 rounded-lg bg-emerald-50 px-3 py-1.5">
                                            <span class="min-w-[120px] text-[11px] font-bold text-emerald-600">{{ $field }}</span>
                                            <span class="text-[11px] text-emerald-800 break-all">{{ $val ?? 'null' }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-6 py-4">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</main>
@endsection

@push('scripts')
<script>
function toggleDiff(id) {
    const row = document.getElementById('diff-' + id);
    if (row) row.classList.toggle('hidden');
}
</script>
@endpush
