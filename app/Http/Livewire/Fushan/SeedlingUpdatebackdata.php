<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

use App\Models\FsSeedlingSlrecord1;

class SeedlingUpdatebackdata extends Component
{
    public $user;
    public $site;
    public $trap = '';
    public $tag = '';
    public $go = '';
    public $type = '';
    public $from = '';
    public $updateTag = '';
    public $dataNote = '';
    public $updateMtag = '';
    public $dataNoteType = '';
    public $finishnote = '';
    public $goto = '';
    public array $taglist = [];
    public int $alternoteCount = 0;
    public array $csplist = [];
    public array $workRows = [];
    public array $identityRows = [];
    public array $masterRows = [];

    public function mount($user = null, $site = null): void
    {
        abort_unless((int) (auth()->user()?->is_admin ?? 0) === 1, 403);

        $this->user = $user;
        $this->site = $site;

        $this->alternoteCount = FsSeedlingSlrecord1::query()
            ->where('alternote', '!=', '')
            ->count();

        $this->csplist = DB::connection('mysql3')
            ->table('seedling_individuals')
            ->select('csp')
            ->whereNotNull('csp')
            ->distinct()
            ->orderBy('csp')
            ->pluck('csp')
            ->map(fn ($csp) => trim((string) $csp))
            ->filter(fn ($csp) => $csp !== '')
            ->values()
            ->toArray();
    }

    public function alternote(): void
    {
        abort_unless((int) (auth()->user()?->is_admin ?? 0) === 1, 403);

        $this->tag = '';
        $this->taglist = [];
        $this->workRows = [];
        $this->identityRows = [];
        $this->masterRows = [];
        $this->finishnote = '';
        $this->from = 'alternote';
        $this->type = '1';

        $this->trap = '';

        $this->refreshAlternoteTagList();

        if ($this->taglist === []) {
            $this->go = 'no';
            $this->dataNote = '目前沒有特殊修改資料。';
            $this->dataNoteType = 'error';
            return;
        }

        $this->go = 'yes';
        $this->searchTag(0);
    }

    public function indTag(): void
    {
        abort_unless((int) (auth()->user()?->is_admin ?? 0) === 1, 403);

        $tag = strtoupper(trim((string) $this->tag));
        $this->trap = '';
        $this->taglist = [];
        $this->workRows = [];
        $this->identityRows = [];
        $this->masterRows = [];
        $this->finishnote = '';
        $this->from = 'indTag';
        $this->type = '2';

        if ($tag === '') {
            $this->go = 'no';
            $this->dataNote = '請輸入 tag。';
            $this->dataNoteType = 'error';
            return;
        }

        $hasWork = FsSeedlingSlrecord1::query()->where('tag', $tag)->exists();
        $hasMaster = DB::connection('mysql3')->table('seedling_records')->where('tag', $tag)->whereNull('deleted_at')->exists();
        $hasStem = DB::connection('mysql3')->table('seedling_stems')->where('tag', $tag)->whereNull('deleted_at')->exists();

        if (!$hasWork && !$hasMaster && !$hasStem) {
            $this->go = 'no';
            $this->dataNote = '查無此 tag。';
            $this->dataNoteType = 'error';
            return;
        }

        $this->taglist = [$tag];
        $this->go = 'yes';
        $this->loadTag($tag, 'indTag');
    }

    public function searchTag($key): void
    {
        abort_unless((int) (auth()->user()?->is_admin ?? 0) === 1, 403);

        $key = max(0, (int) $key);
        if ($this->taglist === []) {
            $this->go = 'no';
            $this->dataNote = '沒有可修改資料。';
            $this->dataNoteType = 'error';
            return;
        }

        if (!isset($this->taglist[$key])) {
            $key = 0;
            $this->goto = '1';
        }

        $this->loadTag($this->taglist[$key], 'alternote');
    }

