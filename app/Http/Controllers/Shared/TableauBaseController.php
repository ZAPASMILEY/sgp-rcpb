<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Annee;
use App\Models\Caisse;
use App\Models\DelegationTechnique;
use App\Models\Semestre;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Contrôleur de base pour la page Tableaux Excel personnalisés.
 * Partagé entre RH, DG et Personnel — chaque espace surcharge les méthodes abstraites.
 */
abstract class TableauBaseController extends Controller
{
    /** Valeurs de notes affichées */
    private const NOTE_ROWS = ['SN', 5, 6, 7, 8, 9, 10];

    public const TYPES = [
        'rapport_notes_par_delegation' => 'Notes par délégation',
        'rapport_notes_par_caisse'     => 'Notes par caisse',
        'distribution_notes'           => 'Distribution globale des notes',
        'notes_par_poste'              => 'Notes par poste/fonction',
        'notes_par_entite'             => 'Notes par entité/délégation',
        'effectif_par_sexe'            => 'Effectif par sexe',
        'effectif_par_fonction'        => 'Effectif par fonction',
    ];

    abstract protected function indexRoute(): string;
    abstract protected function exportRoute(): string;
    abstract protected function viewName(): string;

    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $annees      = Annee::orderByDesc('annee')->get();
        $delegations = DelegationTechnique::orderBy('region')->orderBy('ville')->get();
        $caisses     = Caisse::orderBy('nom')->get();

        $hasParams = $request->filled('type_analyse');
        $payload   = $hasParams ? $this->compute($request) : null;

        $indexRoute  = $this->indexRoute();
        $exportRoute = $this->exportRoute();

