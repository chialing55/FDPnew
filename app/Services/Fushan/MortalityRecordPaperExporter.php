<?php

namespace App\Services\Fushan;

use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use ZipArchive;

class MortalityRecordPaperExporter
{
    private const PAGE_SIZE = 24;
    private const TEMPLATE = 'templates/fushan/record_template.xlsx';
    private const SHEET_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const REL_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const PACKAGE_REL_NS = 'http://schemas.openxmlformats.org/package/2006/relationships';
    private const CONTENT_TYPES_NS = 'http://schemas.openxmlformats.org/package/2006/content-types';

    /**
     * @return array{path:string, filename:string, page_count:int, record_count:int}
     */
    public function make(): array
    {
        $templatePath = resource_path(self::TEMPLATE);

        if (!is_file($templatePath)) {
            throw new RuntimeException('找不到紀錄紙 Excel 模板。');
        }

        if (!Schema::connection('fs_mortality')->hasTable('record1')) {
            throw new RuntimeException('尚未產生 record1，請先到資料匯入頁面產生輸入表單。');
        }

        $records = $this->recordRows();

        if ($records->isEmpty()) {
            throw new RuntimeException('record1 目前沒有資料，請先產生輸入表單。');
        }

        $sections = $this->buildSections($records);
        $sheetRows = $this->buildSheetRows($sections);

        $sourceZip = new ZipArchive();
        if ($sourceZip->open($templatePath) !== true) {
            throw new RuntimeException('無法開啟紀錄紙 Excel 模板。');
        }

        $baseSheetXml = $sourceZip->getFromName('xl/worksheets/sheet1.xml');
        $baseTableXml = $sourceZip->getFromName('xl/tables/table1.xml');

        if ($baseSheetXml === false || $baseTableXml === false) {
            $sourceZip->close();
            throw new RuntimeException('紀錄紙 Excel 模板缺少必要工作表。');
        }

        $outputPath = tempnam(sys_get_temp_dir(), 'mortality_record_paper_');
        if ($outputPath === false) {
            $sourceZip->close();
            throw new RuntimeException('無法建立暫存 Excel 檔。');
        }

        $targetZip = new ZipArchive();
        if ($targetZip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $sourceZip->close();
            throw new RuntimeException('無法建立紀錄紙 Excel 檔。');
        }

        for ($i = 0; $i < $sourceZip->numFiles; $i++) {
            $name = $sourceZip->getNameIndex($i);

            if ($name === false || $this->shouldSkipTemplatePart($name)) {
                continue;
            }

            $content = $sourceZip->getFromName($name);
            if ($content !== false) {
                $targetZip->addFromString($name, $content);
            }
        }

        $targetZip->addFromString('xl/worksheets/sheet1.xml', $this->buildWorksheetXml($baseSheetXml, $sheetRows));
        $targetZip->addFromString('xl/worksheets/_rels/sheet1.xml.rels', $this->buildWorksheetRelXml());
        $targetZip->addFromString('xl/tables/table1.xml', $this->buildTableXml($baseTableXml, count($sheetRows)));
        $targetZip->addFromString('xl/workbook.xml', $this->buildWorkbookXml());
        $targetZip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelXml());
        $targetZip->addFromString('[Content_Types].xml', $this->buildContentTypesXml());
        $targetZip->addFromString('docProps/app.xml', $this->buildAppXml());

        $targetZip->close();
        $sourceZip->close();

        $census = $records->first()->census ?? 'unknown';
        $filename = "mortality_record_paper_census_{$census}.xlsx";

