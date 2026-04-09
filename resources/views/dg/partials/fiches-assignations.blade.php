<div class="space-y-4">
    <h3 class="text-lg font-semibold text-slate-800 mb-2">Fiches d'objectifs assignées</h3>
    @if($fichesSubordonnes->isEmpty())
        <p class="text-slate-500">Aucune fiche assignée à vos subordonnés.</p>
    @else
        <ul class="divide-y divide-slate-200">
            @foreach($fichesSubordonnes as $fiche)
                <li class="py-2 flex items-center justify-between">
                    <span>{{ $fiche->titre }} ({{ $fiche->annee }}) - {{ $fiche->assignable_nom }}</span>
                    <a href="{{ route('dg.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft">Voir</a>
                </li>
            @endforeach
        </ul>
    @endif

    <h3 class="text-lg font-semibold text-slate-800 mb-2 mt-6">Évaluations assignées</h3>
    @if($evaluationsSubordonnes->isEmpty())
        <p class="text-slate-500">Aucune évaluation assignée à vos subordonnés.</p>
    @else
        <ul class="divide-y divide-slate-200">
            @foreach($evaluationsSubordonnes as $evaluation)
                <li class="py-2 flex items-center justify-between">
                    <span>Période : {{ $evaluation->date_debut->format('m/Y') }} - {{ $evaluation->date_fin->format('m/Y') }} - {{ $evaluation->assignable_nom }}</span>
                    <a href="{{ route('dg.evaluations.show', $evaluation) }}" class="ent-btn ent-btn-soft">Voir</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
