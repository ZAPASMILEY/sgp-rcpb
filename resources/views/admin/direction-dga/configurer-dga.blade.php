@extends('layouts.app')
@section('title', 'Configurer la Direction Générale Adjointe | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Direction Générale Adjointe')

@section('content')
<main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">

    <div class="mb-4">
        <a href="{{ route('admin.direction-dga.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-violet-600 hover:text-violet-800">
            <i class="fas fa-arrow-left"></i>
            <span>Retour à la Direction Générale Adjointe</span>
        </a>
    </div>

    <div class="w-full max-w-2xl space-y-6">

        @if (session('status'))
            <div class="flex items-center gap-3 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-sm">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-100 bg-white px-5 py-4 shadow-sm">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <i class="fas fa-times"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('error') }}</p>
            </div>
        @endif

        <section class="ent-window p-6 sm:p-8">
            <div class="ent-window__bar" aria-hidden="true">
                <span class="ent-window__dot ent-window__dot--danger"></span>
                <span class="ent-window__dot ent-window__dot--warn"></span>
                <span class="ent-window__dot ent-window__dot--ok"></span>
                <span class="ent-window__label">Configuration Direction Générale Adjointe</span>
            </div>

            <div class="mb-6">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Configuration</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Direction Générale Adjointe</h1>
                <p class="mt-1 text-sm text-slate-500">Désignez le Directeur Général Adjoint et sa secrétaire. Leurs comptes seront activés automatiquement.</p>
            </div>

            {{-- ── Section DGA ─────────────────────────────────────── --}}
            <form method="POST" action="{{ route('admin.direction-dga.stocker') }}" class="space-y-5">
                @csrf

                <div class="ent-card space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-xs font-black text-violet-700">DGA</span>
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-600">Directeur Général Adjoint</p>
                    </div>

                    @if ($direction->directeur)
                        <div class="flex items-center gap-3 rounded-xl border border-violet-100 bg-violet-50 px-4 py-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-200 text-xs font-black text-violet-800">
                                {{ strtoupper(substr($direction->directeur->prenom, 0, 1)) }}{{ strtoupper(substr($direction->directeur->nom, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $direction->directeur->prenom }} {{ $direction->directeur->nom }}</p>
                                <p class="text-xs text-slate-500">DGA actuel</p>
                            </div>
                        </div>
                    @endif

                    @if ($errors->hasBag('default') && $errors->has('dga_agent_id'))
                        <p class="text-xs text-rose-600">{{ $errors->first('dga_agent_id') }}</p>
                    @endif

                    @if ($dgaCandidats->isEmpty())
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Aucun agent avec la fonction <strong>DGA</strong> n'est enregistré.
                            <a href="{{ route('admin.agents.create') }}" class="ml-1 font-bold underline">Créer un agent</a>
                        </div>
                    @else
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Agent DGA <span class="text-rose-500">*</span>
                            </label>
                            <select name="dga_agent_id" required class="ent-select">
                                <option value="">— Sélectionner un agent —</option>
                                @foreach ($dgaCandidats as $agent)
                                    <option value="{{ $agent->id }}"
                                        @selected($direction->directeur_agent_id == $agent->id)>
                                        {{ $agent->prenom }} {{ $agent->nom }}
                                        @if($agent->email) — {{ $agent->email }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400">Seuls les agents avec la fonction "DGA" apparaissent ici.</p>
                        </div>

                        <div>
                            <button type="submit"
                                    class="ent-btn ent-btn-primary justify-center px-5 py-2.5 text-sm"
                                    @disabled($dgaCandidats->isEmpty())>
                                <i class="fas fa-check mr-2"></i>
                                Enregistrer le DGA
                            </button>
                        </div>
                    @endif
                </div>
            </form>

            {{-- ── Section Secrétaire ───────────────────────────────── --}}
            <form method="POST" action="{{ route('admin.direction-dga.secretaire.update') }}" class="mt-5 space-y-5">
                @csrf
                @method('PUT')

                <div class="ent-card space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-xs font-black text-slate-600">SEC</span>
                        <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-600">Secrétaire de Direction</p>
                    </div>

                    @if ($direction->secretaire)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-200 text-xs font-black text-slate-700">
                                {{ strtoupper(substr($direction->secretaire->prenom, 0, 1)) }}{{ strtoupper(substr($direction->secretaire->nom, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $direction->secretaire->prenom }} {{ $direction->secretaire->nom }}</p>
                                <p class="text-xs text-slate-500">Secrétaire actuelle</p>
                            </div>
                        </div>
                    @endif

                    @if ($secCandidats->isEmpty())
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Aucun agent avec la fonction <strong>Secrétaire de Direction</strong> n'est enregistré.
                            <a href="{{ route('admin.agents.create') }}" class="ml-1 font-bold underline">Créer un agent</a>
                        </div>
                    @else
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                Agent secrétaire <span class="text-rose-500">*</span>
                            </label>
                            <select name="secretaire_agent_id" required class="ent-select">
                                <option value="">— Sélectionner un agent —</option>
                                @foreach ($secCandidats as $agent)
                                    <option value="{{ $agent->id }}"
                                        @selected($direction->secretaire_agent_id == $agent->id)>
                                        {{ $agent->prenom }} {{ $agent->nom }}
                                        @if($agent->email) — {{ $agent->email }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400">Seuls les agents avec la fonction "Secrétaire de Direction" apparaissent ici.</p>
                        </div>

                        <div>
                            <button type="submit"
                                    class="ent-btn ent-btn-primary justify-center px-5 py-2.5 text-sm"
                                    @disabled($secCandidats->isEmpty())>
                                <i class="fas fa-check mr-2"></i>
                                Enregistrer la secrétaire
                            </button>
                        </div>
                    @endif
                </div>
            </form>

        </section>

    </div>
</main>
@endsection