    #[On('seedlingUpdateSaved')]
    public function refreshAfterSave($data): void
    {
        $tag = $data['tag'] ?? $this->updateTag;
        $from = $data['from'] ?? $this->from;
        $savedNote = (string) ($data['note'] ?? '');
        $savedNoteType = (string) ($data['noteType'] ?? '');

        if ($from === 'alternote') {
            $this->tag = '';
            $this->workRows = [];
            $this->identityRows = [];
            $this->masterRows = [];
            $this->finishnote = '';
            $this->from = 'alternote';
            $this->type = '1';
            $this->trap = '';
            $this->refreshAlternoteTagList();

            if ($this->taglist === []) {
                $this->go = 'no';
                $this->dataNote = '目前沒有特殊修改資料。';
                $this->dataNoteType = 'error';
                return;
            }

            $this->go = 'yes';
            if ($tag !== '') {
                if (!in_array($tag, $this->taglist, true)) {
                    $this->taglist[] = $tag;
                }
                $this->loadTag($tag, 'alternote');
                if ($savedNote !== '') {
                    $this->dataNote = $savedNote;
                    $this->dataNoteType = $savedNoteType;
                }
                return;
            }

            $this->searchTag(0);
            return;
        }

        $this->tag = $tag;
        $this->indTag();
        if ($savedNote !== '') {
            $this->dataNote = $savedNote;
            $this->dataNoteType = $savedNoteType;
        }
    }

    private function refreshAlternoteTagList(): void
    {
        $this->alternoteCount = FsSeedlingSlrecord1::query()
            ->where('alternote', '!=', '')
            ->count();

        $this->taglist = FsSeedlingSlrecord1::query()
            ->where('alternote', '!=', '')
            ->orderBy('trap')
            ->orderBy('plot')
            ->orderBy('tag')
            ->pluck('tag')
            ->map(fn ($tag) => (string) $tag)
            ->unique()
            ->values()
            ->toArray();
    }

    private function loadTag(string $tag, string $from): void
    {
        $this->updateTag = $tag;
        $this->updateMtag = $tag;
        $this->from = $from;
        $this->dataNote = "";
        $this->dataNoteType = "";
        $this->finishnote = "";

        $payload = $this->tagData($tag, $from);

        if ($payload['workRows'] === [] && $payload['identityRows'] === [] && $payload['masterRows'] === []) {
            $this->go = 'no';
            $this->workRows = [];
            $this->identityRows = [];
            $this->masterRows = [];
            $this->dataNote = '查無此 tag。';
            $this->dataNoteType = 'error';
            return;
        }

        $this->updateMtag = (string) ($payload['displayMtag'] ?? $tag);
        $this->workRows = $payload['workRows'];
        $this->identityRows = $payload['identityRows'];
        $this->masterRows = $payload['masterRows'];

        if ($payload['workRows'] === []) {
            $this->dataNote = 'slrecord1 沒有此 tag，僅顯示正式表資料。';
            $this->dataNoteType = 'error';
        }

        $this->dispatch(
            'seedling-update-data',
            tag: $tag,
            workRows: $payload['workRows'],
            identityRows: $payload['identityRows'],
            masterRows: $payload['masterRows'],
            csplist: $this->csplist,
            from: $from,
        );
    }

