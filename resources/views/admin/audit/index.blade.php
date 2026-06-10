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
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 p-5 text-white shadow-lg">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 mb-3">
                <i class="fas fa-list-check text-sm"></i>
            </div>
            <p class="text-3xl font-black">{{ number_format($stats['total']) }}</p>
            <p class="mt-0.5 text-xs font-semibold text-slate-300">Total entrées</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 p-5 text-white shadow-lg shadow-blue-200">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 mb-3">
                <i class="fas fa-calendar-day text-sm"></i>
            </div>
            <p class="text-3xl font-black">{{ number_format($stats['today']) }}</p>
            <p class="mt-0.5 text-xs font-semibold text-blue-100">Aujourd'hui</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-700 p-5 text-white shadow-lg shadow-violet-200">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 mb-3">
                <i class="fas fa-arrow-right-arrow-left text-sm"></i>
            </div>
            <p class="text-3xl font-black">{{ number_format($stats['statut_change']) }}</p>
            <p class="mt-0.5 text-xs font-semibold text-violet-100">Changements statut</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 p-5 text-white shadow-lg shadow-rose-200">
            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-white/10"></div>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/20 mb-3">
                <i class="fas fa-trash text-sm"></i>
            </div>
            <p class="text-3xl font-black">{{ number_format($stats['deleted']) }}</p>
            <p class="mt-0.5 text-xs font-semibold text-rose-100">Suppressions</p>
        </div>
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
        <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 border-b border-slate-200 bg-slate-50 text-xs font-black uppercase tracking-wider text-slate-400">
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
                    @php
                        $rowBg = match($log->action) {
                            'created'       => 'hover:bg-emerald-50/40',
                            'deleted'       => 'hover:bg-rose-50/40',
                            'statut_change' => 'hover:bg-violet-50/40',
                            default         => 'hover:bg-amber-50/40',
                        };
                        $leftBorder = match($log->action) {
                            'created'       => 'border-l-4 border-l-emerald-400',
                            'deleted'       => 'border-l-4 border-l-rose-400',
                            'statut_change' => 'border-l-4 border-l-violet-400',
                            default         => 'border-l-4 border-l-amber-400',
                        };
                        $modelColor = match(true) {
                            str_ends_with($log->auditable_type, 'Agent')      => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
                            str_ends_with($log->auditable_type, 'User')       => ['bg' => 'bg-cyan-100',   'text' => 'text-cyan-700'],
                            str_ends_with($log->auditable_type, 'Evaluation') => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
                            str_ends_with($log->auditable_type, 'Formation')  => ['bg' => 'bg-green-100',  'text' => 'text-green-700'],
                            str_ends_with($log->auditable_type, 'Service')    => ['bg' => 'bg-emerald-100','text' => 'text-emerald-700'],
                            str_ends_with($log->auditable_type, 'Direction')  => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
                            str_ends_with($log->auditable_type, 'Agence')     => ['bg' => 'bg-pink-100',   'text' => 'text-pink-700'],
                            str_ends_with($log->auditable_type, 'Caisse')     => ['bg' => 'bg-teal-100',   'text' => 'text-teal-700'],
                            default                                            => ['bg' => 'bg-slate-100',  'text' => 'text-slate-600'],
                        };
                        $initiale = strtoupper(substr($log->user_name ?? 'A', 0, 1));
                        $avatarColor = match($initiale) {
                            'A' => 'bg-cyan-500', 'B' => 'bg-blue-500', 'C' => 'bg-violet-500',
                            'D' => 'bg-indigo-500', 'E' => 'bg-emerald-500', 'F' => 'bg-pink-500',
                            'G' => 'bg-green-500', 'H' => 'bg-amber-500', 'I' => 'bg-rose-500',
                            default => 'bg-slate-500',
                        };
                    @endphp
                    <tr class="{{ $rowBg }} {{ $leftBorder }} transition-colors">
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="block text-xs font-black text-slate-700">{{ $log->created_at->format('d/m/Y') }}</span>
                            <span class="text-[11px] text-slate-400 font-mono">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $avatarColor }} text-[10px] font-black text-white">
                                    {{ $initiale }}
                                </div>
                                <span class="font-semibold text-slate-800 text-xs">{{ $log->user_name ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="rounded-lg {{ $modelColor['bg'] }} {{ $modelColor['text'] }} px-2.5 py-1 text-[11px] font-bold">
                                {{ $log->auditableLabel() }} #{{ $log->auditable_id }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-black {{ $log->actionBadgeClass() }}">
                                {{ $log->actionLabel() }}
                            </span>
                        </td>
                        <td class="max-w-xs px-4 py-3 text-xs text-slate-600">
                            {{ $log->description }}
                        </td>
                        <td class="px-4 py-3">
                            @if($log->old_values || $log->new_values)
                                <button type="button"
                                    onclick="toggleDiff({{ $log->id }})"
                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-600 hover:bg-emerald-100 transition">
                                    <i class="fas fa-code-compare text-[9px]"></i> Voir diff
                                </button>
                            @else
                                <span class="text-[11px] text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-mono text-slate-400">{{ $log->ip_address ?? '—' }}</span>
                        </td>
                    </tr>
                    {{-- Ligne diff --}}
                    @if($log->old_values || $log->new_values)
                    <tr id="diff-{{ $log->id }}" class="hidden bg-gradient-to-r from-rose-50/60 via-white to-emerald-50/60">
                        <td colspan="7" class="px-6 py-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                @if($log->old_values)
                                <div>
                                    <p class="mb-2 flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-rose-400">
                                        <i class="fas fa-circle-minus"></i> Avant
                                    </p>
                                    <div class="space-y-1 rounded-xl border border-rose-100 bg-rose-50/60 p-3">
                                        @foreach($log->old_values as $field => $val)
                                        <div class="flex items-start gap-2 rounded-lg bg-white/80 px-3 py-1.5 shadow-sm">
                                            <span class="min-w-[120px] text-[11px] font-bold text-rose-500">{{ $field }}</span>
                                            <span class="text-[11px] text-rose-700 break-all">{{ $val ?? 'null' }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @if($log->new_values)
                                <div>
                                    <p class="mb-2 flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-500">
                                        <i class="fas fa-circle-plus"></i> Après
                                    </p>
                                    <div class="space-y-1 rounded-xl border border-emerald-100 bg-emerald-50/60 p-3">
                                        @foreach($log->new_values as $field => $val)
                                        <div class="flex items-start gap-2 rounded-lg bg-white/80 px-3 py-1.5 shadow-sm">
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

        <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">
            {{ $logs->count() }} résultat(s)
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
