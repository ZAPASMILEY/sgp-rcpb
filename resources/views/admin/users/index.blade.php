@extends('layouts.app')

@section('title', 'Comptes utilisateurs | '.config('app.name', 'SGP-RCPB'))

@section('content')
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full space-y-6">

        {{-- Notification flash --}}
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

        {{-- En-tête --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Administration</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Comptes utilisateurs</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $users->count() }} compte(s){{ $search ? ' trouvé(s)' : ' enregistré(s)' }}.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="ent-btn ent-btn-primary">
                <i class="fas fa-plus mr-2"></i> Nouveau compte
            </a>
        </div>

        {{-- Barre de recherche --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-3">
            <div class="relative flex-1 max-w-md">
                <div class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-400">
                    <i class="fas fa-search text-sm"></i>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Nom, email, rôle, fonction..."
                       class="w-full rounded-2xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-semibold text-slate-700 placeholder-slate-300 shadow-sm focus:border-cyan-400 focus:outline-none focus:ring-2 focus:ring-cyan-100 transition">
            </div>
            <button type="submit" class="ent-btn ent-btn-soft text-xs py-2.5 px-4">
                Rechercher
            </button>
            @if($search)
                <a href="{{ route('admin.users.index') }}" class="ent-btn text-xs py-2.5 px-4 border border-slate-200 text-slate-500 hover:bg-slate-50">
                    <i class="fas fa-times mr-1"></i> Effacer
                </a>
            @endif
        </form>

        {{-- Tableau --}}
        <section class="admin-panel p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left">
                            <th class="px-4 py-3 font-semibold text-slate-600">Agent lié</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Email</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Rôle système</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">Manager (N+1)</th>
                            <th class="px-4 py-3 font-semibold text-slate-600">MDP forcé</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    @if ($user->agent)
                                        <span class="font-semibold text-slate-800">{{ $user->agent->prenom }} {{ $user->agent->nom }}</span>
                                        <span class="block text-xs text-slate-500">{{ $user->agent->role }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Sans agent lié</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full bg-cyan-100 px-2.5 py-0.5 text-xs font-semibold text-cyan-800">
                                        {{ \App\Http\Controllers\Admin\UserController::ROLES[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $user->manager?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($user->must_change_password)
                                        <span class="inline-flex items-center gap-1 text-xs text-amber-700 font-semibold">
                                            <i class="fas fa-exclamation-triangle"></i> Oui
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Non</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Activer / Désactiver --}}
                                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}"
                                              @if(!$user->is_active) onsubmit="return confirm('Activer ce compte ?')" @endif>
                                            @csrf @method('PATCH')
                                            @if ($user->is_active)
                                                <button type="submit" title="Désactiver ce compte"
                                                    class="ent-btn text-xs py-1 px-3 border border-emerald-300 text-emerald-700 hover:bg-red-50 hover:border-red-300 hover:text-red-700">
                                                    <i class="fas fa-toggle-on mr-1"></i> Actif
                                                </button>
                                            @else
                                                <button type="submit" title="Activer ce compte"
                                                    class="ent-btn text-xs py-1 px-3 border border-slate-300 text-slate-500 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700">
                                                    <i class="fas fa-toggle-off mr-1"></i> Inactif
                                                </button>
                                            @endif
                                        </form>
                                        <a href="{{ route('admin.users.edit', $user) }}" class="ent-btn ent-btn-soft text-xs py-1 px-3">
                                            <i class="fas fa-pen mr-1"></i> Modifier
                                        </a>
                                        <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" onsubmit="return confirm('Réinitialiser le mot de passe ?')">
                                            @csrf
                                            <button type="submit" class="ent-btn text-xs py-1 px-3 border border-amber-300 text-amber-700 hover:bg-amber-50">
                                                <i class="fas fa-key mr-1"></i> MDP
                                            </button>
                                        </form>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer ce compte ? L\'agent sera conservé.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ent-btn text-xs py-1 px-3 border border-red-300 text-red-700 hover:bg-red-50">
                                                    <i class="fas fa-trash mr-1"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400 italic">
                                    Aucun compte utilisateur enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>
@endsection
