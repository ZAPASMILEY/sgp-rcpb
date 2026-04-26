@extends('layouts.app')

@section('title', 'Modifier compte | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full">
        <section class="admin-panel p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Modification</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $user->name }}</h1>
                    @if ($user->agent)
                        <p class="mt-1 text-sm text-slate-500">{{ $user->agent->fonction }}</p>
                    @endif
                </div>
                <a href="{{ route('admin.users.index') }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-6 grid gap-5">
                @csrf
                @method('PUT')

                {{-- Agent lié (lecture seule) --}}
                @if ($user->agent)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Agent lié</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $user->agent->prenom }} {{ $user->agent->nom }}</p>
                        <p class="text-xs text-slate-500">{{ $user->agent->email }} — {{ $user->agent->fonction }}</p>
                    </div>
                @endif

                {{-- Email de connexion --}}
                <div class="space-y-2">
                    <label for="email" class="text-sm font-semibold text-slate-700">Email de connexion <span class="text-red-500">*</span></label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="ent-input">
                </div>

                {{-- Rôle --}}
                <div class="space-y-2">
                    <label for="role" class="text-sm font-semibold text-slate-700">Rôle système <span class="text-red-500">*</span></label>
                    <select id="role" name="role" required class="ent-select">
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Entité PCA (conditionnel) --}}
                <div id="pca-entite-block" class="space-y-2 {{ old('role', $user->role) === 'PCA' ? '' : 'hidden' }}">
                    <label for="pca_entite_id" class="text-sm font-semibold text-slate-700">
                        Entité faîtière <span class="text-red-500">*</span>
                    </label>
                    @if($user->role === 'PCA' && !$user->pca_entite_id)
                        <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs text-rose-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-rose-500"></i>
                            <span><strong>Ce compte PCA n'a pas d'entité associée</strong> — c'est pour ça que l'utilisateur ne peut pas se connecter. Sélectionnez une entité ci-dessous.</span>
                        </div>
                    @endif
                    <select id="pca_entite_id" name="pca_entite_id" class="ent-select">
                        <option value="">— Sélectionner l'entité faîtière —</option>
                        @foreach ($entites as $entite)
                            <option value="{{ $entite->id }}" @selected(old('pca_entite_id', $user->pca_entite_id) == $entite->id)>
                                {{ $entite->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('pca_entite_id')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Manager N+1 --}}
                <div class="space-y-2">
                    <label for="manager_id" class="text-sm font-semibold text-slate-700">Supérieur direct (N+1)</label>
                    <select id="manager_id" name="manager_id" class="ent-select">
                        <option value="">-- Aucun --</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected((string) old('manager_id', $user->manager_id) === (string) $manager->id)>
                                {{ $manager->name }}
                                ({{ \App\Http\Controllers\Admin\UserController::ROLES[$manager->role] ?? $manager->role }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Nouveau mot de passe (optionnel) --}}
                <fieldset class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-4">
                    <legend class="px-2 text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Changer le mot de passe</legend>
                    <p class="text-xs text-slate-500">Laisser vide pour ne pas modifier.</p>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="generatePassword()" class="ent-btn ent-btn-soft text-xs py-1.5 px-3">
                            <i class="fas fa-dice mr-1"></i> Générer
                        </button>
                        <span id="generated-pwd-display" class="font-mono text-sm text-emerald-700 hidden"></span>
                    </div>
                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-slate-700">Nouveau mot de passe</label>
                            <input id="password" name="password" type="password" class="ent-input" placeholder="Laisser vide pour ne pas changer" autocomplete="new-password">
                        </div>
                        <div class="space-y-2">
                            <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirmation</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="ent-input" placeholder="Retaper le nouveau mot de passe" autocomplete="new-password">
                        </div>
                    </div>
                </fieldset>

                <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                    Enregistrer les modifications
                </button>
            </form>
        </section>
    </div>
</main>

<script>
    // Show/hide champ entité PCA
    const roleSelect = document.getElementById('role');
    const pcaBlock   = document.getElementById('pca-entite-block');
    const pcaSelect  = document.getElementById('pca_entite_id');
    function togglePcaBlock() {
        const isPca = roleSelect.value === 'PCA';
        pcaBlock.classList.toggle('hidden', !isPca);
        if (pcaSelect) pcaSelect.required = isPca;
    }
    roleSelect.addEventListener('change', togglePcaBlock);
    togglePcaBlock();

    function generatePassword() {
        const chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$';
        let pwd = '';
        for (let i = 0; i < 12; i++) {
            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('password').value = pwd;
        document.getElementById('password_confirmation').value = pwd;
        const display = document.getElementById('generated-pwd-display');
        display.textContent = pwd;
        display.classList.remove('hidden');
    }
</script>
@endsection
