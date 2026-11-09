<?php

namespace App\Services\Fushan;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class SeedlingExperimentRecordPaperExporter
{
    private const CONNECTION = 'fs_seedling_exp';

    public function __construct(
        private readonly SeedlingExperimentSurveyService $surveyService
    ) {}

    /**
     * @return array{path:string, filename:string, record_count:int}
     */
    public function make(int $surveyId): array
    {
        $survey = $this->surveyService->findForDownload($surveyId);
        $rows = $this->rows($surveyId);

        if ($rows->isEmpty()) {
            throw new RuntimeException('此調查批次沒有可輸出的個體。');
        }

        $path = tempnam(sys_get_temp_dir(), 'seedling_experiment_record_paper_');
        if ($path === false) {
            throw new RuntimeException('無法建立記錄紙暫存檔。');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($path);
            throw new RuntimeException('無法建立記錄紙 Excel 檔。');
        }

        $parts = $this->xlsxParts($rows->all(), $survey->survey_period);
        foreach ($parts as $name => $contents) {
            $zip->addFromString($name, $contents);
        }
        $zip->close();

        $period = str_replace('-', '', $survey->survey_period);

        return [
            'path' => $path,
            'filename' => "小苗試驗紀錄{$period}紀錄紙.xlsx",
            'record_count' => $rows->count(),
        ];
    }

    private function rows(int $surveyId)
    {
        return DB::connection(self::CONNECTION)
            ->table('survey_individuals as batch_items')
            ->join('individuals as individuals', 'individuals.id', '=', 'batch_items.individual_id')
            ->join('plots as plots', 'plots.id', '=', 'individuals.plot_id')
            ->leftJoin('records as previous', 'previous.id', '=', 'batch_items.previous_record_id')
            ->where('batch_items.survey_id', $surveyId)
            ->orderBy('batch_items.survey_order')
            ->select([
                'plots.plot_code',
                'individuals.individual_no',
                'individuals.species_name',
                'previous.length',
                'previous.leaf_count',
                'previous.status',
                'previous.note',
            ])
            ->get();
    }

    /**
     * @param  array<int, object>  $records
     * @return array<string, string>
     */
    private function xlsxParts(array $records, string $surveyPeriod): array
    {
        $worksheet = $this->worksheetXml($records, $surveyPeriod);
        $lastRow = count($records) + 1;

        return [
            '[Content_Types].xml' => <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML,
            '_rels/.rels' => <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML,
            'docProps/core.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
                .'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
                .'xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                .'<dc:title>'.$this->xml("小苗試驗 {$surveyPeriod} 紀錄紙").'</dc:title>'
                .'<dc:creator>FDP</dc:creator><cp:lastModifiedBy>FDP</cp:lastModifiedBy>'
                .'<dcterms:created xsi:type="dcterms:W3CDTF">'.now()->utc()->format('Y-m-d\TH:i:s\Z').'</dcterms:created>'
                .'</cp:coreProperties>',
            'docProps/app.xml' => <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>FDP</Application>
</Properties>
XML,
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
                .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                .'<sheets><sheet name="小苗試驗紀錄紙" sheetId="1" r:id="rId1"/></sheets>'
                .'<definedNames><definedName name="_xlnm.Print_Area" localSheetId="0">\'小苗試驗紀錄紙\'!$A$1:$I$'.$lastRow.'</definedName>'
                .'<definedName name="_xlnm.Print_Titles" localSheetId="0">\'小苗試驗紀錄紙\'!$1:$1</definedName></definedNames>'
                .'</workbook>',
            'xl/_rels/workbook.xml.rels' => <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML,
            'xl/styles.xml' => $this->stylesXml(),
            'xl/worksheets/sheet1.xml' => $worksheet,
        ];
    }

    /**
     * @param  array<int, object>  $records
     */
    private function worksheetXml(array $records, string $surveyPeriod): string
    {
        $headers = ['Plot', 'No', 'sp.', '高度', '', '葉片', '', '狀態', '備註'];
        $rows = ['<row r="1" ht="20" customHeight="1">'.$this->cells($headers, 1, 1).'</row>'];

        foreach ($records as $index => $record) {
            $rowNumber = $index + 2;
            $rows[] = '<row r="'.$rowNumber.'" ht="20" customHeight="1">'
                .$this->cells([
                    $record->plot_code,
                    $record->individual_no,
                    $record->species_name,
                    $record->length,
                    '',
                    $record->leaf_count,
                    '',
                    $record->status,
                    $record->note,
                ], $rowNumber, 2)
                .'</row>';
        }

        $lastRow = count($records) + 1;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
            .'<dimension ref="A1:I'.$lastRow.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="20"/>'
            .'<cols>'
            .'<col min="1" max="1" width="15" customWidth="1"/>'
            .'<col min="2" max="2" width="13" customWidth="1"/>'
            .'<col min="3" max="3" width="10" customWidth="1"/>'
            .'<col min="4" max="7" width="9" customWidth="1"/>'
            .'<col min="8" max="8" width="8" customWidth="1"/>'
            .'<col min="9" max="9" width="34" customWidth="1"/>'
            .'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .'<autoFilter ref="A1:I'.$lastRow.'"/>'
            .'<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'<pageSetup orientation="portrait" paperSize="9" fitToWidth="1" fitToHeight="0"/>'
            .'<headerFooter>'
            .'<oddHeader>'.$this->xml("&L&14&B地被小苗試驗 {$surveyPeriod}&B&R&11調查日期____________________________").'</oddHeader>'
            .'<oddFooter>'.$this->xml('&R&P/&N').'</oddFooter>'
            .'</headerFooter>'
            .'</worksheet>';
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function cells(array $values, int $row, int $style): string
    {
        $cells = [];

        foreach ($values as $index => $value) {
            $reference = $this->columnName($index + 1).$row;
            $cellStyle = $index === 8 && $style !== 1 ? 3 : $style;

            if ($value !== null && $value !== '' && is_numeric($value) && in_array($index, [3, 5], true)) {
                $cells[] = '<c r="'.$reference.'" s="'.$cellStyle.'" t="n"><v>'.$this->xml((string) $value).'</v></c>';

                continue;
            }

            $cells[] = '<c r="'.$reference.'" s="'.$cellStyle.'" t="inlineStr"><is><t xml:space="preserve">'
                .$this->xml((string) ($value ?? ''))
                .'</t></is></c>';
        }

        return implode('', $cells);
    }

    private function stylesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><name val="Microsoft JhengHei"/></font>
    <font><b/><sz val="11"/><name val="Microsoft JhengHei"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFE2F0D9"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FF000000"/></left>
      <right style="thin"><color rgb="FF000000"/></right>
      <top style="thin"><color rgb="FF000000"/></top>
      <bottom style="thin"><color rgb="FF000000"/></bottom>
      <diagonal/>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="4">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML;
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
