@extends('layouts.app')
@section('title', 'Gestion des postes | '.config('app.name'))
@section('page_title', 'Postes spécifiques')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 pb-10 pt-5 lg:px-8">
<div class="mx-auto max-w-5xl space-y-6">

    {{-- Toast --}}
    @if(session('status'))
        <div id="toast" class="fixed right-6 top-6 z-50 flex items-center gap-3 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-xl">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600"><i class="fas fa-check"></i></span>
            <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
        </div>
        <script>setTimeout(() => document.getElementById('toast')?.remove(), 3000)</script>
    @endif

    {{-- En-tête --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-widest text-slate-400">Administration</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900">Postes spécifiques</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Configurez les intitulés de postes disponibles pour les agents et les conseillers DG.
                </p>
            </div>
            <a href="{{ route('admin.agents.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-500 transition hover:bg-slate-50">
                <i class="fas fa-arrow-left text-[10px]"></i> Retour agents
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr]">

        {{-- Formulaire d'ajout --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="mb-5 flex items-center gap-3">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <i class="fas fa-plus-circle text-sm"></i>
                </span>
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">Ajouter un poste</h2>
                    <p class="text-xs text-slate-400">Le poste sera disponible dans le formulaire agent.</p>
                </div>
            </div>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.postes.store') }}" class="space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Fonction <span class="text-rose-500">*</span>
                    </label>
                    <select name="fonction" required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm focus:border-violet-400 focus:outline-none focus:ring-1 focus:ring-violet-400">
                        <option value="">— Sélectionner —</option>
                        @foreach($fonctionsDisponibles as $clé => $label)
                            <option value="{{ $clé }}" @selected(old('fonction') === $clé)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('fonction')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Intitulé du poste <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="libelle" value="{{ old('libelle') }}" required maxlength="150"
                           placeholder="Ex: Caissier prestataire, Chargé de sécurité…"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm focus:border-violet-400 focus:outline-none focus:ring-1 focus:ring-violet-400">
                    @error('libelle')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-violet-600 py-2.5 text-xs font-black uppercase tracking-wider text-white shadow-sm transition hover:bg-violet-700">
                    <i class="fas fa-plus"></i> Ajouter
                </button>
            </form>
        </div>

        {{-- Liste des postes --}}
        <div class="space-y-4">
            @forelse($fonctionsDisponibles as $clé => $label)
                @php $items = $postes->get($clé, collect()); @endphp
                <div class="rounded-2xl bg-white p-6 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl
                            {{ $clé === 'Agent' ? 'bg-sky-50 text-sky-600' : 'bg-amber-50 text-amber-600' }}">
                            <i class="fas {{ $clé === 'Agent' ? 'fa-user' : 'fa-user-tie' }} text-xs"></i>
                        </span>
                        <div>
                            <h3 class="text-sm font-black text-slate-800">{{ $label }}</h3>
                            <p class="text-xs text-slate-400">{{ $items->count() }} poste(s) configuré(s)</p>
                        </div>
                    </div>

                    @if($items->isEmpty())
                        <p class="rounded-xl bg-slate-50 py-4 text-center text-sm text-slate-400">
                            Aucun poste — ajoutez-en ci-contre.
                        </p>
                    @else
                        <div class="space-y-1.5">
                            @foreach($items as $poste)
                                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-2.5">
                                    <span class="text-sm font-semibold text-slate-700">{{ $poste->libelle }}</span>
                                    <form method="POST" action="{{ route('admin.postes.destroy', $poste) }}"
                                          onsubmit="return confirm('Supprimer « {{ $poste->libelle }} » ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-100 hover:text-rose-500">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400">Aucune fonction disponible.</p>
            @endforelse
        </div>
    </div>

</div>
</div>
@endsection
