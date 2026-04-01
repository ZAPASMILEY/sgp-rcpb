@extends('layouts.app')

@section('title', 'Secrétaires Faîtière | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 font-sans">
    <div class="max-w-[1500px] mx-auto space-y-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Secrétaires de la Faîtière</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Directions centrales</span>
                    <i class="fas fa-chevron-right text-[10px] text-slate-300"></i>
                    <span class="text-xs font-bold text-cyan-500 uppercase tracking-widest">Secrétaires rattachées</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.entites.index') }}" class="ent-btn ent-btn-soft">Retour Faîtière</a>
                <button type="button" class="ent-btn ent-btn-primary" onclick="document.getElementById('secretaire-page-modal').classList.remove('hidden')">
                    Ajouter une secrétaire
                </button>
            </div>
        </div>

        <section class="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-8">
                <form method="GET" class="flex-1 max-w-xl">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher une secrétaire ou une direction..." class="ent-input w-full">
                </form>
                <div class="px-4 py-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs font-black uppercase tracking-widest text-slate-400">
                    {{ $secretaires->total() }} secrétaire(s)
                </div>
            </div>

            <div class="ent-table-wrap overflow-x-auto">
                <table class="ent-table text-left text-sm text-slate-700">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Direction</th>
                            <th>Secrétaire</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($secretaires as $direction)
                            <tr>
                                <td>{{ ($secretaires->firstItem() ?? 1) + $loop->index }}</td>
                                <td>{{ $direction->nom }}</td>
                                <td>{{ trim(($direction->secretaire_prenom ?? '').' '.($direction->secretaire_nom ?? '')) ?: 'Non renseignée' }}</td>
                                <td>{{ $direction->secretaire_email ?: 'Non renseigné' }}</td>
                                <td>{{ $direction->secretaire_telephone ?: 'Non renseigné' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400">Aucune secrétaire trouvée pour la faîtière.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($secretaires->hasPages())
                <div class="mt-6">{{ $secretaires->links() }}</div>
            @endif
        </section>
    </div>
</div>

<div id="secretaire-page-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4">
    <div class="w-full max-w-2xl rounded-[32px] bg-white p-8 shadow-2xl relative">
        <button type="button" class="absolute right-5 top-5 inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-50 hover:text-rose-500" onclick="document.getElementById('secretaire-page-modal').classList.add('hidden')">
            <i class="fas fa-times"></i>
        </button>

        <h3 class="text-2xl font-black text-slate-800 tracking-tight">Ajouter une secrétaire</h3>
        <p class="mt-2 text-sm text-slate-400">Associez une secrétaire à une direction de la faîtière.</p>

        <form method="POST" action="{{ route('admin.secretaires.store') }}" class="mt-8 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Prénom</label>
                    <input name="prenom" type="text" class="ent-input w-full" required>
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Nom</label>
                    <input name="nom" type="text" class="ent-input w-full" required>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Email</label>
                <input name="email" type="email" class="ent-input w-full" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Direction rattachée</label>
                    <select name="direction_id" class="ent-input w-full" required>
                        <option value="">Sélectionner une direction</option>
                        @foreach($directions as $direction)
                            <option value="{{ $direction->id }}">{{ $direction->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-[0.18em] text-slate-400 mb-2">Date de prise de fonction</label>
                    <input name="date_prise_fonction" type="date" class="ent-input w-full" required>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" class="ent-btn ent-btn-soft" onclick="document.getElementById('secretaire-page-modal').classList.add('hidden')">Annuler</button>
                <button type="submit" class="ent-btn ent-btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.getElementById('secretaire-page-modal')?.classList.add('hidden');
    }
});
</script>
@endpush
