<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SimpleDocx
{
    public static function download(string $filename, array $headings, array $rows, string $title = '植物名錄'): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows, $title) {
            $path = tempnam(sys_get_temp_dir(), 'docx-');
            self::write($path, $headings, $rows, $title);
            readfile($path);
            @unlink($path);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    private static function write(string $path, array $headings, array $rows, string $title): void
    {
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('無法建立 Word 檔案。');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::relsXml());
        $zip->addFromString('word/styles.xml', self::stylesXml());
        $zip->addFromString('word/document.xml', self::documentXml($headings, $rows, $title));

        if (!$zip->close()) {
            throw new \RuntimeException('Word 檔案寫入失敗。');
        }
    }

    private static function documentXml(array $headings, array $rows, string $title): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>'
            . self::paragraph($title, 'center', true)
            . self::tableXml($headings, $rows)
            . self::legendXml()
            . '<w:sectPr><w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/><w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720" w:header="360" w:footer="360" w:gutter="0"/></w:sectPr>'
            . '</w:body></w:document>';
    }

    private static function tableXml(array $headings, array $rows): string
    {
        $widths = self::columnWidths($headings);
        $tableWidth = array_sum($widths);

        $xml = '<w:tbl><w:tblPr><w:tblW w:w="' . $tableWidth . '" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/>'
            . '<w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '</w:tblBorders></w:tblPr><w:tblGrid>';

        foreach ($widths as $width) {
            $xml .= '<w:gridCol w:w="' . $width . '"/>';
        }

        $xml .= '</w:tblGrid><w:tr><w:trPr><w:tblHeader/></w:trPr>';

        foreach ($headings as $index => $heading) {
            $options = ['align' => 'center', 'bold' => true];
            $text = $heading;

            if ($heading === '行號') {
                $options['borderless'] = true;
                $text = '';
            }

            if (in_array($heading, ['科名', '學名', '中文名'], true)) {
                $options['leftMargin'] = 120;
            }

            $xml .= self::cell($text, $widths[$index] ?? 1200, $options);
        }

        $xml .= '</w:tr>';

        $familySpans = self::familySpans($rows);
        foreach (array_values($rows) as $index => $row) {
            $excelRow = $index + 2;
            $xml .= '<w:tr>';

            foreach ($headings as $columnIndex => $heading) {
                $options = [];
                if ($heading === '科名') {
                    if (($familySpans[$excelRow] ?? null) === 'continue') {
                        $options['vMerge'] = 'continue';
                        $row[$heading] = '';
                    } elseif (($familySpans[$excelRow] ?? null) === 'restart') {
                        $options['vMerge'] = 'restart';
                    }
                }

                $align = in_array($heading, ['開花', '結果', '小苗'], true) ? 'center' : 'left';
                $options['align'] = $align;

                if ($heading === '行號') {
                    $options['align'] = 'right';
                    $options['borderless'] = true;
                    $options['color'] = '666666';
                }

                if (in_array($heading, ['科名', '學名', '中文名'], true)) {
                    $options['leftMargin'] = 120;
                }

                if ($heading === '學名') {
                    $options['scientific'] = true;
                }

                $xml .= self::cell((string) ($row[$heading] ?? ''), $widths[$columnIndex] ?? 1200, $options);
            }

            $xml .= '</w:tr>';
        }

        return $xml . '</w:tbl>';
    }

    private static function columnWidths(array $headings): array
    {
        $default = [
            '行號' => 520,
            '科名' => 2200,
            '學名' => 5600,
            '中文名' => 2600,
            '開花' => 900,
            '結果' => 900,
            '小苗' => 900,
        ];

        return array_map(fn ($heading) => $default[$heading] ?? 1200, $headings);
    }

    private static function legendXml(): string
    {
        return self::paragraph('', 'left')
            . self::paragraph('●：有紀錄到花、成熟果實或小苗的植物', 'left')
            . self::paragraph('●*：有紀錄到成熟果實但因種子太小而不算種子數', 'left')
            . self::paragraph('●#：僅紀錄到成熟果實的碎片的植物', 'left')
            . self::paragraph('●^：僅紀錄到成熟種子的植物', 'left')
            . self::paragraph('●$：調查期間有紀錄到新增苗', 'left');
    }

    private static function familySpans(array $rows): array
    {
        $spans = [];
        $start = 2;
        $previous = null;

        foreach (array_values($rows) as $index => $row) {
            $wordRow = $index + 2;
            $current = trim((string) ($row['科名'] ?? ''));

            if ($previous === null) {
                $previous = $current;
                $start = $wordRow;
                continue;
            }

            if ($current !== $previous) {
                if ($previous !== '' && $wordRow - 1 > $start) {
                    $spans[$start] = 'restart';
                    for ($r = $start + 1; $r <= $wordRow - 1; $r++) {
                        $spans[$r] = 'continue';
                    }
                }

                $previous = $current;
                $start = $wordRow;
            }
        }

        $lastRow = count($rows) + 1;
        if ($previous !== null && $previous !== '' && $lastRow > $start) {
            $spans[$start] = 'restart';
            for ($r = $start + 1; $r <= $lastRow; $r++) {
                $spans[$r] = 'continue';
            }
        }

        return $spans;
    }

    private static function cell(string $text, int $width, array $options = []): string
    {
        $props = '<w:tcPr><w:tcW w:w="' . $width . '" w:type="dxa"/>';
        if (($options['vMerge'] ?? null) === 'restart') {
            $props .= '<w:vMerge w:val="restart"/>';
        } elseif (($options['vMerge'] ?? null) === 'continue') {
            $props .= '<w:vMerge/>';
        }
        if (!empty($options['borderless'])) {
            $props .= '<w:tcBorders><w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/><w:insideH w:val="nil"/><w:insideV w:val="nil"/></w:tcBorders>';
        }
        if (isset($options['leftMargin'])) {
            $props .= '<w:tcMar><w:left w:w="' . (int) $options['leftMargin'] . '" w:type="dxa"/></w:tcMar>';
        }
        $props .= '<w:vAlign w:val="top"/></w:tcPr>';

        $paragraph = !empty($options['scientific'])
            ? self::scientificParagraph($text, $options['align'] ?? 'left', $options['color'] ?? null)
            : self::paragraph($text, $options['align'] ?? 'left', (bool) ($options['bold'] ?? false), $options['color'] ?? null);

        return '<w:tc>' . $props . $paragraph . '</w:tc>';
    }

    private static function paragraph(string $text, string $align = 'left', bool $bold = false, ?string $color = null): string
    {
        return '<w:p><w:pPr><w:jc w:val="' . $align . '"/></w:pPr>'
            . self::run($text, ['bold' => $bold, 'color' => $color])
            . '</w:p>';
    }

    private static function scientificParagraph(string $text, string $align = 'left', ?string $color = null): string
    {
        $xml = '<w:p><w:pPr><w:jc w:val="' . $align . '"/></w:pPr>';

        foreach (ScientificNameFormatter::segments($text) as $segment) {
            $xml .= self::run($segment['text'], ['italic' => $segment['italic'], 'color' => $color]);
        }

        return $xml . '</w:p>';
    }

    private static function run(string $text, array $options = []): string
    {
        $boldXml = !empty($options['bold']) ? '<w:b/>' : '';
        $italicXml = !empty($options['italic']) ? '<w:i/>' : '';
        $colorXml = isset($options['color']) ? '<w:color w:val="' . self::escape((string) $options['color']) . '"/>' : '';

        return '<w:r><w:rPr>'
            . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:eastAsia="標楷體" w:cs="Times New Roman"/>'
            . '<w:sz w:val="24"/>' . $colorXml . $boldXml . $italicXml . '</w:rPr><w:t xml:space="preserve">'
            . self::escape($text)
            . '</w:t></w:r>';
    }

    private static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . '</Types>';
    }

    private static function relsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';
    }

    private static function stylesXml(): string
    {
        $fontXml = '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:eastAsia="標楷體" w:cs="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:docDefaults><w:rPrDefault><w:rPr>' . $fontXml . '</w:rPr></w:rPrDefault></w:docDefaults>'
            . '<w:style w:type="paragraph" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr>' . $fontXml . '</w:rPr></w:style>'
            . '</w:styles>';
    }
}
