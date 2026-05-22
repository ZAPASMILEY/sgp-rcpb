{{--
    Partial : Formations automatiques liées à une évaluation.
    Se base sur la période de l'évaluation et l'agent évalué pour afficher
    les formations issues du module Formation (créées par le RH).

    Requiert : $evaluation (Evaluation model, chargé avec evaluable si possible)
--}}
@php
    use App\Models\Agent;
    use App\Models\Formation;

    // Résoudre l'agent évalué
    $evalAgentId = null;

    if (isset($evaluation)) {
        if ($evaluation->evaluable_type === Agent::class || $evaluation->evaluable_type === 'App\\Models\\Agent') {
            $evalAgentId = $evaluation->evaluable_id;
        } elseif ($evaluation->evaluable_type === \App\Models\User::class || $evaluation->evaluable_type === 'App\\Models\\User') {
            // L'évalué est un User (DG, DGA...) — retrouver son agent via agent_id
            $evalUser    = $evaluation->evaluable ?? \App\Models\User::find($evaluation->evaluable_id);
            $evalAgentId = $evalUser?->agent_id;
        }
    }

    // Formations chevauchant la période de l'évaluation
    $autoFormations = ($evalAgentId && isset($evaluation->date_debut) && isset($evaluation->date_fin))
        ? Formation::where('agent_id', $evalAgentId)
            ->chevaucheEvaluation($evaluation->date_debut, $evaluation->date_fin)
            ->with('agent')
            ->orderBy('date_debut')
            ->get()
        : collect();
@endphp

@forelse ($autoFormations as $f)
    <tr class="border-t border-slate-200">
        <td class="px-3 py-2 whitespace-nowrap text-xs text-slate-500">
            {{ $f->date_debut->translatedFormat('d M Y') }} → {{ $f->date_fin->translatedFormat('d M Y') }}
        </td>
        <td class="px-3 py-2">{{ $f->theme }}</td>
        <td class="px-3 py-2">
            <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                {{ $f->domaine_label }}
            </span>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="3" class="px-3 py-3 text-slate-400">Aucune formation renseignée.</td>
    </tr>
@endforelse
