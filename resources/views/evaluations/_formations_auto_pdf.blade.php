{{--
    Partial PDF : Formations automatiques liées à une évaluation (pour DomPDF).
    Même logique que _formations_auto mais sans classes Tailwind.
    Requiert : $evaluation (Evaluation model)
--}}
@php
    use App\Models\Agent;
    use App\Models\Formation;

    $evalAgentId = null;
    if (isset($evaluation)) {
        if ($evaluation->evaluable_type === Agent::class || $evaluation->evaluable_type === 'App\\Models\\Agent') {
            $evalAgentId = $evaluation->evaluable_id;
        } elseif ($evaluation->evaluable_type === \App\Models\User::class || $evaluation->evaluable_type === 'App\\Models\\User') {
            $evalUser    = $evaluation->evaluable ?? \App\Models\User::find($evaluation->evaluable_id);
            $evalAgentId = $evalUser?->agent_id;
        }
    }

    $autoFormations = ($evalAgentId && isset($evaluation->date_debut) && isset($evaluation->date_fin))
        ? Formation::where('agent_id', $evalAgentId)
            ->chevaucheEvaluation($evaluation->date_debut, $evaluation->date_fin)
            ->orderBy('date_debut')
            ->get()
        : collect();
@endphp

@forelse ($autoFormations as $f)
    <tr>
        <td>{{ $f->date_debut->translatedFormat('d M Y') }} – {{ $f->date_fin->translatedFormat('d M Y') }}</td>
        <td>{{ $f->titre }}</td>
        <td>{{ $f->domaine_label }}</td>
    </tr>
@empty
    <tr><td colspan="3">Aucune formation renseignée.</td></tr>
@endforelse
