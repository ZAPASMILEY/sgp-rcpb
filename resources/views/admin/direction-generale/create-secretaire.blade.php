@extends('layouts.app')

@section('title', 'Ajouter un secrétaire | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mb-4">
            <a href="{{ route('admin.direction-generale.index') }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
                <i class="fas fa-arrow-left"></i>
                <span>Retour à la Direction Générale</span>
            </a>
        </div>

        <div class="w-full">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Direction Générale — {{ $entite->ville ?? '' }}</span>
                </div>

                <div class="flex items-start gap-4 mt-2">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-600 text-lg font-black shrink-0">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Direction Générale</p>
                        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">Affecter un secrétaire</h1>
                        <p class="mt-1 text-sm text-slate-500">Sélectionnez un agent existant avec la fonction <strong>Secrétaire Assistante</strong>.</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if ($agents->isEmpty())
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Aucun agent avec la fonction <strong>Secrétaire Assistante</strong> n'a de compte utilisateur actif.
                        <a href="{{ route('admin.agents.create') }}" class="ml-2 font-bold underline">Créer un agent</a>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.direction-generale.secretaires.store') }}" class="mt-6 grid gap-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="agent_id" class="text-sm font-semibold text-slate-700">
                            Secrétaire Assistante <span class="text-red-500">*</span>
                        </label>
                        <select id="agent_id" name="agent_id" required class="ent-select" @disabled($agents->isEmpty())>
                            <option value="">— Sélectionner un agent —</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" @selected(old('agent_id') == $agent->id)>
                                    {{ $agent->prenom }} {{ $agent->nom }}
                                    @if ($agent->user)
                                        — {{ $agent->user->email }}
                                    @else
                                        (sans compte)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Seuls les agents avec la fonction "Secrétaire Assistante" apparaissent ici.</p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" @disabled($agents->isEmpty()) class="ent-btn ent-btn-primary flex-1 justify-center px-5 py-3 text-sm disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="fas fa-link mr-2"></i>
                            Affecter le secrétaire
                        </button>
                        <a href="{{ route('admin.direction-generale.index') }}" class="ent-btn flex-none justify-center px-5 py-3 text-sm border border-slate-200 text-slate-600 hover:bg-slate-50">
                            Annuler
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </main>
@endsection
