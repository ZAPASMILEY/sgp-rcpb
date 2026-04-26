@extends('layouts.app')

@section('title', $agent->prenom.' '.$agent->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('admin.agents.index') }}" class="font-semibold text-cyan-600 hover:text-cyan-800">Agents</a>
            <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
            <span class="text-slate-500">{{ $agent->prenom }} {{ $agent->nom }}</span>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Carte principale --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Fiche agent</p>
                    <h1 class="mt-1 text-3xl font-black tracking-tight text-slate-900">
                        {{ $agent->prenom }} {{ $agent->nom }}
                    </h1>
                    <p class="mt-1 text-sm font-semibold text-slate-500">{{ $agent->fonction }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.agents.edit', $agent) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        <i class="fas fa-pen text-xs text-cyan-300"></i> Modifier
                    </a>
                    <form method="POST" action="{{ route('admin.agents.destroy', $agent) }}"
                          onsubmit="return confirm('Supprimer définitivement cet agent ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-bold text-red-600 transition hover:bg-red-50">
                            <i class="fas fa-trash text-xs"></i> Supprimer
                        </button>
                    </form>
                    <a href="{{ route('admin.agents.index') }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-500 transition hover:bg-slate-50">
                        Retour
                    </a>
                </div>
            </div>

            {{-- Corps --}}
            <div class="mt-8 grid gap-6 lg:grid-cols-[200px_1fr]">

                {{-- Photo --}}
                <div class="flex flex-col items-center gap-4">
                    @if ($agent->photo_path)
                        <img src="{{ Storage::url($agent->photo_path) }}"
                             alt="Photo de {{ $agent->prenom }} {{ $agent->nom }}"
                             class="h-48 w-48 rounded-2xl object-cover shadow-md ring-2 ring-slate-200">
                    @else
                        <div class="flex h-48 w-48 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 text-5xl font-black uppercase tracking-widest text-slate-400 shadow-inner ring-2 ring-slate-200">
                            {{ strtoupper(substr($agent->prenom, 0, 1) . substr($agent->nom, 0, 1)) }}
                        </div>
                    @endif

                    {{-- Compte utilisateur --}}
                    @if ($agent->user)
                        <div class="w-full rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Compte actif</p>
                            <p class="mt-0.5 text-xs text-emerald-700 font-semibold">{{ $agent->user->role }}</p>
                            <a href="{{ route('admin.users.edit', $agent->user) }}"
                               class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:underline">
                                <i class="fas fa-external-link-alt text-[10px]"></i> Gérer le compte
                            </a>
                        </div>
                    @else
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-center">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Sans compte</p>
                            <a href="{{ route('admin.users.create') }}"
                               class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-blue-600 hover:underline">
                                <i class="fas fa-plus text-[10px]"></i> Créer un compte
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Informations --}}
                <div class="grid gap-4 sm:grid-cols-2">

                    {{-- Identité --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <i class="fas fa-user mr-1.5 text-blue-400"></i> Identité
                        </p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nom</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $agent->nom }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Prénom</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $agent->prenom }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sexe</p>
                                <p class="mt-1 font-bold text-slate-800">{{ ucfirst($agent->sexe ?? '—') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Profession --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <i class="fas fa-briefcase mr-1.5 text-amber-400"></i> Profession
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Fonction</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $agent->fonction }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date de prise de fonction</p>
                                <p class="mt-1 font-bold text-slate-800">
                                    {{ optional($agent->date_debut_fonction)->format('d/m/Y') ?: '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Structure de rattachement --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <i class="fas fa-sitemap mr-1.5 text-violet-400"></i> Rattachement hiérarchique
                        </p>
                        @php
                            $rattachements = [];
                            if ($agent->guichet)            $rattachements[] = ['label' => 'Guichet',            'value' => $agent->guichet->nom,                       'icon' => 'fa-window-maximize',    'color' => 'text-cyan-700',   'bg' => 'bg-cyan-50',   'border' => 'border-cyan-200'];
                            if ($agent->agence)             $rattachements[] = ['label' => 'Agence',             'value' => $agent->agence->nom,                        'icon' => 'fa-building-columns',   'color' => 'text-blue-700',   'bg' => 'bg-blue-50',   'border' => 'border-blue-200'];
                            if ($agent->caisse)             $rattachements[] = ['label' => 'Caisse',             'value' => $agent->caisse->nom,                        'icon' => 'fa-university',         'color' => 'text-amber-700',  'bg' => 'bg-amber-50',  'border' => 'border-amber-200'];
                            if ($agent->delegationTechnique)$rattachements[] = ['label' => 'Délégation',         'value' => $agent->delegationTechnique->region.' — '.$agent->delegationTechnique->ville, 'icon' => 'fa-sitemap', 'color' => 'text-violet-700', 'bg' => 'bg-violet-50', 'border' => 'border-violet-200'];
                            if ($agent->service) {
                                $svcLabel = $agent->service->nom;
                                if ($agent->service->direction)           $svcLabel .= ' / ' . $agent->service->direction->nom;
                                if ($agent->service->delegationTechnique) $svcLabel .= ' (DT '.$agent->service->delegationTechnique->region.')';
                                if ($agent->service->caisse)              $svcLabel .= ' (Caisse '.$agent->service->caisse->nom.')';
                                $rattachements[] = ['label' => 'Service', 'value' => $svcLabel, 'icon' => 'fa-layer-group', 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'border' => 'border-emerald-200'];
                            }
                        @endphp
                        @if (count($rattachements) > 0)
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($rattachements as $r)
                                    <div class="rounded-xl border {{ $r['border'] }} {{ $r['bg'] }} p-4">
                                        <p class="text-[10px] font-bold uppercase tracking-wider {{ $r['color'] }}">
                                            <i class="fas {{ $r['icon'] }} mr-1"></i> {{ $r['label'] }}
                                        </p>
                                        <p class="mt-1 font-bold text-slate-800">{{ $r['value'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-400">
                                <i class="fas fa-link-slash text-xl mb-2 block"></i>
                                Agent non affecté à une structure.
                                <a href="{{ route('admin.agents.edit', $agent) }}" class="block mt-1 text-blue-500 hover:underline font-semibold text-xs">
                                    Affecter maintenant
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Contact --}}
                    <div class="sm:col-span-2">
                        <p class="mb-3 text-[11px] font-black uppercase tracking-[0.2em] text-slate-400">
                            <i class="fas fa-address-card mr-1.5 text-rose-400"></i> Contact
                        </p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email professionnel</p>
                                <p class="mt-1 font-bold text-slate-800 break-all">{{ $agent->email }}</p>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Téléphone</p>
                                <p class="mt-1 font-bold text-slate-800">{{ $agent->numero_telephone ?: '—' }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
