@extends('layouts.app')

@section('title', 'Modifier Delegation Technique | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <div class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mx-auto max-w-3xl">
            <section class="admin-panel p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Delegation Technique</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Modifier la delegation</h1>

                <form method="POST" action="{{ route('admin.delegations-techniques.update', $delegationTechnique) }}" class="mt-6 grid gap-4">
                    @csrf
                    @method('PUT')

                    <div class="space-y-2">
                        <label for="region" class="text-sm font-semibold text-slate-700">Region</label>
                        <input id="region" name="region" type="text" value="{{ old('region', $delegationTechnique->region) }}" required class="ent-input">
                    </div>

                    <div class="space-y-2">
                        <label for="ville" class="text-sm font-semibold text-slate-700">Ville</label>
                        <input id="ville" name="ville" type="text" value="{{ old('ville', $delegationTechnique->ville) }}" required class="ent-input">
                    </div>

                    <div class="space-y-2">
                        <label for="secretariat_telephone" class="text-sm font-semibold text-slate-700">Numero du secretariat</label>
                        <input id="secretariat_telephone" name="secretariat_telephone" type="text" value="{{ old('secretariat_telephone', $delegationTechnique->secretariat_telephone) }}" required class="ent-input">
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="submit" class="ent-btn ent-btn-primary">Enregistrer</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection