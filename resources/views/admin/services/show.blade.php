@extends('layouts.app')

@section('title', $service->nom.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-8 pt-4 lg:px-8">
    <div class="w-full space-y-6">

        @if (session('status'))
            <div id="status-msg" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                    <i class="fas fa-check"></i>
                </div>
                <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
            </div>
            <script>setTimeout(() => document.getElementById('status-msg')?.remove(), 3000);</script>
        @endif

        {{-- En-tête --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">{{ $service->nom }}</h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Service
                        @if($service->direction)
                            · {{ $service->direction->nom }}
                        @elseif($service->delegationTechnique ?? null)
                            · {{ $service->delegationTechnique->region }} / {{ $service->delegationTechnique->ville }}
                        @elseif($service->caisse ?? null)
                            · {{ $service->caisse->nom }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.services.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">
                    <i class="fas fa-arrow-left text-xs"></i> Retour
                </a>
                <a href="{{ route('admin.services.edit', $service) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                    <i class="fas fa-pen text-xs"></i> Modifier
                </a>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">

            {{-- Chef de service --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-black uppercase tracking-wider text-slate-400">Chef de service</h2>
                @if($service->chef)
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-cyan-100 text-lg font-black text-cyan-700">
                            {{ strtoupper(substr($service->chef->prenom, 0, 1)) }}{{ strtoupper(substr($service->chef->nom, 0, 1)) }}
                        </div>
                        <div class="space-y-1">
                            <p class="font-black text-slate-900">{{ $service->chef->prenom }} {{ $service->chef->nom }}</p>
                            <p class="text-xs text-slate-500">{{ $service->chef->fonction }}</p>
                            @if($service->chef->email)
                                <p class="text-xs text-slate-400"><i class="fas fa-envelope w-3.5 mr-1"></i>{{ $service->chef->email }}</p>
                            @endif
                            @if($service->chef->numero_telephone)
                                <p class="text-xs text-slate-400"><i class="fas fa-phone w-3.5 mr-1"></i>{{ $service->chef->numero_telephone }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                        <p class="text-xs text-slate-400">Aucun chef de service désigné.</p>
                        <a href="{{ route('admin.services.edit', $service) }}"
                           class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-cyan-600 hover:text-cyan-800">
                            <i class="fas fa-pen text-[10px]"></i> Désigner un chef
                        </a>
                    </div>
                @endif
            </div>

            {{-- Rattachement --}}
            <div class="rounded-2xl bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-black uppercase tracking-wider text-slate-400">Rattachement</h2>
                <dl class="space-y-3 text-sm">
                    @if($service->direction)
                        <div class="flex items-start gap-2">
                            <dt class="w-24 shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">Direction</dt>
                            <dd class="font-semibold text-slate-700">{{ $service->direction->nom }}</dd>
                        </div>
                        @if($service->direction->entite)
                            <div class="flex items-start gap-2">
                                <dt class="w-24 shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">Faîtière</dt>
                                <dd class="font-semibold text-slate-700">{{ $service->direction->entite->nom }}</dd>
                            </div>
                        @endif
                    @elseif($service->caisse ?? null)
                        <div class="flex items-start gap-2">
                            <dt class="w-24 shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">Caisse</dt>
                            <dd class="font-semibold text-slate-700">{{ $service->caisse->nom }}</dd>
                        </div>
                    @elseif($service->delegationTechnique ?? null)
                        <div class="flex items-start gap-2">
                            <dt class="w-24 shrink-0 text-xs font-bold uppercase tracking-wider text-slate-400">Délégation</dt>
                            <dd class="font-semibold text-slate-700">{{ $service->delegationTechnique->region }} / {{ $service->delegationTechnique->ville }}</dd>
                        </div>
                    @else
                        <p class="text-xs text-slate-400 italic">Aucun rattachement défini.</p>
                    @endif
                </dl>
            </div>

        </div>
    </div>
</div>
@endsection
