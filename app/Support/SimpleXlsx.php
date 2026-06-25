<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SimpleXlsx
{
    public static function download(string $filename, array $headings, array $rows, string $sheetTitle = 'Sheet1', array $mergeSameColumns = []): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows, $sheetTitle, $mergeSameColumns) {
            $path = tempnam(sys_get_temp_dir(), 'xlsx-');
            self::write($path, $headings, $rows, $sheetTitle, $mergeSameColumns);
            readfile($path);
            @unlink($path);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private static function write(string $path, array $headings, array $rows, string $sheetTitle, array $mergeSameColumns = []): void
    {
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('無法建立 Excel 檔案。');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::rootRelsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml($sheetTitle));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelsXml());
        $zip->addFromString('xl/styles.xml', self::stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::sheetXml($headings, $rows, $mergeSameColumns));
        $zip->close();
    }

    private static function sheetXml(array $headings, array $rows, array $mergeSameColumns = []): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<cols>';

        foreach (self::columnWidths($headings) as $index => $width) {
            $col = $index + 1;
            $xml .= '<col min="' . $col . '" max="' . $col . '" width="' . $width . '" customWidth="1"/>';
        }

        $xml .= '</cols><sheetData>';
        $xml .= self::rowXml(1, $headings, null, 1);

        foreach (array_values($rows) as $index => $row) {
            $values = [];
            foreach ($headings as $heading) {
                $values[] = $row[$heading] ?? '';
            }
            $xml .= self::rowXml($index + 2, $values, $headings);
        }

        $xml .= '</sheetData>';

        $mergeCells = self::mergeCellsXml($headings, $rows, $mergeSameColumns);
        if ($mergeCells !== '') {
            $xml .= $mergeCells;
        }

        return $xml . '</worksheet>';
    }

    private static function mergeCellsXml(array $headings, array $rows, array $mergeSameColumns): string
    {
        $ranges = [];

        foreach ($mergeSameColumns as $heading) {
            $columnIndex = array_search($heading, $headings, true);

            if ($columnIndex === false) {
                continue;
            }

            $columnName = self::columnName($columnIndex + 1);
            $startRow = 2;
            $previous = null;

            foreach (array_values($rows) as $index => $row) {
                $excelRow = $index + 2;
                $current = trim((string) ($row[$heading] ?? ''));

                if ($previous === null) {
                    $previous = $current;
                    $startRow = $excelRow;
                    continue;
                }

                if ($current !== $previous) {
                    if ($previous !== '' && $excelRow - 1 > $startRow) {
                        $ranges[] = "{$columnName}{$startRow}:{$columnName}" . ($excelRow - 1);
                    }

                    $previous = $current;
                    $startRow = $excelRow;
                }
            }

            $lastRow = count($rows) + 1;
            if ($previous !== null && $previous !== '' && $lastRow > $startRow) {
                $ranges[] = "{$columnName}{$startRow}:{$columnName}{$lastRow}";
            }
        }

        if ($ranges === []) {
            return '';
        }

        $xml = '<mergeCells count="' . count($ranges) . '">';
        foreach ($ranges as $range) {
            $xml .= '<mergeCell ref="' . $range . '"/>';
        }

        return $xml . '</mergeCells>';
    }

    private static function columnWidths(array $headings): array
    {
        $default = [
            '行號' => 8,
            '科名' => 20,
            '學名' => 44,
            '中文名' => 24,
            '開花' => 10,
            '結果' => 10,
            '小苗' => 10,
        ];

        return array_map(fn ($heading) => $default[$heading] ?? 14, $headings);
    }

    private static function rowXml(int $rowNumber, array $values, ?array $headings = null, int $style = 2): string
    {
        $xml = '<row r="' . $rowNumber . '">';

        foreach (array_values($values) as $index => $value) {
            $cell = self::columnName($index + 1) . $rowNumber;
            $isScientific = ($headings[$index] ?? null) === '學名';
            $cellStyle = $isScientific ? 2 : $style;
            $styleAttr = $cellStyle > 0 ? ' s="' . $cellStyle . '"' : '';
            $inlineString = $isScientific
                ? self::scientificInlineString((string) $value)
                : '<is><t>' . self::escape((string) $value) . '</t></is>';

            $xml .= '<c r="' . $cell . '" t="inlineStr"' . $styleAttr . '>' . $inlineString . '</c>';
        }

        return $xml . '</row>';
    }

    private static function scientificInlineString(string $text): string
    {
        $xml = '<is>';

        foreach (ScientificNameFormatter::segments($text) as $segment) {
            $xml .= self::richTextRun($segment['text'], $segment['italic']);
        }

        return $xml . '</is>';
    }

    private static function richTextRun(string $text, bool $italic): string
    {
        $runProps = $italic ? '<rPr><i/><sz val="11"/><rFont val="Calibri"/></rPr>' : '<rPr><sz val="11"/><rFont val="Calibri"/></rPr>';

        return '<r>' . $runProps . '<t xml:space="preserve">' . self::escape($text) . '</t></r>';
    }

    private static function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function rootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbookXml(string $sheetTitle): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . self::escape(mb_substr($sheetTitle, 0, 31)) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function workbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private static function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border/></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top"/></xf></cellXfs>'
            . '</styleSheet>';
    }
}
