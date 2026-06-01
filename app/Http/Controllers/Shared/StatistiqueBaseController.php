<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Helpers\XlsxHelper;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contrôleur de base pour la page Statistiques du personnel.
 * Partagé entre RH et DG — chaque espace surcharge les 3 méthodes abstraites.
 */
abstract class StatistiqueBaseController extends Controller
{
    /** Nom de route (ex: 'rh.statistiques' ou 'dg.statistiques') */
    abstract protected function routeName(): string;

    /** Vue à rendre (ex: 'rh.statistiques.index' ou 'dg.statistiques.index') */
    abstract protected function viewName(): string;

    /** Préfixe du fichier XLSX exporté */
    abstract protected function csvFilenamePrefix(): string;

    // ────────────────────────────────────────────────────────────────────────

    public function __invoke(Request $request): View|\Illuminate\Http\Response|StreamedResponse
    {
        $annees = Annee::orderByDesc('annee')->get();

        $anneeSelectionnee = $request->filled('annee_id')
            ? $annees->find($request->integer('annee_id'))
            : (Annee::currentOpen() ?? $annees->first());

        $agents = collect();
        $s1     = null;
        $s2     = null;

        $type = $request->input('type', ''); // 'siege' | 'faitiere' | 'rcpb' | ''

        if ($anneeSelectionnee) {
            $semestres = $anneeSelectionnee->semestres->keyBy('numero');
            $s1 = $semestres->get(1);
            $s2 = $semestres->get(2);

            $agents = Agent::personnel()
                ->with([
                    'delegationTechnique',
                    'caisse.delegationTechnique',
                    'direction',
                    // Évaluations créées via User (Directeur, DGA, DG, Assistante)
                    'evaluationsPersonnel' => fn ($q) => $q
                        ->where('annee_id', $anneeSelectionnee->id)
                        ->where('statut', '!=', 'brouillon')
                        ->with(['semestre.annee', 'identification']),
                    // Évaluations créées directement sur Agent (Chef de Service/Guichet)
                    'evaluations' => fn ($q) => $q
                        ->where('annee_id', $anneeSelectionnee->id)
                        ->where('statut', '!=', 'brouillon')
                        ->with(['semestre.annee', 'identification']),
                ])
                // ── Filtres de périmètre ─────────────────────────────────────
                ->when($type === 'siege', fn ($q) => $q
                    ->where(fn ($sub) => $sub
                        ->whereNotNull('direction_id')
                        ->orWhereNotNull('entite_id')
                        ->orWhereHas('user', fn ($u) => $u->whereIn('role', ['DG', 'Assistante_Dg']))
                    )
                    ->whereNull('caisse_id')
                    ->whereNull('agence_id')
                    ->whereNull('guichet_id')
                    ->whereNull('delegation_technique_id')
                )
                ->when($type === 'faitiere', fn ($q) => $q
                    ->where(fn ($sub) => $sub
                        ->whereNotNull('direction_id')
                        ->orWhereNotNull('entite_id')
                        ->orWhereNotNull('delegation_technique_id')
                        ->orWhereHas('user', fn ($u) => $u->whereIn('role', ['DG', 'Assistante_Dg']))
                    )
                    ->whereNull('caisse_id')
                    ->whereNull('agence_id')
                    ->whereNull('guichet_id')
                )
                ->when($type === 'rcpb', fn ($q) => $q->where(fn ($sub) => $sub
                    ->whereNotNull('caisse_id')
                    ->orWhereNotNull('agence_id')
                    ->orWhereNotNull('guichet_id')
                ))
                // ── Filtres secondaires ──────────────────────────────────────
                ->when($request->filled('delegation_id'), fn ($q) => $q->where(fn ($sub) => $sub
                    ->where('delegation_technique_id', $request->integer('delegation_id'))
                    ->orWhereHas('caisse', fn ($c) => $c->where('delegation_technique_id', $request->integer('delegation_id')))
                ))
                ->when($request->filled('caisse_id'), fn ($q) => $q->where('caisse_id', $request->integer('caisse_id')))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $s = $request->string('search');
                    $q->where(fn ($sub) => $sub
                        ->where('nom', 'like', "%{$s}%")
                        ->orWhere('prenom', 'like', "%{$s}%")
                        ->orWhere('matricule', 'like', "%{$s}%")
                    );
                })
                ->orderBy('nom')->orderBy('prenom')
                ->get();
        }

        if ($request->boolean('export')) {
            return $this->exportCsv($agents, $anneeSelectionnee, $s1, $s2, $type);
        }

        $delegations = DelegationTechnique::orderBy('region')->orderBy('ville')->get();
        $caisses     = Caisse::orderBy('nom')->get();
        $routeName   = $this->routeName();

        return view($this->viewName(), compact(
            'annees', 'anneeSelectionnee', 'agents', 's1', 's2',
            'delegations', 'caisses', 'type', 'routeName'
        ));
    }

    // ────────────────────────────────────────────────────────────────────────

    private function exportCsv($agents, $annee, $s1, $s2, string $type): \Illuminate\Http\Response
    {
        $filename   = $this->csvFilenamePrefix() . ($annee?->annee ?? 'export') . '.xlsx';
        $isFaitiere = in_array($type, ['faitiere', 'siege']);

        $headers = ['Matricule', 'Nom et Prénom', 'Sexe', 'Fonction', 'Grade'];
        if ($type === 'siege')    $headers[] = 'Direction';
        elseif ($isFaitiere)      $headers[] = 'Structure';
        else                      $headers[] = 'Délégation';
        if (! $isFaitiere)        $headers[] = 'Caisse';
        $headers = array_merge($headers, ['Date de prise de fonction', 'Note S1', 'Note S2', 'Note Annuelle']);

        $rows = [];
        foreach ($agents as $agent) {
            $evals  = $agent->evaluations->merge($agent->evaluationsPersonnel)
                ->keyBy(fn ($e) => $e->semestre?->numero);
            $evalS1 = $s1 ? $evals->get(1) : null;
            $evalS2 = $s2 ? $evals->get(2) : null;
            $grade  = ($evalS1?->identification?->grade ?? $evalS2?->identification?->grade) ?? '—';
            $noteS1 = $evalS1?->note_finale !== null ? (float) number_format((float)$evalS1->note_finale, 2, '.', '') : '';
            $noteS2 = $evalS2?->note_finale !== null ? (float) number_format((float)$evalS2->note_finale, 2, '.', '') : '';
            $noteAn = ($noteS1 !== '' && $noteS2 !== '')
                ? (float) number_format(((float)$noteS1 + (float)$noteS2) / 2, 2, '.', '')
                : ($noteS1 !== '' ? $noteS1 : ($noteS2 !== '' ? $noteS2 : ''));

            if ($type === 'siege') {
                $col = $agent->direction?->nom ?? 'FCPB';
            } elseif ($isFaitiere) {
                $col = $agent->delegationTechnique
                    ? $agent->delegationTechnique->region . ' - ' . $agent->delegationTechnique->ville
                    : 'FCPB';
            } else {
                $col = $agent->delegationTechnique
                    ? $agent->delegationTechnique->region . ' - ' . $agent->delegationTechnique->ville
                    : ($agent->caisse?->delegationTechnique
                        ? $agent->caisse->delegationTechnique->region . ' - ' . $agent->caisse->delegationTechnique->ville
                        : '—');
            }

            $row = [
                $agent->matricule ?? '—',
                trim(($agent->prenom ?? '') . ' ' . ($agent->nom ?? '')),
                $agent->sexe ?? '—',
                $agent->poste ?? $agent->role ?? '—',
                $grade,
                $col,
            ];
            if (! $isFaitiere) $row[] = $agent->caisse?->nom ?? '—';
            $row[] = $agent->date_debut_fonction?->format('d/m/Y') ?? '—';
            $row[] = $noteS1;
            $row[] = $noteS2;
            $row[] = $noteAn;

            $rows[] = $row;
        }

        $content = XlsxHelper::build($headers, $rows, 'Statistiques');

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
