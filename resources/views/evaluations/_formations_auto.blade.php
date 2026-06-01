{{--
    Partial : Formations liées à une évaluation.
    Source primaire : JSON snapshot (evaluation_identifications.formations) saisi par l'évaluateur.
    Fallback       : module Formation RH (formations chevauchant la période de l'évaluation).

    Requiert : $evaluation (Evaluation model, chargé avec evaluable si possible)
--}}
@php
    use App\Models\Agent;
    use App\Models\Formation;

    $jsonFormations = $evaluation->identification?->formations ?? [];
@endphp

@if (!empty($jsonFormations))
    {{-- Source primaire : snapshot JSON saisi sur le formulaire d'évaluation --}}
    @foreach ($jsonFormations as $row)
        <tr class="border-t border-slate-200">
            <td class="px-3 py-2 whitespace-nowrap text-xs text-slate-500">
                {{ $row['periode'] ?? '—' }}
            </td>
            <td class="px-3 py-2">{{ $row['libelle'] ?? '—' }}</td>
            <td class="px-3 py-2">
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">
                    {{ $row['domaine'] ?? '—' }}
                </span>
            </td>
        </tr>
    @endforeach
@else
    {{-- Fallback : module Formation RH (formations enregistrées par le RH) --}}
    @php
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
@endif
