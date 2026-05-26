<?php

namespace App\Http\Livewire\Fushan;

use App\Models\FsBaseSpinfo;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class MortalityDataviewer extends Component
{
    public $user;
    public $site;
    public array $data = [];
    public array $maps = [];
    public array $yearOptions = [];
    public array $speciesOptions = [];
    public array $stemidOptions = [];
    public array $qxOptions = [];
    public array $qyOptions = [];
    public array $statusOptions = [];
    public array $modeOptions = [];
    public array $illuminationOptions = [];
    public array $lianaOptions = [];
    public array $fungiOptions = [];
    public array $woundedStemOptions = [];
    public array $deformityOptions = [];
    public array $rottenOptions = [];
    public array $leafDamageOptions = [];
    public array $operatorOptions = ['>', '≧', '=', '≦', '<'];
    public string $map = 'all';
    public string $year = 'all';
    public string $stemid = 'all';
    public string $species = 'all';
    public string $qx = 'all';
    public string $qy = 'all';
    public string $status = 'all';
    public string $mode = 'all';
    public string $illumination = 'all';
    public string $liana = 'all';
    public string $fungi = 'all';
    public string $woundedStem = 'all';
    public string $deformity = 'all';
    public string $rotten = 'all';
    public string $leafDamage = 'all';
    public string $dbhOperator = '=';
    public string $dbhValue = '';
    public string $branchesOperator = '=';
    public string $branchesValue = '';
    public string $leavesOperator = '=';
    public string $leavesValue = '';
    public int $page = 1;
    public int $perPage = 50;
    public int $total = 0;
    public int $totalPages = 1;
    public string $latestSurveyYear = "尚無資料";

    public function mount($user = null, $site = null): void
    {
        $this->user = $user;
        $this->site = $site;

        $this->latestSurveyYear = $this->loadLatestSurveyYear();
        $this->refreshFilterOptions();
        $this->search();
    }


    public function setFilter(string $property, $value): void
    {
        if (!in_array($property, $this->filterProperties(), true) || !property_exists($this, $property)) {
            return;
        }

        $this->{$property} = trim((string) $value);
        $this->search();
    }

    private function filterProperties(): array
    {
        return [
            'map',
            'year',
            'stemid',
            'species',
            'qx',
            'qy',
            'status',
            'mode',
            'illumination',
            'liana',
            'fungi',
            'woundedStem',
            'deformity',
            'rotten',
            'leafDamage',
            'dbhOperator',
            'dbhValue',
            'branchesOperator',
            'branchesValue',
            'leavesOperator',
            'leavesValue',
        ];
    }

    public function search(): void
    {
        $this->page = 1;
        $this->refreshFilterOptions();
        $this->loadData();
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadData();
        }
    }

    public function nextPage(): void
    {
        if ($this->page < $this->totalPages) {
            $this->page++;
            $this->loadData();
        }
    }

    public function goToPage($page): void
    {
        $page = max(1, min((int) $page, $this->totalPages));
        $this->page = $page;
        $this->loadData();
    }

    private function loadLatestSurveyYear(): string
    {
        $latestCensus = DB::connection("fs_mortality")
            ->table("census_records")
            ->max("census");

        if ($latestCensus === null) {
            return "尚無資料";
        }

        $surveyYear = DB::connection("fs_mortality")
            ->table("censuses")
            ->where("census", $latestCensus)
            ->value("survey_year");

        return $surveyYear !== null && $surveyYear !== ""
            ? (string) $surveyYear
            : "census " . (string) $latestCensus;
    }

    private function loadData(): void
    {
        $query = $this->baseQuery();

        $this->total = (clone $query)->count();
        $this->totalPages = max(1, (int) ceil($this->total / $this->perPage));
        $this->page = max(1, min($this->page, $this->totalPages));

        $rows = $query
            ->orderBy('cr.map')
            ->orderBy('ti.qx')
            ->orderBy('ti.qy')
            ->orderBy('ti.subqx')
            ->orderBy('ti.subqy')
            ->orderBy('cr.census')
            ->orderBy('cr.stemid')
            ->offset(($this->page - 1) * $this->perPage)
            ->limit($this->perPage)
            ->get();

        $speciesMap = $this->speciesMap($rows->pluck('spcode')->filter()->unique()->values()->all());
        $comments = $this->commentsByRecordId($rows->pluck('id')->all());

        $this->data = $rows
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'map' => $row->map,
                'census' => $row->census,
                'year' => $row->survey_year,
                'date' => $this->formatDate($row->date),
                'stemid' => $row->stemid,
                'spcode' => $row->spcode,
                'species' => $speciesMap[$row->spcode] ?? $row->spcode,
                'qx' => $row->qx,
                'qy' => $row->qy,
                'subqx' => $row->subqx,
                'subqy' => $row->subqy,
                'qudx' => $this->formatNumber($row->qudx),
                'qudy' => $this->formatNumber($row->qudy),
                'dbh' => $this->formatNumber($row->dbh),
                'status' => $row->status,
                'mode' => $row->mode,
                'living_length' => $this->formatNumber($row->living_length),
                'branches' => $row->branches,
                'illumination' => $row->illumination,
                'leaning' => $row->leaning,
                'liana' => $row->liana,
                'fungi' => $this->formatBoolean($row->fungi),
                'wounded_stem' => $row->wounded_stem,
                'deformity' => $row->deformity,
                'rotten' => $row->rotten,
                'leaves' => $row->leaves,
                'leaf_damage' => $this->formatBoolean($row->leaf_damage),
                'comments' => $comments[(int) $row->id] ?? '',
            ])
            ->toArray();
    }

    private function refreshFilterOptions(): void
    {
        $this->maps = $this->distinctCensusRecordOptions('map', 'map');
        $this->yearOptions = $this->distinctSurveyYearOptions();

        $speciesCodes = $this->distinctTreeIndividualOptions('spcode', 'species');
        $speciesNames = $this->speciesMap($speciesCodes);
        $this->speciesOptions = collect($speciesCodes)
            ->map(fn ($spcode) => [
                'spcode' => $spcode,
                'label' => $speciesNames[$spcode] ?? $spcode,
            ])
            ->toArray();

        $this->stemidOptions = $this->distinctCensusRecordOptions('stemid', 'stemid');
        $this->qxOptions = $this->distinctTreeIndividualOptions('qx', 'qx');
        $this->qyOptions = $this->distinctTreeIndividualOptions('qy', 'qy');
        $this->statusOptions = $this->distinctCensusRecordOptions('status', 'status');
        $this->modeOptions = $this->distinctCensusRecordOptions('mode', 'mode');
        $this->illuminationOptions = $this->distinctCensusRecordOptions('illumination', 'illumination');
        $this->lianaOptions = $this->distinctCensusRecordOptions('liana', 'liana');
        $this->fungiOptions = $this->distinctCensusRecordOptions('fungi', 'fungi');
        $this->woundedStemOptions = $this->distinctCensusRecordOptions('wounded_stem', 'woundedStem');
        $this->deformityOptions = $this->distinctCensusRecordOptions('deformity', 'deformity');
        $this->rottenOptions = $this->distinctCensusRecordOptions('rotten', 'rotten');
        $this->leafDamageOptions = $this->distinctCensusRecordOptions('leaf_damage', 'leafDamage');
    }

    private function baseQuery()
    {
        $query = DB::connection('fs_mortality')
            ->table('census_records as cr')
            ->leftJoin('tree_individuals as ti', 'cr.stemid', '=', 'ti.stemid')
            ->leftJoin('censuses as c', 'cr.census', '=', 'c.census')
            ->select([
                'cr.id',
                'cr.map',
                'cr.census',
                'c.survey_year',
                'cr.date',
                'cr.stemid',
                'ti.spcode',
                'ti.qx',
                'ti.qy',
                'ti.subqx',
                'ti.subqy',
                'ti.qudx',
                'ti.qudy',
                'cr.dbh',
                'cr.status',
                'cr.mode',
                'cr.living_length',
                'cr.branches',
                'cr.illumination',
                'cr.leaning',
                'cr.liana',
                'cr.fungi',
                'cr.wounded_stem',
                'cr.deformity',
                'cr.rotten',
                'cr.leaves',
                'cr.leaf_damage',
            ]);

        return $this->applyFilters($query);
    }

    private function optionBaseQuery(?string $except = null)
    {
        $query = DB::connection('fs_mortality')
            ->table('census_records as cr')
            ->leftJoin('tree_individuals as ti', 'cr.stemid', '=', 'ti.stemid')
            ->leftJoin('censuses as c', 'cr.census', '=', 'c.census');

        return $this->applyFilters($query, $except);
    }

    private function applyFilters($query, ?string $except = null)
    {
        $stemid = trim($this->stemid);
        $species = trim($this->species);

        return $query
            ->when($except !== 'map' && $this->map !== 'all', fn ($query) => $query->where('cr.map', $this->map))
            ->when($except !== 'year' && $this->year !== 'all', fn ($query) => $query->where('c.survey_year', $this->year))
            ->when($except !== 'stemid' && $stemid !== '' && $stemid !== 'all', fn ($query) => $query->where('cr.stemid', $stemid))
            ->when($except !== 'species' && $species !== '' && $species !== 'all', fn ($query) => $query->where('ti.spcode', $species))
            ->when($except !== 'qx' && $this->qx !== 'all', fn ($query) => $query->where('ti.qx', $this->qx))
            ->when($except !== 'qy' && $this->qy !== 'all', fn ($query) => $query->where('ti.qy', $this->qy))
            ->when($except !== 'status' && $this->status !== 'all', fn ($query) => $query->where('cr.status', $this->status))
            ->when($except !== 'mode' && $this->mode !== 'all', fn ($query) => $query->where('cr.mode', $this->mode))
            ->when($except !== 'illumination' && $this->illumination !== 'all', fn ($query) => $query->where('cr.illumination', $this->illumination))
            ->when($except !== 'liana' && $this->liana !== 'all', fn ($query) => $query->where('cr.liana', $this->liana))
            ->when($except !== 'fungi' && $this->fungi !== 'all', fn ($query) => $query->where('cr.fungi', $this->fungi))
            ->when($except !== 'woundedStem' && $this->woundedStem !== 'all', fn ($query) => $query->where('cr.wounded_stem', $this->woundedStem))
            ->when($except !== 'deformity' && $this->deformity !== 'all', fn ($query) => $query->where('cr.deformity', $this->deformity))
            ->when($except !== 'rotten' && $this->rotten !== 'all', fn ($query) => $query->where('cr.rotten', $this->rotten))
            ->when($except !== 'leafDamage' && $this->leafDamage !== 'all', fn ($query) => $query->where('cr.leaf_damage', $this->leafDamage))
            ->when($this->hasNumericFilter($this->dbhValue), fn ($query) => $this->applyNumericFilter($query, 'cr.dbh', $this->dbhOperator, $this->dbhValue))
            ->when($this->hasNumericFilter($this->branchesValue), fn ($query) => $this->applyNumericFilter($query, 'cr.branches', $this->branchesOperator, $this->branchesValue))
            ->when($this->hasNumericFilter($this->leavesValue), fn ($query) => $this->applyNumericFilter($query, 'cr.leaves', $this->leavesOperator, $this->leavesValue));
    }


    private function distinctTreeIndividualOptions(string $column, ?string $except = null): array
    {
        return $this->optionBaseQuery($except)
            ->select("ti.{$column}")
            ->whereNotNull("ti.{$column}")
            ->distinct()
            ->orderBy("ti.{$column}")
            ->pluck("ti.{$column}")
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->toArray();
    }

    private function distinctCensusRecordOptions(string $column, ?string $except = null): array
    {
        return $this->optionBaseQuery($except)
            ->select("cr.{$column}")
            ->whereNotNull("cr.{$column}")
            ->distinct()
            ->orderBy("cr.{$column}")
            ->pluck("cr.{$column}")
            ->map(fn ($value) => trim((string) $value))
            ->filter(fn ($value) => $value !== '')
            ->values()
            ->toArray();
    }

    private function distinctSurveyYearOptions(): array
    {
        return $this->optionBaseQuery('year')
            ->select('c.survey_year')
            ->whereNotNull('c.survey_year')
            ->distinct()
            ->orderBy('c.survey_year')
            ->pluck('c.survey_year')
            ->map(fn ($year) => (string) $year)
            ->toArray();
    }

    private function commentsByRecordId(array $recordIds): array
    {
        $recordIds = array_values(array_filter(array_map('intval', $recordIds)));

        if (empty($recordIds)) {
            return [];
        }

        return DB::connection('fs_mortality')
            ->table('census_record_comments as crc')
            ->leftJoin('comment_options as co', 'crc.comment_option_id', '=', 'co.id')
            ->whereIn('crc.census_record_id', $recordIds)
            ->orderBy('crc.census_record_id')
            ->orderBy('crc.sort_order')
            ->orderBy('crc.id')
            ->get([
                'crc.census_record_id',
                'crc.comment_other',
                'co.comment_zh',
                'co.comment_en',
                'co.code',
            ])
            ->groupBy('census_record_id')
            ->map(function ($rows) {
                return $rows
                    ->map(function ($row) {
                        $optionText = $this->blankToNull($row->comment_zh)
                            ?? $this->blankToNull($row->comment_en)
                            ?? $this->blankToNull($row->code);
                        $otherText = $this->blankToNull($row->comment_other);

                        return trim(implode(' ', array_filter([$optionText, $otherText])));
                    })
                    ->filter(fn ($text) => $text !== '')
                    ->implode('；');
            })
            ->all();
    }

    private function speciesMap(array $spcodes): array
    {
        if (empty($spcodes)) {
            return [];
        }

        return FsBaseSpinfo::query()
            ->whereIn('spcode', $spcodes)
            ->pluck('csp', 'spcode')
            ->all();
    }

    private function hasNumericFilter(string $value): bool
    {
        return trim($value) !== '' && is_numeric($value);
    }

    private function applyNumericFilter($query, string $column, string $operator, string $value)
    {
        if (!in_array($operator, $this->operatorOptions, true) || !$this->hasNumericFilter($value)) {
            return $query;
        }

        $sqlOperator = match ($operator) {
            '≧' => '>=',
            '≦' => '<=',
            default => $operator,
        };

        return $query->where($column, $sqlOperator, (float) $value);
    }

    private function formatNumber($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return rtrim(rtrim((string) $value, '0'), '.');
    }

    private function formatBoolean($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (int) $value === 1 ? '1' : '0';
    }

    private function formatDate($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return substr((string) $value, 0, 10);
    }

    private function blankToNull($value): ?string
    {
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    public function render()
    {
        return view('livewire.fushan.mortality-dataviewer');
    }
}
