<div class="space-y-4">
    <h3 class="text-lg font-semibold text-slate-800 mb-2">Fiches d'objectifs</h3>
    @if($fiches->isEmpty())
        <p class="text-slate-500">Aucune fiche d'objectifs trouvée.</p>
    @else
        <ul class="divide-y divide-slate-200">
            @foreach($fiches as $fiche)
                <li class="py-2 flex items-center justify-between">
                    <span>{{ $fiche->titre }} ({{ $fiche->annee }})</span>
                    <a href="{{ route('dg.objectifs.show', $fiche) }}" class="ent-btn ent-btn-soft">Voir</a>
                </li>
            @endforeach
        </ul>
    @endif

    <h3 class="text-lg font-semibold text-slate-800 mb-2 mt-6">Évaluations</h3>
    @if($evaluations->isEmpty())
        <p class="text-slate-500">Aucune évaluation trouvée.</p>
    @else
        <ul class="divide-y divide-slate-200">
            @foreach($evaluations as $evaluation)
                <li class="py-2 flex items-center justify-between">
                    <span>Période : {{ $evaluation->date_debut->format('m/Y') }} - {{ $evaluation->date_fin->format('m/Y') }}</span>
                    <a href="{{ route('dg.evaluations.show', $evaluation) }}" class="ent-btn ent-btn-soft">Voir</a>
                </li>
            @endforeach
        </ul>
    @endif
</div>
