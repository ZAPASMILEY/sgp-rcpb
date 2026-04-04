@extends('layouts.app')

@section('title', 'Nouveau Contrat d\'Objectifs')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 py-8 lg:px-8">
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-black tracking-tight text-slate-900 mb-2">Nouveau Contrat d'Objectifs</h1>
            <form method="POST" action="{{ route('contrats.store') }}" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Collaborateur</label>
                    <input type="text" name="collaborateur" required class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400" placeholder="Nom du collaborateur">
                </div>
                <div class="space-y-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Période</label>
                    <div class="flex gap-3">
                        <input type="date" name="date_debut" required class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400" placeholder="Début">
                        <input type="date" name="date_fin" required class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400" placeholder="Fin">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-500">Activités / Articles</label>
                    <div id="articles-container" class="space-y-2">
                        <div class="article-row flex items-center gap-3">
                            <input type="text" name="articles[0][description]" required class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400" placeholder="Description de l'activité ou article">
                            <button type="button" onclick="this.closest('.article-row').remove()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"><i class="fas fa-trash text-xs"></i></button>
                        </div>
                    </div>
                    <button type="button" id="add-article-btn" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600"><i class="fas fa-plus text-[10px]"></i> Ajouter une activité</button>
                </div>
                <div class="flex items-center gap-4 pt-2">
                    <button type="submit" class="inline-flex h-11 items-center gap-3 rounded-2xl bg-amber-500 px-8 text-sm font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-amber-600">
                        <i class="fas fa-check"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('articles-container');
    var btn = document.getElementById('add-article-btn');
    var index = container.querySelectorAll('.article-row').length;

    btn.addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'article-row flex items-center gap-3';
        row.innerHTML =
            '<input type="text" name="articles[' + index + '][description]" required class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-amber-400 focus:ring-amber-400" placeholder="Description de l\'activité ou article">' +
            '<button type="button" onclick="this.closest(\'.article-row\').remove()" class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-400 transition hover:bg-rose-100 hover:text-rose-600"><i class="fas fa-trash text-xs"></i></button>';
        container.appendChild(row);
        row.querySelector('input').focus();
        index++;
    });
});
</script>
@endpush
