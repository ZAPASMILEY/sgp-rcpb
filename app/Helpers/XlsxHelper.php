<?php

namespace App\Helpers;

/**
 * Génère un fichier XLSX simple (une feuille, tableau plat) sans dépendance externe.
 * Utilise PharData (natif PHP) pour construire le ZIP/XLSX.
 */
class XlsxHelper
{
    /**
     * Construit le contenu binaire d'un fichier XLSX.
     *
     * @param  array<string>         $headers  Ligne d'en-tête
     * @param  array<array<mixed>>   $rows     Lignes de données
     * @param  string                $sheet    Nom de l'onglet
     * @return string                Contenu binaire du fichier XLSX
     */
    public static function build(array $headers, array $rows, string $sheet = 'Export'): string
    {
        $colLetter = static function (int $c): string {
            if ($c <= 26) return chr(64 + $c);
            return chr(64 + intdiv($c - 1, 26)) . chr(65 + ($c - 1) % 26);
        };

        $nCols = count($headers);

        // ── Feuille ────────────────────────────────────────────────────────────
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>';
        for ($i = 1; $i <= $nCols; $i++) {
            $w = 18;
            $sheetXml .= '<col min="'.$i.'" max="'.$i.'" width="'.$w.'" customWidth="1"/>';
        }
        $sheetXml .= '</cols><sheetData>';

        // En-tête
        $sheetXml .= '<row r="1" ht="20" customHeight="1">';
        foreach ($headers as $ci => $hdr) {
            $ref = $colLetter($ci + 1).'1';
            $v   = htmlspecialchars((string)$hdr, ENT_XML1 | ENT_QUOTES);
            $sheetXml .= '<c r="'.$ref.'" t="inlineStr" s="1"><is><t>'.$v.'</t></is></c>';
        }
        $sheetXml .= '</row>';

        // Données
        foreach ($rows as $ri => $row) {
            $rn   = $ri + 2;
            $even = $ri % 2 === 0;
            $sheetXml .= '<row r="'.$rn.'" ht="15" customHeight="1">';
            foreach ($row as $ci => $cell) {
                $ref   = $colLetter($ci + 1).$rn;
                $isNum = is_int($cell) || is_float($cell);
                if ($isNum) {
                    $style = $even ? 4 : 5;
                    $sheetXml .= '<c r="'.$ref.'" s="'.$style.'"><v>'.(float)$cell.'</v></c>';
                } else {
                    $style = $even ? 2 : 3;
                    $v     = htmlspecialchars((string)($cell ?? ''), ENT_XML1 | ENT_QUOTES);
                    $sheetXml .= '<c r="'.$ref.'" t="inlineStr" s="'.$style.'"><is><t>'.$v.'</t></is></c>';
                }
            }
            $sheetXml .= '</row>';
        }
        $sheetXml .= '</sheetData></worksheet>';

        // ── Package ZIP/XLSX ────────────────────────────────────────────────────
        $uid     = uniqid('xlsx_', true);
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $uid . '.zip';

        $phar = new \PharData($zipPath);
        $phar->addFromString('[Content_Types].xml',        static::contentTypes());
        $phar->addFromString('_rels/.rels',                static::pkgRels());
        $phar->addFromString('xl/workbook.xml',            static::workbook($sheet));
        $phar->addFromString('xl/_rels/workbook.xml.rels', static::workbookRels());
        $phar->addFromString('xl/styles.xml',              static::styles());
        $phar->addFromString('xl/worksheets/sheet1.xml',   $sheetXml);

        $content = (string)file_get_contents($zipPath);
        @unlink($zipPath);
        return $content;
    }

    // ── XML helpers ──────────────────────────────────────────────────────────

    private static function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml"  ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml"'
            .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml"'
            .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml"'
            .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function pkgRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            .   ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            .   ' Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbook(string $sheet): string
    {
        $s = htmlspecialchars($sheet, ENT_XML1 | ENT_QUOTES);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .   ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="'.$s.'" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function workbookRels(): string
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

    private static function styles(): string
    {
        // Styles :
        //  0 : défaut
        //  1 : en-tête  (bold, texte blanc, fond vert foncé #1D6F42)
        //  2 : ligne paire  texte  (fond blanc, bordures)
        //  3 : ligne impaire texte (fond vert très clair #E8F5E9, bordures)
        //  4 : ligne paire  nombre (fond blanc, bordures, centre)
        //  5 : ligne impaire nombre (fond vert très clair, bordures, centre)
        $thin = '<left style="thin"><color rgb="FFCCCCCC"/></left>'
              . '<right style="thin"><color rgb="FFCCCCCC"/></right>'
              . '<top style="thin"><color rgb="FFCCCCCC"/></top>'
              . '<bottom style="thin"><color rgb="FFCCCCCC"/></bottom>'
              . '<diagonal/>';
        $none = '<left/><right/><top/><bottom/><diagonal/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="3">'
            .   '<font><sz val="11"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            .   '<font><sz val="11"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="5">'
            .   '<fill><patternFill patternType="none"/></fill>'
            .   '<fill><patternFill patternType="gray125"/></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FF1D6F42"/></patternFill></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FFE8F5E9"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            .   '<border>'.$none.'</border>'
            .   '<border>'.$thin.'</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="6">'
            // 0 défaut
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            // 1 en-tête
            .   '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1">'
            .     '<alignment horizontal="left" vertical="center" wrapText="0"/></xf>'
            // 2 ligne paire texte
            .   '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment horizontal="left" vertical="center"/></xf>'
            // 3 ligne impaire texte
            .   '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment horizontal="left" vertical="center"/></xf>'
            // 4 ligne paire nombre
            .   '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment horizontal="center" vertical="center"/></xf>'
            // 5 ligne impaire nombre
            .   '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment horizontal="center" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
