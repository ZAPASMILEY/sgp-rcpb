@extends('layouts.app')

@section('title', 'Modifier agence | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">
        <a href="{{ route('admin.agences.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-700">
            <i class="fas fa-arrow-left text-xs"></i> Retour aux agences
        </a>

        <div class="rounded-2xl bg-white p-6 shadow-sm lg:p-8">
            <div class="mb-6">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-emerald-500">Modification</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $agence->nom }}</h1>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.agences.update', $agence) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom de l'agence <span class="text-rose-500">*</span></label>
                        <input type="text" name="nom" value="{{ old('nom', $agence->nom) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Délégation <span class="text-rose-500">*</span></label>
                        <select name="delegation_technique_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                            <option value="">-- Choisir --</option>
                            @foreach ($delegations as $d)
                                <option value="{{ $d->id }}" @selected(old('delegation_technique_id', $agence->delegation_technique_id) == $d->id)>{{ $d->region }} — {{ $d->ville }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Chef d'agence</label>
                    @if($chefs->isEmpty())
                        <div class="mb-1 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec la fonction <strong>Chef d'Agence</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @endif
                    <select name="chef_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">— Aucun chef pour l'instant —</option>
                        @foreach ($chefs as $agent)
                            <option value="{{ $agent->id }}" @selected(old('chef_agent_id', $agence->chef_agent_id) == $agent->id)>
                                {{ $agent->nom }} {{ $agent->prenom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Secretaire d'agence</label>
                    @if($secretaires->isEmpty())
                        <div class="mb-1 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                            <span>Aucun agent avec la fonction <strong>Secrétaire d'Agence</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                        </div>
                    @endif
                    <select name="secretaire_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">— Aucune secretaire pour l'instant —</option>
                        @foreach ($secretaires as $agent)
                            <option value="{{ $agent->id }}" @selected(old('secretaire_agent_id', $agence->secretaire_agent_id) == $agent->id)>
                                {{ $agent->nom }} {{ $agent->prenom }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Caisse superviseur <span class="text-rose-500">*</span></label>
                    <select name="caisse_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">-- Choisir --</option>
                        @foreach ($caisses as $caisse)
                            <option value="{{ $caisse->id }}" @selected(old('caisse_id', $agence->caisse_id) == $caisse->id)>{{ $caisse->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-check text-xs text-emerald-300"></i> Enregistrer
                    </button>
                    <a href="{{ route('admin.agences.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
