<?php

namespace App\Http\Livewire\PlantCatalog;

use App\Models\PlantCatalog\TaiwanChecklist;
use App\Support\SimpleDocx;
use App\Support\SimpleXlsx;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ChecklistDownload extends Component
{
    public string $site = 'fushan';
    public array $selectedTypes = ['seeds', 'seedling'];
    public array $ranges = [];
    public string $message = '';

    public function mount(): void
    {
        foreach ($this->dataTypeOptions() as $key => $option) {
            $dateOptions = $this->dateOptions($key);
            $this->ranges[$key] = [
                'start' => $dateOptions->first()['census'] ?? null,
                'end' => $dateOptions->last()['census'] ?? null,
            ];
        }
    }

    public function download()
    {
        $download = $this->buildDownloadRows();

        if ($download === null) {
            return null;
        }

        [$headings, $rows] = $download;
        $filename = 'plant_catalog_fushan_' . now()->format('Ymd') . '.xlsx';

        return SimpleXlsx::download($filename, $headings, $rows, '植物名錄', ['科名']);
    }

    public function downloadWord()
    {
        $download = $this->buildDownloadRows();

        if ($download === null) {
            return null;
        }

        [$headings, $rows] = $download;
        $filename = 'plant_catalog_fushan_' . now()->format('Ymd') . '.docx';

        return SimpleDocx::download($filename, $headings, $rows, '植物名錄');
    }

    private function buildDownloadRows(): ?array
    {
        $this->message = '';

        if ($this->site !== 'fushan') {
            $this->message = '暫未開放下載。';
            return null;
        }

        $selectedTypes = array_values(array_intersect($this->selectedTypes, array_keys($this->dataTypeOptions())));

        if ($selectedTypes === []) {
            $this->message = '請至少選擇一種資料類型。';
            return null;
        }

        $marksByCsp = [];

        foreach ($selectedTypes as $type) {
            [$start, $end] = $this->normalizedRange($type);

            if ($start === null || $end === null) {
                $this->message = '請確認資料範圍。';
                return null;
            }

            if ($type === 'seeds') {
                $this->mergeMarks($marksByCsp, $this->seedMarks($start, $end));
            }

            if ($type === 'seedling') {
                $this->mergeMarks($marksByCsp, $this->seedlingMarks($start, $end));
            }
        }

        $rows = $this->catalogRows($marksByCsp);
        $headings = ['行號', '科名', '學名', '中文名', '開花', '結果', '小苗'];

        return [$headings, $rows];
    }

    public function siteOptions(): array
    {
        return [
            'fushan' => '福山',
            'shoushan' => '壽山',
            'nanjenshan' => '南仁山',
        ];
    }

    public function dataTypeOptions(): array
    {
        return [
            'seeds' => [
                'label' => '種子雨',
                'range_label' => '種子雨資料範圍',
            ],
            'seedling' => [
                'label' => '小苗',
                'range_label' => '小苗資料範圍',
            ],
        ];
    }

    public function dateOptions(string $type): Collection
    {
        if ($type === 'seeds') {
            return DB::connection('mysql2')
                ->table('dateinfo')
                ->select('census', 'date')
                ->whereNotNull('census')
                ->orderByDesc('census')
                ->get()
                ->map(fn ($row) => [
                    'census' => $row->census,
                    'label' => 'census ' . $row->census . ' : ' . ($row->date ?? ''),
                ])
                ->values();
        }

        if ($type === 'seedling') {
            return DB::connection('mysql3')
                ->table('seedling_records')
                ->select('census', 'year', 'month')
                ->whereNotNull('census')
                ->whereNotNull('year')
                ->whereNotNull('month')
                ->groupBy('census', 'year', 'month')
                ->orderByDesc('census')
                ->get()
                ->map(function ($row) {
                    return [
                        'census' => $row->census,
                        'label' => sprintf('census %s: %04d-%02d', $row->census, (int) $row->year, (int) $row->month),
                    ];
                })
                ->values();
        }

        return collect();
    }

    private function normalizedRange(string $type): array
    {
        $valid = $this->dateOptions($type)->pluck('census')->map(fn ($census) => (string) $census)->all();
        $start = $this->ranges[$type]['start'] ?? null;
        $end = $this->ranges[$type]['end'] ?? null;

        if (!in_array((string) $start, $valid, true) || !in_array((string) $end, $valid, true)) {
            return [null, null];
        }

        return [min((int) $start, (int) $end), max((int) $start, (int) $end)];
    }

    private function seedMarks(int $start, int $end): array
    {
        return DB::connection('mysql2')
            ->table('fulldata as f')
            ->leftJoin('splist as s', 'f.csp', '=', 's.csp')
            ->select('f.csp')
            ->selectRaw("MAX(CASE WHEN f.code = '6' THEN 1 ELSE 0 END) as has_flower")
            ->selectRaw("MAX(CASE WHEN f.code = '1' THEN 1 ELSE 0 END) as has_fruit")
            ->selectRaw("MAX(CASE WHEN f.code = '2' THEN 1 ELSE 0 END) as has_seed")
            ->selectRaw("MAX(CASE WHEN f.code IN ('3', '4') THEN 1 ELSE 0 END) as has_fragment")
            ->selectRaw("MAX(CASE WHEN s.size = 'S' THEN 1 ELSE 0 END) as is_small_seed")
            ->whereBetween('f.census', [$start, $end])
            ->whereNotNull('f.csp')
            ->where('f.csp', '!=', '')
            ->where('f.csp', '!=', 'nothing')
            ->whereIn('f.code', ['1', '2', '3', '4', '6'])
            ->groupBy('f.csp')
            ->get()
            ->mapWithKeys(function ($row) {
                $hasFruit = (bool) $row->has_fruit;
                $hasSeed = (bool) $row->has_seed;
                $hasFragment = (bool) $row->has_fragment;
                $isSmallSeed = (bool) $row->is_small_seed;

                return [
                    trim((string) $row->csp) => [
                        '開花' => (bool) $row->has_flower ? '●' : '',
                        '結果' => $this->seedResultMark($hasFruit, $hasSeed, $hasFragment, $isSmallSeed),
                        '小苗' => '',
                    ],
                ];
            })
            ->all();
    }

    private function seedResultMark(bool $hasFruit, bool $hasSeed, bool $hasFragment, bool $isSmallSeed): string
    {
        if ($hasFruit) {
            return $isSmallSeed ? '●*' : '●';
        }

        if ($hasSeed) {
            return '●^';
        }

        if ($hasFragment) {
            return '●#';
        }

        return '';
    }

    private function seedlingMarks(int $start, int $end): array
    {
        $query = DB::connection('mysql3')
            ->table('seedling_records as r')
            ->join('seedling_stems as st', 'r.tag', '=', 'st.tag')
            ->join('seedling_individuals as i', 'st.mtag', '=', 'i.mtag')
            ->whereBetween('r.census', [$start, $end])
            ->whereIn('r.recruit', ['O', 'R'])
            ->whereIn('r.status', ['A', 'T', 'S'])
            ->whereRaw("UPPER(COALESCE(st.sprout, '')) = 'FALSE'")
            ->whereNotNull('i.csp')
            ->where('i.csp', '!=', '')
            ->where('i.csp', '!=', 'UNK')
            ->where('i.csp', '!=', 'unk')
            ->select('i.csp')
            ->selectRaw("MAX(CASE WHEN r.recruit = 'R' THEN 1 ELSE 0 END) as has_recruit")
            ->groupBy('i.csp');

        foreach ([
            'seedling_records' => 'r',
            'seedling_stems' => 'st',
            'seedling_individuals' => 'i',
        ] as $table => $alias) {
            if (Schema::connection('mysql3')->hasColumn($table, 'deleted_at')) {
                $query->whereNull($alias . '.deleted_at');
            }
        }

        return $query->get()
            ->mapWithKeys(fn ($row) => [
                trim((string) $row->csp) => [
                    '開花' => '',
                    '結果' => '',
                    '小苗' => (bool) $row->has_recruit ? '●$' : '●',
                ],
            ])
            ->all();
    }

    private function mergeMarks(array &$target, array $source): void
    {
        foreach ($source as $csp => $marks) {
            $target[$csp] ??= ['開花' => '', '結果' => '', '小苗' => ''];
            foreach (['開花', '結果', '小苗'] as $column) {
                $target[$csp][$column] = $this->preferredMark($target[$csp][$column], $marks[$column] ?? '');
            }
        }
    }

    private function preferredMark(string $current, string $incoming): string
    {
        $priority = [
            '' => 0,
            '●^' => 1,
            '●#' => 2,
            '●*' => 3,
            '●' => 4,
            '●$' => 5,
        ];

        return ($priority[$incoming] ?? 0) > ($priority[$current] ?? 0) ? $incoming : $current;
    }

    private function catalogRows(array $marksByCsp): array
    {
        if ($marksByCsp === []) {
            return [];
        }

        $cspList = array_keys($marksByCsp);
        $codeColumn = $this->spinfoCodeColumn();
        $spinfoRows = DB::connection('mysql4')
            ->table('spinfo')
            ->whereIn('csp', $cspList)
            ->select('csp', $codeColumn . ' as checklist_spcode')
            ->get();

        $marksByCode = [];
        $cspByCode = [];

        foreach ($spinfoRows as $spinfo) {
            $csp = trim((string) $spinfo->csp);
            $code = trim((string) $spinfo->checklist_spcode);

            if ($csp === '' || $code === '') {
                continue;
            }

            $marksByCode[$code] ??= ['開花' => '', '結果' => '', '小苗' => ''];
            $cspByCode[$code] ??= $csp;

            foreach (['開花', '結果', '小苗'] as $column) {
                $marksByCode[$code][$column] = $this->preferredMark($marksByCode[$code][$column], $marksByCsp[$csp][$column] ?? '');
            }
        }

        $checklistRows = TaiwanChecklist::query()
            ->whereIn('spcode', array_keys($marksByCode))
            ->get(['spcode', 'chfamily', 'canonical_name']);

        return $checklistRows
            ->map(function ($item) use ($marksByCode, $cspByCode) {
                $spcode = (string) $item->spcode;
                $marks = $marksByCode[$spcode] ?? [];

                return [
                    '科名' => $item->chfamily ?? '',
                    '學名' => $item->canonical_name ?? '',
                    '中文名' => $cspByCode[$spcode] ?? '',
                    '開花' => $marks['開花'] ?? '',
                    '結果' => $marks['結果'] ?? '',
                    '小苗' => $marks['小苗'] ?? '',
                ];
            })
            ->sortBy([
                ['科名', 'asc'],
                ['學名', 'asc'],
                ['中文名', 'asc'],
            ])
            ->values()
            ->map(function ($row, $index) {
                return ['行號' => $index + 1] + $row;
            })
            ->all();
    }

    private function spinfoCodeColumn(): string
    {
        if (Schema::connection('mysql4')->hasColumn('spinfo', 'code')) {
            return 'code';
        }

        return 'spcode';
    }

    public function render()
    {
        return view('livewire.plant-catalog.checklist-download', [
            'siteOptions' => $this->siteOptions(),
            'dataTypeOptions' => $this->dataTypeOptions(),
        ]);
    }
}
