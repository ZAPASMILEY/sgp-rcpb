@extends('layouts.app')

@section('title', 'Modifier '.$service->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mise a jour</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier le service</h1>
                        <p class="mt-2 text-sm text-slate-600">Mettez a jour les informations demandees.</p>
                    </div>
                    <a href="{{ route('admin.services.show', $service) }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.services.update', $service) }}" class="mt-6 grid gap-5">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom du service</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom', $service->nom) }}" required class="ent-input">
                    </div>

                    <div class="space-y-2">
                        <label for="direction_id" class="text-sm font-semibold text-slate-700">Direction</label>
                        <select id="direction_id" name="direction_id" required class="ent-select">
                            <option value="">Selectionner une direction</option>
                            @foreach ($directions as $direction)
                                <option value="{{ $direction->id }}" @selected((string) old('direction_id', $service->direction_id) === (string) $direction->id)>
                                    {{ $direction->nom }} @if ($direction->entite) - {{ $direction->entite->nom }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="chef_agent_id" class="text-sm font-semibold text-slate-700">Chef de service</label>
                        @if($chefs->isEmpty())
                            <div class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                                <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                                <span>Aucun agent avec la fonction <strong>Chef de Service</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                            </div>
                        @endif
                        <select id="chef_agent_id" name="chef_agent_id" class="ent-select">
                            <option value="">— Aucun chef pour l'instant —</option>
                            @foreach ($chefs as $agent)
                                <option value="{{ $agent->id }}" @selected(old('chef_agent_id', $service->chef_agent_id) == $agent->id)>
                                    {{ $agent->nom }} {{ $agent->prenom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        Enregistrer les modifications
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
