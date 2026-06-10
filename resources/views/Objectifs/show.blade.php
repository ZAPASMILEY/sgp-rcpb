@extends($layout ?? 'layouts.app')
@section('title', $fiche->titre.' | '.config('app.name', 'SGP-RCPB'))

@section('content')
@php
    $statut = $fiche->statut ?? 'en_attente';
    $sc = match($statut) {
        'acceptee'  => ['label'=>'Acceptée',   'bg'=>'bg-indigo-100', 'text'=>'text-indigo-700', 'dot'=>'bg-indigo-500', 'border'=>'border-indigo-200'],
        'refusee'   => ['label'=>'Refusée',    'bg'=>'bg-rose-100',   'text'=>'text-rose-700',   'dot'=>'bg-rose-500',   'border'=>'border-rose-200'],
        'contesté'  => ['label'=>'Contestée',  'bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'dot'=>'bg-orange-500', 'border'=>'border-orange-200'],
        'brouillon' => ['label'=>'Brouillon',  'bg'=>'bg-slate-100',  'text'=>'text-slate-600',  'dot'=>'bg-slate-400',  'border'=>'border-slate-200'],
        default     => ['label'=>'En attente', 'bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'dot'=>'bg-amber-400',  'border'=>'border-amber-200'],
    };
    $avancement    = (int) ($fiche->avancement_percentage ?? 0);
    $progressColor = $avancement >= 75 ? 'bg-emerald-500' : ($avancement >= 40 ? 'bg-sky-500' : ($avancement > 0 ? 'bg-amber-400' : 'bg-slate-200'));
    $echeance      = $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance) : null;
    $expired       = $echeance && $echeance->isPast();
    $isPending     = $statut === 'en_attente' || $statut === 'soumis';
    // $isAssignee peut être passé explicitement (pour les fiches Agent::class) ou calculé
    $isOwnFiche = isset($isAssignee)
        ? (bool) $isAssignee
        : ($fiche->assignable_type === \App\Models\User::class && (int) $fiche->assignable_id === auth()->id());
@endphp

<div class="min-h-screen bg-[#f1f5f9] pb-10">
    {{-- Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 px-6 py-8 lg:px-10">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.25em] text-indigo-200">Consultation Fiche · Objectifs</p>
                <h1 class="mt-1 text-2xl font-black text-white leading-tight">{{ $fiche->titre }}</h1>
                <p class="mt-0.5 text-sm text-indigo-100/80">Année {{ $fiche->annee?->annee ?? $fiche->annee_id }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-bold text-white ring-1 ring-white/20">
                    <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>
                    {{ $sc['label'] }}
                </span>
                <a href="{{ $backRoute ?? '#' }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white ring-1 ring-white/20 transition hover:bg-white/20">
                    <i class="fas fa-arrow-left text-[10px]"></i> Retour
                </a>
            </div>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">
        <div class="flex flex-col gap-5">
            @if (session('status'))
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-semibold text-emerald-700">
                    <i class="fas fa-circle-check"></i> {{ session('status') }}
                </div>
            @endif

            {{-- Bannière de contestation active --}}
            @if ($statut === 'contesté' && $isOwnFiche)
                <div class="flex items-center gap-4 rounded-[24px] border-2 border-orange-200 bg-orange-50 px-6 py-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-orange-100 text-orange-600">
                        <i class="fas fa-flag text-lg"></i>
                    </div>
                    <div>
                        <p class="font-black text-orange-900">Fiche contestée</p>
                        <p class="mt-0.5 text-sm text-orange-700">Vous avez contesté un ou plusieurs objectifs. Votre supérieur a été notifié.</p>
                    </div>
                </div>
            @endif

            {{-- Actions de Validation/Refus (S'affiche si requis) --}}
            @if ($isPending && $isOwnFiche && isset($statusRoute))
                <div class="flex flex-col gap-4 rounded-[24px] border-2 border-amber-200 bg-amber-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                            <i class="fas fa-hourglass-half text-xl"></i>
                        </div>
                        <div>
                            <p class="font-black text-amber-900">Validation requise</p>
                            <p class="mt-0.5 text-sm text-amber-700">Examinez ces objectifs avant de donner votre réponse.</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button type="button"
                                onclick="sgpOpenMotifModal('refus', '{{ route($statusRoute, $fiche) }}', null)"
                                class="inline-flex items-center gap-2 rounded-xl border-2 border-rose-200 bg-white px-5 py-2.5 text-sm font-black text-rose-600 transition hover:bg-rose-50">
                            <i class="fas fa-times text-xs"></i> Refuser
                        </button>
                        <form action="{{ route($statusRoute, $fiche) }}" method="POST">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="accepter">
                            <button type="submit" onclick="return confirm('Valider ce contrat d\'objectifs ?')"
                                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-black text-white shadow-md shadow-indigo-200 transition hover:bg-indigo-700">
                                <i class="fas fa-check text-xs"></i> Accepter la fiche
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Assignée le</p>
                    <p class="mt-2 text-lg font-black text-slate-900">{{ $fiche->date ? \Carbon\Carbon::parse($fiche->date)->format('d/m/Y') : '—' }}</p>
                </div>
                <div class="rounded-[20px] border {{ $expired ? 'border-rose-200 bg-rose-50' : 'border-slate-100 bg-white' }} px-5 py-4 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Échéance</p>
                    <p class="mt-2 text-lg font-black {{ $expired ? 'text-rose-600' : 'text-slate-900' }}">{{ $echeance ? $echeance->format('d/m/Y') : '—' }}</p>
                    @if ($expired)<p class="mt-0.5 text-[10px] font-bold text-rose-500">Dépassée</p>@endif
                </div>
                <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Avancement global</p>
                    <p class="mt-2 text-2xl font-black text-slate-900">{{ $avancement }}<span class="text-sm font-bold text-slate-400">%</span></p>
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full transition-all {{ $progressColor }}" style="width: {{ $avancement }}%"></div>
                    </div>
                </div>
                <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Date validation</p>
                    @if ($fiche->date_validation)
                        <p class="mt-2 text-lg font-black text-emerald-700">{{ \Carbon\Carbon::parse($fiche->date_validation)->format('d/m/Y') }}</p>
                        <p class="mt-0.5 text-[10px] font-bold text-emerald-500"><i class="fas fa-circle-check mr-1"></i>Validée</p>
                    @else
                        <p class="mt-2 text-lg font-black text-slate-400">—</p>
                    @endif
                </div>
                <div class="rounded-[20px] border border-slate-100 bg-white px-5 py-4 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">Statut</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border {{ $sc['border'] }} {{ $sc['bg'] }} px-3 py-1 text-xs font-black {{ $sc['text'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $sc['dot'] }}"></span>{{ $sc['label'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Objectifs List --}}
            <div class="overflow-hidden rounded-[24px] bg-white shadow-sm ring-1 ring-slate-100">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <p class="text-sm font-black text-slate-800">
                        Objectifs assignés <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-500">{{ $fiche->objectifs->count() }}</span>
                    </p>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                        <i class="fas fa-bullseye"></i>
                    </span>
                </div>
                <div class="divide-y divide-slate-50 px-6 py-2">
                    @foreach ($fiche->objectifs as $index => $objectif)
                        @php
                            $contested  = ($objectif->statut ?? 'normal') === 'contesté';
                            $ligneAv    = (int) ($objectif->avancement_percentage ?? 0);
                            $ligneColor = $ligneAv >= 75 ? 'bg-emerald-500' : ($ligneAv >= 40 ? 'bg-sky-500' : ($ligneAv > 0 ? 'bg-amber-400' : 'bg-slate-200'));
                        @endphp
                        <div class="flex items-start gap-4 py-4 rounded-xl {{ $contested ? 'bg-rose-50 -mx-2 px-2' : '' }}">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-sm font-black ring-1 {{ $contested ? 'bg-rose-100 text-rose-600 ring-rose-200' : 'bg-indigo-50 text-indigo-600 ring-indigo-100' }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="flex-1 min-w-0 pt-1">
                                <p class="text-sm leading-relaxed {{ $contested ? 'text-rose-700 font-semibold' : 'text-slate-700' }}">{{ $objectif->description }}</p>

                                @if ($contested)
                                    <p class="mt-1 text-[10px] font-bold text-rose-500"><i class="fas fa-flag mr-1"></i>Contesté</p>
                                    @if ($objectif->motif)
                                        <p class="mt-0.5 text-[11px] text-rose-600 italic">« {{ $objectif->motif }} »</p>
                                    @endif
                                @else
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="flex-1 h-1.5 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full transition-all {{ $ligneColor }}" style="width: {{ $ligneAv }}%"></div>
                                        </div>
                                        <span class="shrink-0 text-[11px] font-black text-slate-600 w-8 text-right">{{ $ligneAv }}%</span>
                                    </div>
                                    
                                    {{-- Mise à jour de l'avancement autonome --}}
                                    @if ($statut === 'acceptee' && $isOwnFiche && isset($avancementRoute))
                                        <form method="POST" action="{{ route($avancementRoute, [$fiche, $objectif]) }}" class="mt-2">
                                            @csrf @method('PATCH')
                                            <select name="avancement_percentage" onchange="this.form.submit()"
                                                    class="w-full max-w-[160px] rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold text-slate-700 outline-none focus:ring-1 focus:ring-indigo-400 cursor-pointer">
                                                @for ($p = 0; $p <= 100; $p += 5)
                                                    <option value="{{ $p }}" @selected($ligneAv === $p)>{{ $p }}%</option>
                                                @endfor
                                            </select>
                                        </form>
                                    @endif
                                @endif
                            </div>

                            {{-- Bouton de contestation ligne par ligne (exclusif avec le refus global) --}}
                            @if (in_array($statut, ['en_attente', 'soumis', 'contesté', null]) && !$contested && $isOwnFiche && isset($contesterRoute))
                                <button type="button"
                                        onclick="sgpOpenMotifModal('contestation', '{{ route($contesterRoute, [$fiche, $objectif]) }}', {{ $index + 1 }})"
                                        class="shrink-0 mt-1 inline-flex items-center gap-1.5 rounded-xl border border-orange-200 bg-orange-50 px-3 py-1.5 text-xs font-black text-orange-600 transition hover:bg-orange-100">
                                    <i class="fas fa-flag text-[10px]"></i> Contester
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Bannière motif de refus (visible par l'assignateur) --}}
            @if ($statut === 'refusee' && $fiche->motif_refus)
                <div class="flex items-start gap-4 rounded-[24px] border-2 border-rose-200 bg-rose-50 px-6 py-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                        <i class="fas fa-comment-slash text-lg"></i>
                    </div>
                    <div>
                        <p class="font-black text-rose-900">Motif du refus</p>
                        <p class="mt-0.5 text-sm text-rose-700 italic">« {{ $fiche->motif_refus }} »</p>
                    </div>
                </div>
            @endif

            {{-- Footer Actions --}}
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-[24px] bg-white px-6 py-4 shadow-sm ring-1 ring-slate-100">
                <a href="{{ $backRoute ?? '#' }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-600 shadow-sm transition hover:border-slate-300">
                    <i class="fas fa-arrow-left text-xs"></i> Retour
                </a>
                <div class="flex flex-wrap items-center gap-2">
                    {{-- Supprimer : assignateur, fiche non acceptée --}}
                    @if (!$isOwnFiche && isset($destroyRoute) && $statut !== 'acceptee')
                        <form method="POST" action="{{ route($destroyRoute, $fiche) }}"
                              onsubmit="return confirm('Supprimer définitivement cette fiche d\'objectifs ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-black text-rose-600 shadow-sm transition hover:bg-rose-100">
                                <i class="fas fa-trash text-xs"></i> Supprimer
                            </button>
                        </form>
                    @endif
                    {{-- Soumettre : assignateur, brouillon uniquement --}}
                    @if (!$isOwnFiche && isset($soumettreRoute) && $statut === 'brouillon')
                        <form method="POST" action="{{ route($soumettreRoute, $fiche) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-violet-700">
                                <i class="fas fa-paper-plane text-xs"></i> Soumettre la fiche
                            </button>
                        </form>
                    @endif
                    {{-- Modifier : assignateur, fiche modifiable --}}
                    @if (!$isOwnFiche && isset($editRoute) && in_array($statut, ['brouillon', 'contesté', 'refusee']))
                        <a href="{{ route($editRoute, $fiche) }}" class="inline-flex items-center gap-2 rounded-xl border-2 border-orange-200 bg-orange-50 px-4 py-2.5 text-sm font-black text-orange-700 shadow-sm transition hover:bg-orange-100">
                            <i class="fas fa-pen text-xs"></i>
                            {{ $statut === 'contesté' ? 'Réviser les objectifs' : 'Modifier la fiche' }}
                        </a>
                    @endif
                    {{-- PDF : assignataire toujours, assignateur si avancement = 0 --}}
                    @if (isset($pdfRoute) && ($isOwnFiche || $avancement === 0))
                        <a href="{{ route($pdfRoute, $fiche) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-indigo-700">
                            <i class="fas fa-file-pdf text-xs"></i> Télécharger PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Modale motif refus / contestation ─────────────────────────────────── --}}
<div id="sgp-motif-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm px-4"
     onclick="if(event.target===this)sgpCloseMotifModal()">
    <div class="w-full max-w-md rounded-[24px] bg-white shadow-2xl overflow-hidden">
        <div id="sgp-modal-header" class="px-6 py-5 border-b border-slate-100">
            <p id="sgp-modal-title" class="text-lg font-black text-slate-900"></p>
            <p id="sgp-modal-subtitle" class="mt-0.5 text-sm text-slate-500"></p>
        </div>
        <div class="px-6 py-5">
            <label class="block text-xs font-black uppercase tracking-[0.14em] text-slate-500 mb-2">
                Motif <span class="text-red-500">*</span>
            </label>
            <textarea id="sgp-motif-input" rows="4" maxlength="1000"
                      placeholder="Expliquez votre décision..."
                      class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none resize-none focus:border-indigo-400 focus:bg-white transition"></textarea>
            <p id="sgp-motif-error" class="mt-1 hidden text-xs font-bold text-rose-500">Ce champ est obligatoire.</p>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50">
            <button type="button" onclick="sgpCloseMotifModal()"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-600 transition hover:bg-slate-50">
                Annuler
            </button>
            <button type="button" id="sgp-modal-confirm"
                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-black text-white shadow-sm transition">
                <i id="sgp-modal-icon" class="text-xs"></i>
                <span id="sgp-modal-btn-label"></span>
            </button>
        </div>
    </div>
</div>

<form id="sgp-motif-form" method="POST" class="hidden">
    @csrf @method('PATCH')
    <input type="hidden" id="sgp-form-action" name="action">
    <input type="hidden" id="sgp-form-motif-refus" name="motif_refus">
    <input type="hidden" id="sgp-form-motif" name="motif">
</form>

@endsection

@push('scripts')
<script>
    let _sgpModalMode = null;

    function sgpOpenMotifModal(mode, action, ligneNum) {
        _sgpModalMode = mode;
        const modal    = document.getElementById('sgp-motif-modal');
        const title    = document.getElementById('sgp-modal-title');
        const subtitle = document.getElementById('sgp-modal-subtitle');
        const btn      = document.getElementById('sgp-modal-confirm');
        const icon     = document.getElementById('sgp-modal-icon');
        const label    = document.getElementById('sgp-modal-btn-label');

        document.getElementById('sgp-motif-input').value = '';
        document.getElementById('sgp-motif-error').classList.add('hidden');
        document.getElementById('sgp-motif-form').action = action;

        if (mode === 'refus') {
            title.textContent    = 'Refuser la fiche';
            subtitle.textContent = 'Indiquez le motif du refus pour que l\'assignateur puisse réviser la fiche.';
            btn.className        = 'inline-flex items-center gap-2 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-rose-700';
            icon.className       = 'fas fa-times text-xs';
            label.textContent    = 'Confirmer le refus';
        } else {
            title.textContent    = 'Contester l\'objectif #' + ligneNum;
            subtitle.textContent = 'Expliquez pourquoi vous contestez cet objectif.';
            btn.className        = 'inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-black text-white shadow-sm transition hover:bg-orange-600';
            icon.className       = 'fas fa-flag text-xs';
            label.textContent    = 'Contester';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('sgp-motif-input').focus();
    }

    function sgpCloseMotifModal() {
        const modal = document.getElementById('sgp-motif-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('sgp-modal-confirm').addEventListener('click', function () {
        const motif = document.getElementById('sgp-motif-input').value.trim();
        if (!motif) {
            document.getElementById('sgp-motif-error').classList.remove('hidden');
            return;
        }
        if (_sgpModalMode === 'refus') {
            document.getElementById('sgp-form-action').value      = 'refuser';
            document.getElementById('sgp-form-motif-refus').value = motif;
            document.getElementById('sgp-form-motif').value       = '';
        } else {
            document.getElementById('sgp-form-action').value      = '';
            document.getElementById('sgp-form-motif').value       = motif;
            document.getElementById('sgp-form-motif-refus').value = '';
        }
        document.getElementById('sgp-motif-form').submit();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') sgpCloseMotifModal();
    });
</script>
@endpush