@extends('layouts.app')

@section('title', $agent->prenom.' '.$agent->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.agents.index') }}" class="font-semibold text-cyan-600 hover:text-cyan-800">Agents</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-slate-500">{{ $agent->prenom }} {{ $agent->nom }}</span>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Carte principale --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Fiche agent</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">
                        {{ $agent->prenom }} {{ $agent->nom }}
                    </h1>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $agent->role }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.agents.edit', $agent) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-pen text-xs text-cyan-300"></i> Modifier
                    </a>
                    <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}"
                          onsubmit="return confirm('Supprimer définitivement cet agent ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50">
                            <i class="fas fa-trash text-xs"></i> Supprimer
                        </button>
                    </form>
                    <a href="{{ url()->previous() }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        Retour
                    </a>
                </div>
            </div>

            {{-- Corps --}}
            <div class="mt-8 grid gap-6 lg:grid-cols-[200px_1fr]">

                {{-- Photo --}}
                <div class="flex flex-col items-center gap-4">
                    @if ($agent->photo_path)
                        <img src="{{ Storage::url($agent->photo_path) }}"
                             alt="Photo de {{ $agent->prenom }} {{ $agent->nom }}"
                             class="h-48 w-48 rounded-2xl object-cover shadow-md ring-2 ring-slate-200">
                    @else
                        <div class="flex h-48 w-48 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-5xl font-black uppercase tracking-widest text-slate-400 shadow-inner ring-2 ring-slate-200">
                            {{ strtoupper(substr($agent->prenom, 0, 1) . substr($agent->nom, 0, 1)) }}
                        </div>
                    @endif

                    {{-- Compte utilisateur --}}
                    @if ($agent->user)
                        <div class="w-full rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Compte actif</p>
                            <p class="mt-0.5 text-xs text-emerald-700 font-semibold">{{ $agent->user->role }}</p>
                            <a href="{{ route('admin.users.edit', $agent->user) }}"
                               class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:underline">
                                <i class="fas fa-external-link-alt text-[10px]"></i> Gérer le compte
                            </a>
                        </div>
                    @else
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Sans compte</p>
                            <a href="{{ route('admin.users.create', ['agent_id' => $agent->id]) }}"
                               class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline">
                                <i class="fas fa-plus text-[10px]"></i> Créer un compte
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Informations --}}
                <div class="grid gap-4 sm:grid-cols-2">

                    {{-- Identité --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <i class="fas fa-user mr-1.5 text-blue-400"></i> Identité
                        </p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nom</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $agent->nom }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Prénom</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $agent->prenom }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sexe</p>
                                <p class="mt-1 font-bold text-slate-800">{{ ucfirst($agent->sexe ?? '—') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Profession --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <i class="fas fa-briefcase mr-1.5 text-amber-400"></i> Profession
                        </p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rôle</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $agent->role }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date de prise de fonction</p>
                                <p class="mt-1 font-bold text-slate-800">
                                    {{ optional($agent->date_debut_fonction)->format('d/m/Y') ?: '—' }}
                                </p>
                            </div>
                            {{-- Poste (fonction détaillée) — modifiable inline --}}
                            <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Fonction occupée</p>
                                <div id="poste-display" class="mt-1 flex items-center justify-between gap-2">
                                    <p class="font-bold text-slate-800">{{ $agent->poste ?: '—' }}</p>
                                    <button type="button" onclick="document.getElementById('poste-display').style.display='none'; document.getElementById('poste-form').style.display='flex';"
                                            class="inline-flex items-center gap-1 rounded-lg border border-amber-200 bg-white px-2 py-1 text-[11px] font-bold text-amber-700 transition hover:bg-amber-100">
                                        <i class="fas fa-pen text-[9px]"></i> Modifier
                                    </button>
                                </div>
                                <form id="poste-form" method="POST"
                                      action="{{ route('admin.agents.update-poste', $agent) }}"
                                      style="display:none"
                                      class="mt-2 flex flex-col gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="poste"
                                           value="{{ old('poste', $agent->poste) }}"
                                           list="postes-profil-list"
                                           required maxlength="150"
                                           class="ent-input w-full text-sm"
                                           placeholder="Ex : Caissier, Agent de crédit…">
                                    <datalist id="postes-profil-list">
                                        @foreach ($postes as $libelle)
                                            <option value="{{ $libelle }}">
                                        @endforeach
                                    </datalist>
                                    <div class="flex gap-2">
                                        <button type="submit"
                                                class="ent-btn ent-btn-primary flex-1 justify-center py-1.5 text-xs">
                                            <i class="fas fa-check mr-1"></i> Enregistrer
                                        </button>
                                        <button type="button" onclick="document.getElementById('poste-form').style.display='none'; document.getElementById('poste-display').style.display='flex';"
                                                class="ent-btn ent-btn-soft flex-1 justify-center py-1.5 text-xs">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                   {{-- Structure de rattachement --}}
<div class="sm:col-span-2">
    <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
        <i class="fas fa-sitemap mr-1.5 text-violet-400"></i> Rattachement hiérarchique
    </p>
   @php
    $rattachements = [];
    
    if (!empty($agent->entite)) {
        $rattachements[] = ['label' => 'Entité (Faîtière)', 'value' => $agent->entite->nom, 'icon' => 'fa-gavel', 'color' => 'text-slate-700', 'bg' => 'bg-slate-50', 'border' => 'border-slate-200'];
    }
    if (!empty($agent->direction)) {
        $rattachements[] = ['label' => 'Direction', 'value' => $agent->direction->nom, 'icon' => 'fa-building', 'color' => 'text-indigo-700', 'bg' => 'bg-indigo-50', 'border' => 'border-indigo-200'];
    }
    if (!empty($agent->guichet)) {
        $rattachements[] = ['label' => 'Guichet', 'value' => $agent->guichet->nom, 'icon' => 'fa-window-maximize', 'color' => 'text-cyan-700', 'bg' => 'bg-cyan-50', 'border' => 'border-cyan-200'];
    }
    if (!empty($agent->agence)) {
        $rattachements[] = ['label' => 'Agence', 'value' => $agent->agence->nom, 'icon' => 'fa-building-columns', 'color' => 'text-blue-700', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'];
    }
    if (!empty($agent->caisse)) {
        $rattachements[] = ['label' => 'Caisse', 'value' => $agent->caisse->nom, 'icon' => 'fa-university', 'color' => 'text-amber-700', 'bg' => 'bg-amber-50', 'border' => 'border-amber-200'];
    }
    if (!empty($agent->delegationTechnique)) {
        $rattachements[] = ['label' => 'Délégation', 'value' => $agent->delegationTechnique->region.' — '.$agent->delegationTechnique->ville, 'icon' => 'fa-sitemap', 'color' => 'text-violet-700', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200'];
    }
    if (!empty($agent->service)) {
        $svcLabel = $agent->service->nom;
        if ($agent->service->direction)           $svcLabel .= ' / ' . $agent->service->direction->nom;
        if ($agent->service->delegationTechnique) $svcLabel .= ' (DT '.$agent->service->delegationTechnique->region.')';
        if ($agent->service->caisse)              $svcLabel .= ' (Caisse '.$agent->service->caisse->nom.')';
        $rattachements[] = ['label' => 'Service', 'value' => $svcLabel, 'icon' => 'fa-layer-group', 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'];
    }
@endphp

    @if (count($rattachements) > 0)
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($rattachements as $r)
                <div class="rounded-xl border {{ $r['border'] }} {{ $r['bg'] }} p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider {{ $r['color'] }}">
                        <i class="fas {{ $r['icon'] }} mr-1"></i> {{ $r['label'] }}
                    </p>
                    <p class="mt-1 font-bold text-slate-800">{{ $r['value'] }}</p>
                </div>
            @endforeach
        </div>
    @else
        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-400">
            <i class="fas fa-link-slash text-xl mb-2 block"></i>
            Agent non affecté à une structure.
        </div>
    @endif
</div>

                    {{-- Contact --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <i class="fas fa-address-card mr-1.5 text-rose-400"></i> Contact
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email professionnel</p>
                                <p class="mt-1 font-bold text-slate-800 break-all">{{ $agent->email }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Téléphone</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $agent->numero_telephone ?: '—' }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Permissions déléguées --}}
                @if ($agent->user)
                <div class="sm:col-span-2">
                    <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <i class="fas fa-key mr-1.5 text-amber-400"></i> Permissions déléguées
                    </p>
                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-4 flex-wrap">
                            <div>
                                <p class="text-sm font-bold text-slate-800">Validation des formations</p>
                                <p class="mt-0.5 text-xs text-slate-500">
                                    Autoriser cet agent à accepter ou refuser les formations soumises par les agents
                                    (page <span class="font-mono">/gerer/formations/validation</span>).
                                </p>
                                @if ($agent->user->hasDirectPermission('formations.valider'))
                                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700">
                                        <i class="fas fa-check-circle text-[9px]"></i> Permission accordée
                                    </span>
                                @else
                                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-slate-200 px-2.5 py-0.5 text-[11px] font-bold text-slate-500">
                                        <i class="fas fa-minus-circle text-[9px]"></i> Non accordée
                                    </span>
                                @endif
                            </div>
                            <form method="POST"
                                  action="{{ route('admin.agents.toggle-formation-valider', $agent) }}"
                                  onsubmit="return confirm('{{ $agent->user->hasDirectPermission('formations.valider') ? 'Retirer cette permission ?' : 'Accorder cette permission ?' }}')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold transition
                                            {{ $agent->user->hasDirectPermission('formations.valider')
                                                ? 'border border-rose-200 bg-white text-rose-600 hover:bg-rose-50'
                                                : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                    @if ($agent->user->hasDirectPermission('formations.valider'))
                                        <i class="fas fa-ban text-[10px]"></i> Retirer la permission
                                    @else
                                        <i class="fas fa-plus text-[10px]"></i> Accorder la permission
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Accès & Sécurité --}}
                @if ($agent->user)
                <div class="sm:col-span-2">
                    <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                        <i class="fas fa-lock mr-1.5 text-indigo-400"></i> Accès & Sécurité
                    </p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email de connexion</p>
                            <p class="mt-1 font-bold text-slate-800 break-all">{{ $agent->user->email }}</p>
                        </div>
                        <div class="rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Mot de passe</p>
                            @if ($agent->user->password_plain)
                                <p class="mt-1 font-mono font-bold text-indigo-700 tracking-widest text-lg">
                                    {{ $agent->user->password_plain }}
                                </p>
                                @if ($agent->user->must_change_password)
                                    <p class="mt-0.5 text-[10px] text-amber-600 font-semibold">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Temporaire – changement requis
                                    </p>
                                @endif
                            @else
                                <p class="mt-1 font-bold text-slate-500 italic text-sm">Personnalisé par l'agent</p>
                                <p class="mt-0.5 text-[10px] text-emerald-600 font-semibold">
                                    <i class="fas fa-check-circle mr-1"></i>Modifié par l'agent
                                </p>
                            @endif

                            {{-- Bouton réinitialiser à 11111111 --}}
                            <form method="POST"
                                  action="{{ route('admin.users.reset-to-default', $agent->user) }}"
                                  onsubmit="return confirm('Remettre le mot de passe de {{ e($agent->prenom) }} {{ e($agent->nom) }} à 11111111 ?')"
                                  class="mt-3">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-1.5 text-[11px] font-bold text-amber-700 transition hover:bg-amber-100">
                                    <i class="fas fa-rotate-left text-[10px]"></i>
                                    Remettre à 11111111
                                </button>
                            </form>

                            {{-- Bouton débloquer (visible seulement si compte suspendu) --}}
                            @if($agent->user->isBlocked())
                                <form method="POST"
                                      action="{{ route('admin.users.unblock', $agent->user) }}"
                                      class="mt-2">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 text-[11px] font-bold text-rose-700 transition hover:bg-rose-100">
                                        <i class="fas fa-lock-open text-[10px]"></i>
                                        Débloquer le compte
                                        <span class="ml-1 rounded bg-rose-200 px-1 py-0.5 text-[9px] font-black">
                                            suspendu jusqu'au {{ $agent->user->blocked_until->format('d/m H:i') }}
                                        </span>
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Rôle système</p>
                            <p class="mt-1 font-bold text-slate-800">
                                {{ \App\Http\Controllers\Admin\UserController::ROLES[$agent->user->role] ?? $agent->user->role }}
                            </p>
                            <span class="mt-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold
                                {{ $agent->user->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $agent->user->is_active ? 'Compte actif' : 'Compte inactif' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
@if ($errors->has('poste'))
<script>
    document.getElementById('poste-display').style.display = 'none';
    document.getElementById('poste-form').style.display    = 'flex';
</script>
@endif
@endpush