    private function tagData(string $tag, string $from = ""): array
    {
        if ($from !== "indTag") {
            $workRows = FsSeedlingSlrecord1::query()
                ->where("tag", $tag)
                ->orderBy("census")
                ->get()
                ->map(fn ($row) => $this->normalizeWorkRow($row->toArray()))
                ->toArray();

            $identityRows = DB::connection("mysql3")
                ->table("seedling_stems as st")
                ->leftJoin("seedling_individuals as i", "st.mtag", "=", "i.mtag")
                ->where("st.tag", $tag)
                ->whereNull("st.deleted_at")
                ->whereNull("i.deleted_at")
                ->select([
                    "i.id as individual_id",
                    "st.id as stem_id",
                    "i.trap",
                    "i.plot",
                    "st.tag",
                    "st.mtag",
                    "st.branch",
                    "i.csp",
                    "st.ind",
                    "st.sprout",
                    "i.x",
                    "i.y",
                    "st.updated_id",
                ])
                ->orderByRaw("COALESCE(st.branch, 0)")
                ->orderBy("st.tag")
                ->get()
                ->map(fn ($row) => $this->normalizeIdentityRow((array) $row))
                ->toArray();

            $masterRows = DB::connection("mysql3")
                ->table("seedling_records as r")
                ->where("r.tag", $tag)
                ->whereNull("r.deleted_at")
                ->select([
                    "r.id as record_id",
                    "r.census",
                    "r.year",
                    "r.month",
                    "r.date",
                    "r.tag",
                    "r.ht",
                    "r.cotno",
                    "r.leafno",
                    "r.note",
                    "r.recruit",
                    "r.status",
                    "r.updated_id",
                ])
                ->orderBy("r.census")
                ->get()
                ->map(fn ($row) => $this->normalizeMasterRow((array) $row))
                ->toArray();

            $displayMtag = $tag;

            return compact("workRows", "identityRows", "masterRows", "displayMtag");
        }

        $mtags = collect([explode(".", $tag)[0] ?? $tag])
            ->merge(FsSeedlingSlrecord1::query()
                ->where("tag", $tag)
                ->pluck("mtag"))
            ->merge(DB::connection("mysql3")
                ->table("seedling_stems")
                ->where("tag", $tag)
                ->whereNull("deleted_at")
                ->pluck("mtag"))
            ->map(fn ($mtag) => trim((string) $mtag))
            ->filter(fn ($mtag) => $mtag !== "")
            ->unique()
            ->values();

        $displayMtag = (string) ($mtags->first() ?? $tag);

        $relatedTags = collect([$tag])
            ->merge(FsSeedlingSlrecord1::query()
                ->whereIn("mtag", $mtags->all())
                ->pluck("tag"))
            ->merge(DB::connection("mysql3")
                ->table("seedling_stems")
                ->whereIn("mtag", $mtags->all())
                ->whereNull("deleted_at")
                ->pluck("tag"))
            ->map(fn ($relatedTag) => strtoupper(trim((string) $relatedTag)))
            ->filter(fn ($relatedTag) => $relatedTag !== "")
            ->unique()
            ->values();

        $workRows = FsSeedlingSlrecord1::query()
            ->whereIn("tag", $relatedTags->all())
            ->orderBy("mtag")
            ->orderBy("tag")
            ->orderBy("census")
            ->get()
            ->map(fn ($row) => $this->normalizeWorkRow($row->toArray()))
            ->toArray();

        $identityRows = DB::connection("mysql3")
            ->table("seedling_stems as st")
            ->leftJoin("seedling_individuals as i", "st.mtag", "=", "i.mtag")
            ->whereIn("st.mtag", $mtags->all())
            ->whereNull("st.deleted_at")
            ->whereNull("i.deleted_at")
            ->select([
                "i.id as individual_id",
                "st.id as stem_id",
                "i.trap",
                "i.plot",
                "st.tag",
                "st.mtag",
                "st.branch",
                "i.csp",
                "st.ind",
                "st.sprout",
                "i.x",
                "i.y",
                "st.updated_id",
            ])
            ->orderBy("st.mtag")
            ->orderByRaw("COALESCE(st.branch, 0)")
            ->orderBy("st.tag")
            ->get()
            ->map(fn ($row) => $this->normalizeIdentityRow((array) $row))
            ->toArray();

        $masterRows = DB::connection("mysql3")
            ->table("seedling_records as r")
            ->whereIn("r.tag", $relatedTags->all())
            ->whereNull("r.deleted_at")
            ->select([
                "r.id as record_id",
                "r.census",
                "r.year",
                "r.month",
                "r.date",
                "r.tag",
                "r.ht",
                "r.cotno",
                "r.leafno",
                "r.note",
                "r.recruit",
                "r.status",
                "r.updated_id",
            ])
            ->orderBy("r.tag")
            ->orderBy("r.census")
            ->get()
            ->map(fn ($row) => $this->normalizeMasterRow((array) $row))
            ->toArray();

        return compact("workRows", "identityRows", "masterRows", "displayMtag");
    }

    private function normalizeWorkRow(array $row): array
    {
        $row["source"] = "work";
        $row["work_id"] = $row["id"] ?? "";
        $row["record_id"] = "";
        $row["individual_id"] = "";
        $row["stem_id"] = "";
        $row["original_tag"] = $row["tag"] ?? "";
        $row["original_mtag"] = $row["mtag"] ?? "";
        $row["original_work_id"] = $row["id"] ?? "";

        return $row;
    }

    private function normalizeIdentityRow(array $row): array
    {
        $row["source"] = "identity";
        $row["work_id"] = "";
        $row["record_id"] = "";
        $row["id"] = $row["stem_id"] ?? "";
        $row["alternote"] = "";
        $row["original_tag"] = $row["tag"] ?? "";
        $row["original_mtag"] = $row["mtag"] ?? "";
        $row["original_work_id"] = "";

        return $row;
    }

    private function normalizeMasterRow(array $row): array
    {
        $row["source"] = "master";
        $row["work_id"] = "";
        $row["id"] = $row["record_id"] ?? "";
        $row["alternote"] = "";
        $row["original_tag"] = $row["tag"] ?? "";
        $row["original_mtag"] = $row["tag"] ?? "";
        $row["original_work_id"] = "";
        $row["individual_id"] = "";
        $row["stem_id"] = "";

        return $row;
    }

    public function render()
    {
        return view('livewire.fushan.seedling-updatebackdata');
    }
}
