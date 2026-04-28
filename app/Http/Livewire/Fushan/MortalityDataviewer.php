<?php

namespace App\Http\Livewire\Fushan;

use App\Models\FsMortality\CensusRecord;
use App\Models\FsMortality\Record1;
use App\Models\FsMortality\Record2;
use App\Models\FsMortality\TreeIndividual;
use App\Models\FsBaseSpinfo;
use Livewire\Component;

class MortalityDataviewer extends Component
{
    public $user;
    public $site;
    public $tag;
    public $resultnote = '';
    public $basedata = null;
    public $result = [];
    public $lastCensus;
    public $tableTag;

    public function mount($user = null, $site = null)
    {
        $this->user = $user;
        $this->site = $site;
        $this->lastCensus = Record1::query()->max('census') ?? CensusRecord::query()->max('census');
    }

    public function submitTagForm()
    {
        $this->searchTag($this->tag);
    }

    public function searchTag($tag)
    {
        $stemid = strtoupper(trim((string) $tag));

        if ($stemid === '') {
            $this->resultnote = '請輸入 Tag';
            $this->basedata = null;
            $this->result = [];
            return;
        }

        $this->tag = $stemid;
        $this->tableTag = preg_replace('/[^A-Za-z0-9_-]/', '_', $stemid);

        $tree = TreeIndividual::query()
            ->where('stemid', $stemid)
            ->first();

        $records = CensusRecord::query()
            ->with(['treeIndividual', 'comments.commentOption', 'stemCorrections'])
            ->where('stemid', $stemid)
            ->orderBy('census')
            ->get();

        $rows = $records
            ->map(fn ($record) => $this->normalizeCensusRecord($record))
            ->values()
            ->all();

        $draftRows = collect([Record1::class, Record2::class])
            ->flatMap(function ($model) use ($stemid) {
                return $model::query()
                    ->where('stemid', $stemid)
                    ->get()
                    ->map(fn ($record) => $this->normalizeDraftRecord($record));
            })
            ->filter(function ($row) use ($records) {
                return $row['updated_at'] !== ''
                    && ! $records->contains(fn ($record) => (string) $record->census === (string) $row['census']);
            })
            ->sortBy('census')
            ->values()
            ->all();

        $rows = collect($rows)
            ->concat($draftRows)
            ->sortBy('census')
            ->values()
            ->all();

        $splist = $this->speciesList($tree?->spcode, $rows);
        $rows = collect($rows)
            ->map(function ($row) use ($splist) {
                $row['spcode'] = $splist[$row['spcode']] ?? $row['spcode'];
                return $row;
            })
            ->all();

        if (empty($rows) && ! $tree) {
            $this->resultnote = '查無此 Tag';
            $this->basedata = null;
            $this->result = [];
            return;
        }

        $firstRow = $rows[0] ?? [];

        $this->resultnote = '';
        $this->basedata = [
            'stemid' => $stemid,
            'spcode' => $tree?->spcode ? ($splist[$tree->spcode] ?? $tree->spcode) : ($firstRow['spcode'] ?? ''),
            'map' => $firstRow['map'] ?? '',
            'qx' => $tree?->qx ?? ($firstRow['qx'] ?? ''),
            'qy' => $tree?->qy ?? ($firstRow['qy'] ?? ''),
            'subqx' => $tree?->subqx ?? ($firstRow['subqx'] ?? ''),
            'subqy' => $tree?->subqy ?? ($firstRow['subqy'] ?? ''),
            'qudx' => $tree?->qudx ?? '',
            'qudy' => $tree?->qudy ?? '',
            'is_active' => $tree ? ($tree->is_active ? 'Y' : 'N') : '',
            'note' => $tree?->note ?? '',
        ];
        $this->result = $rows;

        $this->dispatch('rePlots', plots: $this->tableTag);
    }

