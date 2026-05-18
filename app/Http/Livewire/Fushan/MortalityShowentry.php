<?php

namespace App\Http\Livewire\Fushan;

use App\Models\FsMortality\Census;
use App\Models\FsMortality\CommentOption;
use App\Models\FsMortality\Person;
use App\Models\FsMortality\Record1;
use App\Models\FsMortality\Record2;
use App\Models\FsMortality\Team;
use App\Models\FsMortality\TeamMember;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MortalityShowentry extends Component
{
    private const DEFAULT_PERSONNEL_SLOTS = 4;

    public $entry;
    public $user;
    public $site;
    public $selectedMapKey;
    public $selectedMapSort;
    public $selectedMap;
    public $mapOptions = [];
    public $records = [];
    public $suggestedMapKey;
    public $firstPendingMapKey;
    public $suggestedMapSort;
    public $firstPendingMapSort;
    public $entryCompleted = false;
    public $currentPageCompleted = false;
    public $recordCount = 0;
    public $pendingCount = 0;
    public $currentCensus;
    public $surveyYear;
    public $inputDate;
    public $completionHint;
    public $previousMapKey;
    public $nextMapKey;
    public $previousMapSort;
    public $nextMapSort;
    public $surveyDate;
    public $selectedTeamId = '';
    public $surveyPersonnel = [];
    public $teamOptions = [];
    public $showTeamBuilder = false;
    public $teamSearch = '';
    public $teamSortField = 'id';
    public $teamSortDirection = 'asc';
    public $editingTeamId = '';
    public $personOptions = [];
    public $surveyMetaMessage;
    public $commentsModalOpen = false;
    public $editingCommentRecordId;
    public $editingCommentMeta = [];
    public $commentItems = [];
    public $stemCorrectionItems = [];
    public $commentOptions = [];
    public $stemCorrectionOptions = [
        'qx' => '20x',
        'qy' => '20y',
        'subqx' => '10x',
        'subqy' => '10y',
        'stemid' => 'stemid',
        'csp' => 'csp',
        'other' => 'other',
    ];
    public $commentsSaveMessage;
    public $entrySaveMessage;
    public $entrySaveErrors = [];
    public $entrySaveErrorRecordIds = [];
    public $showCommentOptionForm = false;
    public $newCommentOption = [
        'code' => '',
        'comment_zh' => '',
        'comment_en' => '',
        'category' => '',
    ];
    public $commentOptionMessage;
    public $draftEntryRecords = [];
    public $mainStatusMessage;

    public function mount($entry, $user, $site): void
    {
        $this->entry = (string) $entry;
        $this->user = $user;
        $this->site = $site;
        $this->inputDate = now()->toDateString();
        $this->surveyDate = now()->toDateString();
        $this->selectedMapKey = null;
        $this->selectedMapSort = null;
        $this->selectedMap = null;
        $this->surveyPersonnel = array_fill(0, self::DEFAULT_PERSONNEL_SLOTS, '');

        $this->refreshEntryState();
    }

    public function loadMapSort($value): void
    {
        [$mapSort, $map] = $this->parseMapSelectionValue($value);
        $this->selectedMapKey = $mapSort !== null && $map !== null
            ? $this->buildMapOptionKey($mapSort, $map)
            : null;
        $this->selectedMapSort = $mapSort;
        $this->selectedMap = $map;
        $this->entrySaveMessage = null;
        $this->entrySaveErrors = [];
        $this->entrySaveErrorRecordIds = [];
        $this->mainStatusMessage = null;
        $this->refreshEntryState();
        $this->dispatchEntryGridEvent();
    }

    public function updatedSelectedMapKey($value): void
    {
        $this->loadMapSort($value);
    }

    public function updatedSelectedTeamId($value): void
    {
        if ($value === null || $value === '') {
            $this->selectedTeamId = '';
            $this->ensureMinimumPersonnelSlots();

            return;
        }

        $team = Team::query()
            ->with(['teamMembers' => function ($query) {
                $query->orderBy('person_id');
            }, 'teamMembers.person' => function ($query) {
                $query->orderBy('id');
            }])
            ->find((int) $value);

        if (!$team) {
            $this->selectedTeamId = '';
            $this->ensureMinimumPersonnelSlots();

            return;
        }

        $this->selectedTeamId = (string) $team->id;
        $this->surveyPersonnel = $team->teamMembers
            ->map(fn ($member) => $member->person?->name)
            ->filter(fn ($name) => trim((string) $name) !== '')
            ->values()
            ->all();

        $this->ensureMinimumPersonnelSlots();
    }

    public function addPersonnelField(): void
    {
        $this->surveyPersonnel[] = '';
    }

    public function toggleTeamBuilder(): void
    {
        $this->resetTeamBuilder();

        $this->showTeamBuilder = !$this->showTeamBuilder;
    }

    public function loadTeamForEditing($teamId): void
    {
        $team = Team::query()
            ->with(['teamMembers' => function ($query) {
                $query->orderBy('person_id');
            }, 'teamMembers.person'])
            ->find((int) $teamId);

        if (!$team) {
            return;
        }

        $this->editingTeamId = (string) $team->id;
        $this->selectedTeamId = (string) $team->id;
        $this->surveyPersonnel = $team->teamMembers
            ->map(fn ($member) => $member->person?->name)
            ->filter(fn ($name) => trim((string) $name) !== '')
            ->values()
            ->all();
        $this->ensureMinimumPersonnelSlots();
        $this->surveyMetaMessage = '已載入 team_id ' . $team->id . '，可修改後儲存。';
        $this->showTeamBuilder = true;
    }

    public function resetTeamBuilder(): void
    {
        $this->editingTeamId = '';
        $this->selectedTeamId = '';
        $this->surveyPersonnel = array_fill(0, self::DEFAULT_PERSONNEL_SLOTS, '');
        $this->surveyMetaMessage = null;
        $this->resetErrorBag('surveyPersonnel');
    }

    public function startNewTeam(): void
    {
        $this->resetTeamBuilder();
        $this->showTeamBuilder = true;
    }

    public function sortTeamOptions(string $field): void
    {
        if (!in_array($field, ['id', 'label'], true)) {
            return;
        }

        if ($this->teamSortField === $field) {
            $this->teamSortDirection = $this->teamSortDirection === 'asc' ? 'desc' : 'asc';

            return;
        }

        $this->teamSortField = $field;
        $this->teamSortDirection = 'asc';
    }

    public function deleteEditingTeam(): void
    {
        if ($this->editingTeamId === '') {
            return;
        }

        $teamId = (int) $this->editingTeamId;
        $isUsed = Record1::query()->where('team_id', $teamId)->exists()
            || Record2::query()->where('team_id', $teamId)->exists()
            || DB::connection('fs_mortality')->table('census_records')->where('team_id', $teamId)->exists();

        if ($isUsed) {
            $this->surveyMetaMessage = 'team_id ' . $teamId . ' 已被調查資料使用，不能刪除。';

            return;
        }

        DB::connection('fs_mortality')->transaction(function () use ($teamId) {
            TeamMember::query()->where('team_id', $teamId)->delete();
            Team::query()->where('id', $teamId)->delete();
        });

        $this->refreshEntryState();
        $this->resetTeamBuilder();
        $this->showTeamBuilder = true;
        $this->surveyMetaMessage = '已刪除 team_id ' . $teamId . '。';
        $this->dispatchEntryGridEvent();
    }

    public function openCommentsEditor(int $recordId, array $draftRecords = []): void
    {
        $modelClass = $this->getRecordModelClass();
        $record = $modelClass::query()->find($recordId);

        if (!$record) {
            return;
        }

        $this->editingCommentRecordId = $recordId;
        $this->editingCommentMeta = [
            'map' => (string) $record->map,
            'map_sort' => (int) $record->map_sort,
            'stemid' => (string) $record->stemid,
            'csp' => (string) $record->csp,
        ];
        $this->commentItems = collect((array) $record->comments_json)
            ->map(function ($item) {
                return [
                    'comment_id' => (string) ($item['comment_id'] ?? ''),
                    'text' => (string) ($item['text'] ?? ''),
                ];
            })
            ->values()
            ->all();
        $this->stemCorrectionItems = collect((array) $record->stem_corrections_json)
            ->map(function ($item) {
                return [
                    'field' => (string) ($item['field'] ?? ''),
                    'text' => (string) ($item['text'] ?? ''),
                ];
            })
            ->values()
            ->all();

        if (empty($this->commentItems)) {
            $this->commentItems = [['comment_id' => '', 'text' => '']];
        }

        if (empty($this->stemCorrectionItems)) {
            $this->stemCorrectionItems = [['field' => '', 'text' => '']];
        }

        $this->commentsSaveMessage = null;
        $this->draftEntryRecords = $draftRecords;
        $this->showCommentOptionForm = false;
        $this->commentOptionMessage = null;
        $this->newCommentOption = [
            'code' => '',
            'comment_zh' => '',
            'comment_en' => '',
            'category' => '',
        ];
        $this->commentsModalOpen = true;
    }

    public function closeCommentsEditor(): void
    {
        $this->commentsModalOpen = false;
        $this->editingCommentRecordId = null;
        $this->editingCommentMeta = [];
        $this->commentItems = [];
        $this->stemCorrectionItems = [];
        $this->commentsSaveMessage = null;
        $this->draftEntryRecords = [];
        $this->resetErrorBag(['commentItems', 'stemCorrectionItems']);
    }

    public function addCommentItemRow(): void
    {
        $this->commentItems[] = ['comment_id' => '', 'text' => ''];
    }

    public function addStemCorrectionItemRow(): void
    {
        $this->stemCorrectionItems[] = ['field' => '', 'text' => ''];
    }

    public function toggleCommentOptionForm(): void
    {
        $this->showCommentOptionForm = !$this->showCommentOptionForm;
        $this->commentOptionMessage = null;
        $this->resetErrorBag(['newCommentOption.code', 'newCommentOption.comment_zh', 'newCommentOption.comment_en', 'newCommentOption.category']);
    }

    public function createCommentOption(): void
    {
        $validated = $this->validate([
            'newCommentOption.code' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('fs_mortality.comment_options', 'code'),
            ],
            'newCommentOption.comment_zh' => ['required', 'string', 'max:255'],
            'newCommentOption.comment_en' => ['required', 'string', 'max:255'],
            'newCommentOption.category' => ['nullable', 'string', 'max:50'],
        ], [
            'newCommentOption.code.unique' => '這個 option code 已存在。',
            'newCommentOption.comment_zh.required' => '請輸入中文內容。',
            'newCommentOption.comment_en.required' => '請輸入英文內容。',
        ]);

        $maxSortOrder = (int) CommentOption::query()->max('sort_order');

        CommentOption::query()->create([
            'code' => $this->blankToNull($validated['newCommentOption']['code'] ?? null),
            'comment_zh' => $validated['newCommentOption']['comment_zh'],
            'comment_en' => $validated['newCommentOption']['comment_en'],
            'category' => $this->blankToNull($validated['newCommentOption']['category'] ?? null),
            'is_active' => true,
            'sort_order' => $maxSortOrder + 1,
        ]);

        $this->newCommentOption = [
            'code' => '',
            'comment_zh' => '',
            'comment_en' => '',
            'category' => '',
        ];
        $this->commentOptionMessage = '已新增新的 comment option。';
        $this->refreshCommentOptions();
    }

    public function saveCommentsEditor(): void
    {
        if (!$this->editingCommentRecordId) {
            return;
        }

        $modelClass = $this->getRecordModelClass();
        $record = $modelClass::query()->find($this->editingCommentRecordId);

        if (!$record) {
            return;
        }

        $commentsJson = $this->buildCommentReviewPayload($this->commentItems);
        $stemCorrectionsJson = $this->buildStemCorrectionPayload($this->stemCorrectionItems);

        $record->update([
            'comments_json' => !empty($commentsJson) ? $commentsJson : null,
            'stem_corrections_json' => !empty($stemCorrectionsJson) ? $stemCorrectionsJson : null,
            'updated_by' => (string) $this->user,
        ]);

        $draftResult = $this->persistDraftRecords(
            $this->draftEntryRecords,
            [$this->editingCommentRecordId => $commentsJson]
        );

        $this->entrySaveErrors = $draftResult['errors'];
        $this->entrySaveErrorRecordIds = $draftResult['errorRecordIds'];

        if (!empty($draftResult['errors'])) {
            $savedDraftText = $draftResult['savedCount'] > 0 ? "，並同步儲存 {$draftResult['savedCount']} 筆表格資料" : '';
            $this->mainStatusMessage = '備註已儲存' . $savedDraftText . '；其餘未通過驗證的表格內容已保留在前端。';
        } elseif ($draftResult['savedCount'] > 0) {
            $this->mainStatusMessage = "備註與 {$draftResult['savedCount']} 筆表格資料已儲存。";
        } else {
            $this->mainStatusMessage = '備註已儲存。';
        }

        $this->commentsModalOpen = false;
        $this->dispatch(
            'mortality-entry-comment-saved',
            recordId: $this->editingCommentRecordId,
            commentsJson: $commentsJson,
            stemCorrectionsJson: $stemCorrectionsJson,
            commentsSummary: $this->summarizeComments($commentsJson),
            errorRecordIds: $draftResult['errorRecordIds']
        );
    }

    public function createTeamFromBuilder(): void
    {
        if ($this->selectedMapSort === null || $this->selectedMap === null) {
            return;
        }

        $personNames = $this->validateSurveyPersonnel();

        if ($personNames === null) {
            return;
        }

        $teamId = DB::connection('fs_mortality')->transaction(function () use ($personNames) {
            return $this->resolveTeamId($personNames);
        });

        $this->selectedTeamId = (string) $teamId;
        $this->editingTeamId = (string) $teamId;
        $this->surveyMetaMessage = '已建立 team_id ' . $teamId . '，可在下方資料表逐筆填寫。';
        $this->refreshEntryState();
        $this->showTeamBuilder = true;
        $this->dispatchEntryGridEvent();
    }

    public function updateEditingTeam(): void
    {
        if ($this->selectedMapSort === null || $this->selectedMap === null || $this->editingTeamId === '') {
            return;
        }

        $personNames = $this->validateSurveyPersonnel();

        if ($personNames === null) {
            return;
        }

        $teamId = DB::connection('fs_mortality')->transaction(function () use ($personNames) {
            return $this->updateTeamMembers((int) $this->editingTeamId, $personNames);
        });

        $this->selectedTeamId = (string) $teamId;
        $this->editingTeamId = (string) $teamId;
        $this->surveyMetaMessage = 'team_id ' . $teamId . ' 已修改，可在下方資料表逐筆填寫。';
        $this->refreshEntryState();
        $this->showTeamBuilder = true;
        $this->dispatchEntryGridEvent();
    }

    public function saveEntryRecords(array $records): void
    {
        if ($this->selectedMapSort === null || $this->selectedMap === null) {
            return;
        }

        $result = $this->persistDraftRecords($records);
        $this->entrySaveErrors = $result['errors'];
        $this->entrySaveErrorRecordIds = $result['errorRecordIds'];

        if ($result['savedCount'] > 0 && empty($result['errors'])) {
            $this->entrySaveMessage = "已儲存 {$result['savedCount']} 筆資料。";
            $this->refreshEntryState();
            $this->dispatchEntryGridEvent();
            $this->dispatch('mortality-entry-save-result', errorRecordIds: []);

            return;
        }

        if ($result['savedCount'] > 0) {
            $this->entrySaveMessage = "已儲存 {$result['savedCount']} 筆資料，其餘資料請修正後再儲存。";
            $this->dispatch('mortality-entry-save-result', errorRecordIds: $this->entrySaveErrorRecordIds);

            return;
        }

        $this->entrySaveMessage = '目前沒有可儲存的資料。';
        $this->dispatch('mortality-entry-save-result', errorRecordIds: $this->entrySaveErrorRecordIds);
    }

    private function refreshEntryState(): void
    {
        $modelClass = $this->getRecordModelClass();
        $baseQuery = $modelClass::query();

        $this->mapOptions = (clone $baseQuery)
            ->select('map_sort', 'map')
            ->whereNotNull('map')
            ->where('map', '!=', '')
            ->distinct()
            ->orderBy('map_sort')
            ->orderBy('map')
            ->get()
            ->map(fn ($row) => [
                'key' => $this->buildMapOptionKey((int) $row->map_sort, (string) $row->map),
                'map_sort' => (int) $row->map_sort,
                'map' => trim((string) $row->map),
            ])
            ->all();

        $this->currentCensus = (clone $baseQuery)->orderBy('id')->value('census');
        $this->surveyYear = $this->currentCensus !== null
            ? Census::query()->where('census', $this->currentCensus)->value('survey_year')
            : null;
        $this->refreshCommentOptions();
        $this->personOptions = Person::query()
            ->orderBy('name')
            ->pluck('name')
            ->filter(fn ($name) => trim((string) $name) !== '')
            ->values()
            ->all();
        $this->teamOptions = $this->currentCensus !== null
            ? Team::query()
                ->where('census', $this->currentCensus)
                ->with(['teamMembers' => function ($query) {
                    $query->orderBy('person_id');
                }, 'teamMembers.person' => function ($query) {
                    $query->orderBy('id');
                }])
                ->orderBy('id')
                ->get()
                ->map(function ($team) {
                    $members = $team->teamMembers
                        ->map(fn ($member) => $member->person?->name)
                        ->filter(fn ($name) => trim((string) $name) !== '')
                        ->values()
                        ->all();

                    return [
                        'id' => (int) $team->id,
                        'label' => implode('、', $members),
                        'sort_label' => implode('、', $members),
                    ];
                })
                ->filter(fn ($team) => $team['label'] !== '')
                ->sortBy([
                    ['sort_label', 'asc'],
                    ['id', 'asc'],
                ])
            ->values()
            ->map(fn ($team) => [
                'id' => $team['id'],
                'label' => $team['label'],
                'display' => $team['id'] . '：' . $team['label'],
            ])
            ->all()
            : [];

        $firstPendingRow = (clone $baseQuery)
            ->select('map_sort', 'map')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '');
            })
            ->orderBy('map_sort')
            ->orderBy('map')
            ->orderBy('id')
            ->first();
        $this->firstPendingMapKey = $firstPendingRow
            ? $this->buildMapOptionKey((int) $firstPendingRow->map_sort, (string) $firstPendingRow->map)
            : null;
        $this->firstPendingMapSort = $firstPendingRow ? (int) $firstPendingRow->map_sort : null;
        $mapOptionKeys = array_map(fn ($row) => (string) $row['key'], $this->mapOptions);
        $this->suggestedMapKey = $this->firstPendingMapKey ?? ($mapOptionKeys[0] ?? null);
        $this->suggestedMapSort = $this->firstPendingMapSort ?? (!empty($this->mapOptions) ? (int) $this->mapOptions[0]['map_sort'] : null);

        $this->entryCompleted = !$this->hasPendingStatus(clone $baseQuery);

        $selectedMapKey = $this->buildCurrentSelectedMapKey();

        if ($selectedMapKey !== null && !in_array($selectedMapKey, $mapOptionKeys, true)) {
            $this->selectedMapKey = null;
            $this->selectedMapSort = null;
            $this->selectedMap = null;
        }

        if ($selectedMapKey !== null && in_array($selectedMapKey, $mapOptionKeys, true)) {
            $this->selectedMapKey = $selectedMapKey;
        }

        $this->previousMapKey = null;
        $this->nextMapKey = null;
        $this->previousMapSort = null;
        $this->nextMapSort = null;

        if ($selectedMapKey !== null) {
            $currentIndex = array_search($selectedMapKey, $mapOptionKeys, true);

            if ($currentIndex !== false) {
                $this->previousMapKey = $currentIndex > 0 ? $mapOptionKeys[$currentIndex - 1] : null;
                $this->nextMapKey = $currentIndex < count($mapOptionKeys) - 1 ? $mapOptionKeys[$currentIndex + 1] : null;
                $this->previousMapSort = $currentIndex > 0 ? (int) $this->mapOptions[$currentIndex - 1]['map_sort'] : null;
                $this->nextMapSort = $currentIndex < count($mapOptionKeys) - 1 ? (int) $this->mapOptions[$currentIndex + 1]['map_sort'] : null;
            }
        }

        $pageQuery = $this->selectedMapSort !== null && $this->selectedMap !== null
            ? $this->scopeSelectedMap(clone $baseQuery)->orderBy('id')
            : null;

        $this->records = $pageQuery
            ? $pageQuery->get([
                'id',
                'map_sort',
                'map',
                'q20',
                'q10',
                'stemid',
                'csp',
                'dbh1',
                'dbh2',
                'status',
                'mode',
                'living_length',
                'branches',
                'illumination',
                'leaning',
                'liana',
                'fungi',
                'wounded_stem',
                'deformity',
                'rotten',
                'leaves',
                'leaf_damage',
                'comments_json',
                'stem_corrections_json',
                'date',
                'team_id',
            ])->map(function ($record) {
                $record->fungi = $record->fungi !== null ? (int) $record->fungi : null;
                $record->wounded_stem = $record->wounded_stem !== null ? (int) $record->wounded_stem : null;
                $record->deformity = $record->deformity !== null ? (int) $record->deformity : null;
                $record->rotten = $record->rotten !== null ? (int) $record->rotten : null;
                $record->leaf_damage = $record->leaf_damage !== null ? (int) $record->leaf_damage : null;
                $record->comments_summary = $this->summarizeComments(is_array($record->comments_json) ? $record->comments_json : []);
                $recordArray = $record->toArray();
                $recordArray['date'] = $record->date ? $record->date->format('Y-m-d') : null;
                $recordArray['team_id'] = $record->team_id !== null ? (string) $record->team_id : '';
                $recordArray['comments_summary'] = $record->comments_summary;

                return $recordArray;
            })->all()
            : [];

        $this->surveyDate = $this->surveyDate ?: now()->toDateString();
        $this->ensureMinimumPersonnelSlots();
        $this->recordCount = count($this->records);
        $this->pendingCount = collect($this->records)
            ->filter(fn ($record) => trim((string) ($record['status'] ?? '')) === '')
            ->count();
        $this->currentPageCompleted = $this->recordCount > 0 && $this->pendingCount === 0;
        $this->completionHint = $this->buildCompletionHint();
    }

    private function getRecordModelClass(): string
    {
        return $this->entry === '2' ? Record2::class : Record1::class;
    }

    private function hasPendingStatus($query): bool
    {
        return $query
            ->where(function ($builder) {
                $builder->whereNull('status')
                    ->orWhere('status', '');
            })
            ->exists();
    }

    private function buildCompletionHint(): ?string
    {
        $firstEntryCompleted = !$this->hasPendingStatus(Record1::query());
        $secondEntryCompleted = !$this->hasPendingStatus(Record2::query());

        if ($this->entry === '1') {
            if ($firstEntryCompleted && $secondEntryCompleted) {
                return '第1次與第2次輸入皆已完成，可進行資料比對。';
            }

            if ($firstEntryCompleted) {
                return '第1次輸入已完成。';
            }

            return null;
        }

        if ($firstEntryCompleted && $secondEntryCompleted) {
            return '第1次與第2次輸入皆已完成，可進行資料比對。';
        }

        if ($firstEntryCompleted) {
            return '第1次輸入已完成。若已完成第2次輸入，可進行資料比對。';
        }

        return null;
    }

    private function dispatchEntryGridEvent(): void
    {
        $this->dispatch(
            'mortality-entry-data',
            records: $this->records,
            enabled: $this->selectedMapSort !== null && $this->selectedMap !== null && !empty($this->records),
            mapSort: $this->selectedMapSort,
            map: $this->selectedMap,
            entry: $this->entry,
            teamOptions: $this->teamOptions
        );
    }

    private function parseMapSelectionValue($value): array
    {
        if ($value === null || $value === '') {
            return [null, null];
        }

        $parts = explode('|', (string) $value, 2);
        $mapSort = isset($parts[0]) && $parts[0] !== '' ? (int) $parts[0] : null;
        $map = isset($parts[1]) && $parts[1] !== '' ? $parts[1] : null;

        return [$mapSort, $map];
    }

    private function buildMapOptionKey(int $mapSort, string $map): string
    {
        return $mapSort . '|' . trim($map);
    }

    private function buildCurrentSelectedMapKey(): ?string
    {
        if ($this->selectedMapSort === null || $this->selectedMap === null || $this->selectedMap === '') {
            return null;
        }

        return $this->buildMapOptionKey((int) $this->selectedMapSort, (string) $this->selectedMap);
    }

    private function scopeSelectedMap($query)
    {
        return $query
            ->where('map_sort', $this->selectedMapSort)
            ->where('map', $this->selectedMap);
    }

    private function ensureMinimumPersonnelSlots(): void
    {
        $this->surveyPersonnel = array_values($this->surveyPersonnel);

        while (count($this->surveyPersonnel) < self::DEFAULT_PERSONNEL_SLOTS) {
            $this->surveyPersonnel[] = '';
        }
    }

    private function validateSurveyPersonnel(): ?array
    {
        $personNames = collect($this->surveyPersonnel)
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->values()
            ->all();

        if (empty($personNames)) {
            $this->addError('surveyPersonnel', '請至少填入一位調查人員。');

            return null;
        }

        $duplicateNames = collect($personNames)
            ->countBy()
            ->filter(fn ($count) => $count > 1)
            ->keys()
            ->values()
            ->all();

        if (!empty($duplicateNames)) {
            $this->addError(
                'surveyPersonnel',
                '調查人員有重複：' . implode('、', $duplicateNames) . '，請確認後再儲存。'
            );

            return null;
        }

        $this->resetErrorBag('surveyPersonnel');

        return $personNames;
    }

    private function resolveTeamId(array $personNames): int
    {
        $uniqueNames = collect($personNames)
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->unique()
            ->values();

        $existingPeople = Person::query()
            ->whereIn('name', $uniqueNames->all())
            ->pluck('id', 'name')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($uniqueNames as $name) {
            if (!isset($existingPeople[$name])) {
                $person = Person::query()->create([
                    'name' => $name,
                ]);

                $existingPeople[$name] = (int) $person->id;
            }
        }

        $personIds = $uniqueNames
            ->map(fn ($name) => (int) $existingPeople[$name])
            ->sort()
            ->values()
            ->all();

        $signature = implode('-', $personIds);
        $teamSignatures = [];

        Team::query()
            ->where('census', $this->currentCensus)
            ->with(['teamMembers' => function ($query) {
                $query->orderBy('person_id');
            }])
            ->get()
            ->each(function ($team) use (&$teamSignatures) {
                $memberIds = $team->teamMembers
                    ->pluck('person_id')
                    ->map(fn ($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                if (empty($memberIds)) {
                    return;
                }

                $teamSignatures[implode('-', $memberIds)] = (int) $team->id;
            });

        if (isset($teamSignatures[$signature])) {
            return $teamSignatures[$signature];
        }

        $team = Team::query()->create([
            'census' => $this->currentCensus,
        ]);

        foreach ($personIds as $personId) {
            TeamMember::query()->create([
                'team_id' => $team->id,
                'person_id' => $personId,
                'role' => null,
            ]);
        }

        return (int) $team->id;
    }

    private function updateTeamMembers(int $teamId, array $personNames): int
    {
        $team = Team::query()->where('census', $this->currentCensus)->find($teamId);

        if (!$team) {
            return $this->resolveTeamId($personNames);
        }

        $personIds = $this->resolvePersonIds($personNames);

        TeamMember::query()->where('team_id', $teamId)->delete();

        foreach ($personIds as $personId) {
            TeamMember::query()->create([
                'team_id' => $teamId,
                'person_id' => $personId,
                'role' => null,
            ]);
        }

        return $teamId;
    }

    private function resolvePersonIds(array $personNames): array
    {
        $uniqueNames = collect($personNames)
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn ($name) => $name !== '')
            ->unique()
            ->values();

        $existingPeople = Person::query()
            ->whereIn('name', $uniqueNames->all())
            ->pluck('id', 'name')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($uniqueNames as $name) {
            if (!isset($existingPeople[$name])) {
                $person = Person::query()->create([
                    'name' => $name,
                ]);

                $existingPeople[$name] = (int) $person->id;
            }
        }

        return $uniqueNames
            ->map(fn ($name) => (int) $existingPeople[$name])
            ->sort()
            ->values()
            ->all();
    }

    public function getFilteredTeamOptionsProperty(): array
    {
        $keyword = trim((string) $this->teamSearch);

        return collect($this->teamOptions)
            ->when($keyword !== '', function ($teams) use ($keyword) {
                return $teams->filter(function ($team) use ($keyword) {
                    return str_contains((string) $team['id'], $keyword)
                        || str_contains($team['label'], $keyword)
                        || str_contains($team['display'] ?? '', $keyword);
                });
            })
            ->sortBy(function ($team) {
                if ($this->teamSortField === 'label') {
                    return (string) $team['label'];
                }

                return (int) $team['id'];
            }, SORT_REGULAR, $this->teamSortDirection === 'desc')
            ->values()
            ->all();
    }

    public function getTeamSortMarkersProperty(): array
    {
        return [
            'id' => $this->teamSortField === 'id' ? ($this->teamSortDirection === 'asc' ? ' ↑' : ' ↓') : '',
            'label' => $this->teamSortField === 'label' ? ($this->teamSortDirection === 'asc' ? ' ↑' : ' ↓') : '',
        ];
    }

    private function persistDraftRecords(array $records, array $commentsJsonOverrides = []): array
    {
        $modelClass = $this->getRecordModelClass();
        $recordIds = collect($records)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        $existingRecords = $modelClass::query()
            ->whereIn('id', $recordIds)
            ->where('map_sort', $this->selectedMapSort)
            ->where('map', $this->selectedMap)
            ->get()
            ->keyBy('id');

        $savedCount = 0;
        $errors = [];
        $errorRecordIds = [];

        foreach ($records as $index => $payload) {
            $recordId = (int) ($payload['id'] ?? 0);
            $record = $existingRecords->get($recordId);

            if (!$record) {
                continue;
            }

            $statusRaw = $this->blankToNull($payload['status'] ?? null);
            if ($statusRaw === null) {
                continue;
            }

            $validated = $this->validateEntryRecordPayload(
                $payload,
                $record,
                $commentsJsonOverrides[$recordId] ?? null
            );

            if (!empty($validated['errors'])) {
                $label = trim((string) ($record->stemid ?: $record->id));
                $errors[] = '第 ' . ($index + 1) . ' 筆（' . $label . '）：' . implode(' | ', $validated['errors']);
                $errorRecordIds[] = $recordId;
                continue;
            }

            $currentComparable = $this->extractComparableEntryData($record);
            if ($currentComparable === $validated['data']) {
                continue;
            }

            $record->fill($validated['data']);
            $record->updated_by = (string) $this->user;
            $record->save();
            $savedCount++;
        }

        return [
            'savedCount' => $savedCount,
            'errors' => $errors,
            'errorRecordIds' => array_values(array_unique($errorRecordIds)),
        ];
    }

    private function extractComparableEntryData($record): array
    {
        return [
            'dbh2' => $record->dbh2 !== null ? round((float) $record->dbh2, 2) : null,
            'status' => $this->blankToNull($record->status),
            'mode' => $this->blankToNull($record->mode),
            'living_length' => $record->living_length !== null ? round((float) $record->living_length, 2) : null,
            'branches' => $record->branches !== null ? (int) $record->branches : null,
            'illumination' => $record->illumination !== null ? (int) $record->illumination : null,
            'leaning' => $record->leaning !== null ? (int) $record->leaning : null,
            'liana' => $this->blankToNull($record->liana),
            'fungi' => $record->fungi !== null ? (int) $record->fungi : null,
            'wounded_stem' => $record->wounded_stem !== null ? (int) $record->wounded_stem : null,
            'deformity' => $record->deformity !== null ? (int) $record->deformity : null,
            'rotten' => $record->rotten !== null ? (int) $record->rotten : null,
            'leaves' => $record->leaves !== null ? (int) $record->leaves : null,
            'leaf_damage' => $record->leaf_damage !== null ? (int) $record->leaf_damage : null,
            'date' => $record->date ? $record->date->format('Y-m-d') : null,
            'team_id' => $record->team_id !== null ? (int) $record->team_id : null,
        ];
    }

    private function refreshCommentOptions(): void
    {
        $categoryOrder = array_flip($this->getCommentOptionCategoryOrder());

        $this->commentOptions = CommentOption::query()
            ->where('is_active', 1)
            ->get()
            ->map(function ($option) use ($categoryOrder) {
                $option->category_sort = $categoryOrder[$option->category ?? ''] ?? 999;

                return $option;
            })
            ->sortBy(function ($option) {
                return sprintf(
                    '%03d|%s|%s|%010d',
                    $option->category_sort,
                    $option->comment_zh ?? '',
                    $option->comment_en ?? '',
                    $option->id
                );
            })
            ->values()
            ->map(function ($option) {
                $fullLabel = $option->comment_zh
                    . ($option->comment_en ? ' / ' . $option->comment_en : '')
                    . ($option->code ? ' (' . $option->code . ')' : '');

                return [
                    'id' => (int) $option->id,
                    'label' => $fullLabel,
                    'short_label' => $option->comment_zh ?: $option->comment_en,
                    'category' => $option->category,
                ];
            })
            ->all();

        $groupedOptions = [];
        $lastCategory = null;

        foreach ($this->commentOptions as $option) {
            if ($lastCategory !== null && $lastCategory !== ($option['category'] ?? null)) {
                $groupedOptions[] = [
                    'id' => null,
                    'label' => '──────────',
                    'short_label' => '──────────',
                    'category' => null,
                    'is_divider' => true,
                ];
            }

            $groupedOptions[] = [
                ...$option,
                'is_divider' => false,
            ];
            $lastCategory = $option['category'] ?? null;
        }

        $this->commentOptions = $groupedOptions;
    }

    private function getCommentOptionCategoryOrder(): array
    {
        return [
            'stem_condition',
            'POM_issue',
            'structural_change',
            'biotic_damage',
            'disease',
            'other',
        ];
    }

    private function buildCommentReviewPayload(array $items): array
    {
        $payload = [];

        foreach ($items as $item) {
            $commentId = (int) ($item['comment_id'] ?? 0);
            $text = trim((string) ($item['text'] ?? ''));

            if ($commentId === 0 && $text === '') {
                continue;
            }

            if ($commentId > 0) {
                $entry = [
                    'kind' => 'option',
                    'comment_id' => $commentId,
                ];

                if ($text !== '') {
                    $entry['text'] = $text;
                }

                $payload[] = $entry;
                continue;
            }

            $payload[] = [
                'kind' => 'other',
                'text' => $text,
            ];
        }

        return $payload;
    }

    private function summarizeComments(array $commentsJson): string
    {
        if (empty($commentsJson)) {
            return '';
        }

        $commentIds = collect($commentsJson)
            ->map(fn ($item) => (int) ($item['comment_id'] ?? 0))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $commentLabels = !empty($commentIds)
            ? CommentOption::query()
                ->whereIn('id', $commentIds)
                ->pluck('comment_zh', 'id')
                ->all()
            : [];

        return collect($commentsJson)
            ->map(function ($item) use ($commentLabels) {
                $text = trim((string) ($item['text'] ?? ''));
                $commentId = (int) ($item['comment_id'] ?? 0);
                $label = $commentId > 0
                    ? trim((string) ($commentLabels[$commentId] ?? ''))
                    : '';

                if ($label !== '' && $text !== '') {
                    return $label . '：' . $text;
                }

                return $label !== '' ? $label : $text;
            })
            ->filter(fn ($text) => trim((string) $text) !== '')
            ->implode('；');
    }

    private function buildStemCorrectionPayload(array $items): array
    {
        $payload = [];

        foreach ($items as $item) {
            $field = trim((string) ($item['field'] ?? ''));
            $text = trim((string) ($item['text'] ?? ''));

            if ($field === '' && $text === '') {
                continue;
            }

            $payload[] = [
                'field' => $field !== '' ? $field : 'other',
                'text' => $text,
            ];
        }

        return $payload;
    }

    private function validateEntryRecordPayload(array $payload, $record, ?array $commentsJsonOverride = null): array
    {
        $errors = [];
        $commentsJson = $commentsJsonOverride
            ?? (is_array($payload['comments_json'] ?? null)
                ? $payload['comments_json']
                : (is_array($record->comments_json) ? $record->comments_json : []));
        $hasDbhShrinkComment = collect((array) $commentsJson)
            ->contains(function ($item) {
                $commentId = (int) ($item['comment_id'] ?? 0);

                return in_array($commentId, [21, 2], true);
            });

        $dbh1 = $record->dbh1 !== null ? (float) $record->dbh1 : null;
        $dbh2Raw = $this->blankToNull($payload['dbh2'] ?? null);
        $status = strtoupper((string) ($this->blankToNull($payload['status'] ?? null) ?? ''));
        $modeRaw = strtoupper((string) ($this->blankToNull($payload['mode'] ?? null) ?? ''));
        $livingLengthRaw = $this->blankToNull($payload['living_length'] ?? null);
        $branchesRaw = $this->blankToNull($payload['branches'] ?? null);
        $illuminationRaw = $this->blankToNull($payload['illumination'] ?? null);
        $leaningRaw = $this->blankToNull($payload['leaning'] ?? null);
        $liana = strtoupper((string) ($this->blankToNull($payload['liana'] ?? null) ?? ''));
        $fungiRaw = $this->blankToNull($payload['fungi'] ?? null);
        $woundedStemRaw = $this->blankToNull($payload['wounded_stem'] ?? null);
        $deformityRaw = $this->blankToNull($payload['deformity'] ?? null);
        $rottenRaw = $this->blankToNull($payload['rotten'] ?? null);
        $leavesRaw = $this->blankToNull($payload['leaves'] ?? null);
        $leafDamageRaw = $this->blankToNull($payload['leaf_damage'] ?? null);
        $dateRaw = $this->normalizeDateValue($payload['date'] ?? null);
        $teamIdRaw = $this->blankToNull($payload['team_id'] ?? null);

        $allowedStatuses = ['OK', 'A', 'D', 'X', 'NF'];
        if ($status === '' || !in_array($status, $allowedStatuses, true)) {
            $errors[] = 'status 必須填入 OK、A、D、X 或 NF。';
        }

        if ($dateRaw === null) {
            $errors[] = '調查日期必須填寫。';
            $date = null;
        } elseif (!$this->isValidDate($dateRaw)) {
            $errors[] = '調查日期格式需為 YYYY-MM-DD。';
            $date = null;
        } else {
            $date = $dateRaw;
        }

        if ($teamIdRaw === null) {
            $errors[] = '調查團隊必須選擇。';
            $teamId = null;
        } elseif (!ctype_digit((string) $teamIdRaw) || !Team::query()->where('census', $this->currentCensus)->where('id', (int) $teamIdRaw)->exists()) {
            $errors[] = '調查團隊不存在或不屬於本次 census，請重新選擇。';
            $teamId = null;
        } else {
            $teamId = (int) $teamIdRaw;
        }

        $statusAllowsDetails = in_array($status, ['A', 'OK'], true);

        if ($dbh2Raw !== null) {
            if (!is_numeric($dbh2Raw)) {
                $errors[] = 'DBH(new) 必須為數字。';
                $dbh2 = null;
            } else {
                $dbh2 = round((float) $dbh2Raw, 2);

                if ($dbh1 !== null && $dbh2 < $dbh1 && !$hasDbhShrinkComment) {
                    $errors[] = 'DBH(new) 需大於等於 DBH(old)，若小於請填寫備註: 確認縮水 或 DBH shrink。';
                }
            }
        } else {
            $dbh2 = null;
        }

        if (!$statusAllowsDetails && $dbh2 !== null) {
            $errors[] = '只有 status 為 A 或 OK 時才可填寫 DBH(new)。';
        }

        if ($status === 'OK' && $dbh2 === null) {
            $errors[] = 'status 為 OK 時必須填寫 DBH(new)。';
        }

        if (in_array($status, ['OK', 'NF'], true) && $modeRaw !== '') {
            $errors[] = 'status 為 OK 或 NF 時不可填寫 mode。';
        }

        if ($modeRaw !== '') {
            if ($modeRaw !== '?' && !preg_match('/^[SBU]{1,3}$/', $modeRaw)) {
                $errors[] = 'mode 只能填 S、B、U 的組合或 ?。';
            }

            if ($modeRaw !== '?' && count(array_unique(str_split($modeRaw))) !== strlen($modeRaw)) {
                $errors[] = 'mode 不可重複字母。';
            }
        }

        if ($livingLengthRaw !== null) {
            if (!is_numeric($livingLengthRaw)) {
                $errors[] = 'Living length 必須為數字。';
                $livingLength = null;
            } else {
                $livingLength = round((float) $livingLengthRaw, 2);
            }
        } else {
            $livingLength = null;
        }

        if ($status === 'A' && str_contains($modeRaw, 'B') && $livingLength === null) {
            $errors[] = 'status 為 A 且 mode 包含 B 時必須填寫 Living length。若缺乏調查資料請填 -1。';
        }

        $dbhDependencyExemptForShortLivingLength = $status === 'A'
            && $livingLength !== null
            && $livingLength < 1.3;

        $branchesInvalid = false;
        if ($branchesRaw !== null) {
            if (!ctype_digit((string) $branchesRaw) || (int) $branchesRaw < 0 || (int) $branchesRaw > 100) {
                $errors[] = 'branches 必須為 0 到 100 的整數。';
                $branchesInvalid = true;
                $branches = null;
            } else {
                $branches = (int) $branchesRaw;
            }
        } else {
            $branches = null;
        }

        if (!$branchesInvalid && $status === 'A' && $dbh2 !== null) {
            if ($branches === null || $branches < 1 || $branches > 100) {
                $errors[] = 'status 為 A 且有 DBH(new) 時，branches 必須為 1 到 100。';
            }
        }

        if (!$branchesInvalid && $status === 'A' && $dbh2 !== null && $branches === null) {
            $errors[] = 'status 為 A 且有 DBH(new) 時必須填寫 branches。';
        }

        if (
            !$branchesInvalid
            && $status === 'A'
            && $dbh2 === null
            && !$dbhDependencyExemptForShortLivingLength
            && $branches !== null
            && $branches !== 0
        ) {
            $errors[] = 'status 為 A 且有填寫 branches 時必須填寫 DBH(new)。';
        }

        if ($status !== 'A' && $branches !== null) {
            $errors[] = '只有 status 為 A 時才可填寫 branches。';
        }

        if ($illuminationRaw !== null) {
            if (!ctype_digit((string) $illuminationRaw) || (int) $illuminationRaw < 0 || (int) $illuminationRaw > 5) {
                $errors[] = 'illumination 必須為 0 到 5。';
                $illumination = null;
            } else {
                $illumination = (int) $illuminationRaw;
            }
        } else {
            $illumination = null;
        }

        if ($status === 'A' && $dbh2 !== null) {
            if ($illumination === null || $illumination < 1 || $illumination > 5) {
                $errors[] = 'status 為 A 且有 DBH(new) 時，illumination 必須為 1 到 5。';
            }
        }

        if ($status === 'A' && $dbh2 === null && $branches === 0 && $illumination !== null && $illumination !== 0) {
            $errors[] = 'status 為 A 且未填 DBH(new)、branches 為 0 時，illumination 需為 0。';
        }

        if (
            $status === 'A'
            && $dbh2 === null
            && !$dbhDependencyExemptForShortLivingLength
            && $illumination !== null
            && $illumination !== 0
        ) {
            $errors[] = 'status 為 A 且有填寫 illumination 時必須填寫 DBH(new)。';
        }

        if (!in_array($status, ['A', 'OK'], true) && $illumination !== null) {
            $errors[] = '只有 status 為 A 或 OK 時才可填寫 illumination。';
        }

        if ($leaningRaw !== null) {
            if (!is_numeric($leaningRaw) || (float) $leaningRaw < 10 || (float) $leaningRaw > 150) {
                $errors[] = 'leaning 必須為 10 到 150。';
                $leaning = null;
            } else {
                $leaning = (int) round((float) $leaningRaw);
            }
        } else {
            $leaning = null;
        }

        if (str_contains($modeRaw, 'U') && $leaning === null) {
            $errors[] = 'mode 包含 U 時必須填寫 leaning。';
        }

        $allowedLiana = ['', 'L', 'S', 'LS'];
        if (!in_array($liana, $allowedLiana, true)) {
            $errors[] = 'liana 只能填 L、S 或 LS。';
        }

        $fungi = $this->validateOptionalDiscrete($fungiRaw, ['1'], 'fungi', $errors);
        $woundedStem = $this->validateOptionalDiscrete($woundedStemRaw, ['1', '2', '3'], 'wounded_stem', $errors);
        $deformity = $this->validateOptionalDiscrete($deformityRaw, ['1', '2', '3'], 'deformity', $errors);
        $rotten = $this->validateOptionalDiscrete($rottenRaw, ['1', '2', '3'], 'rotten', $errors);
        $leafDamage = $this->validateOptionalDiscrete($leafDamageRaw, ['1'], 'leaf_damage', $errors);

        if ($leavesRaw !== null) {
            if (!ctype_digit((string) $leavesRaw) || (int) $leavesRaw < 0 || (int) $leavesRaw > 100) {
                $errors[] = 'leaves 必須為 0 到 100 的整數。';
                $leaves = null;
            } else {
                $leaves = (int) $leavesRaw;
            }
        } else {
            $leaves = null;
        }

        if (!$statusAllowsDetails && ($woundedStem !== null || $rotten !== null)) {
            $errors[] = '只有 status 為 A 或 OK 時才可填寫 wounded_stem、rotten。';
        }

        if (!$statusAllowsDetails && ($deformity !== null || $leaves !== null || $leafDamage !== null)) {
            $errors[] = '只有 status 為 A 或 OK 時才可填寫 deformity、leaves、leaf_damage。';
        }

        return [
            'errors' => $errors,
            'data' => [
                'dbh2' => $dbh2,
                'status' => $status,
                'mode' => $modeRaw !== '' ? $modeRaw : null,
                'living_length' => $livingLength,
                'branches' => $branches,
                'illumination' => $illumination,
                'leaning' => $leaning,
                'liana' => $liana !== '' ? $liana : null,
                'fungi' => $fungi,
                'wounded_stem' => $woundedStem,
                'deformity' => $deformity,
                'rotten' => $rotten,
                'leaves' => $leaves,
                'leaf_damage' => $leafDamage,
                'date' => $date,
                'team_id' => $teamId,
            ],
        ];
    }

    private function isValidDate(string $date): bool
    {
        $parsed = \DateTime::createFromFormat('Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function normalizeDateValue($value): ?string
    {
        $value = $this->blankToNull($value);

        if ($value === null) {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}/', (string) $value, $matches)) {
            return $matches[0];
        }

        return (string) $value;
    }

    private function validateOptionalDiscrete($value, array $allowed, string $field, array &$errors): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $stringValue = (string) $value;
        if (!in_array($stringValue, $allowed, true)) {
            $errors[] = $field . ' 只能填 ' . implode('、', $allowed) . '。';

            return null;
        }

        return (int) $stringValue;
    }

    private function blankToNull($value): mixed
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    public function render()
    {
        return view('livewire.fushan.mortality-showentry');
    }
}
