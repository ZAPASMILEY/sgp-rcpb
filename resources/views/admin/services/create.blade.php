@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-cyan-600 hover:text-cyan-800 font-semibold text-sm">
        <i class="fas fa-arrow-left"></i>
        <span>Retour</span>
    </a>
</div>
<div class="min-h-screen bg-slate-500/20 backdrop-blur-sm flex items-center justify-center p-4 lg:p-8">
    <div class="relative w-full max-w-3xl bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.15)] overflow-hidden animate-in zoom-in duration-300">
        <a href="{{ route('admin.services.index') }}" class="absolute -top-2 -right-2 lg:top-6 lg:right-6 z-50 h-12 w-12 bg-white rounded-full shadow-xl flex items-center justify-center text-cyan-500 hover:scale-110 transition-all border border-slate-50">
            <i class="fas fa-times text-xl"></i>
            <span class="absolute top-2 right-2 h-2.5 w-2.5 bg-rose-500 rounded-full border-2 border-white"></span>
        </a>
        <div class="bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] p-8 lg:p-10 flex items-center gap-4">
            <div class="h-10 w-12 bg-white rounded-xl flex items-center justify-center text-[#22d3ee] font-black shadow-sm">
                <i class="fas fa-layer-group"></i>
            </div>
            <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Nouveau Service</h1>
        </div>
        <form method="POST" action="{{ route('admin.services.store') }}" class="p-8 lg:p-12 space-y-8">
            @csrf
            <div class="space-y-4">
                <label for="nom" class="text-xs font-bold text-slate-400 ml-1">Nom du service</label>
                <input id="nom" name="nom" type="text" value="{{ old('nom') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500 transition-all placeholder-slate-300" placeholder="Ex: Service Tresorerie">
            </div>
            {{-- Type de rattachement --}}
            <div class="space-y-4">
                <label class="text-xs font-bold text-slate-400 ml-1">Type de rattachement</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="flex-1 flex items-center gap-3 bg-slate-100 rounded-[20px] p-4 cursor-pointer transition-all" id="label-faitiere">
                        <input type="radio" name="parent_type" value="faitiere" class="text-cyan-500 w-4 h-4" {{ old('parent_type', 'faitiere') === 'faitiere' ? 'checked' : '' }}>
                        <span class="font-bold text-slate-700 text-sm">Direction Faîtière</span>
                    </label>
                    <label class="flex-1 flex items-center gap-3 bg-slate-100 rounded-[20px] p-4 cursor-pointer transition-all" id="label-delegation">
                        <input type="radio" name="parent_type" value="delegation" class="text-cyan-500 w-4 h-4" {{ old('parent_type') === 'delegation' ? 'checked' : '' }}>
                        <span class="font-bold text-slate-700 text-sm">Délégation Technique</span>
                    </label>
                    <label class="flex-1 flex items-center gap-3 bg-slate-100 rounded-[20px] p-4 cursor-pointer transition-all" id="label-caisse">
                        <input type="radio" name="parent_type" value="caisse" class="text-cyan-500 w-4 h-4" {{ old('parent_type') === 'caisse' ? 'checked' : '' }}>
                        <span class="font-bold text-slate-700 text-sm">Caisse</span>
                    </label>
                </div>
            </div>

            {{-- Faîtière direction selector --}}
            <div id="panel-faitiere" class="space-y-4">
                <label class="text-xs font-bold text-slate-400 ml-1">Direction Faîtière</label>
                <select name="direction_id" class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                    <option value="">Sélectionner une direction</option>
                    @foreach ($faitiereDirections as $dir)
                        <option value="{{ $dir->id }}" @selected((string) old('direction_id') === (string) $dir->id)>{{ $dir->nom }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Délégation selector --}}
            <div id="panel-delegation" class="space-y-4 hidden">
                <label class="text-xs font-bold text-slate-400 ml-1">Délégation Technique</label>
                <select name="delegation_technique_id" class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                    <option value="">Sélectionner une délégation</option>
                    @foreach ($delegations as $del)
                        <option value="{{ $del->id }}" @selected((string) old('delegation_technique_id') === (string) $del->id)>{{ $del->region }} / {{ $del->ville }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Caisse selector --}}
            <div id="panel-caisse" class="space-y-4 hidden">
                <label class="text-xs font-bold text-slate-400 ml-1">Caisse</label>
                <select name="caisse_id" class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                    <option value="">Sélectionner une caisse</option>
                    @foreach ($caisses as $caisse)
                        <option value="{{ $caisse->id }}" @selected((string) old('caisse_id') === (string) $caisse->id)>
                            {{ $caisse->nom }}@if($caisse->delegationTechnique) — {{ $caisse->delegationTechnique->region }} / {{ $caisse->delegationTechnique->ville }}@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="chef_prenom" class="text-xs font-bold text-slate-400 ml-1">Prenom du chef de service</label>
                    <input id="chef_prenom" name="chef_prenom" type="text" value="{{ old('chef_prenom') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Prenom">
                </div>
                <div class="space-y-2">
                    <label for="chef_nom" class="text-xs font-bold text-slate-400 ml-1">Nom du chef de service</label>
                    <input id="chef_nom" name="chef_nom" type="text" value="{{ old('chef_nom') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Nom">
                </div>
            </div>
            <div class="space-y-4">
                <label for="chef_email" class="text-xs font-bold text-slate-400 ml-1">Email du chef de service</label>
                <input id="chef_email" name="chef_email" type="email" value="{{ old('chef_email') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="chef.service@entreprise.com">
            </div>
            <div class="space-y-4">
                <label for="chef_telephone" class="text-xs font-bold text-slate-400 ml-1">Numero de telephone</label>
                <input id="chef_telephone" name="chef_telephone" type="text" value="{{ old('chef_telephone') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Ex: +226 70 00 00 00">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="chef_sexe" class="text-xs font-bold text-slate-400 ml-1">Sexe du chef de service</label>
                    <select id="chef_sexe" name="chef_sexe" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 text-slate-700 font-bold focus:ring-2 focus:ring-cyan-500">
                        <option value="">Choisir</option>
                        <option value="Homme" @selected(old('chef_sexe') === 'Homme')>Homme</option>
                        <option value="Femme" @selected(old('chef_sexe') === 'Femme')>Femme</option>
                        <option value="Autres" @selected(old('chef_sexe') === 'Autres')>Autres</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label for="chef_date_debut_mois" class="text-xs font-bold text-slate-400 ml-1">Date de prise de fonction</label>
                    <input id="chef_date_debut_mois" name="chef_date_debut_mois" type="month" value="{{ old('chef_date_debut_mois') }}" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>
            <div class="bg-cyan-50 rounded-2xl p-4 flex items-center gap-3">
                <div class="h-6 w-6 rounded-full bg-cyan-500 text-white flex items-center justify-center text-[10px] font-black">i</div>
                <p class="text-[10px] font-black text-cyan-700 uppercase tracking-tight">L'email du chef servira d'identifiant de connexion</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="password" class="text-xs font-bold text-slate-400 ml-1">Mot de passe</label>
                    <input id="password" name="password" type="password" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Min. 8 caracteres" autocomplete="new-password">
                </div>
                <div class="space-y-2">
                    <label for="password_confirmation" class="text-xs font-bold text-slate-400 ml-1">Confirmer le mot de passe</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full bg-slate-100 border-none rounded-[20px] p-4 font-bold focus:ring-2 focus:ring-cyan-500" placeholder="Retaper le mot de passe" autocomplete="new-password">
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-4 pt-6">
                <button type="submit" class="w-full sm:flex-[2] py-5 bg-gradient-to-r from-[#22d3ee] to-[#3b82f6] text-white rounded-full text-sm font-black uppercase tracking-widest shadow-xl shadow-cyan-200 hover:scale-[1.02] transition-all">
                    Creer le service
                </button>
                <a href="{{ route('admin.services.index') }}" class="w-full sm:flex-1 py-5 bg-white border-2 border-slate-100 text-slate-400 rounded-full text-sm font-black uppercase tracking-widest text-center hover:bg-slate-50 transition-all">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    .animate-in {
        animation: zoomIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes zoomIn {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    .radio-active {
        background-color: rgb(236 254 255);
        outline: 2px solid rgb(34 211 238);
        outline-offset: 0;
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="parent_type"]');
    const panels = {
        faitiere: document.getElementById('panel-faitiere'),
        delegation: document.getElementById('panel-delegation'),
        caisse: document.getElementById('panel-caisse'),
    };
    const labels = {
        faitiere: document.getElementById('label-faitiere'),
        delegation: document.getElementById('label-delegation'),
        caisse: document.getElementById('label-caisse'),
    };

    function switchPanel(val) {
        Object.keys(panels).forEach(function (key) {
            panels[key].classList.toggle('hidden', key !== val);
            labels[key].classList.toggle('radio-active', key === val);
        });
    }

    radios.forEach(function (r) {
        r.addEventListener('change', function () { switchPanel(r.value); });
    });

    const checked = document.querySelector('input[name="parent_type"]:checked');
    switchPanel(checked ? checked.value : 'faitiere');
});
</script>
@endpush
@endsection