    private function speciesList(?string $treeSpcode, array $rows): array
    {
        $spcodes = collect($rows)
            ->pluck('spcode')
            ->push($treeSpcode)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($spcodes)) {
            return [];
        }

        return FsBaseSpinfo::query()
            ->whereIn('spcode', $spcodes)
            ->pluck('csp', 'spcode')
            ->all();
    }

    private function normalizeCensusRecord(CensusRecord $record): array
    {
        return [
            'source' => '',
            'census' => $record->census,
            'map' => $record->map,
            'date' => optional($record->date)->format('Y-m-d'),
            'spcode' => $record->treeIndividual?->spcode ?? '',
            'qx' => $record->treeIndividual?->qx ?? '',
            'qy' => $record->treeIndividual?->qy ?? '',
            'subqx' => $record->treeIndividual?->subqx ?? '',
            'subqy' => $record->treeIndividual?->subqy ?? '',
            'dbh' => $this->formatDecimal($record->dbh),
            'status' => $record->status,
            'mode' => $record->mode,
            'living_length' => $this->formatDecimal($record->living_length),
            'branches' => $record->branches,
            'illumination' => $record->illumination,
            'leaning' => $record->leaning,
            'liana' => $record->liana,
            'fungi' => $this->formatBoolean($record->fungi),
            'wounded_stem' => $record->wounded_stem,
            'deformity' => $record->deformity,
            'rotten' => $record->rotten,
            'leaves' => $record->leaves,
            'leaf_damage' => $this->formatBoolean($record->leaf_damage),
            'comments' => $this->formatCensusComments($record),
            'corrections' => $record->stemCorrections->pluck('description')->filter()->implode('；'),
            'updated_at' => optional($record->updated_at)->toDateTimeString(),
        ];
    }

    private function normalizeDraftRecord($record): array
    {
        return [
            'source' => '暫存',
            'census' => $record->census,
            'map' => $record->map,
            'date' => optional($record->date)->format('Y-m-d'),
            'spcode' => $record->csp,
            'qx' => $record->qx,
            'qy' => $record->qy,
            'subqx' => $record->subqx,
            'subqy' => $record->subqy,
            'dbh' => $this->formatDecimal($record->dbh2),
            'status' => $record->status,
            'mode' => $record->mode,
            'living_length' => $this->formatDecimal($record->living_length),
            'branches' => $record->branches,
            'illumination' => $record->illumination,
            'leaning' => $record->leaning,
            'liana' => $record->liana,
            'fungi' => $this->formatBoolean($record->fungi),
            'wounded_stem' => $record->wounded_stem,
            'deformity' => $record->deformity,
            'rotten' => $record->rotten,
            'leaves' => $record->leaves,
            'leaf_damage' => $this->formatBoolean($record->leaf_damage),
            'comments' => $this->formatJsonList($record->comments_json),
            'corrections' => $this->formatJsonList($record->stem_corrections_json),
            'updated_at' => optional($record->updated_at)->toDateTimeString(),
        ];
    }

    private function formatCensusComments(CensusRecord $record): string
    {
        return $record->comments
            ->map(function ($comment) {
                $option = $comment->commentOption;
                $text = $option?->comment_zh ?: $option?->comment_en ?: $option?->code;
                return trim(implode(' ', array_filter([$text, $comment->comment_other])));
            })
            ->filter()
            ->implode('；');
    }

    private function formatJsonList($items): string
    {
        if (! is_array($items)) {
            return '';
        }

        return collect($items)
            ->map(function ($item) {
                if (is_array($item)) {
                    return implode(' ', array_filter(array_map(
                        fn ($value) => is_scalar($value) ? (string) $value : '',
                        $item
                    )));
                }

                return is_scalar($item) ? (string) $item : '';
            })
            ->filter()
            ->implode('；');
    }

    private function formatDecimal($value): string
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

    public function render()
    {
        return view('livewire.fushan.mortality-dataviewer');
    }
}
