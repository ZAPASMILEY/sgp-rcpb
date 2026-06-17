@extends('layouts.app')
@section('title', 'Chef de service — '.$service->nom.' | '.config('app.name'))

@section('content')
<div class="min-h-screen bg-slate-500/20 backdrop-blur-sm flex items-center justify-center p-4 lg:p-8">
    <div class="relative w-full max-w-lg bg-white rounded-[40px] shadow-[0_30px_100px_rgba(0,0,0,0.15)] overflow-hidden">
        <a href="{{ route('admin.direction-dga.index') }}"
           class="absolute top-4 right-4 z-50 h-10 w-10 bg-white rounded-full shadow-xl flex items-center justify-center text-slate-400 hover:scale-110 transition">
            <i class="fas fa-times"></i>
        </a>
        <div class="bg-gradient-to-r from-purple-600 to-violet-500 px-8 py-7 flex items-center gap-4">
            <div class="h-10 w-10 bg-white rounded-xl flex items-center justify-center text-purple-600 font-black shadow">
                <i class="fas fa-user-tie"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-purple-200">Direction DGA</p>
                <h1 class="text-xl font-black text-white">Chef de service</h1>
                <p class="text-sm text-purple-200 truncate">{{ $service->nom }}</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mx-8 mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.direction-dga.services.chef.update', $service) }}" class="p-8 space-y-6">
            @csrf @method('PUT')

            @if($service->chef)
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                    <i class="fas fa-exchange-alt mr-1"></i>
                    Chef actuel : <strong>{{ $service->chef->prenom }} {{ $service->chef->nom }}</strong> — son compte sera désactivé si vous changez.
                </div>
            @endif

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-400 ml-1">Nouveau chef de service</label>
                @if($candidats->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                        Aucun agent avec la fonction <strong>Chef de Service</strong> n'est disponible.
                        <a href="{{ route('admin.agents.create') }}" class="font-bold underline ml-1">Créer un agent</a>
                    </div>
                @endif
                <select name="chef_agent_id" id="chef_agent_id" required
                        class="w-full bg-slate-100 border-none rounded-[18px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-purple-400">
                    <option value="">— Sélectionner un agent —</option>
                    @foreach($candidats as $agent)
                        <option value="{{ $agent->id }}" @selected(old('chef_agent_id', $service->chef_agent_id) == $agent->id)>
                            {{ $agent->nom }} {{ $agent->prenom }} — {{ $agent->role_genree }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" @disabled($candidats->isEmpty())
                        class="flex-1 py-4 bg-gradient-to-r from-purple-600 to-violet-500 text-white rounded-full text-sm font-black uppercase tracking-widest shadow-lg hover:scale-[1.02] transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Confirmer
                </button>
                <a href="{{ route('admin.direction-dga.index') }}"
                   class="flex-1 py-4 bg-white border-2 border-slate-100 text-slate-400 rounded-full text-sm font-black uppercase tracking-widest text-center hover:bg-slate-50 transition">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

@push('head')
@endpush
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('chef_agent_id');
            if (el) new TomSelect(el, { placeholder: 'Rechercher un agent...', allowEmptyOption: true });
        });
    </script>
@endpush
@endsection
