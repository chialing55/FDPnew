<?php

namespace App\Http\Controllers\Fushan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;

use Dompdf\Dompdf;
use setasign\Fpdi\Fpdi;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\FsSeedlingSlrecord;

//產生pdf紀錄紙的內容
//小苗比對之後，產生比對結果的資料表

class SeedlingPDFController extends Controller
{
    public function record($start, $end){
        $start = (int) $start;
        $end = (int) $end;

        if ($start < 1 || $end < 1 || $start > $end) {
            return redirect()->back()->with('alert', '請輸入正確的樣站範圍。');
        }

        $rangeData = $this->buildRangeData($start, $end);

        if ($rangeData['record']->isEmpty()) {
            return redirect()->back()->with('alert', '此範圍沒有可輸出的紀錄紙資料。');
        }

        $chunks = $this->buildSafeChunks($start, $end);

        if (count($chunks) > 1) {
            return view('pages.fushan.seedling_record_split', [
                'site' => 'fushan',
                'project' => '小苗',
                'user' => auth()->user()?->name ?? '',
                'title' => '小苗紀錄紙分段下載',
                'start' => $start,
                'end' => $end,
                'chunks' => $chunks,
                'metrics' => $rangeData['metrics'],
            ]);
        }

        return $this->streamRecordPdf($rangeData);
    }

    protected function buildRangeData(int $start, int $end): array
    {
        $plots = [];
        for ($trap = $start; $trap <= $end; $trap++) {
            for ($plot = 1; $plot < 4; $plot++) {
                $plots[] = $trap . "-" . $plot;
            }
        }

        $record = FsSeedlingSlrecord::whereBetween('trap', [$start, $end])
            ->orderBy('trap', 'asc')
            ->orderBy('plot', 'asc')
            ->orderBy('tag', 'asc')
            ->get();

        $groupedRecord = [];
        foreach ($record as $item) {
            $plot = $item['trap'] . "-" . $item['plot'];
            $item['TP'] = $plot;
            $groupedRecord[$plot][] = $item;
        }

        $recordCount = $record->count();
        $noteCharCount = $record->sum(function ($item) {
            return mb_strlen((string) ($item['note'] ?? ''));
        });
        $longNoteCount = $record->filter(function ($item) {
            return mb_strlen((string) ($item['note'] ?? '')) > 35;
        })->count();
        $trapSpan = ($end - $start) + 1;
        $estimatedPages = (int) ceil(($recordCount + count($plots)) / 31);

        return [
            'start' => $start,
            'end' => $end,
            'plot' => $plots,
            'record' => $record,
            'groupedRecord' => $groupedRecord,
            'metrics' => [
                'record_count' => $recordCount,
                'note_char_count' => $noteCharCount,
                'long_note_count' => $longNoteCount,
                'trap_span' => $trapSpan,
                'estimated_pages' => $estimatedPages,
            ],
        ];
    }

    protected function shouldSplitRange(array $rangeData): bool
    {
        $metrics = $rangeData['metrics'];

        return $metrics['record_count'] > 600
            || $metrics['estimated_pages'] > 20
            || $metrics['note_char_count'] > 3200
            || $metrics['long_note_count'] > 20;
    }

    protected function buildSafeChunks(int $start, int $end): array
    {
        $rangeData = $this->buildRangeData($start, $end);

        if ($start === $end || !$this->shouldSplitRange($rangeData)) {
            return [[
                'start' => $start,
                'end' => $end,
                'metrics' => $rangeData['metrics'],
            ]];
        }

        $chunks = [];
        $chunkStart = $start;
        $trap = $start;

        while ($trap <= $end) {
            $candidateRangeData = $this->buildRangeData($chunkStart, $trap);

            if (!$this->shouldSplitRange($candidateRangeData)) {
                $trap++;
                continue;
            }

            if ($trap === $chunkStart) {
                $chunks[] = [
                    'start' => $trap,
                    'end' => $trap,
                    'metrics' => $candidateRangeData['metrics'],
                ];
                $chunkStart = $trap + 1;
                $trap = $chunkStart;
                continue;
            }

            $safeRangeData = $this->buildRangeData($chunkStart, $trap - 1);
            $chunks[] = [
                'start' => $chunkStart,
                'end' => $trap - 1,
                'metrics' => $safeRangeData['metrics'],
            ];
            $chunkStart = $trap;
        }

        if ($chunkStart <= $end) {
            $safeRangeData = $this->buildRangeData($chunkStart, $end);
            $chunks[] = [
                'start' => $chunkStart,
                'end' => $end,
                'metrics' => $safeRangeData['metrics'],
            ];
        }

        return $chunks;
    }

    protected function streamRecordPdf(array $rangeData)
    {
        $record = $rangeData['record'];
        $start = $rangeData['start'];
        $end = $rangeData['end'];

        $mtags = $record->pluck('mtag')->filter()->unique()->values();
        $maxbtable = DB::connection('mysql3')
            ->table('seedling_stems')
            ->select('mtag', DB::raw('MAX(CAST(SUBSTRING_INDEX(tag, ".", -1) AS DECIMAL)) AS max_b'))
            ->where('sprout', 'like', 'TRUE')
            ->when($mtags->isNotEmpty(), function ($query) use ($mtags) {
                $query->whereIn('mtag', $mtags);
            })
            ->groupBy('mtag')
            ->get();

        $maxb = [];
        foreach ($maxbtable as $item) {
            if ($item->max_b != '0' && $item->max_b < 200) {
                $maxb[$item->mtag] = $item->max_b;
            }
        }

        $firstRecord = $record->first();
        $fileYear = date('Y');
        $census = $firstRecord['census'];
        $month = ((int) $firstRecord['census'] % 2 === 1) ? '八' : '二';
        $title = $fileYear . ' 年' . $month . '月第 ' . $census . ' 次福山喬木小苗調查 (' . $start . "-" . $end . ")";
        $filename = $fileYear . '_' . $census . '_seedling_record_' . $start . '-' . $end . '.pdf';

        $data = [
            'title' => $title,
            'record' => $rangeData['groupedRecord'],
            'maxb' => $maxb,
            'plot' => $rangeData['plot'],
            'numPagesTotal' => $rangeData['metrics']['estimated_pages'],
            'start' => $start,
            'end' => $end,
        ];

        ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $pdf = PDF::loadView('pages.fushan.seedling_record', $data)->setPaper('A4', 'landscape');
        $pdf->set_option('isFontSubsettingEnabled', true);

        return $pdf->stream($filename);
    }


    public function compare(Request $request){
        $comnote = $request->session()->get('comnote');
        $data = [
            'title' => '福山小苗資料比對結果',
            'comnote' => $comnote ?: '目前沒有可匯出的比對結果。',
        ];

        $pdf = PDF::loadView('pages.fushan.seedling_compare_pdf', $data)->setPaper('A4');
        $pdf->set_option('isFontSubsettingEnabled', true);
        $pdf->set_option('defaultFont', 'msjh');
        return $pdf->stream("seedling_compare.pdf");


    }

}
