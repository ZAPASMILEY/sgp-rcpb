@extends('layouts.app')
@section('title', 'Configurer le DGA | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Direction Générale Adjointe')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
<div class="w-full max-w-xl space-y-6">

    <div class="mb-2">
        <a href="{{ route('admin.direction-dga.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-violet-600 hover:text-violet-800">
            <i class="fas fa-arrow-left"></i> Retour à la Direction Générale Adjointe
        </a>
    </div>

    @if (session('status'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-sm">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600"><i class="fas fa-check"></i></div>
            <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
        </div>
    @endif

    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="mb-6 flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-600 text-lg font-black">
                DGA
            </div>
            <div>
                <h1 class="text-xl font-black tracking-tight text-slate-900">Configurer le DGA</h1>
                <p class="mt-0.5 text-sm text-slate-400">Direction : <strong class="text-slate-600">{{ $direction->nom }}</strong></p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($candidats->isEmpty())
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-700">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                Aucun agent avec la fonction <strong>DGA</strong> n'est enregistré.
                <a href="{{ route('admin.agents.create') }}" class="ml-1 font-bold underline">Créer un agent</a>
            </div>
        @else
            <form method="POST" action="{{ route('admin.direction-dga.stocker') }}" class="space-y-5">
                @csrf

                <div class="space-y-2">
                    <label class="text-xs font-black uppercase tracking-wider text-slate-500">
                        Agent DGA <span class="text-rose-500">*</span>
                    </label>
                    <select name="dga_agent_id" required class="ent-select" @disabled($candidats->isEmpty())>
                        <option value="">— Sélectionner un agent —</option>
                        @foreach ($candidats as $agent)
                            <option value="{{ $agent->id }}"
                                @selected($direction->directeur_agent_id == $agent->id)>
                                {{ $agent->prenom }} {{ $agent->nom }}
                                @if($agent->email) — {{ $agent->email }} @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400">Seuls les agents avec la fonction "DGA" apparaissent ici.</p>
                </div>

                @if ($direction->directeur)
                    <div class="flex items-center gap-3 rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-700 font-black">
                            {{ strtoupper(substr($direction->directeur->prenom, 0, 1)) }}{{ strtoupper(substr($direction->directeur->nom, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">DGA actuel : {{ $direction->directeur->prenom }} {{ $direction->directeur->nom }}</p>
                            <p class="text-xs text-slate-500">{{ $direction->directeur->email }}</p>
                        </div>
                    </div>
                @endif

                <div class="flex gap-3 pt-1">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 py-3 text-sm font-black uppercase tracking-wider text-white shadow-md shadow-violet-200 transition hover:bg-violet-700 active:scale-95">
                        <i class="fas fa-check"></i> Confirmer
                    </button>
                    <a href="{{ route('admin.direction-dga.index') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-6 py-3 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        Annuler
                    </a>
                </div>
            </form>
        @endif
    </div>

</div>
</div>
@endsection
