@extends('layouts.app')

@section('title', 'Details caisse | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="min-h-screen bg-[#f1f5f9] px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-6xl">
            <div class="mb-4">
                <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
                    <i class="fas fa-arrow-left"></i>
                    <span>Retour</span>
                </a>
            </div>
            <section class="admin-panel ent-window h-full w-full p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Consultation</span>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Referentiel / Caisses</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $caisse->nom }}</h1>
                        <p class="mt-2 text-sm text-slate-600">Fiche de consultation de la caisse et de son rattachement technique.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.caisses.edit', $caisse) }}" class="ent-btn ent-btn-primary">Modifier</a>
                        <a href="{{ route('admin.caisses.index') }}" target="_top" class="ent-btn ent-btn-soft">Retour a la liste</a>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur de caisse</p>
                        <div class="grid gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom complet</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $caisse->directeur_prenom }} {{ $caisse->directeur_nom }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Sexe</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->directeur_sexe }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Email</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->directeur_email }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Telephone</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->directeur_telephone }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Debut de fonction</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->directeur_date_debut_mois ?: '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Secretaire du Directeur</p>
                        <div class="grid gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Nom complet</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">{{ $caisse->secretaire_prenom }} {{ $caisse->secretaire_nom }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Sexe</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->secretaire_sexe }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Email</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->secretaire_email }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Telephone</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->secretaire_telephone ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Debut de fonction</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->secretaire_date_debut_mois ?: '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Rattachement</p>
                        <div class="grid gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Numero du secretariat</p>
                                <p class="mt-2 text-base text-slate-700">{{ $caisse->secretariat_telephone }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Direction de caisse</p>
                                <p class="mt-2 text-base font-semibold text-slate-900">
                                    {{ $caisse->superviseur?->directeur_prenom }} {{ $caisse->superviseur?->directeur_nom }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.15em] text-slate-500">Reference interne</p>
                                <p class="mt-2 text-base text-slate-700">
                                    @if ($caisse->superviseur?->delegationTechnique)
                                        {{ $caisse->superviseur->nom ?: 'Direction rattachee a la caisse' }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($caisse->superviseur?->delegationTechnique)
                    <div class="mt-8 grid gap-6 md:grid-cols-2">
                        <article class="ent-card space-y-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directions</p>
                            <h2 class="text-xl font-semibold text-slate-950">Directions de cette caisse</h2>
                            <p class="text-sm text-slate-600">
                                Acceder a la direction rattachee a cette caisse.
                            </p>
                            <a
                                href="{{ route('admin.caisses.directions.index', $caisse) }}"
                                class="ent-btn ent-btn-primary"
                            >
                                Voir les directions
                            </a>
                        </article>

                        <article class="ent-card space-y-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Services</p>
                            <h2 class="text-xl font-semibold text-slate-950">Services de cette caisse</h2>
                            <p class="text-sm text-slate-600">
                                Acceder aux services rattaches a la direction de cette caisse.
                            </p>
                            <a
                                href="{{ route('admin.services.caisse', $caisse) }}"
                                class="ent-btn ent-btn-soft"
                            >
                                Voir les services
                            </a>
                        </article>
                    </div>
                @else
                    <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        Cette caisse n'est reliee a aucune direction exploitable pour afficher ses services.
                    </div>
                @endif
            </section>
        </div>
    </main>
@endsection
