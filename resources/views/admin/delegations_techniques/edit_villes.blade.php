@extends('layouts.app')

@section('title', 'Villes — '.$delegation->region.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.delegations-techniques.show', $delegation) }}" class="inline-flex items-center gap-2 text-violet-600 hover:text-violet-800 font-semibold text-sm">
        <i class="fas fa-arrow-left"></i>
        <span>Retour à la délégation</span>
    </a>
</div>

<div class="min-h-screen bg-slate-500/20 backdrop-blur-sm flex items-center justify-center p-4 lg:p-8">
    <div class="relative w-full max-w-xl bg-white rounded-[50px] shadow-[0_30px_100px_rgba(0,0,0,0.15)] overflow-hidden animate-in">

        <a href="{{ route('admin.delegations-techniques.show', $delegation) }}" class="absolute -top-2 -right-2 lg:top-6 lg:right-6 z-50 h-12 w-12 bg-white rounded-full shadow-xl flex items-center justify-center text-violet-500 hover:scale-110 transition-all border border-slate-50">
            <i class="fas fa-times text-xl"></i>
        </a>

        <div class="bg-gradient-to-r from-[#7c3aed] to-[#a78bfa] p-8 lg:p-10 flex items-center gap-4">
            <div class="h-10 w-12 bg-white rounded-xl flex items-center justify-center text-violet-500 font-black shadow-sm">
                <i class="fas fa-city"></i>
            </div>
            <div>
                <h1 class="text-2xl lg:text-3xl font-black text-white tracking-tight">Villes couvertes</h1>
                <p class="text-white/80 text-xs font-bold uppercase mt-1 tracking-wider">{{ $delegation->region }} — {{ $delegation->ville }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.delegations-techniques.villes.update', $delegation) }}" class="p-8 lg:p-10 space-y-6">
            @csrf
            @method('PUT')

            @if(session('status'))
                <div class="flex items-start gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <i class="fas fa-check-circle mt-0.5 shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @error('villes')
                <div class="flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <p class="text-xs text-slate-400">Ajoutez les villes que cette délégation couvre. Une ville ne peut appartenir qu'à une seule délégation.</p>

            <div id="villes-container" class="space-y-3">
                @foreach ($delegation->villes as $index => $ville)
                    <div class="ville-row flex items-center gap-3">
                        <input type="hidden" name="villes[{{ $index }}][id]" value="{{ $ville->id }}">
                        <input type="text" name="villes[{{ $index }}][nom]" value="{{ old("villes.{$index}.nom", $ville->nom) }}" required
                            class="flex-1 bg-slate-100 border-none rounded-[20px] px-4 py-3 text-slate-700 font-bold focus:ring-2 focus:ring-violet-400"
                            placeholder="Nom de la ville">
                        <button type="button" onclick="this.closest('.ville-row').remove()"
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-ville-btn"
                class="inline-flex items-center gap-2 rounded-2xl bg-violet-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-violet-600">
                <i class="fas fa-plus text-xs"></i> Ajouter une ville
            </button>

            <div class="flex flex-col sm:flex-row items-center gap-4 pt-4">
                <button type="submit"
                    class="w-full sm:flex-[2] py-5 bg-gradient-to-r from-[#7c3aed] to-[#a78bfa] text-white rounded-full text-sm font-black uppercase tracking-widest shadow-xl shadow-violet-200 hover:scale-[1.02] transition-all">
                    Enregistrer
                </button>
                <a href="{{ route('admin.delegations-techniques.show', $delegation) }}"
                    class="w-full sm:flex-1 py-5 bg-white border-2 border-slate-100 text-slate-400 rounded-full text-sm font-black uppercase tracking-widest text-center hover:bg-slate-50 transition-all">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    .animate-in { animation: zoomIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes zoomIn {
        from { opacity: 0; transform: scale(0.9); }
        to   { opacity: 1; transform: scale(1); }
    }
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('villes-container');
    var btn       = document.getElementById('add-ville-btn');
    var index     = container.querySelectorAll('.ville-row').length;

    btn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'ville-row flex items-center gap-3';
        row.innerHTML =
            '<input type="text" name="villes[' + index + '][nom]" required ' +
            'class="flex-1 bg-slate-100 border-none rounded-[20px] px-4 py-3 text-slate-700 font-bold focus:ring-2 focus:ring-violet-400" placeholder="Nom de la ville">' +
            '<button type="button" onclick="this.closest(\'.ville-row\').remove()" ' +
            'class="flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"><i class="fas fa-trash text-xs"></i></button>';
        container.appendChild(row);
        index++;
        row.querySelector('input').focus();
    });
});
</script>
@endpush
