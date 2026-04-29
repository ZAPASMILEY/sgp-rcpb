@extends('layouts.app')

@section('title', 'Modifier '.$direction->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="w-full">
            <section class="admin-panel p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Mise à jour</p>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier la Direction</h1>
                        <p class="mt-2 text-sm text-slate-600">Mettez à jour les informations de la direction.</p>
                    </div>
                    <a href="{{ route('admin.directions.show', $direction) }}" class="ent-btn ent-btn-soft">Retour</a>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.directions.update', $direction) }}" class="mt-6 grid gap-6">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="nom" class="text-sm font-semibold text-slate-700">Nom de la direction</label>
                        <input id="nom" name="nom" type="text" value="{{ old('nom', $direction->nom) }}" required class="ent-input">
                    </div>

                    <div class="ent-card space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Responsables</p>

                        <div class="space-y-2">
                            <label for="directeur_agent_id" class="text-sm font-semibold text-slate-700">Directeur</label>
                            <select id="directeur_agent_id" name="directeur_agent_id" class="ent-select">
                                <option value="">— Aucun —</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}" @selected((string) old('directeur_agent_id', $direction->directeur_agent_id) === (string) $agent->id)>
                                        {{ $agent->nom }} {{ $agent->prenom }}{{ $agent->fonction ? ' — '.$agent->fonction : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="secretaire_agent_id" class="text-sm font-semibold text-slate-700">Secrétaire</label>
                            <select id="secretaire_agent_id" name="secretaire_agent_id" class="ent-select">
                                <option value="">— Aucun —</option>
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}" @selected((string) old('secretaire_agent_id', $direction->secretaire_agent_id) === (string) $agent->id)>
                                        {{ $agent->nom }} {{ $agent->prenom }}{{ $agent->fonction ? ' — '.$agent->fonction : '' }}
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
