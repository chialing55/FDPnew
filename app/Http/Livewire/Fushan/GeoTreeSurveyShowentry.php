<?php

namespace App\Http\Livewire\Fushan;

use App\Models\FsGeoTreeSurvey\Record1;
use App\Models\FsGeoTreeSurvey\Record2;
use App\Models\PlantCatalog\SiteSpecies;
use App\Services\Fushan\GeoTreeEntrySaveService;
use App\Services\Fushan\GeoTreeEntryStartingPointService;
use App\Services\Fushan\GeoTreeEntryRowLockResolver;
use App\Services\Fushan\GeoTreeSurveyRecordPaperService;
use App\Services\Fushan\GeoTreeSpecialModificationService;
use App\Support\ResolvesActorAccount;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Throwable;

class GeoTreeSurveyShowentry extends Component
{
    use ResolvesActorAccount;

    public string $entry;
    public string $user;
    public string $site;
    public ?int $qx = null;
    public ?int $qy = null;
    public int $sqx = 1;
    public int $sqy = 1;
    public array $qxOptions = [];
    public array $qyOptions = [];
    public array $records = [];
    public array $excludedQuadrats = [];
    public array $speciesOptions = [];
    public ?array $suggestedStartingPoint = null;
    public string $entrynote = '';
    public bool $recordTablesReady = false;
    public ?string $previousAction = null;
    public ?string $nextAction = null;
    public ?string $previousSubquadratAction = null;
    public ?string $nextSubquadratAction = null;

    public function mount(string $entry, string $user, string $site): void
    {
        $this->entry = $entry;
        $this->user = $user;
        $this->site = $site;
        $this->recordTablesReady = Schema::connection('fs_geo_tree_survey')->hasTable('record1')
            && Schema::connection('fs_geo_tree_survey')->hasTable('record2');

        if (!$this->recordTablesReady) {
            $this->entrynote = '尚未建立 record1、record2，請先執行資料表 migration。';

            return;
        }

        $this->excludedQuadrats = app(GeoTreeSurveyRecordPaperService::class)->excludedQuadrats();
        $this->suggestedStartingPoint = app(GeoTreeEntryStartingPointService::class)
            ->firstWithoutDate($this->entry);
        $this->speciesOptions = SiteSpecies::query()
            ->fushan()
            ->orderBy('csp')
            ->pluck('csp')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $this->qxOptions = $this->availableQuadrats()
            ->pluck('qx')->unique()->sort()->values()->all();
    }

    public function updatedQx($value): void
    {
        $this->qy = null;
        $this->qyOptions = [];
        $this->records = [];
        $this->entrynote = '';
        $this->previousAction = null;
        $this->nextAction = null;
        $this->previousSubquadratAction = null;
        $this->nextSubquadratAction = null;

        if ($value === null || $value === '') {
            return;
        }

        $this->qx = (int) $value;
        $this->refreshQyOptions();
    }

    public function submitForm(): void
    {
        if ($this->qx === null || $this->qy === null) {
            $this->entrynote = '請先選擇要輸入的樣方。';

            return;
        }

        $this->refreshQyOptions();
        if (!in_array($this->qy, $this->qyOptions, true)) {
            $this->entrynote = '查無此樣方，請重新選擇。';

            return;
        }

        $this->loadQuadrat($this->qx, $this->qy, 1, 1);
    }

    public function loadQuadrat(int $qx, int $qy, int $sqx = 1, int $sqy = 1): void
    {
        $this->qx = $qx;
        $this->refreshQyOptions();

        if (!in_array($qy, $this->qyOptions, true)) {
            $this->entrynote = $this->isExcludedQuadrat($qx, $qy)
                ? '此樣區不需輸出紀錄紙，也不需輸入。'
                : '查無此樣方，請重新選擇。';

            return;
        }

        $this->qy = $qy;
        $this->entrynote = '';
        $this->sqx = $sqx;
        $this->sqy = $sqy;
        $records = $this->tableQuery()
            ->where('qx', $qx)->where('qy', $qy)
            ->where('sqx', $sqx)->where('sqy', $sqy)
            ->where('show', 1)
            ->orderBy('tag')->orderBy('branch')
            ->get()->toArray();
        $this->records = app(GeoTreeEntryRowLockResolver::class)->resolve($records)['records'];
        $this->refreshNavigation();
        $this->refreshSubquadratNavigation();
        $this->dispatch(
            'geo-tree-entry-grid-data',
            containerId: 'geo-tree-entry-hot',
            records: $this->records,
            columns: config('tree-entry.surveys.fushan_geo_trees.columns', []),
            specialModification: config('tree-entry.surveys.fushan_geo_trees.specialModification', []),
            options: ['species' => $this->speciesOptions],
            pageSize: 20,
        );
    }

    public function selectSubquadrat(int $sqx, int $sqy): void
    {
        $this->loadQuadrat($this->qx, $this->qy, $sqx, $sqy);
    }

