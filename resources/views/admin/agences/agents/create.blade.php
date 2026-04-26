@extends('layouts.app')

@section('title', 'Affecter un agent | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.agences.agents.index', $agence) }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour aux agents de l'agence</span>
        </a>
    </div>
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Affectation d'agent</span>
                </div>

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Agence / {{ $agence->nom }}</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Affecter un agent</h1>
                        <p class="mt-2 text-sm text-slate-600">Sélectionnez un agent existant à rattacher à cette agence.</p>
                    </div>
                    <a href="{{ route('admin.agences.agents.index', $agence) }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if ($agents->isEmpty())
                    <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Aucun agent disponible à affecter. Tous les agents sont déjà rattachés à une structure ou aucun agent n'est enregistré.
                        <a href="{{ route('admin.agents.create') }}" class="ml-2 font-bold underline">Créer un agent</a>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.agences.agents.store', $agence) }}" class="mt-6 grid gap-5">
                    @csrf

                    <div class="space-y-2">
                        <label for="agent_id" class="text-sm font-semibold text-slate-700">
                            Agent à affecter <span class="text-red-500">*</span>
                        </label>
                        <select id="agent_id" name="agent_id" required class="ent-select" @disabled($agents->isEmpty())>
                            <option value="">— Sélectionner un agent —</option>
                            @foreach ($agents as $agent)
                                <option value="{{ $agent->id }}" @selected(old('agent_id') == $agent->id)>
                                    {{ $agent->prenom }} {{ $agent->nom }}
                                    @if ($agent->fonction) — {{ $agent->fonction }} @endif
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-500">Seuls les agents sans rattachement d'agence apparaissent ici.</p>
                    </div>

                    <button type="submit" @disabled($agents->isEmpty()) class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm disabled:opacity-60 disabled:cursor-not-allowed">
                        <i class="fas fa-link mr-2"></i>
                        Affecter à {{ $agence->nom }}
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
