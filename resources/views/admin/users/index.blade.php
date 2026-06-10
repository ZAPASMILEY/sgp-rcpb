@extends('layouts.app')

@section('title', 'Comptes utilisateurs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<main class="admin-shell min-h-screen bg-[#f1f5f9] px-4 py-6 sm:px-6 lg:px-10">
<div class="w-full flex flex-col gap-5">

    {{-- ── FLASH ────────────────────────────────────────────────────────────── --}}
    @if (session('status'))
        <div id="flash-msg" class="fixed right-6 top-6 z-50 flex items-center gap-3 rounded-2xl border border-emerald-100 bg-white px-5 py-3.5 shadow-2xl shadow-emerald-100/60 transition-all duration-500">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <i class="fas fa-check text-xs"></i>
            </div>
            <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            <button onclick="dismissFlash()" class="ml-2 text-slate-300 hover:text-slate-500 transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <script>
            function dismissFlash() {
                var el = document.getElementById('flash-msg');
                if (el) { el.style.opacity = '0'; el.style.transform = 'translateX(20px)'; setTimeout(() => el.remove(), 500); }
            }
            setTimeout(dismissFlash, 2500);
        </script>
    @endif

    {{-- ── HEADER ───────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Administration</p>
            <h1 class="mt-0.5 text-xl font-black text-slate-900">Comptes utilisateurs</h1>
        </div>
        <a href="{{ route('admin.users.create') }}" class="ent-btn ent-btn-primary text-xs py-1.5 px-4">
            <i class="fas fa-plus mr-1.5"></i> Nouveau compte
        </a>
    </div>

    {{-- ── STATS ────────────────────────────────────────────────────────────── --}}
    @php
        $totalUsers  = $users->count();
        $activeCount = \App\Models\User::where('is_active', true)->count();
        $mdpCount    = \App\Models\User::where('must_change_password', true)->count();
    @endphp
    <div class="grid grid-cols-3 gap-3">
        {{-- Total --}}
        <div class="flex items-center gap-4 rounded-2xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-white p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white shadow shadow-cyan-100">
                <i class="fas fa-users text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-cyan-600">{{ $totalUsers }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $search ? 'Trouvés' : 'Total comptes' }}</p>
            </div>
        </div>
        {{-- Actifs --}}
        <div class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow shadow-emerald-100">
                <i class="fas fa-toggle-on text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-emerald-600">{{ $activeCount }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Comptes actifs</p>
            </div>
        </div>
        {{-- MDP forcé --}}
        <div class="flex items-center gap-4 rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow shadow-amber-100">
                <i class="fas fa-key text-sm"></i>
            </div>
            <div>
                <p class="text-2xl font-black text-amber-600">{{ $mdpCount }}</p>
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">MDP à changer</p>
            </div>
        </div>
    </div>

    {{-- ── RECHERCHE ────────────────────────────────────────────────────────── --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2">
        <div class="relative flex-1 max-w-sm">
            <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                <i class="fas fa-search text-xs"></i>
            </div>
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Nom, email, rôle…"
                   class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-4 text-xs font-semibold text-slate-700 placeholder-slate-300 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100 transition">
        </div>
        <button type="submit" class="ent-btn ent-btn-soft text-xs py-2 px-4">Rechercher</button>
        @if($search)
            <a href="{{ route('admin.users.index') }}"
               class="flex h-8 w-8 items-center justify-center rounded-xl border border-slate-200 text-slate-400 hover:bg-slate-50 hover:text-rose-500 transition">
                <i class="fas fa-times text-xs"></i>
            </a>
        @endif
    </form>

    {{-- ── TABLEAU ──────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">

        @if($users->isEmpty())
            <div class="flex flex-col items-center py-12 text-center">
                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-300">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <p class="text-sm italic text-slate-400">Aucun compte utilisateur trouvé.</p>
            </div>
        @else
            <div class="overflow-x-auto overflow-y-auto" style="max-height:520px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="bg-gradient-to-r from-slate-700 to-slate-800">
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300 w-8">#</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Agent lié</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Email</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Rôle système</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Entité</th>
                            <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-300">Statut</th>
                            <th class="px-4 py-3 text-right text-[10px] font-black uppercase tracking-wider text-slate-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($users as $user)
                        @php
                            $a = $user->agent;
                            $entiteNom   = null;
                            $entiteType  = null;
                            $entiteColor = 'bg-slate-100 text-slate-500';
                            if ($a) {
                                $dt    = $a->delegation_technique_id ? $a->delegationTechnique : null;
                                $dtNom = $dt ? trim(($dt->ville ?? '') . ($dt->region ? ' ('.$dt->region.')' : '')) : null;
                                if ($a->service_id)                 { $entiteNom = $a->service?->nom;   $entiteType = 'Service';   $entiteColor = 'bg-violet-100 text-violet-700'; }
                                elseif ($a->guichet_id)             { $entiteNom = $a->guichet?->nom;   $entiteType = 'Guichet';   $entiteColor = 'bg-pink-100 text-pink-700'; }
                                elseif ($a->agence_id)              { $entiteNom = $a->agence?->nom;    $entiteType = 'Agence';    $entiteColor = 'bg-cyan-100 text-cyan-700'; }
                                elseif ($a->caisse_id)              { $entiteNom = $a->caisse?->nom;    $entiteType = 'Caisse';    $entiteColor = 'bg-emerald-100 text-emerald-700'; }
                                elseif ($a->delegation_technique_id){ $entiteNom = $dtNom;              $entiteType = 'DT';        $entiteColor = 'bg-orange-100 text-orange-700'; }
                                elseif ($a->direction_id)           { $entiteNom = $a->direction?->nom; $entiteType = 'Direction'; $entiteColor = 'bg-blue-100 text-blue-700'; }
                                elseif ($a->entite_id)              { $entiteNom = $a->entite?->nom;    $entiteType = 'Faîtière';  $entiteColor = 'bg-green-100 text-green-700'; }
                            }

                            // Couleur de l'avatar selon le rôle
                            $avatarClass = match(true) {
                                in_array($user->role, ['Admin'])                        => 'from-rose-500 to-red-600',
                                in_array($user->role, ['PCA', 'DG'])                   => 'from-purple-500 to-indigo-600',
                                in_array($user->role, ['DGA'])                         => 'from-indigo-500 to-blue-600',
                                in_array($user->role, ['Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse']) => 'from-blue-500 to-cyan-600',
                                in_array($user->role, ['Chef_Agence', 'Chef_Guichet', 'Chef_Service'])                   => 'from-teal-500 to-emerald-600',
                                in_array($user->role, ['RH'])                          => 'from-fuchsia-500 to-pink-600',
                                default                                                => 'from-slate-500 to-slate-600',
                            };

                            // Couleur du badge rôle
                            $roleColor = match(true) {
                                in_array($user->role, ['Admin'])                        => 'bg-rose-100 text-rose-700',
                                in_array($user->role, ['PCA', 'DG'])                   => 'bg-purple-100 text-purple-700',
                                in_array($user->role, ['DGA'])                         => 'bg-indigo-100 text-indigo-700',
                                in_array($user->role, ['Directeur_Direction', 'Directeur_Technique', 'Directeur_Caisse']) => 'bg-blue-100 text-blue-700',
                                in_array($user->role, ['Chef_Agence', 'Chef_Guichet', 'Chef_Service'])                   => 'bg-teal-100 text-teal-700',
                                in_array($user->role, ['RH'])                          => 'bg-fuchsia-100 text-fuchsia-700',
                                default                                                => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition group">
                            {{-- # --}}
                            <td class="px-4 py-3.5 text-xs text-slate-300 font-medium">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Agent lié --}}
                            <td class="px-4 py-3.5">
                                @if ($a)
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $avatarClass }} text-xs font-black text-white shadow-sm {{ !$user->is_active ? 'opacity-50 grayscale' : '' }}">
                                            {{ strtoupper(substr($a->prenom ?? '', 0, 1) . substr($a->nom ?? '', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 text-sm leading-tight">{{ trim($a->prenom . ' ' . $a->nom) }}</p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $a->role ?: '—' }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                            <i class="fas fa-user text-xs"></i>
                                        </div>
                                        <span class="text-xs italic text-slate-400">Sans agent lié</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Email --}}
                            <td class="px-4 py-3.5">
                                <span class="text-xs text-slate-500 font-medium">{{ $user->email }}</span>
                            </td>

                            {{-- Rôle système --}}
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center rounded-lg {{ $roleColor }} px-2.5 py-1 text-[10px] font-bold">
                                    {{ \App\Http\Controllers\Admin\UserController::ROLES[$user->role] ?? $user->role }}
                                </span>
                            </td>

                            {{-- Entité --}}
                            <td class="px-4 py-3.5">
                                @if ($entiteNom)
                                    <span class="inline-flex items-center rounded-lg {{ $entiteColor }} px-2 py-0.5 text-[10px] font-bold">
                                        {{ $entiteType }}
                                    </span>
                                    <p class="mt-0.5 text-[11px] text-slate-500 font-medium leading-tight max-w-[140px] truncate">{{ $entiteNom }}</p>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>

                            {{-- Statut --}}
                            <td class="px-4 py-3.5">
                                <div class="flex flex-col gap-1">
                                    @if ($user->is_active)
                                        <span class="inline-flex w-fit items-center gap-1.5 rounded-lg bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Actif
                                        </span>
                                    @else
                                        <span class="inline-flex w-fit items-center gap-1.5 rounded-lg bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Inactif
                                        </span>
                                    @endif
                                    @if ($user->must_change_password)
                                        <span class="inline-flex w-fit items-center gap-1 rounded-lg bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700">
                                            <i class="fas fa-key text-[8px]"></i> MDP forcé
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Activer / Désactiver --}}
                                    <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}"
                                          @if(!$user->is_active) onsubmit="return confirm('Activer ce compte ?')" @endif>
                                        @csrf @method('PATCH')
                                        @if ($user->is_active)
                                            <button type="submit" title="Désactiver"
                                                class="inline-flex items-center gap-1 rounded-lg border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-700 hover:bg-red-50 hover:border-red-200 hover:text-red-600 transition">
                                                <i class="fas fa-toggle-on"></i> Actif
                                            </button>
                                        @else
                                            <button type="submit" title="Activer"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-bold text-slate-500 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 transition">
                                                <i class="fas fa-toggle-off"></i> Inactif
                                            </button>
                                        @endif
                                    </form>

                                    {{-- Modifier --}}
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold text-slate-500 shadow-sm hover:border-cyan-200 hover:text-cyan-600 transition">
                                        <i class="fas fa-pen text-[9px]"></i> Modifier
                                    </a>

                                    {{-- Reset MDP --}}
                                    <form method="POST" action="{{ route('admin.users.reset-password', $user) }}"
                                          onsubmit="return confirm('Réinitialiser le mot de passe ?')">
                                        @csrf
                                        <button type="submit" title="Réinitialiser MDP"
                                            class="inline-flex items-center justify-center h-[26px] w-[26px] rounded-lg border border-amber-200 bg-amber-50 text-[10px] font-bold text-amber-600 hover:bg-amber-100 transition">
                                            <i class="fas fa-key"></i>
                                        </button>
                                    </form>

                                    {{-- Supprimer --}}
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('Supprimer ce compte ? L\'agent sera conservé.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Supprimer"
                                                class="inline-flex items-center justify-center h-[26px] w-[26px] rounded-lg border border-red-200 bg-red-50 text-[10px] font-bold text-red-500 hover:bg-red-100 transition">
                                                <i class="fas fa-trash text-[9px]"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">
                {{ $users->count() }} compte{{ $users->count() > 1 ? 's' : '' }} affiché{{ $users->count() > 1 ? 's' : '' }}
                @if($search) · <span class="text-blue-500 font-semibold">filtre actif</span>@endif
            </div>
        @endif

    </div>

</div>
</main>
@endsection
