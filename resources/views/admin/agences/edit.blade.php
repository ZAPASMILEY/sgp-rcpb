@extends('layouts.app')

@section('title', 'Modifier agence | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="mx-auto max-w-3xl space-y-6">
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
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-user-tie text-emerald-500"></i> Chef d'agence
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom complet <span class="text-rose-500">*</span></label>
                            <input type="text" name="chef_nom" value="{{ old('chef_nom', $agence->chef_nom) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="chef_email" value="{{ old('chef_email', $agence->chef_email) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone <span class="text-rose-500">*</span></label>
                            <input type="text" name="chef_telephone" value="{{ old('chef_telephone', $agence->chef_telephone) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="flex items-center gap-2 text-sm font-black uppercase tracking-[0.14em] text-slate-700 mb-3">
                        <i class="fas fa-user-check text-violet-500"></i> Secrétaire
                    </h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Nom complet <span class="text-rose-500">*</span></label>
                            <input type="text" name="secretaire_nom" value="{{ old('secretaire_nom', $agence->secretaire_nom) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="secretaire_email" value="{{ old('secretaire_email', $agence->secretaire_email) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Téléphone <span class="text-rose-500">*</span></label>
                            <input type="text" name="secretaire_telephone" value="{{ old('secretaire_telephone', $agence->secretaire_telephone) }}" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Caisse superviseur <span class="text-rose-500">*</span></label>
                    <select name="superviseur_caisse_id" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-emerald-400 focus:ring-emerald-400">
                        <option value="">-- Choisir --</option>
                        @foreach ($caisses as $caisse)
                            <option value="{{ $caisse->id }}" @selected(old('superviseur_caisse_id', $agence->superviseur_caisse_id) == $caisse->id)>{{ $caisse->nom }}</option>
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