        return view($this->viewName(), compact(
            'annees', 'delegations', 'caisses', 'hasParams', 'payload',
            'indexRoute', 'exportRoute'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $payload  = $this->compute($request);
        $filename = 'rapport_' . date('Ymd_His') . '.xlsx';
        $content  = $this->buildXlsx($payload);

        return response()->streamDownload(
            static function () use ($content) { echo $content; },
            $filename,
            [
                'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control'       => 'no-cache, no-store',
                'Pragma'              => 'no-cache',
            ]
        );
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function compute(Request $request): array
    {
        $typeAnalyse = $request->input('type_analyse', 'rapport_notes_par_delegation');
        $anneeId     = $request->integer('annee_id');
        $semestreNum = $request->input('semestre', '1');
        $scope       = $request->input('scope') ?? '';
        $delegId     = $request->integer('delegation_id') ?: null;
        $caisseId    = $request->integer('caisse_id')    ?: null;

        $annee = $anneeId
            ? Annee::find($anneeId)
            : (Annee::currentOpen() ?? Annee::orderByDesc('annee')->first());

        $semLabel = match ($semestreNum) {
            '1' => 'SEMESTRE 1', '2' => 'SEMESTRE 2', 'annuel' => 'NOTE ANNUELLE', default => '',
        };
        $titrePrincipal = 'STATISTIQUES DES NOTES '.$semLabel.'  '.($annee?->annee ?? '');

        if (! $annee) {
            return ['titre' => $titrePrincipal, 'annee' => null,
                    'semestreNum' => $semestreNum, 'typeAnalyse' => $typeAnalyse,
                    'groupes' => collect(), 'isNotes' => true];
        }

        $s1 = Semestre::where('annee_id', $annee->id)->where('numero', 1)->first();
        $s2 = Semestre::where('annee_id', $annee->id)->where('numero', 2)->first();

        $agents = Agent::personnel()
            ->with([
                'delegationTechnique',
                'caisse.delegationTechnique',
                'evaluationsPersonnel' => fn ($q) => $q->where('annee_id', $annee->id)->where('statut', 'valide')->with('semestre.annee'),
                'evaluations' => fn ($q) => $q->where('annee_id', $annee->id)->where('statut', 'valide')->with('semestre.annee'),
            ])
            ->when($scope === 'faitiere', fn ($q) => $q
                ->where(fn ($s) => $s
                    ->whereNotNull('direction_id')
                    ->orWhereNotNull('entite_id')
                    ->orWhereNotNull('delegation_technique_id')
                    ->orWhereHas('user', fn ($u) => $u->whereIn('role', ['DG', 'Assistante_Dg']))
                )
                ->whereNull('caisse_id')->whereNull('agence_id')->whereNull('guichet_id')
            )
            ->when($scope === 'rcpb', fn ($q) => $q->where(fn ($s) => $s
                ->whereNotNull('caisse_id')->orWhereNotNull('agence_id')->orWhereNotNull('guichet_id')
            ))
            ->when($delegId, fn ($q) => $q->where(fn ($s) => $s
                ->where('delegation_technique_id', $delegId)
                ->orWhereHas('caisse', fn ($c) => $c->where('delegation_technique_id', $delegId))
            ))
            ->when($caisseId, fn ($q) => $q->where('caisse_id', $caisseId))
            ->get();

        $isNotes = in_array($typeAnalyse, [
            'rapport_notes_par_delegation', 'rapport_notes_par_caisse', 'distribution_notes'
        ]);

        $groupes = match ($typeAnalyse) {
            'rapport_notes_par_delegation' => $this->groupesParDelegation($agents, $s1, $s2, $semestreNum),
            'rapport_notes_par_caisse'     => $this->groupesParCaisse($agents, $s1, $s2, $semestreNum),
            'distribution_notes'           => collect([['nom' => 'ENSEMBLE DU RÉSEAU',
                                                        'rows' => $this->notesRows($agents, $s1, $s2, $semestreNum)]]),
            'notes_par_poste'              => collect([$this->groupeParPoste($agents, $s1, $s2, $semestreNum)]),
            'notes_par_entite'             => collect([$this->groupeParEntite($agents, $s1, $s2, $semestreNum)]),
            'effectif_par_sexe'            => collect([['nom' => 'EFFECTIF PAR SEXE',
                                                        'rows' => $this->sexeRows($agents),
                                                        'colonnes' => ['Sexe', 'Nombre', '%']]]),
            'effectif_par_fonction'        => collect([['nom' => 'EFFECTIF PAR FONCTION',
                                                        'rows' => $this->fonctionRows($agents),
                                                        'colonnes' => ['Fonction', 'Nombre', '%']]]),
            default => collect(),
        };

        return compact('titrePrincipal', 'annee', 'semestreNum', 'typeAnalyse', 'groupes', 'isNotes');
    }

    // ─── Groupement par structure ─────────────────────────────────────────────

    private function groupesParDelegation(Collection $agents, $s1, $s2, string $sem): Collection
    {
        $dtMap = DelegationTechnique::all()->keyBy('id');

        $grouped = $agents->groupBy(function ($a) {
            $dt = $a->delegationTechnique ?? $a->caisse?->delegationTechnique;
            return $dt?->id ?? 0;
        });

        return $grouped->map(function ($grp, $dtId) use ($s1, $s2, $sem, $dtMap) {
            $dt  = $dtMap->get($dtId);
            $nom = $dt ? strtoupper($dt->region).' – '.strtoupper($dt->ville) : 'FCPB (SIÈGE)';
            return ['nom' => $nom, 'rows' => $this->notesRows($grp, $s1, $s2, $sem)];
        })->sortKeys()->values();
    }

    private function groupesParCaisse(Collection $agents, $s1, $s2, string $sem): Collection
    {
        $caisseMap = Caisse::all()->keyBy('id');

        $grouped = $agents->groupBy(fn ($a) => $a->caisse_id ?? 0);

        return $grouped->map(function ($grp, $caisseId) use ($s1, $s2, $sem, $caisseMap) {
            $caisse = $caisseMap->get($caisseId);
            $nom    = $caisse ? strtoupper($caisse->nom) : 'FCPB (SIÈGE / SANS CAISSE)';
            return ['nom' => $nom, 'rows' => $this->notesRows($grp, $s1, $s2, $sem)];
        })->sortKeys()->values();
    }

    // ─── Construction des lignes notes ────────────────────────────────────────

    private function notesRows(Collection $agents, $s1, $s2, string $sem): Collection
    {
        $total = $agents->count();
        if ($total === 0) return collect();

        $notesParAgent = $agents->map(function ($agent) use ($s1, $s2, $sem) {
            $evals = $agent->evaluations->merge($agent->evaluationsPersonnel)->keyBy(fn ($e) => $e->semestre?->numero);
            $n1    = ($s1 && $evals->get(1)?->note_finale !== null) ? (float)$evals->get(1)->note_finale : null;
            $n2    = ($s2 && $evals->get(2)?->note_finale !== null) ? (float)$evals->get(2)->note_finale : null;
            $raw   = match ($sem) {
                '1'      => $n1,
                '2'      => $n2,
                'annuel' => ($n1 !== null && $n2 !== null) ? ($n1 + $n2) / 2 : ($n1 ?? $n2),
                default  => null,
            };
            return $raw !== null ? (int) round($raw) : null;
        });

        $rows = collect();
        foreach (self::NOTE_ROWS as $val) {
            if ($val === 'SN') {
                $nb = $notesParAgent->filter(fn ($n) => $n === null)->count();
            } else {
                $nb = $notesParAgent->filter(fn ($n) => $n === $val)->count();
            }
            $pct = $total > 0 ? round($nb / $total * 100, 2) : 0;
            $rows->push([
                'note'    => $val,
                'nombre'  => $nb > 0 ? $nb : '-',
                'pct'     => number_format($pct, 2, ',', ' ').'%',
                'nb_raw'  => $nb,
                'pct_raw' => $pct,
            ]);
        }

        $rows->push([
            'note'     => 'EFFECTIF',
            'nombre'   => $total,
            'pct'      => number_format(100, 2, ',', ' ').'%',
            'nb_raw'   => $total,
            'pct_raw'  => 100,
            'is_total' => true,
        ]);

        return $rows;
    }

    private function sexeRows(Collection $agents): Collection
    {
        $total  = $agents->count();
        $groups = $agents->groupBy(fn ($a) => ucfirst(strtolower($a->sexe ?? 'Non renseigné')));
        $rows   = $groups->map(fn ($g, $label) => [
            'col1' => $label, 'nombre' => $g->count(),
            'pct'  => number_format($total > 0 ? round($g->count() / $total * 100, 2) : 0, 2, ',', ' ').'%',
        ])->values();
        $rows->push(['col1' => 'EFFECTIF', 'nombre' => $total, 'pct' => '100,00%', 'is_total' => true]);
        return $rows;
    }

    private function fonctionRows(Collection $agents): Collection
    {
        $total  = $agents->count();
        $groups = $agents->groupBy(fn ($a) => $a->poste ?? 'Non renseigné');
        
        $rows   = $groups->sortByDesc(fn ($g) => $g->count())
            ->map(fn ($g, $label) => [
                'col1'  => $label, 'nombre' => $g->count(),
                'pct'   => number_format($total > 0 ? round($g->count() / $total * 100, 2) : 0, 2, ',', ' ').'%',
            ])->values();
        $rows->push(['col1' => 'EFFECTIF', 'nombre' => $total, 'pct' => '100,00%', 'is_total' => true]);
        return $rows;
    }

    // ─── Tableaux croisés ────────────────────────────────────────────────────

    private function agentNote($agent, $s1, $s2, string $sem): ?int
    {
        $evals = $agent->evaluations->merge($agent->evaluationsPersonnel)->keyBy(fn ($e) => $e->semestre?->numero);
        $n1    = ($s1 && $evals->get(1)?->note_finale !== null) ? (float)$evals->get(1)->note_finale : null;
        $n2    = ($s2 && $evals->get(2)?->note_finale !== null) ? (float)$evals->get(2)->note_finale : null;
        $raw   = match ($sem) {
            '1'      => $n1,
            '2'      => $n2,
            'annuel' => ($n1 !== null && $n2 !== null) ? ($n1 + $n2) / 2 : ($n1 ?? $n2),
            default  => null,
        };
        return $raw !== null ? (int) round($raw) : null;
    }

    private function agentEntiteLabel($agent): string
    {
        $dt = $agent->delegationTechnique ?? $agent->caisse?->delegationTechnique;
        return $dt ? strtoupper($dt->region.' '.$dt->ville) : 'FCPB';
    }

    private function groupeParPoste(Collection $agents, $s1, $s2, string $sem): array
    {
        $noteVals = [5, 6, 7, 8, 9, 10];

        $data = $agents->map(fn ($a) => [
            'poste' => $a->poste ?? 'Non défini',
            'note'  => $this->agentNote($a, $s1, $s2, $sem),
        ]);

        $grouped = $data->groupBy('poste')->sortKeys();
        $rows    = [];
        $tot     = ['nombre' => 0, 'sn' => 0] + array_fill_keys($noteVals, 0);

        foreach ($grouped as $poste => $grp) {
            $n     = $grp->count();
            $sn    = $grp->filter(fn ($x) => $x['note'] === null)->count();
            $cells = [$poste, $n];
            foreach ($noteVals as $v) {
                $c = $grp->filter(fn ($x) => $x['note'] === $v)->count();
                $cells[] = $c > 0 ? $c : '-';
                $tot[$v] += $c;
            }
            $cells[] = $sn > 0 ? $sn : '-';
            $tot['nombre'] += $n;
            $tot['sn']     += $sn;
            $rows[] = ['cells' => $cells, 'is_total' => false];
        }

        $totCells = ['TOTAUX', $tot['nombre']];
        foreach ($noteVals as $v) { $totCells[] = $tot[$v] > 0 ? $tot[$v] : '-'; }
        $totCells[] = $tot['sn'] > 0 ? $tot['sn'] : '-';
        $rows[] = ['cells' => $totCells, 'is_total' => true];

        return [
            'nom'     => 'DISTRIBUTION DES NOTES PAR POSTE',
            'headers' => ['Postes', 'Nombre', '5', '6', '7', '8', '9', '10', 'Notes manquantes'],
            'rows'    => collect($rows),
        ];
    }

    private function groupeParEntite(Collection $agents, $s1, $s2, string $sem): array
    {
        $noteKeys = ['sn' => null, '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9, '10' => 10];

        $data = $agents->map(fn ($a) => [
            'entite' => $this->agentEntiteLabel($a),
            'note'   => $this->agentNote($a, $s1, $s2, $sem),
        ]);

        $grouped    = $data->groupBy('entite')->sortKeys();
        $rows       = [];
        $totCounts  = array_fill_keys(array_keys($noteKeys), 0);
        $grandTotal = 0;

        foreach ($grouped as $entite => $grp) {
            $total = $grp->count();
            $cells = [$entite];
            foreach ($noteKeys as $label => $nv) {
                $count = $grp->filter(fn ($x) => $x['note'] === $nv)->count();
                $pct   = $total > 0 ? round($count / $total * 100, 2) : 0;
                $cells[] = number_format($pct, 2, ',', ' ').'%';
                $totCounts[$label] += $count;
            }
            $cells[] = '100,00%';
            $grandTotal += $total;
            $rows[] = ['cells' => $cells, 'is_total' => false];
        }

        $totCells = ['TOTAUX'];
        foreach ($noteKeys as $label => $nv) {
            $pct = $grandTotal > 0 ? round($totCounts[$label] / $grandTotal * 100, 2) : 0;
            $totCells[] = number_format($pct, 2, ',', ' ').'%';
        }
        $totCells[] = '100,00%';
        $rows[] = ['cells' => $totCells, 'is_total' => true];

        return [
            'nom'     => 'DISTRIBUTION DES NOTES PAR ENTITÉ',
            'headers' => ['NOTE/10', 'SN', '5', '6', '7', '8', '9', '10', 'TOTAUX'],
            'rows'    => collect($rows),
        ];
    }

    // ─── Export XLSX (PharData / ZIP) ────────────────────────────────────────

    private function buildXlsx(array $payload): string
    {
        $uid     = uniqid('xlsx_', true);
        $zipPath = sys_get_temp_dir() . '/' . $uid . '.zip';

        [$sheetXml, $chartRanges] = $this->xlsxSheetAndRanges($payload);
        $numCharts = $payload['isNotes'] ? count($chartRanges) : 0;
        $hasCharts = $numCharts > 0;

        $phar = new \PharData($zipPath);
        $phar->addFromString('[Content_Types].xml',        $this->xlsxContentTypes($hasCharts, $numCharts));
        $phar->addFromString('_rels/.rels',                $this->xlsxPkgRels());
        $phar->addFromString('xl/workbook.xml',            $this->xlsxWorkbook());
        $phar->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels());
        $phar->addFromString('xl/styles.xml',              $this->xlsxStyles());
        $phar->addFromString('xl/worksheets/sheet1.xml',   $sheetXml);

        if ($hasCharts) {
            $phar->addFromString('xl/worksheets/_rels/sheet1.xml.rels', $this->xlsxSheetRels());
            $phar->addFromString('xl/drawings/drawing1.xml',            $this->xlsxDrawingXml($chartRanges));
            $phar->addFromString('xl/drawings/_rels/drawing1.xml.rels', $this->xlsxDrawingRels($numCharts));
            foreach ($chartRanges as $i => $range) {
                $phar->addFromString('xl/charts/chart'.($i + 1).'.xml', $this->xlsxChartXml($range));
            }
        }

        $content = (string) file_get_contents($zipPath);
        @unlink($zipPath);
        return $content;
    }

    private function xlsxContentTypes(bool $hasCharts = false, int $numCharts = 0): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
             . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
             . '<Default Extension="xml"  ContentType="application/xml"/>'
             . '<Override PartName="/xl/workbook.xml"'
             .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
             . '<Override PartName="/xl/worksheets/sheet1.xml"'
             .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
             . '<Override PartName="/xl/styles.xml"'
             .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        if ($hasCharts) {
            $xml .= '<Override PartName="/xl/drawings/drawing1.xml"'
                  .   ' ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
            for ($i = 1; $i <= $numCharts; $i++) {
                $xml .= '<Override PartName="/xl/charts/chart'.$i.'.xml"'
                      .   ' ContentType="application/vnd.openxmlformats-officedocument.drawingml.chart+xml"/>';
            }
        }
        return $xml . '</Types>';
    }

    private function xlsxPkgRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            .   ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            .   ' Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function xlsxWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .   ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Statistiques" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function xlsxWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            .   ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
            .   ' Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2"'
            .   ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
            .   ' Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function xlsxStyles(): string
    {
        $thin = '<left style="thin"><color rgb="FFAAAAAA"/></left>'
              . '<right style="thin"><color rgb="FFAAAAAA"/></right>'
              . '<top style="thin"><color rgb="FFAAAAAA"/></top>'
              . '<bottom style="thin"><color rgb="FFAAAAAA"/></bottom>'
              . '<diagonal/>';
        $none = '<left/><right/><top/><bottom/><diagonal/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="4">'
            .   '<font><sz val="11"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="13"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="6">'
            .   '<fill><patternFill patternType="none"/></fill>'
            .   '<fill><patternFill patternType="gray125"/></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FFE8F5E9"/><bgColor indexed="64"/></patternFill></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FF1D6F42"/><bgColor indexed="64"/></patternFill></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FF4E9A56"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            .   '<border>'.$none.'</border>'
            .   '<border>'.$thin.'</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="10">'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .   '<xf numFmtId="0" fontId="3" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">'
            .     '<alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .   '<xf numFmtId="0" fontId="2" fillId="4" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">'
            .     '<alignment horizontal="center" vertical="center"/></xf>'
            .   '<xf numFmtId="0" fontId="2" fillId="5" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">'
            .     '<alignment horizontal="center"/></xf>'
            .   '<xf numFmtId="0" fontId="0" fillId="2" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment horizontal="center"/></xf>'
            .   '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment horizontal="center"/></xf>'
            .   '<xf numFmtId="0" fontId="0" fillId="2" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment horizontal="left"/></xf>'
            .   '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment horizontal="left"/></xf>'
            .   '<xf numFmtId="0" fontId="2" fillId="4" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">'
            .     '<alignment horizontal="center"/></xf>'
            .   '<xf numFmtId="0" fontId="2" fillId="4" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">'
            .     '<alignment horizontal="left"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function xlsxSheetAndRanges(array $payload): array
    {
        $L = static function (int $c): string {
            if ($c <= 26) return chr(64 + $c);
            return chr(64 + intdiv($c - 1, 26)) . chr(65 + ($c - 1) % 26);
        };

        $grid        = [];
        $rowHt       = [];
        $merges      = [];
        $chartRanges = [];

        $rowHt[1] = 14;
        $grid[2][2] = ['s' => 1, 't' => 's', 'v' => $payload['titrePrincipal']];
        $rowHt[2]   = 22;
        $merges[]   = 'B2:D2';
        $rowHt[3]   = 10;

        $groupes  = $payload['groupes'];
        $startRow = 4;

        if ($payload['isNotes']) {
            $TABLE_H = 10;
            $ROW_GAP = 2;

            foreach ($groupes as $gi => $groupe) {
                $tableTop = $startRow + $gi * ($TABLE_H + $ROW_GAP);
                $rows = $groupe['rows'] ?? collect();
                $nom  = $groupe['nom']  ?? '';

                $grid[$tableTop][2] = ['s' => 2, 't' => 's', 'v' => 'NOTES '.$nom];
                $merges[]           = 'B'.$tableTop.':D'.$tableTop;
                $rowHt[$tableTop]   = 18;

                $ch = $tableTop + 1;
                foreach (['NOTE/10', 'NOMBRE', '%'] as $ci => $h) {
                    $grid[$ch][2 + $ci] = ['s' => 3, 't' => 's', 'v' => $h];
                }
                $rowHt[$ch] = 16;

                $dataStart = $tableTop + 2;
                $dataEnd   = $tableTop + 2;
                $di        = 0;
                foreach ($rows as $row) {
                    $isTotal = $row['is_total'] ?? false;
                    $rn      = $tableTop + 2 + $di;
                    if ($isTotal) {
                        $nb = (int)($row['nb_raw'] ?? $row['nombre'] ?? 0);
                        $grid[$rn][2] = ['s' => 9, 't' => 's', 'v' => 'EFFECTIF'];
                        $grid[$rn][3] = ['s' => 8, 't' => 'n', 'v' => $nb];
                        $grid[$rn][4] = ['s' => 8, 't' => 's', 'v' => $row['pct'] ?? '100,00%'];
                        $rowHt[$rn]   = 16;
                    } else {
                        $sl = $di % 2 === 0 ? 6 : 7;
                        $sd = $di % 2 === 0 ? 4 : 5;
                        $grid[$rn][2] = ['s' => $sl, 't' => 's', 'v' => (string)($row['note'] ?? '')];
                        $grid[$rn][3] = ['s' => $sd, 't' => 'n', 'v' => (int)($row['nb_raw'] ?? 0)];
                        $grid[$rn][4] = ['s' => $sd, 't' => 's', 'v' => $row['pct'] ?? '0,00%'];
                        $rowHt[$rn]   = 15;
                        $dataEnd      = $rn;
                    }
                    $di++;
                }

                $chartRanges[] = [
                    'name'      => $nom,
                    'labelCol'  => 'B',
                    'valueCol'  => 'C',
                    'dataStart' => $dataStart,
                    'dataEnd'   => $dataEnd,
                    'fromRow'   => $tableTop - 1,
                    'toRow'     => $tableTop - 1 + $TABLE_H,
                ];
            }

        } else {
            $r = $startRow;
            foreach ($groupes as $groupe) {
                $rows = $groupe['rows'] ?? collect();
                $nom  = $groupe['nom']  ?? '';
                if ($rows->isEmpty()) continue;

                $isWide = isset($groupe['headers']);

                if ($isWide) {
                    $headers = $groupe['headers'];
                    $nCols   = count($headers);
                    $lastCol = $L(1 + $nCols);

                    $grid[$r][2] = ['s' => 2, 't' => 's', 'v' => $nom];
                    $merges[]    = 'B'.$r.':'.$lastCol.$r;
                    $rowHt[$r]   = 18; $r++;

                    foreach ($headers as $ci => $col) {
                        $grid[$r][2 + $ci] = ['s' => 3, 't' => 's', 'v' => $col];
                    }
                    $rowHt[$r] = 16; $r++;

                    $di = 0;
                    foreach ($rows as $row) {
                        $isTotal = $row['is_total'] ?? false;
                        foreach (($row['cells'] ?? []) as $ci => $val) {
                            $col = 2 + $ci;
                            $s   = $isTotal
                                ? ($ci === 0 ? 9 : 8)
                                : ($ci === 0 ? ($di % 2 === 0 ? 6 : 7) : ($di % 2 === 0 ? 4 : 5));
                            $isNum          = is_int($val) || is_float($val);
                            $grid[$r][$col] = ['s' => $s, 't' => $isNum ? 'n' : 's', 'v' => $val];
                        }
                        $rowHt[$r] = $isTotal ? 16 : 15;
                        if (! $isTotal) $di++;
                        $r++;
                    }

                } else {
                    $grid[$r][2] = ['s' => 2, 't' => 's', 'v' => $nom];
                    $merges[]    = 'B'.$r.':D'.$r;
                    $rowHt[$r]   = 18; $r++;

                    $cols = $groupe['colonnes'] ?? ['Catégorie', 'Nombre', '%'];
                    foreach ($cols as $ci => $col) {
                        $grid[$r][2 + $ci] = ['s' => 3, 't' => 's', 'v' => $col];
                    }
                    $rowHt[$r] = 16; $r++;

                    $di = 0;
                    foreach ($rows as $row) {
                        $isTotal = $row['is_total'] ?? false;
                        if ($isTotal) {
                            $v1 = $row['col1'] ?? 'EFFECTIF';
                            $nb = (int)($row['nb_raw'] ?? $row['nombre'] ?? 0);
                            $grid[$r][2] = ['s' => 9, 't' => 's', 'v' => $v1];
                            $grid[$r][3] = ['s' => 8, 't' => 'n', 'v' => $nb];
                            $grid[$r][4] = ['s' => 8, 't' => 's', 'v' => $row['pct'] ?? '100,00%'];
                            $rowHt[$r]   = 16;
                        } else {
                            $sl          = $di % 2 === 0 ? 6 : 7;
                            $sd          = $di % 2 === 0 ? 4 : 5;
                            $grid[$r][2] = ['s' => $sl, 't' => 's', 'v' => $row['col1'] ?? ''];
                            $grid[$r][3] = ['s' => $sd, 't' => 'n', 'v' => (int)($row['nb_raw'] ?? 0)];
                            $grid[$r][4] = ['s' => $sd, 't' => 's', 'v' => $row['pct'] ?? '0,00%'];
                            $rowHt[$r]   = 15;
                            $di++;
                        }
                        $r++;
                    }
                }

                $rowHt[$r] = 12; $r++;
                $rowHt[$r] = 12; $r++;
            }
        }

        $colsXml = '<cols>'
            . '<col min="1"  max="1"  width="3"  customWidth="1"/>'
            . '<col min="2"  max="2"  width="20" customWidth="1"/>'
            . '<col min="3"  max="3"  width="11" customWidth="1"/>'
            . '<col min="4"  max="4"  width="10" customWidth="1"/>'
            . '<col min="5"  max="5"  width="3"  customWidth="1"/>'
            . '<col min="6"  max="16" width="10" customWidth="1"/>'
            . '</cols>';

        $ns        = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
        $nsR       = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $hasCharts = $payload['isNotes'] && count($chartRanges) > 0;

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<worksheet xmlns="' . $ns . '" xmlns:r="' . $nsR . '">'
             . '<sheetViews><sheetView tabSelected="1" workbookViewId="0"/></sheetViews>'
             . '<sheetFormatPr defaultRowHeight="15"/>'
             . $colsXml
             . '<sheetData>';

        $maxRow = count($grid) > 0 ? max(array_keys($grid)) : 0;

        for ($r = 1; $r <= $maxRow; $r++) {
            $ht = $rowHt[$r] ?? 15;
            if (!isset($grid[$r]) && !isset($rowHt[$r])) {
                continue;
            }
            $xml .= '<row r="' . $r . '" ht="' . $ht . '" customHeight="1">';
            if (isset($grid[$r])) {
                foreach ($grid[$r] as $c => $cell) {
                    $ref = $L($c) . $r;
                    $s   = $cell['s'] ?? 0;
                    $t   = $cell['t'] ?? 's';
                    $v   = $cell['v'];

                    $xml .= '<c r="' . $ref . '" s="' . $s . '" t="' . $t . '">';
                    if ($t === 's') {
                        $xml .= '<v>' . htmlspecialchars($v, ENT_QUOTES, 'UTF-8') . '</v>';
                    } else {
                        $xml .= '<v>' . $v . '</v>';
                    }
                    $xml .= '</c>';
                }
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData>';

        if (count($merges) > 0) {
            $xml .= '<mergeCells count="' . count($merges) . '">';
            foreach ($merges as $m) {
                $xml .= '<mergeCell ref="' . $m . '"/>';
            }
            $xml .= '</mergeCells>';
        }

        if ($hasCharts) {
            $xml .= '<drawing r:id="rId1"/>';
        }

        $xml .= '</worksheet>';

        return [$xml, $chartRanges];
    }

    private function xlsxSheetRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/>'
            . '</Relationships>';
    }

    private function xlsxDrawingXml(array $chartRanges): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">';
        
        foreach ($chartRanges as $i => $range) {
            $rId = 'rId' . ($i + 1);
            $topRow = $range['fromRow'];
            $botRow = $range['toRow'];

            $xml .= '<xdr:twoCellAnchor>'
                . '<xdr:from><xdr:col>5</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>' . $topRow . '</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:from>'
                . '<xdr:to><xdr:col>12</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>' . $botRow . '</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:to>'
                . '<xdr:graphicFrame macro="">'
                . '<xdr:nvGraphicFramePr><xdr:cNvPr id="' . ($i + 10) . '" name="Graphique ' . ($i + 1) . '"/><xdr:cNvGraphicFramePr/></xdr:nvGraphicFramePr>'
                . '<xdr:xfrm><a:off x="0" y="0"/><a:ext x="0" y="0"/></xdr:xfrm>'
                . '<a:graphic><a:graphicData xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" r:id="' . $rId . '" Type="http://schemas.openxmlformats.org/drawingml/2006/chart"/></a:graphic>'
                . '</xdr:graphicFrame>'
                . '<xdr:clientData/>'
                . '</xdr:twoCellAnchor>';
        }
        return $xml . '</xdr:wsDr>';
    }

    private function xlsxDrawingRels(int $numCharts): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        for ($i = 1; $i <= $numCharts; $i++) {
            $xml .= '<Relationship Id="rId' . $i . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/chart" Target="../charts/chart' . $i . '.xml"/>';
        }
        return $xml . '</Relationships>';
    }

    private function xlsxChartXml(array $range): string
    {
        $title = htmlspecialchars($range['name'], ENT_QUOTES, 'UTF-8');
        $lCol  = $range['labelCol'];
        $vCol  = $range['valueCol'];
        $sRow  = $range['dataStart'];
        $eRow  = $range['dataEnd'];

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<c:chartSpace xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<c:chart>'
            . '<c:title><c:tx><c:rich><a:bodyPr/><a:lstStyle/><a:p><a:r><a:rPr b="1"/><a:t>' . $title . '</a:t></a:r></a:p></c:rich></c:tx></c:title>'
            . '<c:plotArea>'
            . '<c:barChart>'
            . '<c:barDir val="col"/><c:grouping val="clustered"/>'
            . '<c:ser>'
            . '<c:idx val="0"/><c:order val="0"/>'
            . '<c:cat><c:strRef><c:f>Statistiques!$' . $lCol . '$' . $sRow . ':$' . $lCol . '$' . $eRow . '</c:f></c:strRef></c:cat>'
            . '<c:val><c:numRef><c:f>Statistiques!$' . $vCol . '$' . $sRow . ':$' . $vCol . '$' . $eRow . '</c:f></c:numRef></c:val>'
            . '</c:ser>'
            . '<c:axId val="11111"/><c:axId val="22222"/>'
            . '</c:barChart>'
            . '<c:catAx><c:axId val="11111"/><c:scaling><c:orientation val="minMax"/></c:scaling><c:axPos val="b"/><c:lblAlgn val="ctr"/></c:catAx>'
            . '<c:valAx><c:axId val="22222"/><c:scaling><c:orientation val="minMax"/></c:scaling><c:axPos val="l"/><c:majorGridlines/></c:valAx>'
            . '</c:plotArea>'
            . '</c:chart>'
            . '</c:chartSpace>';
    }
}