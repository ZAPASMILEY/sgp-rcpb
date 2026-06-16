@extends($layout ?? 'layouts.app')

@section('title', 'Validation des formations | SGP-RCPB')

@section('content')
<div class="min-h-screen bg-[#f1f5f9] px-4 pb-10 pt-4 lg:px-8">
<div class="flex flex-col gap-6">

    {{-- En-tête --}}
    <header class="admin-panel px-6 py-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Formations</p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950">Validation des formations</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $enAttente->count() }} formation(s) en attente de décision
                </p>
            </div>
        </div>
    </header>

    @if(session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
            <i class="fas fa-check-circle mr-2"></i>{{ session('status') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-semibold text-rose-700">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($enAttente->isEmpty())
        {{-- Aucune formation en attente --}}
        <div class="admin-panel px-6 py-16 text-center">
            <i class="fas fa-check-circle text-5xl text-emerald-300"></i>
            <p class="mt-4 text-base font-black text-slate-700">Aucune formation en attente</p>
            <p class="mt-1 text-sm text-slate-400">Toutes les formations soumises ont été traitées.</p>
        </div>
    @else
        <div class="overflow-hidden rounded-[24px] border-2 border-amber-200 bg-amber-50 shadow-sm">
            <div class="flex items-center gap-3 border-b border-amber-200 bg-amber-100/60 px-6 py-4">
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-white">
                    <i class="fas fa-clock text-sm"></i>
                </div>
                <div>
                    <p class="font-black text-amber-900">Formations en attente de validation</p>
                    <p class="text-xs text-amber-700">{{ $enAttente->count() }} formation(s) soumise(s) par des agents</p>
                </div>
            </div>

            <div class="divide-y divide-amber-100">
                @foreach($enAttente as $f)
                <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                    {{-- Infos formation --}}
                    <div class="flex items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-200 text-sm font-black text-amber-800">
                            {{ strtoupper(substr($f->agent->prenom ?? 'A', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900">{{ $f->theme }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                <span class="font-semibold text-slate-700">
                                    {{ trim(($f->agent->prenom ?? '') . ' ' . ($f->agent->nom ?? '')) }}
                                </span>
                                · {{ $f->agent->role ?? '' }}
                            </p>
                            <p class="mt-0.5 text-xs text-slate-400">
                                {{ ucfirst($f->type ?? 'interne') }}
                                · {{ $f->domaine_label }}
                                · {{ $f->date_debut->format('d/m/Y') }} – {{ $f->date_fin->format('d/m/Y') }}
                                · {{ $f->duree_heures }}h
                            </p>
                            @if($f->attestation_path)
                                <a href="{{ Storage::url($f->attestation_path) }}" target="_blank"
                                   class="mt-1 inline-flex items-center gap-1 text-xs font-bold text-amber-700 hover:underline">
                                    <i class="fas fa-paperclip text-[10px]"></i> Voir l'attestation
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        @if ($f->agent_id && $f->agent_id === $monAgentId)
                            {{-- Formation appartenant à l'utilisateur connecté : actions bloquées --}}
                            <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-xs font-bold text-slate-400">
                                <i class="fas fa-lock text-[10px]"></i> Votre propre formation
                            </span>
                        @else
                            {{-- Valider --}}
                            <form method="POST" action="{{ route('gerer.formations.valider', $f) }}">
                                @csrf
                                <input type="hidden" name="decision" value="validee">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-700">
                                    <i class="fas fa-check text-[10px]"></i> Valider
                                </button>
                            </form>
                            {{-- Refuser (ouvre modal) --}}
                            <button type="button"
                                    onclick="document.getElementById('refus-modal-{{ $f->id }}').classList.remove('hidden')"
                                    class="inline-flex items-center gap-1.5 rounded-xl border border-rose-300 bg-white px-4 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50">
                                <i class="fas fa-times text-[10px]"></i> Refuser
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Modal refus --}}
                <div id="refus-modal-{{ $f->id }}"
                     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
                    <div class="w-full max-w-md rounded-[24px] bg-white p-6 shadow-2xl">
                        <p class="font-black text-slate-900">Motif du refus</p>
                        <p class="mt-0.5 text-sm text-slate-500">Formation : <strong>{{ $f->theme }}</strong></p>
                        <p class="mt-0.5 text-xs text-slate-400">
                            Agent : {{ trim(($f->agent->prenom ?? '') . ' ' . ($f->agent->nom ?? '')) }}
                        </p>
                        <form method="POST" action="{{ route('gerer.formations.valider', $f) }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="decision" value="refusee">
                            <textarea name="motif_refus" rows="3" required
                                      placeholder="Expliquer le motif du refus…"
                                      class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-rose-400 focus:bg-white"></textarea>
                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button"
                                        onclick="document.getElementById('refus-modal-{{ $f->id }}').classList.add('hidden')"
                                        class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50">
                                    Annuler
                                </button>
                                <button type="submit"
                                        class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700">
                                    Confirmer le refus
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
</div>
@endsection
