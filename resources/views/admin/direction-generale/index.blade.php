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
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h1 class="text-2xl font-black tracking-tight text-slate-900 mb-6">Structure de la Direction Générale</h1>
                <div class="flex flex-wrap gap-8 items-end">
                    @foreach($membres as $membre)
                        @php
                            $role = strtoupper($membre->role);
                            $color = match($role) {
                                'PCA' => 'bg-orange-400',
                                'DG' => 'bg-cyan-400',
                                'DGA' => 'bg-purple-400',
                                'ASSISTANTE_DG' => 'bg-pink-400',
                                default => 'bg-slate-400',
                            };
                            $fonction = match($role) {
                                'PCA' => 'Président du Conseil d\'Administration',
                                'DG' => 'Directeur Général',
                                'DGA' => 'Directeur Général Adjoint',
                                'ASSISTANTE_DG' => 'Assistante DG',
                                default => ucfirst(strtolower(str_replace('_', ' ', $role))),
                            };
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-20 h-20 flex items-center justify-center rounded-full text-white font-black text-lg shadow {{ $color }} mb-2">
                                {{ $role }}
                            </div>
                            <div class="text-base font-black text-slate-800">{{ $membre->name }}</div>
                            <div class="text-xs text-slate-400 text-center leading-tight">{{ $fonction }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
                <div class="space-y-6">
                    <div class="rounded-2xl bg-white shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold">Secrétaires</h2>
                            <a href="{{ route('admin.direction-generale.secretaires.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                <i class="fas fa-user-plus"></i> Ajouter
                            </a>
                        </div>
                        <ul class="space-y-2">
                            @forelse($secretaires as $secretaire)
                                <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 justify-between">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $secretaire->name }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ $secretaire->sexe ?? '—' }}
                                            @if($secretaire->date_prise_fonction)
                                                · En poste depuis {{ \Carbon\Carbon::createFromFormat('Y-m', $secretaire->date_prise_fonction)->translatedFormat('M Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('admin.direction-generale.secretaires.destroy', $secretaire) }}" onsubmit="return confirm('Supprimer ce secrétaire ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-100 transition">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="text-slate-400 text-sm">Aucun secrétaire enregistré.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="rounded-2xl bg-white shadow-sm p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-bold">Conseillers du DG</h2>
                            <a href="{{ route('admin.direction-generale.conseillers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                <i class="fas fa-user-plus"></i> Ajouter
                            </a>
                        </div>
                        <ul class="space-y-2">
                            @forelse($conseillers as $conseiller)
                                <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 justify-between">
                                    <div>
                                        <div class="font-bold text-slate-800">{{ $conseiller->name }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ $conseiller->sexe ?? '—' }}
                                            @if($conseiller->date_prise_fonction)
                                                · En poste depuis {{ \Carbon\Carbon::createFromFormat('Y-m', $conseiller->date_prise_fonction)->translatedFormat('M Y') }}
                                            @endif
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('admin.direction-generale.conseillers.destroy', $conseiller) }}" onsubmit="return confirm('Supprimer ce conseiller ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 hover:bg-red-100 transition">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                        </button>
                                    </form>
                                </li>
                            @empty
                                <li class="text-slate-400 text-sm">Aucun conseiller enregistré.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <p class="text-slate-500">Aucune direction générale n'est encore créée.</p>
                <a href="{{ route('admin.direction-generale.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fas fa-plus"></i> Créer la Direction Générale
                </a>
            </div>
        @endif
</div>
@endsection
