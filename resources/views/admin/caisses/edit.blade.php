@extends('layouts.app')

@section('title', 'Modifier caisse | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel ent-window h-full w-full p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Fenetre de modification</span>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Modification</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier la caisse</h1>
                        <p class="mt-2 text-sm text-slate-600">Mettez a jour les coordonnees du directeur, du secretariat et du superviseur technique.</p>
                    </div>
                    <a href="{{ route('admin.caisses.index') }}" target="_top" class="ent-btn ent-btn-soft">Index caisses</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.caisses.update', $caisse) }}" target="_top" class="mt-6 grid gap-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="delegation_technique_id" class="text-sm font-semibold text-slate-700">Delegation Technique</label>
                        <select id="delegation_technique_id" name="delegation_technique_id" required class="ent-select">
                            <option value="">Selectionner une delegation</option>
                            @php
                                $selectedDelegationId = (string) old('delegation_technique_id', $caisse->superviseur?->delegation_technique_id);
                            @endphp
                            @foreach ($delegations as $delegation)
                                <option value="{{ $delegation->id }}" @selected($selectedDelegationId === (string) $delegation->id)>
                                    {{ $delegation->region }} / {{ $delegation->ville }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Le superviseur est filtre selon cette delegation.</p>
                    </div>

                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom de la caisse</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom', $caisse->nom) }}" required class="ent-input" placeholder="Ex: Caisse de Ouagadougou Centre">
                    </div>

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur de caisse</p>
                        <div class="space-y-2">
                            <label for="directeur_nom" class="text-sm font-semibold text-slate-700">Nom complet</label>
                            <input id="directeur_nom" name="directeur_nom" type="text" value="{{ old('directeur_nom', $caisse->directeur_nom) }}" required class="ent-input" placeholder="Nom et prenom du directeur de caisse">
                        </div>
                        <div class="ent-form-grid">
                            <div class="space-y-2">
                                <label for="directeur_email" class="text-sm font-semibold text-slate-700">Email</label>
                                <input id="directeur_email" name="directeur_email" type="email" value="{{ old('directeur_email', $caisse->directeur_email) }}" required class="ent-input" placeholder="directeur.caisse@rcpb.org">
                            </div>
                            <div class="space-y-2">
                                <label for="directeur_telephone" class="text-sm font-semibold text-slate-700">Telephone</label>
                                <input id="directeur_telephone" name="directeur_telephone" type="text" value="{{ old('directeur_telephone', $caisse->directeur_telephone) }}" required class="ent-input" placeholder="+226 70 00 00 00">
                            </div>
                        </div>
                    </div>

                    <div class="ent-form-grid">
                        <div class="space-y-2">
                            <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Numero du secretariat</label>
                            <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone', $caisse->secretariat_telephone) }}" required class="ent-input" placeholder="+226 25 00 00 00">
                        </div>
                        <div class="space-y-2">
                            <label for="superviseur_direction_id" class="text-sm font-semibold text-slate-700">Directeur technique superviseur</label>
                            <select id="superviseur_direction_id" name="superviseur_direction_id" required class="ent-select">
                                <option value="">Choisissez d'abord une delegation</option>
                                @php
                                    $selectedSuperviseurId = (string) old('superviseur_direction_id', $caisse->superviseur_direction_id);
                                @endphp
                                @foreach ($directions as $direction)
                                    <option value="{{ $direction->id }}" data-delegation-id="{{ $direction->delegation_technique_id }}" @selected($selectedSuperviseurId === (string) $direction->id)>
                                        {{ $direction->directeur_prenom }} {{ $direction->directeur_nom }}
                                        @if ($direction->delegationTechnique)
                                            - {{ $direction->delegationTechnique->region }} / {{ $direction->delegationTechnique->ville }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer les modifications
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var delegationSelect = document.getElementById('delegation_technique_id');
            var superviseurSelect = document.getElementById('superviseur_direction_id');

            if (!delegationSelect || !superviseurSelect) {
                return;
            }

            function filterSuperviseursByDelegation() {
                var delegationId = delegationSelect.value;
                var hasMatch = false;

                Array.from(superviseurSelect.options).forEach(function (option) {
                    if (!option.value) {
                        option.textContent = delegationId ? 'Selectionner un superviseur' : "Choisissez d'abord une delegation";
                        option.hidden = false;
                        option.disabled = false;
                        return;
                    }

                    var isMatch = option.getAttribute('data-delegation-id') === delegationId;
                    option.hidden = !isMatch;
                    option.disabled = !isMatch;

                    if (isMatch) {
                        hasMatch = true;
                    }
                });

                superviseurSelect.disabled = !delegationId || !hasMatch;
            }

            delegationSelect.addEventListener('change', filterSuperviseursByDelegation);
            filterSuperviseursByDelegation();
        });
    </script>
@endpush
