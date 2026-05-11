@extends('layouts.rh')

@section('title', 'Modifier formation | SGP-RCPB')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="mx-auto max-w-2xl flex flex-col gap-6">

    <header class="admin-panel px-6 py-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('rh.formations.index') }}"
               class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Ressources Humaines</p>
                <h1 class="text-xl font-black tracking-tight text-slate-950">Modifier la formation</h1>
                <p class="mt-0.5 text-sm text-slate-500 truncate">{{ $formation->titre }}</p>
            </div>
        </div>
    </header>

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
            <p class="font-bold mb-1"><i class="fas fa-exclamation-circle mr-1"></i> Veuillez corriger les erreurs :</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('rh.formations.update', $formation) }}"
          class="admin-panel px-6 py-6 flex flex-col gap-5">
        @csrf @method('PUT')

        {{-- Agent --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Agent *</label>
            <select name="agent_id" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                @foreach($agents as $ag)
                    <option value="{{ $ag->id }}" @selected(old('agent_id', $formation->agent_id) == $ag->id)>
                        {{ trim($ag->prenom . ' ' . $ag->nom) }} — {{ $ag->fonction }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Titre --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Titre *</label>
            <input type="text" name="titre" value="{{ old('titre', $formation->titre) }}" required maxlength="255"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
        </div>

        {{-- Domaine --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Domaine *</label>
            <select name="domaine" required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                @foreach($domaines as $key => $label)
                    <option value="{{ $key }}" @selected(old('domaine', $formation->domaine) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Dates --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Date de début *</label>
                <input type="date" name="date_debut"
                       value="{{ old('date_debut', $formation->date_debut->format('Y-m-d')) }}" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
            </div>
            <div>
                <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Date de fin *</label>
                <input type="date" name="date_fin"
                       value="{{ old('date_fin', $formation->date_fin->format('Y-m-d')) }}" required
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-emerald-400 focus:bg-white">
            </div>
        </div>

        {{-- Durée --}}
        <div>
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-1.5">Durée (heures) *</label>
            <div class="relative">
                <input type="number" name="duree_heures"
                       value="{{ old('duree_heures', $formation->duree_heures) }}" required min="1" max="9999"
                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pr-14 text-sm outline-none focus:border-emerald-400 focus:bg-white">
                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">heures</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
            <a href="{{ route('rh.formations.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Annuler
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fas fa-save text-xs"></i> Enregistrer
            </button>
        </div>
    </form>

</div>
</div>
@endsection
