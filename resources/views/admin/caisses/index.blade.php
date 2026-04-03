@extends('layouts.app')

@section('title', 'Caisses | '.config('app.name', 'SGP-RCPB'))

@push('head')
    <style>
        .caisses-table thead th,
        .caisses-table tbody td {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .caisses-table thead th {
            letter-spacing: 0.12em;
        }

        .caisses-nowrap {
            white-space: nowrap;
        }

        .caisses-subtext {
            margin-top: 0.1rem;
            font-size: 0.74rem;
            line-height: 1.2;
        }

        .caisses-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .caisses-actions .ent-btn {
            width: 2.6rem;
            height: 2.6rem;
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <section class="admin-panel p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Referentiel / Caisses</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Les Caisses de la RCPB </h1>
                        <p class="mt-2 text-sm text-slate-600">Liste des caisses avec les coordonnees du directeur, le numero du secretariat et la delegation technique.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">{{ $caisses->total() }} caisse(s)</div>
                        <button type="button" onclick="document.getElementById('caisse-form').classList.remove('hidden')" class="ent-btn ent-btn-primary">Ajouter une caisse</button>
                    </div>
                </div>
            </section>

            @if (session('status'))
                <div id="caisse-status-message" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
                <script>setTimeout(() => document.getElementById('caisse-status-message')?.remove(), 3000);</script>
            @endif

            <section class="admin-panel p-6">
                <form method="GET" action="{{ route('admin.caisses.index') }}" class="mb-6 flex flex-col gap-3 md:flex-row md:items-center">
                    <div class="flex-1">
                        <label for="search" class="sr-only">Rechercher une caisse</label>
                        <input
                            id="search"
                            name="search"
                            type="search"
                            value="{{ $search }}"
                            class="ent-input"
                            placeholder="Rechercher par caisse, directeur, contact, secretariat ou delegation"
                        >
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="ent-btn ent-btn-primary">
                            <i class="fas fa-search"></i>
                            <span>Rechercher</span>
                        </button>
                        @if ($search !== '')
                            <a href="{{ route('admin.caisses.index') }}" class="ent-btn ent-btn-soft">Reinitialiser</a>
                        @endif
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="caisses-table ent-table ent-table--compact text-left text-sm text-slate-700">
                        <thead>
                            <tr>
                                <th>N</th>
                                <th>Caisse</th>
                                <th>Directeur de caisse</th>
                                <th>Contact directeur</th>
                                <th>Secretariat</th>
                                <th>Délégation</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($caisses as $caisse)
                                <tr>
                                    <td class="caisses-nowrap">{{ ($caisses->firstItem() ?? 1) + $loop->index }}</td>
                                    <td class="caisses-nowrap">{{ $caisse->nom }}</td>
                                    <td class="caisses-nowrap">{{ $caisse->directeur_prenom }} {{ $caisse->directeur_nom }}</td>
                                    <td class="caisses-nowrap">
                                        <p>{{ $caisse->directeur_email }}</p>
                                    </td>
                                    <td class="caisses-nowrap">{{ $caisse->secretariat_telephone }}</td>
                                    <td>
                                        @if ($caisse->delegationTechnique)
                                            <p class="caisses-nowrap">{{ $caisse->delegationTechnique->region }} / {{ $caisse->delegationTechnique->ville }}</p>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="caisses-actions">
                                            <a
                                                href="{{ route('admin.caisses.show', $caisse) }}"
                                                class="ent-btn ent-btn-soft inline-flex h-10 w-10 items-center justify-center p-0"
                                                title="Voir"
                                                aria-label="Voir"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a
                                                href="{{ route('admin.caisses.edit', $caisse) }}"
                                                class="ent-btn ent-btn-soft inline-flex h-10 w-10 items-center justify-center p-0"
                                                title="Modifier"
                                                aria-label="Modifier"
                                            >
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.caisses.destroy', $caisse) }}" onsubmit="return confirm('Supprimer cette caisse ?');" class="inline-flex">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="ent-btn ent-btn-danger inline-flex h-10 w-10 items-center justify-center p-0"
                                                    title="Supprimer"
                                                    aria-label="Supprimer"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-sm text-slate-500">
                                        {{ $search !== '' ? 'Aucune caisse ne correspond a votre recherche.' : 'Aucune caisse enregistree.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($caisses->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $caisses->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>

    {{-- Caisse creation modal --}}
    <div id="caisse-form" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('caisse-form').classList.add('hidden')"></div>
        <div class="relative w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-[28px] border border-white/70 bg-white p-6 shadow-2xl lg:p-8">
            <button type="button" onclick="document.getElementById('caisse-form').classList.add('hidden')" class="absolute right-5 top-5 flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
                <i class="fas fa-times"></i>
            </button>

            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-500">Nouvelle caisse</p>
                <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">Ajouter une Caisse</h2>
            </div>

            <form method="POST" action="{{ route('admin.caisses.store') }}" class="space-y-6">
                @csrf

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-map-marker-alt text-emerald-500"></i>
                        Informations de la Caisse
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Délégation <span class="text-rose-500">*</span></label>
                            <select name="delegation_technique_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                <option value="">-- Choisir --</option>
                                @foreach ($delegations as $d)
                                    <option value="{{ $d->id }}" {{ (int) old('delegation_technique_id') === $d->id ? 'selected' : '' }}>{{ $d->region }} — {{ $d->ville }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom de la caisse <span class="text-rose-500">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Ex: Caisse Populaire de Koudougou">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Année d'ouverture <span class="text-rose-500">*</span></label>
                            <input type="text" name="annee_ouverture" value="{{ old('annee_ouverture') }}" required maxlength="4" pattern="\d{4}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="2020">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Quartier <span class="text-rose-500">*</span></label>
                            <input type="text" name="quartier" value="{{ old('quartier') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="Ex: Secteur 5">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tél. secrétariat <span class="text-rose-500">*</span></label>
                            <input type="text" name="secretariat_telephone" value="{{ old('secretariat_telephone') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400" placeholder="+226 XX XX XX XX">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-user-tie text-sky-500"></i>
                        Directeur de Caisse
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                            <input type="text" name="directeur_prenom" value="{{ old('directeur_prenom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-rose-500">*</span></label>
                            <input type="text" name="directeur_nom" value="{{ old('directeur_nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe <span class="text-rose-500">*</span></label>
                            <select name="directeur_sexe" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                <option value="">-- Choisir --</option>
                                <option value="Masculin" {{ old('directeur_sexe') === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                                <option value="Feminin" {{ old('directeur_sexe') === 'Feminin' ? 'selected' : '' }}>Féminin</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="directeur_email" value="{{ old('directeur_email') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone <span class="text-rose-500">*</span></label>
                            <input type="text" name="directeur_telephone" value="{{ old('directeur_telephone') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Début fonction (mois) <span class="text-rose-500">*</span></label>
                            <input type="month" name="directeur_date_debut_mois" value="{{ old('directeur_date_debut_mois') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-user-pen text-fuchsia-500"></i>
                        Secrétaire du Directeur
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Prénom <span class="text-rose-500">*</span></label>
                            <input type="text" name="secretaire_prenom" value="{{ old('secretaire_prenom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom <span class="text-rose-500">*</span></label>
                            <input type="text" name="secretaire_nom" value="{{ old('secretaire_nom') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Sexe <span class="text-rose-500">*</span></label>
                            <select name="secretaire_sexe" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                                <option value="">-- Choisir --</option>
                                <option value="Masculin" {{ old('secretaire_sexe') === 'Masculin' ? 'selected' : '' }}>Masculin</option>
                                <option value="Feminin" {{ old('secretaire_sexe') === 'Feminin' ? 'selected' : '' }}>Féminin</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="secretaire_email" value="{{ old('secretaire_email') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone</label>
                            <input type="text" name="secretaire_telephone" value="{{ old('secretaire_telephone') }}" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Début fonction (mois) <span class="text-rose-500">*</span></label>
                            <input type="month" name="secretaire_date_debut_mois" value="{{ old('secretaire_date_debut_mois') }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-emerald-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                        <i class="fas fa-check"></i>
                        Enregistrer
                    </button>
                    <button type="button" onclick="document.getElementById('caisse-form').classList.add('hidden')" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
