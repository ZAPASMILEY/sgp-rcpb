@extends('layouts.app')

@section('title', 'Configurer la Direction Generale | '.config('app.name', 'SGP-RCPB'))

@section('content')
    <main class="admin-shell min-h-screen px-4 py-6 sm:px-6 lg:px-10">
        <div class="mb-4">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
                <i class="fas fa-arrow-left"></i>
                <span>Retour</span>
            </a>
        </div>
        <div class="w-full">
            <section class="admin-panel ent-window p-6 sm:p-8">
                <div class="ent-window__bar" aria-hidden="true">
                    <span class="ent-window__dot ent-window__dot--danger"></span>
                    <span class="ent-window__dot ent-window__dot--warn"></span>
                    <span class="ent-window__dot ent-window__dot--ok"></span>
                    <span class="ent-window__label">Etape 2 sur 2</span>
                </div>

                {{-- Indicateur d'étapes --}}
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-green-500 text-xs font-bold text-white">
                            <i class="fas fa-check text-xs"></i>
                        </span>
                        <span class="text-sm font-medium text-slate-400 line-through">Faitiere & PCA</span>
                    </div>
                    <div class="h-px flex-1 bg-cyan-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-cyan-600 text-xs font-bold text-white">2</span>
                        <span class="text-sm font-semibold text-cyan-700">Direction Generale</span>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Configuration du siege — {{ $entite->ville ?? '' }}</p>
                    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Direction Generale</h1>
                    <p class="mt-2 text-sm text-slate-600">Renseignez les grands responsables de la Direction Generale. Leurs comptes de connexion seront generes automatiquement.</p>
                </div>

                @if ($errors->any())
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="mt-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($dejaConfiguree)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        La Direction Generale est deja configuree. Cette action va creer de nouveaux comptes.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.direction-generale.store') }}" class="mt-6 grid gap-5">
                    @csrf

                    {{-- Directeur General --}}
                    <div class="ent-card space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-xs font-bold text-white">DG</span>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur General</p>
                        </div>
                        <div class="space-y-2">
                            <label for="dg_agent_id" class="text-sm font-semibold text-slate-700">Choisir un agent</label>
                            <select id="dg_agent_id" name="dg_agent_id" class="ent-select">
                                <option value="">— Aucun DG pour l'instant —</option>
                                @foreach ($dg_agents as $agent)
                                    <option value="{{ $agent->id }}" @selected(old('dg_agent_id') == $agent->id)>
                                        {{ $agent->nom }} {{ $agent->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Directeur General Adjoint --}}
                    <div class="ent-card space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-600 text-xs font-bold text-white">DGA</span>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Directeur General Adjoint</p>
                        </div>
                        <div class="space-y-2">
                            <label for="dga_agent_id" class="text-sm font-semibold text-slate-700">Choisir un agent</label>
                            <select id="dga_agent_id" name="dga_agent_id" class="ent-select">
                                <option value="">— Aucun DGA pour l'instant —</option>
                                @foreach ($dga_agents as $agent)
                                    <option value="{{ $agent->id }}" @selected(old('dga_agent_id') == $agent->id)>
                                        {{ $agent->nom }} {{ $agent->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Assistante DG --}}
                    <div class="ent-card space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-400 text-xs font-bold text-white">ASS</span>
                            <p class="text-sm font-semibold uppercase tracking-[0.15em] text-slate-500">Assistante du Directeur General</p>
                        </div>
                        <div class="space-y-2">
                            <label for="assistante_agent_id" class="text-sm font-semibold text-slate-700">Choisir un agent</label>
                            <select id="assistante_agent_id" name="assistante_agent_id" class="ent-select">
                                <option value="">— Aucune assistante pour l'instant —</option>
                                @foreach ($assistantes as $agent)
                                    <option value="{{ $agent->id }}" @selected(old('assistante_agent_id') == $agent->id)>
                                        {{ $agent->nom }} {{ $agent->prenom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="ent-btn ent-btn-primary justify-center px-5 py-3 text-sm">
                        <i class="fas fa-check mr-2"></i>
                        Configurer la Direction Generale
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection
