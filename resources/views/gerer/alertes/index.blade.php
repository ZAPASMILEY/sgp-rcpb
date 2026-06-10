@extends($layout)

@section('title', 'Alertes | '.config('app.name', 'SGP-RCPB'))

@section('content')
<div class="min-h-screen bg-[#f1f5f9] pb-12">

    @if(session('status'))
        <div id="gerer-status-msg" class="fixed right-6 top-6 z-50 flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white px-5 py-4 shadow-2xl">
            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                <i class="fas fa-check"></i>
            </div>
            <p class="text-sm font-bold text-slate-700">{{ session('status') }}</p>
        </div>
        <script>setTimeout(() => document.getElementById('gerer-status-msg')?.remove(), 3000);</script>
    @endif

    <div class="relative overflow-hidden px-6 py-8 lg:px-10"
         style="background:linear-gradient(135deg,#0c4a6e 0%,#0369a1 50%,#0284c7 100%)">
        <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
        <div class="relative flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl text-white shadow ring-1 ring-white/20">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-sky-200">Gestion · RCPB</p>
                    <h1 class="mt-0.5 text-2xl font-black text-white">Alertes</h1>
                    <p class="mt-0.5 text-sm text-sky-100/75">Gestion des alertes du système</p>
                </div>
            </div>
            <button onclick="document.getElementById('modal-alerte-create').classList.remove('hidden')"
                    class="rounded-xl bg-white/20 px-4 py-2.5 text-sm font-bold text-white ring-1 ring-white/30 transition hover:bg-white/30">
                <i class="fas fa-plus mr-1"></i> Nouvelle alerte
            </button>
        </div>
    </div>

    <div class="px-4 pt-6 lg:px-8">

        {{-- Liste --}}
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            @if($alertes->isEmpty())
                <div class="px-8 py-16 text-center">
                    <i class="fas fa-bell-slash text-slate-200 text-5xl mb-4 block"></i>
                    <p class="text-sm font-semibold text-slate-400">Aucune alerte.</p>
                </div>
            @else
            <div class="overflow-x-auto overflow-y-auto" style="max-height:480px">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10">
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Titre</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Type</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Priorité</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Statut</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Créé par</th>
                            <th class="px-4 py-3 text-left text-[11px] font-black uppercase tracking-wide text-slate-500">Date</th>
                            <th class="px-4 py-3 text-right text-[11px] font-black uppercase tracking-wide text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($alertes as $alerte)
                        @php
                            $typeColors = [
                                'info'          => 'bg-blue-50 text-blue-700',
                                'avertissement' => 'bg-amber-50 text-amber-700',
                                'critique'      => 'bg-red-50 text-red-700',
                                'securite'      => 'bg-rose-50 text-rose-700',
                            ];
                            $prioriteColors = [
                                'faible'    => 'bg-slate-100 text-slate-600',
                                'normale'   => 'bg-sky-50 text-sky-700',
                                'haute'     => 'bg-orange-50 text-orange-700',
                                'critique'  => 'bg-red-50 text-red-700',
                            ];
                            $statutColors = [
                                'active'  => 'bg-emerald-50 text-emerald-700',
                                'resolue' => 'bg-slate-100 text-slate-500',
                                'ignoree' => 'bg-slate-50 text-slate-400',
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-800 max-w-[200px] truncate" title="{{ $alerte->titre }}">{{ $alerte->titre }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $typeColors[$alerte->type] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($alerte->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $prioriteColors[$alerte->priorite] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($alerte->priorite) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-block rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $statutColors[$alerte->statut] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ ucfirst($alerte->statut) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">{{ $alerte->createur?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap">{{ $alerte->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($alerte->statut === 'active')
                                        <form method="POST" action="{{ route('gerer.alertes.statut', $alerte) }}" class="inline-flex">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="statut" value="resolue">
                                            <button type="submit" title="Résoudre"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-emerald-50 hover:text-emerald-500 transition">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('gerer.alertes.statut', $alerte) }}" class="inline-flex">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="statut" value="ignoree">
                                            <button type="submit" title="Ignorer"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-amber-50 hover:text-amber-500 transition">
                                                <i class="fas fa-eye-slash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('gerer.alertes.destroy', $alerte) }}"
                                          onsubmit="return confirm('Supprimer cette alerte ?')" class="inline-flex">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Supprimer"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-3 text-right text-xs text-slate-400">{{ $alertes->count() }} résultat(s)</div>
            @endif
        </div>
    </div>
</div>

{{-- Modal Créer Alerte --}}
<div id="modal-alerte-create" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm"
             onclick="document.getElementById('modal-alerte-create').classList.add('hidden')"></div>
        <div class="relative w-full max-w-lg rounded-2xl bg-white shadow-2xl flex flex-col" style="max-height:calc(100vh - 2rem)">

            {{-- Header fixe --}}
            <div class="flex shrink-0 items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="text-lg font-black text-slate-900">Nouvelle alerte</h2>
                <button onclick="document.getElementById('modal-alerte-create').classList.add('hidden')"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Corps scrollable --}}
            <div class="overflow-y-auto flex-1 px-6 py-5">
                <form method="POST" action="{{ route('gerer.alertes.store') }}" id="form-gerer-alerte-create">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Titre *</label>
                            <input type="text" name="titre" required
                                   class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:ring-0"
                                   placeholder="Titre de l'alerte">
                        </div>
                        <div>
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Message</label>
                            <textarea name="message" rows="3"
                                      class="mt-1.5 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:ring-0"
                                      placeholder="Description détaillée (optionnel)"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Type *</label>
                                <select name="type" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:ring-0">
                                    <option value="info">Information</option>
                                    <option value="avertissement">Avertissement</option>
                                    <option value="critique">Critique</option>
                                    <option value="securite">Sécurité</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Priorité *</label>
                                <select name="priorite" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 focus:border-sky-400 focus:ring-0">
                                    <option value="faible">Faible</option>
                                    <option value="normale" selected>Normale</option>
                                    <option value="haute">Haute</option>
                                    <option value="critique">Critique</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Footer fixe avec boutons --}}
            <div class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-100 px-6 py-4">
                <button type="button" onclick="document.getElementById('modal-alerte-create').classList.add('hidden')"
                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-500 hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit" form="form-gerer-alerte-create"
                        class="rounded-xl px-5 py-2.5 text-sm font-bold text-white transition"
                        style="background:#0284c7" onmouseover="this.style.background='#0369a1'" onmouseout="this.style.background='#0284c7'">
                    <i class="fas fa-paper-plane mr-1 text-xs"></i> Envoyer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
