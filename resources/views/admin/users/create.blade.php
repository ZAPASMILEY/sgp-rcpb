@extends('layouts.app')

@section('title', 'Nouveau compte | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
</div>
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
    <div class="w-full">
        <section class="admin-panel ent-window p-6 sm:p-8">
            <div class="ent-window__bar" aria-hidden="true">
                <span class="ent-window__dot ent-window__dot--danger"></span>
                <span class="ent-window__dot ent-window__dot--warn"></span>
                <span class="ent-window__dot ent-window__dot--ok"></span>
                <span class="ent-window__label">Création de compte</span>
            </div>

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Creation</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nouveau compte utilisateur</h1>
                    <p class="mt-2 text-sm text-slate-600">
                        @if($preselectedAgent ?? null)
                            Création du compte de connexion pour <strong>{{ $preselectedAgent->prenom }} {{ $preselectedAgent->nom }}</strong>.
                        @else
                            Associez un agent existant à un compte de connexion. Le rôle est suggéré automatiquement selon sa fonction.
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="ent-btn ent-btn-soft">Retour</a>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 grid gap-5">
                @csrf

                @if($preselectedAgent ?? null)
                    {{-- ── Agent pré-sélectionné (depuis la fiche agent) ── --}}
                    <input type="hidden" name="agent_id" value="{{ $preselectedAgent->id }}">
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-200 text-sm font-black text-emerald-800">
                            {{ strtoupper(substr($preselectedAgent->prenom, 0, 1).substr($preselectedAgent->nom, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-black text-slate-900">{{ $preselectedAgent->prenom }} {{ $preselectedAgent->nom }}</p>
                            <p class="text-xs text-slate-500">{{ $preselectedAgent->fonction ?? 'Fonction non renseignée' }}</p>
                            @if($preselectedAgent->email)
                                <p class="text-xs text-slate-400">{{ $preselectedAgent->email }}</p>
                            @endif
                        </div>
                        <span class="ml-auto inline-flex items-center gap-1 rounded-full bg-emerald-200 px-2.5 py-1 text-xs font-bold text-emerald-800">
                            <i class="fas fa-check text-[8px]"></i> Agent sélectionné
                        </span>
                    </div>
                @else
                    {{-- ── Sélection de l'agent ── --}}
                    <div class="space-y-2">
                        <label for="agent_id" class="text-sm font-semibold text-slate-700">
                            Agent <span class="text-red-500">*</span>
                        </label>
                        <select id="agent_id" name="agent_id" required class="ent-select">
                            <option value="">Sélectionner un agent</option>
                            @foreach ($agents as $agent)
                                <option
                                    value="{{ $agent->id }}"
                                    data-email="{{ $agent->email }}"
                                    data-fonction="{{ $agent->fonction }}"
                                    @selected((string) old('agent_id') === (string) $agent->id)
                                >
                                    {{ $agent->prenom }} {{ $agent->nom }}
                                    @if ($agent->fonction) — {{ $agent->fonction }} @endif
                                    @if ($agent->email) ({{ $agent->email }}) @endif
                                </option>
                            @endforeach
                        </select>
                        @if ($agents->isEmpty())
                            <p class="text-xs text-amber-600 font-semibold">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Tous les agents ont déjà un compte. Créez d'abord un nouvel agent.
                            </p>
                        @endif
                    </div>

                    {{-- ── Aperçu de l'agent sélectionné ── --}}
                    <div id="agent-preview" class="hidden rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-blue-500">Agent sélectionné</p>
                        <p id="preview-nom" class="mt-1 font-bold text-slate-800"></p>
                        <p id="preview-fonction" class="text-xs text-slate-500"></p>
                    </div>
                @endif

                {{-- ── Email de connexion ── --}}
                <div class="space-y-2">
                    <label for="email" class="text-sm font-semibold text-slate-700">
                        Email de connexion <span class="text-red-500">*</span>
                    </label>
                    <input id="email" name="email" type="email"
                           value="{{ old('email', ($preselectedAgent ?? null)?->email ?? '') }}"
                           required class="ent-input" placeholder="connexion@rcpb.bf">
                    <p class="text-xs text-slate-500">Pré-rempli depuis l'email professionnel de l'agent — modifiable.</p>
                </div>

                {{-- ── Rôle système ── --}}
                @php
                    $preselectedRole = old('role', isset($preselectedAgent)
                        ? (\App\Http\Controllers\Admin\UserController::FONCTION_TO_ROLE[$preselectedAgent->fonction] ?? '')
                        : '');
                @endphp
                <div class="space-y-2">
                    <label for="role" class="text-sm font-semibold text-slate-700">
                        Rôle système <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select id="role" name="role" required class="ent-select pr-10">
                            <option value="">Sélectionner un rôle</option>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" @selected($preselectedRole === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @if($preselectedRole)
                            <span class="absolute right-10 top-1/2 -translate-y-1/2 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                <i class="fas fa-magic text-[8px]"></i> Auto
                            </span>
                        @else
                            <span id="role-auto-badge" class="hidden absolute right-10 top-1/2 -translate-y-1/2 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                <i class="fas fa-magic text-[8px]"></i> Auto
                            </span>
                        @endif
                    </div>
                    @if($preselectedRole)
                        <p class="text-xs text-emerald-600 font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>
                            Rôle suggéré automatiquement selon la fonction de l'agent. Modifiable si besoin.
                        </p>
                    @else
                        <p id="role-hint" class="hidden text-xs text-emerald-600 font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>
                            Rôle suggéré automatiquement selon la fonction de l'agent. Modifiable si besoin.
                        </p>
                    @endif
                </div>

                {{-- ── Entité faîtière (PCA, Conseillers_Dg, Secretaire_assistante) ── --}}
                @php
                    $rolesAvecEntite = ['PCA', 'Conseillers_Dg', 'Secretaire_assistante'];
                    $showEntiteBlock = in_array($preselectedRole, $rolesAvecEntite) || in_array(old('role'), $rolesAvecEntite);
                @endphp
                <div id="entite-block" class="space-y-2 {{ $showEntiteBlock ? '' : 'hidden' }}">
                    <label for="entite_id" class="text-sm font-semibold text-slate-700">
                        Entité faîtière <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-start gap-2 rounded-xl border border-blue-100 bg-blue-50 px-4 py-2.5 text-xs text-blue-700 mb-2">
                        <i class="fas fa-info-circle mt-0.5 shrink-0 text-blue-500"></i>
                        <span>Ce rôle est rattaché à la Direction Générale de la faîtière. Sélectionnez l'entité correspondante.</span>
                    </div>
                    <select id="entite_id" name="entite_id" class="ent-select" {{ $showEntiteBlock ? 'required' : '' }}>
                        <option value="">— Sélectionner l'entité faîtière —</option>
                        @foreach ($entites as $entite)
                            <option value="{{ $entite->id }}" @selected(old('entite_id') == $entite->id)>
                                {{ $entite->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('entite_id')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ── Manager N+1 ── --}}
                <div class="space-y-2">
                    <label for="manager_id" class="text-sm font-semibold text-slate-700">Supérieur direct (N+1)</label>
                    <select id="manager_id" name="manager_id" class="ent-select">
                        <option value="">-- Aucun --</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected((string) old('manager_id') === (string) $manager->id)>
                                {{ $manager->name }}
                                ({{ \App\Http\Controllers\Admin\UserController::ROLES[$manager->role] ?? $manager->role }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500">Définit la chaîne de validation des évaluations et objectifs.</p>
                </div>

                {{-- ── Mot de passe ── --}}
                <fieldset class="rounded-2xl border border-slate-200 bg-slate-50 p-4 space-y-4">
                    <legend class="px-2 text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Mot de passe initial</legend>

                    <div class="flex items-center gap-3">
                        <button type="button" onclick="generatePassword()"
                                class="ent-btn ent-btn-soft text-xs py-1.5 px-3">
                            <i class="fas fa-dice mr-1"></i> Générer automatiquement
                        </button>
                        <span id="generated-pwd-display" class="font-mono text-sm font-bold text-emerald-700 hidden"></span>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="password" class="text-sm font-semibold text-slate-700">Mot de passe <span class="text-red-500">*</span></label>
                            <input id="password" name="password" type="password" required class="ent-input" placeholder="Min. 8 caractères" autocomplete="new-password">
                        </div>
                        <div class="space-y-2">
                            <label for="password_confirmation" class="text-sm font-semibold text-slate-700">Confirmation <span class="text-red-500">*</span></label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required class="ent-input" placeholder="Retaper le mot de passe" autocomplete="new-password">
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">L'utilisateur sera invité à changer son mot de passe à la première connexion.</p>
                </fieldset>

                <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                    Créer le compte
                </button>
            </form>
        </section>
    </div>
</main>

<script>
    const rolesAvecEntite = ['PCA', 'Conseillers_Dg', 'Secretaire_assistante'];
    const fonctionToRole  = @json($fonctionToRole);

    const roleSelect  = document.getElementById('role');
    const entiteBlock = document.getElementById('entite-block');
    const entiteSelect = document.getElementById('entite_id');

    function toggleEntiteBlock() {
        const needs = rolesAvecEntite.includes(roleSelect.value);
        entiteBlock.classList.toggle('hidden', !needs);
        if (entiteSelect) entiteSelect.required = needs;
    }

    roleSelect.addEventListener('change', function () {
        // Masquer le badge auto si l'utilisateur change manuellement
        const badge = document.getElementById('role-auto-badge');
        const hint  = document.getElementById('role-hint');
        if (badge) badge.classList.add('hidden');
        if (hint)  hint.classList.add('hidden');
        toggleEntiteBlock();
    });

    toggleEntiteBlock();

    @if(!($preselectedAgent ?? null))
    // Logique de sélection dynamique d'agent (uniquement si pas de pré-sélection)
    const agentSelect  = document.getElementById('agent_id');
    const emailField   = document.getElementById('email');
    const preview      = document.getElementById('agent-preview');
    const previewNom   = document.getElementById('preview-nom');
    const previewFonc  = document.getElementById('preview-fonction');
    const roleBadge    = document.getElementById('role-auto-badge');
    const roleHint     = document.getElementById('role-hint');

    if (agentSelect) {
        agentSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];

            if (!opt || !opt.value) {
                if (preview) preview.classList.add('hidden');
                if (roleBadge) roleBadge.classList.add('hidden');
                if (roleHint) roleHint.classList.add('hidden');
                return;
            }

            const email    = opt.dataset.email    || '';
            const fonction = opt.dataset.fonction  || '';
            const nom      = opt.text.split('—')[0].trim();

            if (preview) {
                previewNom.textContent  = nom;
                previewFonc.textContent = fonction || 'Fonction non renseignée';
                preview.classList.remove('hidden');
            }

            if (!emailField.value && email) emailField.value = email;

            const roleValue = fonctionToRole[fonction] || '';
            if (roleValue) {
                roleSelect.value = roleValue;
                if (roleBadge) roleBadge.classList.remove('hidden');
                if (roleHint)  roleHint.classList.remove('hidden');
            } else {
                if (roleBadge) roleBadge.classList.add('hidden');
                if (roleHint)  roleHint.classList.add('hidden');
            }

            toggleEntiteBlock();
        });

        // Restaurer l'état après erreur de validation
        if (agentSelect.value) agentSelect.dispatchEvent(new Event('change'));
    }
    @endif

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