    public function saveRows(array $rows): void
    {
        if ($this->qx === null || $this->qy === null || $this->records === []) {
            $this->dispatchSaveResult(false, [], '請先選擇要輸入的小樣區。');

            return;
        }

        try {
            $result = app(GeoTreeEntrySaveService::class)->save(
                $this->entry,
                $this->qx,
                $this->qy,
                $this->sqx,
                $this->sqy,
                $rows,
                $this->actorAccount(),
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchSaveResult(false, [], '儲存失敗，資料仍保留在畫面上，請稍後再試。');

            return;
        }

        if (!$result['ok']) {
            $message = $result['errors'][0]['message'] ?? '資料檢查未通過。';
            $this->dispatchSaveResult(false, $result['errors'], $message);

            return;
        }

        $changed = (int) ($result['changed'] ?? 0);
        $this->loadQuadrat($this->qx, $this->qy, $this->sqx, $this->sqy);
        $this->dispatchSaveResult(true, [], $changed > 0 ? "儲存完成，共更新 {$changed} 筆資料。" : '資料檢查完成，沒有需要更新的內容。');
    }

    public function saveSpecialModification(array $data): void
    {
        if ($this->qx === null || $this->qy === null) {
            $this->dispatchSpecialSaveResult(false, [['field' => 'stemid', 'message' => '請先選擇樣方。']], '請先選擇樣方。');

            return;
        }

        try {
            $result = app(GeoTreeSpecialModificationService::class)->save(
                $this->entry,
                $this->qx,
                $this->qy,
                $this->sqx,
                $this->sqy,
                $data,
                $this->actorAccount(),
            );
        } catch (Throwable $exception) {
            report($exception);
            $this->dispatchSpecialSaveResult(false, [], '特殊修改儲存失敗，輸入內容仍保留在畫面上。');

            return;
        }

        if (!$result['ok']) {
            $message = $result['errors'][0]['message'] ?? '特殊修改檢查未通過。';
            $this->dispatchSpecialSaveResult(false, $result['errors'], $message);

            return;
        }

        $this->loadQuadrat($this->qx, $this->qy, $this->sqx, $this->sqy);
        $this->dispatchSpecialSaveResult(true, [], $result['message']);
    }

    private function dispatchSpecialSaveResult(bool $ok, array $errors, string $message): void
    {
        $this->dispatch(
            'geo-tree-special-save-result',
            dialogId: 'geo-tree-entry-hot-special',
            ok: $ok,
            errors: $errors,
            message: $message,
        );
    }

    private function dispatchSaveResult(bool $ok, array $errors, string $message): void
    {
        $this->dispatch(
            'geo-tree-entry-save-result',
            containerId: 'geo-tree-entry-hot',
            ok: $ok,
            errors: $errors,
            message: $message,
        );
    }

    private function refreshQyOptions(): void
    {
        $this->qyOptions = $this->availableQuadrats()
            ->where('qx', $this->qx)
            ->pluck('qy')->unique()->sort()->values()->all();
    }

    private function refreshNavigation(): void
    {
        $quadrats = $this->availableQuadrats()
            ->map(fn (array $row) => [$row['qx'], $row['qy']])->values();
        $index = $quadrats->search(fn ($item) => $item[0] === $this->qx && $item[1] === $this->qy);
        $previous = $index !== false && $index > 0 ? $quadrats[$index - 1] : null;
        $next = $index !== false && $index < $quadrats->count() - 1 ? $quadrats[$index + 1] : null;
        $this->previousAction = $previous ? "loadQuadrat({$previous[0]}, {$previous[1]}, 1, 1)" : null;
        $this->nextAction = $next ? "loadQuadrat({$next[0]}, {$next[1]}, 1, 1)" : null;
    }

    private function refreshSubquadratNavigation(): void
    {
        $order = ['11', '12', '22', '21', '13', '14', '24', '23', '33', '34', '44', '43', '31', '32', '42', '41'];
        $index = array_search($this->sqx . $this->sqy, $order, true);
        $previous = $index !== false && $index > 0 ? $order[$index - 1] : null;
        $next = $index !== false && $index < count($order) - 1 ? $order[$index + 1] : null;
        $this->previousSubquadratAction = $previous
            ? "selectSubquadrat({$previous[0]}, {$previous[1]})"
            : null;
        $this->nextSubquadratAction = $next
            ? "selectSubquadrat({$next[0]}, {$next[1]})"
            : null;
    }

    private function tableQuery()
    {
        return $this->entry === '1' ? Record1::query() : Record2::query();
    }

    private function availableQuadrats()
    {
        return $this->tableQuery()
            ->select(['qx', 'qy'])->distinct()
            ->orderByRaw('CAST(qx AS UNSIGNED)')
            ->orderByRaw('CAST(qy AS UNSIGNED)')
            ->get()
            ->map(fn ($row) => ['qx' => (int) $row->qx, 'qy' => (int) $row->qy])
            ->reject(fn (array $quadrat) => $this->isExcludedQuadrat($quadrat['qx'], $quadrat['qy']))
            ->values();
    }

    private function isExcludedQuadrat(int $qx, int $qy): bool
    {
        return collect($this->excludedQuadrats)->contains(
            fn (array $quadrat) => $quadrat['qx'] === $qx && $quadrat['qy'] === $qy
        );
    }

    public function render()
    {
        return view('livewire.fushan.geo-tree-survey-showentry');
    }
}
