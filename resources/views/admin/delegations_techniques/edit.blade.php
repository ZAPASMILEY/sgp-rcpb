@extends('layouts.app')

@section('title', 'Modifier Délégation Technique | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Modifier — '.$delegationTechnique->region)

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5">
                <ul class="list-disc list-inside text-sm text-rose-600 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">Modifier la Délégation</h1>
                    <p class="mt-1 text-sm text-slate-400">{{ $delegationTechnique->region }} — {{ $delegationTechnique->ville }}</p>
                </div>
                <a href="{{ route('admin.delegations-techniques.show', $delegationTechnique) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-arrow-left text-xs"></i> Retour
                </a>
            </div>
        </div>

        {{-- Main form --}}
        <form method="POST" action="{{ route('admin.delegations-techniques.update', $delegationTechnique) }}" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Infos de base --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                    <i class="fas fa-map-marker-alt text-cyan-500"></i>
                    Informations générales
                </h2>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Région <span class="text-rose-500">*</span></label>
                        <input type="text" name="region" value="{{ old('region', $delegationTechnique->region) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Ville (siège) <span class="text-rose-500">*</span></label>
                        <input type="text" name="ville" value="{{ old('ville', $delegationTechnique->ville) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Tél. secrétariat <span class="text-rose-500">*</span></label>
                        <input type="text" name="secretariat_telephone" value="{{ old('secretariat_telephone', $delegationTechnique->secretariat_telephone) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    </div>
                </div>
            </section>

            {{-- Directeur Régional --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                    <i class="fas fa-user-tie text-sky-500"></i>
                    Directeur Régional
                </h2>
                @if($directeurs->isEmpty())
                    <div class="mb-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                        <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                        <span>Aucun agent avec la fonction <strong>Directeur Technique</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                    </div>
                @endif
                <select name="directeur_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    <option value="">— Aucun directeur pour l'instant —</option>
                    @foreach ($directeurs as $agent)
                        <option value="{{ $agent->id }}" @selected(old('directeur_agent_id', $delegationTechnique->directeur_agent_id) == $agent->id)>
                            {{ $agent->nom }} {{ $agent->prenom }}
                        </option>
                    @endforeach
                </select>
            </section>

            {{-- Secrétaire --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                    <i class="fas fa-user-pen text-fuchsia-500"></i>
                    Secrétaire
                </h2>
                @if($secretaires->isEmpty())
                    <div class="mb-3 flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-700">
                        <i class="fas fa-exclamation-triangle mt-0.5 shrink-0 text-amber-500"></i>
                        <span>Aucun agent avec la fonction <strong>Secrétaire Technique</strong> n'est enregistré. <a href="{{ route('admin.agents.create') }}" class="font-bold underline">Créer un agent</a></span>
                    </div>
                @endif
                <select name="secretaire_agent_id" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-cyan-400 focus:ring-cyan-400">
                    <option value="">— Aucune secrétaire pour l'instant —</option>
                    @foreach ($secretaires as $agent)
                        <option value="{{ $agent->id }}" @selected(old('secretaire_agent_id', $delegationTechnique->secretaire_agent_id) == $agent->id)>
                            {{ $agent->nom }} {{ $agent->prenom }}
                        </option>
                    @endforeach
                </select>
            </section>

            {{-- Villes couvertes --}}
            <section class="rounded-2xl bg-white p-6 shadow-sm">
                <h2 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-4">
                    <i class="fas fa-city text-violet-500"></i>
                    Villes couvertes
                </h2>
                <p class="mb-4 text-xs text-slate-400">Ajoutez les villes que cette délégation couvre. Une ville ne peut appartenir qu'à une seule délégation.</p>

                <div id="villes-container" class="space-y-2">
                    @foreach ($delegationTechnique->villes as $index => $ville)
                        <div class="ville-row flex items-center gap-3">
                            <input type="hidden" name="villes[{{ $index }}][id]" value="{{ $ville->id }}">
                            <input type="text" name="villes[{{ $index }}][nom]" value="{{ old("villes.{$index}.nom", $ville->nom) }}" required class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-violet-400 focus:ring-violet-400" placeholder="Nom de la ville">
                            <button type="button" onclick="this.closest('.ville-row').remove()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="add-ville-btn" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-violet-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-violet-600">
                    <i class="fas fa-plus text-[10px]"></i> Ajouter une ville
                </button>
            </section>

            {{-- Submit --}}
            <div class="flex items-center gap-4">
                <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-cyan-600 px-8 text-sm font-black uppercase tracking-[0.14em] text-white shadow-lg shadow-cyan-200 transition hover:-translate-y-0.5 hover:bg-cyan-700">
                    <i class="fas fa-check"></i> Enregistrer
                </button>
                <a href="{{ route('admin.delegations-techniques.show', $delegationTechnique) }}" class="inline-flex h-11 items-center gap-2 rounded-2xl border border-slate-200 bg-white px-6 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('villes-container');
    var btn = document.getElementById('add-ville-btn');
    var index = container.querySelectorAll('.ville-row').length;

    btn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'ville-row flex items-center gap-3';
        row.innerHTML =
            '<input type="text" name="villes[' + index + '][nom]" required class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-violet-400 focus:ring-violet-400" placeholder="Nom de la ville">' +
            '<button type="button" onclick="this.closest(\'.ville-row\').remove()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"><i class="fas fa-trash text-xs"></i></button>';
        container.appendChild(row);
        row.querySelector('input').focus();
        index++;
    });
});
</script>
@endpush