        return [
            'path' => $outputPath,
            'filename' => $filename,
            'page_count' => count($sections),
            'record_count' => $records->count(),
        ];
    }

    private function recordRows()
    {
        $columns = collect(Schema::connection('fs_mortality')->getColumnListing('record1'));
        $selectColumns = $columns
            ->intersect([
                'id', 'census', 'map_sort', 'map', 'q20', 'q10', 'qx', 'qy', 'subqx', 'subqy',
                'stemid', 'csp', 'x', 'y', 'dbh1', 'status', 'mode',
            ])
            ->values()
            ->all();

        $query = DB::connection('fs_mortality')->table('record1')->select($selectColumns);

        if ($columns->contains('map_sort')) {
            $query->orderBy('map_sort');
        }

        $query->orderBy('map');

        if ($columns->contains('id')) {
            $query->orderBy('id');
        } else {
            $query->orderBy('stemid');
        }

        return $query->get()->map(function ($row) {
            $row->q20 = $this->formatQ20Value($row->q20 ?? null) ?? $this->formatPair($row->qx ?? null, $row->qy ?? null);
            $row->q10 = $this->blankToNull($row->q10 ?? null) ?? $this->formatPair($row->subqx ?? null, $row->subqy ?? null);

            return $row;
        });
    }

    private function buildSections($records): array
    {
        $sections = [];

        $records->groupBy(function ($row) {
            return (string) ($this->blankToNull($row->map ?? null) ?? '未設定map');
        })->each(function ($mapRows) use (&$sections) {
            foreach ($mapRows->chunk(self::PAGE_SIZE) as $chunk) {
                $rows = $chunk->values()->all();
                while (count($rows) < self::PAGE_SIZE) {
                    $rows[] = null;
                }

                $sections[] = $rows;
            }
        });

        return $sections;
    }

    private function buildSheetRows(array $sections): array
    {
        $rows = [];

        foreach ($sections as $sectionRows) {
            array_push($rows, ...$sectionRows);
        }

        return $rows;
    }

    private function shouldSkipTemplatePart(string $name): bool
    {
        return $name === 'xl/workbook.xml'
            || $name === 'xl/_rels/workbook.xml.rels'
            || $name === '[Content_Types].xml'
            || $name === 'docProps/app.xml'
            || preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)
            || preg_match('#^xl/worksheets/_rels/sheet\d+\.xml\.rels$#', $name)
            || preg_match('#^xl/tables/table\d+\.xml$#', $name)
            || $name === 'xl/printerSettings/printerSettings2.bin';
    }

    private function buildWorksheetXml(string $baseSheetXml, array $rows): string
    {
        $dom = $this->loadXml($baseSheetXml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('x', self::SHEET_NS);

        $lastRow = count($rows) + 1;
        $dimension = $xpath->query('/x:worksheet/x:dimension')->item(0);
        if ($dimension instanceof DOMElement) {
            $dimension->setAttribute('ref', "A1:W{$lastRow}");
        }

        $sheetData = $xpath->query('/x:worksheet/x:sheetData')->item(0);
        $templateRow = $xpath->query('/x:worksheet/x:sheetData/x:row[@r="2"]')->item(0);

        if (!$sheetData instanceof DOMElement || !$templateRow instanceof DOMElement) {
            throw new RuntimeException('紀錄紙模板缺少資料列樣板。');
        }

        foreach (iterator_to_array($xpath->query('/x:worksheet/x:sheetData/x:row[@r!="1"]')) as $row) {
            $sheetData->removeChild($row);
        }

        foreach ($rows as $index => $record) {
            $rowNumber = $index + 2;
            $row = $templateRow->cloneNode(true);
            $row->setAttribute('r', (string) $rowNumber);

            foreach ($xpath->query('.//x:c', $row) as $cell) {
                if (!$cell instanceof DOMElement) {
                    continue;
                }

                $column = preg_replace('/\d+/', '', $cell->getAttribute('r'));
                $cell->setAttribute('r', $column . $rowNumber);
                $this->setCellValue($dom, $cell, $this->cellValueForColumn($record, $column), in_array($column, ['F', 'G', 'H'], true));
            }

            $sheetData->appendChild($row);
        }

        return $dom->saveXML();
    }

    private function cellValueForColumn($record, string $column)
    {
        if ($record === null) {
            return null;
        }

        return match ($column) {
            'A' => $this->mapForPaper($record->map ?? null),
            'B' => $record->q20 ?? null,
            'C' => $record->q10 ?? null,
            'D' => $record->stemid ?? null,
            'E' => $record->csp ?? null,
            'F' => $record->x ?? null,
            'G' => $record->y ?? null,
            'H' => $record->dbh1 ?? null,
            'J' => $this->statusForPaper($record->status ?? null),
            default => null,
        };
    }

    private function setCellValue(DOMDocument $dom, DOMElement $cell, $value, bool $numeric = false): void
    {
        while ($cell->firstChild) {
            $cell->removeChild($cell->firstChild);
        }
        $cell->removeAttribute('t');

        $value = $this->blankToNull($value);
        if ($value === null) {
            return;
        }

        if ($numeric && is_numeric($value)) {
            $cell->appendChild($dom->createElementNS(self::SHEET_NS, 'v', $this->formatNumber($value)));
            return;
        }

        $cell->setAttribute('t', 'inlineStr');
        $inlineString = $dom->createElementNS(self::SHEET_NS, 'is');
        $text = $dom->createElementNS(self::SHEET_NS, 't');
        $text->appendChild($dom->createTextNode((string) $value));
        $inlineString->appendChild($text);
        $cell->appendChild($inlineString);
    }

    private function buildWorksheetRelXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="' . self::PACKAGE_REL_NS . '">'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/table" Target="../tables/table1.xml"/>'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/printerSettings" Target="../printerSettings/printerSettings1.bin"/>'
            . '</Relationships>';
    }

    private function buildTableXml(string $baseTableXml, int $rowCount): string
    {
        $dom = $this->loadXml($baseTableXml);
        $table = $dom->documentElement;
        $lastRow = $rowCount + 1;
        $table->setAttribute('id', '1');
        $table->setAttribute('name', 'RecordPaper');
        $table->setAttribute('displayName', 'RecordPaper');
        $table->setAttribute('ref', "A1:W{$lastRow}");

        $autoFilter = $table->getElementsByTagNameNS(self::SHEET_NS, 'autoFilter')->item(0);
        if ($autoFilter instanceof DOMElement) {
            $autoFilter->setAttribute('ref', "A1:W{$lastRow}");
        }

        return $dom->saveXML();
    }

    private function buildWorkbookXml(): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $workbook = $dom->createElementNS(self::SHEET_NS, 'workbook');
        $workbook->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:r', self::REL_NS);
        $dom->appendChild($workbook);

        $bookViews = $dom->createElementNS(self::SHEET_NS, 'bookViews');
        $bookViews->appendChild($dom->createElementNS(self::SHEET_NS, 'workbookView'));
        $workbook->appendChild($bookViews);

        $sheets = $dom->createElementNS(self::SHEET_NS, 'sheets');
        $sheet = $dom->createElementNS(self::SHEET_NS, 'sheet');
        $sheet->setAttribute('name', '工作表1');
        $sheet->setAttribute('sheetId', '1');
        $sheet->setAttributeNS(self::REL_NS, 'r:id', 'rId1');
        $sheets->appendChild($sheet);
        $workbook->appendChild($sheets);

        $definedNames = $dom->createElementNS(self::SHEET_NS, 'definedNames');
        $printTitles = $dom->createElementNS(self::SHEET_NS, 'definedName', '工作表1!$1:$1');
        $printTitles->setAttribute('name', '_xlnm.Print_Titles');
        $printTitles->setAttribute('localSheetId', '0');
        $definedNames->appendChild($printTitles);
        $workbook->appendChild($definedNames);

        $workbook->appendChild($dom->createElementNS(self::SHEET_NS, 'calcPr'));

        return $dom->saveXML();
    }

    private function buildWorkbookRelXml(): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $relationships = $dom->createElementNS(self::PACKAGE_REL_NS, 'Relationships');
        $dom->appendChild($relationships);
        $this->appendRelationship($dom, $relationships, 'rId1', 'worksheet', 'worksheets/sheet1.xml');
        $this->appendRelationship($dom, $relationships, 'rId2', 'theme', 'theme/theme1.xml');
        $this->appendRelationship($dom, $relationships, 'rId3', 'styles', 'styles.xml');
        $this->appendRelationship($dom, $relationships, 'rId4', 'sharedStrings', 'sharedStrings.xml');

        return $dom->saveXML();
    }

    private function appendRelationship(DOMDocument $dom, DOMElement $parent, string $id, string $type, string $target): void
    {
        $relationship = $dom->createElementNS(self::PACKAGE_REL_NS, 'Relationship');
        $relationship->setAttribute('Id', $id);
        $relationship->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/' . $type);
        $relationship->setAttribute('Target', $target);
        $parent->appendChild($relationship);
    }

    private function buildContentTypesXml(): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = false;
        $types = $dom->createElementNS(self::CONTENT_TYPES_NS, 'Types');
        $dom->appendChild($types);

        $defaults = [
            ['bin', 'application/vnd.openxmlformats-officedocument.spreadsheetml.printerSettings'],
            ['rels', 'application/vnd.openxmlformats-package.relationships+xml'],
            ['xml', 'application/xml'],
        ];

        foreach ($defaults as [$extension, $contentType]) {
            $default = $dom->createElementNS(self::CONTENT_TYPES_NS, 'Default');
            $default->setAttribute('Extension', $extension);
            $default->setAttribute('ContentType', $contentType);
            $types->appendChild($default);
        }

        $overrides = [
            ['/xl/workbook.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml'],
            ['/xl/worksheets/sheet1.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml'],
            ['/xl/theme/theme1.xml', 'application/vnd.openxmlformats-officedocument.theme+xml'],
            ['/xl/styles.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml'],
            ['/xl/sharedStrings.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml'],
            ['/xl/tables/table1.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.table+xml'],
            ['/docProps/core.xml', 'application/vnd.openxmlformats-package.core-properties+xml'],
            ['/docProps/app.xml', 'application/vnd.openxmlformats-officedocument.extended-properties+xml'],
        ];

        foreach ($overrides as [$partName, $contentType]) {
            $override = $dom->createElementNS(self::CONTENT_TYPES_NS, 'Override');
            $override->setAttribute('PartName', $partName);
            $override->setAttribute('ContentType', $contentType);
            $types->appendChild($override);
        }

        return $dom->saveXML();
    }

    private function buildAppXml(): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;
        $propsNs = 'http://schemas.openxmlformats.org/officeDocument/2006/extended-properties';
        $vtNs = 'http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes';
        $props = $doc->createElementNS($propsNs, 'Properties');
        $props->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:vt', $vtNs);
        $doc->appendChild($props);

        $props->appendChild($doc->createElementNS($propsNs, 'Application', 'Microsoft Excel'));
        $props->appendChild($doc->createElementNS($propsNs, 'DocSecurity', '0'));
        $props->appendChild($doc->createElementNS($propsNs, 'ScaleCrop', 'false'));

        $headingPairs = $doc->createElementNS($propsNs, 'HeadingPairs');
        $headingVector = $doc->createElementNS($vtNs, 'vt:vector');
        $headingVector->setAttribute('size', '2');
        $headingVector->setAttribute('baseType', 'variant');
        $variantName = $doc->createElementNS($vtNs, 'vt:variant');
        $variantName->appendChild($doc->createElementNS($vtNs, 'vt:lpstr', '工作表'));
        $variantCount = $doc->createElementNS($vtNs, 'vt:variant');
        $variantCount->appendChild($doc->createElementNS($vtNs, 'vt:i4', '1'));
        $headingVector->appendChild($variantName);
        $headingVector->appendChild($variantCount);
        $headingPairs->appendChild($headingVector);
        $props->appendChild($headingPairs);

        $titles = $doc->createElementNS($propsNs, 'TitlesOfParts');
        $titleVector = $doc->createElementNS($vtNs, 'vt:vector');
        $titleVector->setAttribute('size', '1');
        $titleVector->setAttribute('baseType', 'lpstr');
        $titleVector->appendChild($doc->createElementNS($vtNs, 'vt:lpstr', '工作表1'));
        $titles->appendChild($titleVector);
        $props->appendChild($titles);

        $props->appendChild($doc->createElementNS($propsNs, 'Company', 'Smithsonian Tropical Research Institute'));
        $props->appendChild($doc->createElementNS($propsNs, 'LinksUpToDate', 'false'));
        $props->appendChild($doc->createElementNS($propsNs, 'SharedDoc', 'false'));
        $props->appendChild($doc->createElementNS($propsNs, 'HyperlinksChanged', 'false'));
        $props->appendChild($doc->createElementNS($propsNs, 'AppVersion', '16.0300'));

        return $doc->saveXML();
    }

    private function loadXml(string $xml): DOMDocument
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $dom->loadXML($xml);

        return $dom;
    }

    private function mapForPaper($map): ?string
    {
        $map = $this->blankToNull($map);

        if ($map === null) {
            return null;
        }

        return ctype_digit($map) ? $map : '=';
    }

    private function statusForPaper($status): ?string
    {
        $status = strtoupper(trim((string) $status));

        if ($status === '' || $status === 'A') {
            return null;
        }

        return $status;
    }

    private function formatQ20Value($q20): ?string
    {
        $q20 = $this->blankToNull($q20);

        if ($q20 === null || !str_contains($q20, ',')) {
            return $q20;
        }

        [$qx, $qy] = array_map('trim', explode(',', $q20, 2));

        if (is_numeric($qx) && is_numeric($qy)) {
            return str_pad((string) (int) $qx, 2, '0', STR_PAD_LEFT)
                . ','
                . str_pad((string) (int) $qy, 2, '0', STR_PAD_LEFT);
        }

        return $q20;
    }

    private function formatPair($first, $second): ?string
    {
        $first = $this->blankToNull($first);
        $second = $this->blankToNull($second);

        if ($first === null || $second === null) {
            return null;
        }

        if (is_numeric($first) && is_numeric($second)) {
            return str_pad((string) (int) $first, 2, '0', STR_PAD_LEFT)
                . ','
                . str_pad((string) (int) $second, 2, '0', STR_PAD_LEFT);
        }

        return $first . ',' . $second;
    }

    private function formatNumber($value): string
    {
        $number = (float) $value;
        $formatted = number_format($number, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function blankToNull($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
