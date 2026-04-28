@extends('layouts.app')

@section('title', 'Direction Générale | '.config('app.name', 'SGP-RCPB'))
@section('page_title', 'Direction Générale')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="status-message" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl shadow-emerald-100/60">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-message')?.remove(), 3000);</script>
        @endif

        @if($direction)

            {{-- ── Cadres principaux : DG / DGA / Assistante ── --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="mb-5 text-lg font-black text-slate-900">Cadres de la Direction Générale</h2>

                @if($membres->isEmpty())
                    <p class="text-sm text-slate-400">Aucun cadre enregistré.</p>
                @else
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($membres as $membre)
                            @php
                                $role = $membre->role; // original case
                                $roleUp = strtoupper($role);
                                $color = match($roleUp) {
                                    'PCA'           => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'badge' => 'bg-orange-500'],
                                    'DG'            => ['bg' => 'bg-cyan-100',   'text' => 'text-cyan-700',   'badge' => 'bg-cyan-500'],
                                    'DGA'           => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'badge' => 'bg-purple-500'],
                                    'ASSISTANTE_DG' => ['bg' => 'bg-pink-100',   'text' => 'text-pink-700',   'badge' => 'bg-pink-500'],
                                    default         => ['bg' => 'bg-slate-100',  'text' => 'text-slate-700',  'badge' => 'bg-slate-400'],
                                };
                                $fonction = match($roleUp) {
                                    'PCA'           => 'Président du Conseil d\'Administration',
                                    'DG'            => 'Directeur Général',
                                    'DGA'           => 'Directeur Général Adjoint',
                                    'ASSISTANTE_DG' => 'Assistante du Directeur Général',
                                    default         => ucfirst(strtolower(str_replace('_', ' ', $role))),
                                };
                                $initiales = collect(explode(' ', $membre->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->join('');
                            @endphp
                            <div class="flex flex-col rounded-2xl border border-slate-100 bg-slate-50/60 p-4 gap-3">
                                {{-- Avatar + nom + badge rôle --}}
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $color['bg'] }} {{ $color['text'] }} text-sm font-black">
                                        {{ $initiales }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate font-black text-slate-900">{{ $membre->name }}</p>
                                        <span class="inline-block rounded-full {{ $color['badge'] }} px-2 py-0.5 text-[10px] font-bold text-white leading-tight mt-0.5">
                                            {{ $roleUp }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Détails --}}
                                <div class="space-y-1.5 text-xs">
                                    <div class="flex items-center gap-2 text-slate-500">
                                        <i class="fas fa-briefcase w-3.5 text-slate-300"></i>
                                        <span>{{ $fonction }}</span>
                                    </div>
                                    @if($membre->email)
                                        <div class="flex items-center gap-2 text-slate-500">
                                            <i class="fas fa-envelope w-3.5 text-slate-300"></i>
                                            <span class="truncate">{{ $membre->email }}</span>
                                        </div>
                                    @endif
                                    @if($membre->sexe)
                                        <div class="flex items-center gap-2 text-slate-500">
                                            <i class="fas fa-venus-mars w-3.5 text-slate-300"></i>
                                            <span>{{ $membre->sexe }}</span>
                                        </div>
                                    @endif
                                    @if($membre->date_prise_fonction)
                                        <div class="flex items-center gap-2 text-slate-500">
                                            <i class="fas fa-calendar-check w-3.5 text-slate-300"></i>
                                            <span>En poste depuis {{ \Carbon\Carbon::createFromFormat('Y-m', $membre->date_prise_fonction)->translatedFormat('M Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Action modifier --}}
                                @if(in_array($role, ['DG', 'DGA', 'Assistante_Dg']))
                                    <div class="border-t border-slate-100 pt-2">
                                        <a href="{{ route('admin.direction-generale.membres.edit', $membre) }}"
                                           class="inline-flex items-center gap-1.5 rounded-lg bg-white border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-cyan-50 hover:text-cyan-700 transition">
                                            <i class="fas fa-pen text-[10px]"></i> Modifier
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ── Secrétaires & Conseillers (2 colonnes) ── --}}
            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Secrétaires --}}
                <div class="rounded-2xl bg-white shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-black text-slate-900">Secrétaires</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Secrétariat de la Direction Générale</p>
                        </div>
                        <a href="{{ route('admin.direction-generale.secretaires.create') }}"
                           class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fas fa-user-plus"></i> Ajouter
                        </a>
                    </div>

                    <ul class="space-y-2">
                        @forelse($secretaires as $secretaire)
                            <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-200 text-xs font-black text-slate-600">
                                    {{ strtoupper(substr($secretaire->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold text-slate-800 text-sm">{{ $secretaire->name }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $secretaire->sexe ?? '—' }}
                                        @if($secretaire->date_prise_fonction)
                                            · En poste depuis {{ \Carbon\Carbon::createFromFormat('Y-m', $secretaire->date_prise_fonction)->translatedFormat('M Y') }}
                                        @endif
                                    </p>
                                    @if($secretaire->email)
                                        <p class="text-xs text-slate-400 truncate">{{ $secretaire->email }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.direction-generale.secretaires.destroy', $secretaire) }}"
                                      onsubmit="return confirm('Supprimer ce secrétaire ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                                <p class="text-xs text-slate-400">Aucun secrétaire enregistré.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>

                {{-- Conseillers --}}
                <div class="rounded-2xl bg-white shadow-sm p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-black text-slate-900">Conseillers du DG</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Conseil et expertise auprès du Directeur Général</p>
                        </div>
                        <a href="{{ route('admin.direction-generale.conseillers.create') }}"
                           class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                            <i class="fas fa-user-plus"></i> Ajouter
                        </a>
                    </div>

                    <ul class="space-y-2">
                        @forelse($conseillers as $conseiller)
                            <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-xs font-black text-indigo-600">
                                    {{ strtoupper(substr($conseiller->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-bold text-slate-800 text-sm">{{ $conseiller->name }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $conseiller->sexe ?? '—' }}
                                        @if($conseiller->date_prise_fonction)
                                            · En poste depuis {{ \Carbon\Carbon::createFromFormat('Y-m', $conseiller->date_prise_fonction)->translatedFormat('M Y') }}
                                        @endif
                                    </p>
                                    @if($conseiller->email)
                                        <p class="text-xs text-slate-400 truncate">{{ $conseiller->email }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('admin.direction-generale.conseillers.destroy', $conseiller) }}"
                                      onsubmit="return confirm('Supprimer ce conseiller ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition">
                                        <i class="fas fa-trash-alt text-[10px]"></i>
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                                <p class="text-xs text-slate-400">Aucun conseiller enregistré.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>

            </div>{{-- /grid --}}

        @else
            <div class="rounded-2xl bg-white p-8 shadow-sm text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 mb-4">
                    <i class="fas fa-user-tie text-2xl text-slate-300"></i>
                </div>
                <p class="text-sm font-bold text-slate-600">La Direction Générale n'est pas encore configurée.</p>
                <p class="mt-1 text-xs text-slate-400">Définissez le DG, le DGA et les membres fondateurs.</p>
                <a href="{{ route('admin.direction-generale.create') }}"
                   class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fas fa-plus"></i> Configurer la Direction Générale
                </a>
            </div>
        @endif

    </div>
</div>
@endsection
