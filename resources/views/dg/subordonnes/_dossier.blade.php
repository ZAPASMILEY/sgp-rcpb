{{-- Bloc réutilisable : fiches d'objectifs + évaluations d'un subordonné --}}
{{-- Variables attendues : $subordonné (User|null), $fiches (Collection), $evaluations (Collection) --}}

@if (!$subordonné)
    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
        <i class="fas fa-user-slash text-3xl text-slate-300"></i>
        <p class="mt-3 text-sm font-semibold text-slate-400">Aucun compte configuré pour ce rôle.</p>
        <p class="mt-1 text-xs text-slate-400">Contactez l'administrateur pour créer le compte.</p>
    </div>
@else
    {{-- Fiches d'objectifs --}}
    <section class="admin-panel px-6 py-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-900">Fiches d'objectifs</h2>
                <p class="mt-1 text-xs text-slate-400">Objectifs assignés à {{ $subordonné->name }}</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                {{ $fiches->count() }} fiche(s)
            </span>
        </div>

        @if ($fiches->isEmpty())
            <p class="mt-4 text-sm text-slate-400">Aucune fiche d'objectifs pour ce collaborateur.</p>
        @else
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Titre</th>
                            <th class="px-4 py-3">Année</th>
                            <th class="px-4 py-3">Échéance</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Avancement</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fiches as $fiche)
                            @php
                                $statutColors = [
                                    'en_attente' => 'bg-amber-100 text-amber-700',
                                    'acceptee'   => 'bg-emerald-100 text-emerald-700',
                                    'refusee'    => 'bg-rose-100 text-rose-700',
                                ];
                                $statutLabels = [
                                    'en_attente' => 'En attente',
                                    'acceptee'   => 'Acceptée',
                                    'refusee'    => 'Refusée',
                                ];
                                $avg = $fiche->objectifs->avg('avancement_percentage') ?? 0;
                            @endphp
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold">{{ $fiche->titre }}</td>
                                <td class="px-4 py-3">{{ $fiche->annee }}</td>
                                <td class="px-4 py-3">{{ $fiche->date_echeance ? \Carbon\Carbon::parse($fiche->date_echeance)->format('d/m/Y') : '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-bold {{ $statutColors[$fiche->statut] ?? 'bg-slate-100 text-slate-500' }}">
                                        {{ $statutLabels[$fiche->statut] ?? ucfirst($fiche->statut ?? '-') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="h-2 w-24 rounded-full bg-slate-200">
                                            <div class="h-2 rounded-full bg-emerald-500" style="width: {{ round($avg) }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-600">{{ round($avg) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- Évaluations --}}
    <section class="admin-panel px-6 py-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-black text-slate-900">Évaluations</h2>
                <p class="mt-1 text-xs text-slate-400">Évaluations enregistrées pour {{ $subordonné->name }}</p>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">
                {{ $evaluations->count() }} évaluation(s)
            </span>
        </div>

        @if ($evaluations->isEmpty())
            <p class="mt-4 text-sm text-slate-400">Aucune évaluation pour ce collaborateur.</p>
        @else
            <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-full text-left text-sm text-slate-700">
                    <thead class="bg-slate-50 text-xs uppercase tracking-[0.12em] text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Période</th>
                            <th class="px-4 py-3">Note finale</th>
                            <th class="px-4 py-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($evaluations as $eval)
                            @php
                                $sColors = ['brouillon' => 'bg-slate-100 text-slate-600', 'soumis' => 'bg-amber-100 text-amber-700', 'valide' => 'bg-emerald-100 text-emerald-700'];
                                $sLabels = ['brouillon' => 'Brouillon', 'soumis' => 'Soumise', 'valide' => 'Validée'];
                            @endphp
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="px-4 py-3">{{ $eval->date_debut->format('m/Y') }} – {{ $eval->date_fin->format('m/Y') }}</td>
                                <td class="px-4 py-3 font-semibold">{{ number_format((float) $eval->note_finale, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-1 text-xs font-bold {{ $sColors[$eval->statut] ?? 'bg-slate-100 text-slate-500' }}">
                                        {{ $sLabels[$eval->statut] ?? ucfirst($eval->statut ?? '-') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endif
