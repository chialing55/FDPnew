<?php

namespace App\Http\Livewire\Fushan;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

use App\Models\FsSeedlingSlrecord;
use App\Models\FsSeedlingSlrecord1;
use App\Models\FsSeedlingSlrecord2;
use App\Models\FsSeedlingSlcov1;
use App\Models\FsSeedlingSlcov2;
use App\Models\FsSeedlingSlroll1;
use App\Models\FsSeedlingSlroll2;
// use App\Post;

use App\Jobs\SeedlingAddButton;

//小苗輸入資料
class SeedlingShowentry extends Component
{

    public $entry;
    public $user;
    public $site;

    public $census;
    public $maxid;
    public $entrynote;
    public $start;
    public $year;
    public $month;
    public $sustrap;
    // public $entryother;
    public $selectTrap;
    public $selectTrap2;
    public $record;
    public $cov;
    public $roll;

    // public $savecov=[];
    public $covsavenote = '';
    public $seedlingsavenote = '';

    public $recordDate;
    public $trapOptions = [];
    public $previousTrap = null;
    public $nextTrap = null;

    use WithPagination;




    public function mount()
    {   //初始設定
        // $entry=$this->entry;
        $entryother = '';
        $start = '';
        $firsttrap = '';
        // $sustrap='';

        [$table, $tablecov] = $this->resolveEntryTables();
        $entryother = $this->entry == '1' ? '2' : '1';
        $this->trapOptions = $this->loadTrapOptions();


        $census = FsSeedlingSlrecord1::first();
        if (!$census) {
            $this->entrynote = '目前尚未建立小苗輸入表單資料。';
            $this->record = null;
            $this->covs = [];
            $this->roll = [];

            return;
        }

        $maxid = FsSeedlingSlrecord::count();
        $this->census = $census['census'];
        $this->year = $census['year'];
        $this->month = $census['month'];
        $this->maxid = $maxid;

        $slrecord = $tablecov::where('date', 'like', '0000-00-00')->orderBy('trap', 'asc')->first();
        $slrecord2 = $table::where('date', 'like', '0000-00-00')->orderBy('trap', 'asc')->first();

        $firsttrap = '';
        if ($slrecord && $slrecord2) {
            $firsttrap = min($slrecord['trap'], $slrecord2['trap']);
        } elseif ($slrecord) {
            $firsttrap = $slrecord['trap'];
        } elseif ($slrecord2) {
            $firsttrap = $slrecord2['trap'];
        }


        // print_r($slrecord1);
        if ($firsttrap != '') {

            $this->entrynote = '請從第 ' . $firsttrap . ' 個樣站開始輸入';
            // $this->sustrap=$slrecord[0]['trap'];
            // $this->selectTrap=$firsttrap;

        } else {

            $this->entrynote = '第' . $this->entry . '次輸入已完成。 若以完成第' . $entryother . '次輸入，可進行資料比對。';
            // $this->selectTrap='1';
        }

        $this->refreshTrapNavigation();

        // dd($this->selectTrap);
    }

    public $recordstart;
    public $recordend;
    public $thispage;
    public $totalpage;
    public $covs;

    //選擇輸入樣區
    public function searchtrap($selectTrap)
    {
        // $selectTrap=$selectTrap3;
        // dd($selectTrap3);

        [$table, $tablecov, $tableroll] = $this->resolveEntryTables(true);

        $slrecord = $table::where('trap', 'like', $selectTrap)->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();
        $slcov = $tablecov::where('trap', 'like', $selectTrap)->orderBy('plot', 'asc')->get();
        $slroll = $tableroll::where('trap', 'like', $selectTrap)->orderBy('plot', 'asc')->orderBy('tag', 'asc')->get();
        // $slrecord


        if ($slrecord->isEmpty()) {
            $slrecord1[0]['trap'] = $selectTrap;
            $slrecord1[0]['tag'] = '無';
            // $slrecord1=='無';
            // $slrecord='無';
        } else {
            $ob_redata = new SeedlingAddButton;
            $slrecord1 = $ob_redata->addbutton($slrecord, $this->entry);
        }

        $scsplist = session()->get('scsplist', 'no');

        if ($scsplist == 'no') {
            $scsplist = [];

            $scsplist1 = $table::select('csp', DB::raw('count(tag) as count2'))->groupBy('csp')->orderByDesc('count2')->get()->toArray();

            for ($i = 0; $i < count($scsplist1); $i++) {
                $scsplist[] = $scsplist1[$i]['csp'];
            }

            session()->put('scsplist', $scsplist);
        }



        if ($slroll->isEmpty()) {
            $slroll = [];
        } else {
            $slroll = $slroll->toArray();

            for ($m = 0; $m < count($slroll); $m++) {

                $slroll[$m]['delete'] = "<button class='deleteroll' deleteid='" . $slroll[$m]['id'] . "' tag='" . $slroll[$m]['tag'] . "' entry='" . $this->entry . "' trap='" . $selectTrap . "'>X</button>";
            }
        }

        // dd($slroll);

        $this->record = $slrecord1;
        $this->covs = $slcov;
        $this->roll = $slroll;
        $this->selectTrap = $selectTrap;
        $this->refreshTrapNavigation();
        $this->covsavenote = '';
        $this->seedlingsavenote = '';
        //recruittable
        for ($k = 0; $k < 20; $k++) {
            $emptytable[$k]['date'] = '';
            $emptytable[$k]['trap'] = $selectTrap;
            $emptytable[$k]['recruit'] = 'R';
            $emptytable[$k]['sprout'] = 'FALSE';
            $emptytable[$k]['tag'] = '';
            $emptytable[$k]['csp'] = '';
            $emptytable[$k]['ht'] = '';
            $emptytable[$k]['cotno'] = '';
            $emptytable[$k]['leafno'] = '';
            $emptytable[$k]['x'] = '';
            $emptytable[$k]['y'] = '';
            $emptytable[$k]['note'] = '';
            $emptytable[$k]['tofix'] = '';
        }

        // $this->recordstart='0';
        // $this->thispage='1';

        // $this->dispatchBrowserEvent('data', ['covs' => $slcov, 'record' => $slrecord1, 'maxid' => $this->maxid, 'emptytable' => $emptytable, 'csplist' => $scsplist, 'slroll' => $slroll]);

        $this->dispatch(
            'data',
            covs: $slcov,
            record: $slrecord1,
            maxid: $this->maxid,
            emptytable: $emptytable,
            csplist: $scsplist,
            slroll: $slroll
        );
 
        // dd(count($slrecord));
    }





    public function render()
    {
        return view('livewire.fushan.seedling-showentry');
        // $this->dispatchBrowserEvent('jquery');
    }

    private function resolveEntryTables(bool $withRoll = false): array
    {
        if ($this->entry == '1') {
            $tables = [new FsSeedlingSlrecord1, new FsSeedlingSlcov1];
            if ($withRoll) {
                $tables[] = new FsSeedlingSlroll1;
            }

            return $tables;
        }

        $tables = [new FsSeedlingSlrecord2, new FsSeedlingSlcov2];
        if ($withRoll) {
            $tables[] = new FsSeedlingSlroll2;
        }

        return $tables;
    }

    private function loadTrapOptions(): array
    {
        [, $tablecov] = $this->resolveEntryTables();

        return $tablecov::query()
            ->select('trap')
            ->whereNotNull('trap')
            ->distinct()
            ->orderBy('trap')
            ->pluck('trap')
            ->map(fn ($trap) => (int) $trap)
            ->values()
            ->all();
    }

    private function refreshTrapNavigation(): void
    {
        $this->previousTrap = null;
        $this->nextTrap = null;

        if ($this->selectTrap === null || $this->selectTrap === '' || empty($this->trapOptions)) {
            return;
        }

        $currentTrap = (int) $this->selectTrap;
        $currentIndex = array_search($currentTrap, $this->trapOptions, true);

        if ($currentIndex === false) {
            return;
        }

        $this->previousTrap = $currentIndex > 0 ? $this->trapOptions[$currentIndex - 1] : null;
        $this->nextTrap = $currentIndex < count($this->trapOptions) - 1 ? $this->trapOptions[$currentIndex + 1] : null;
    }
}
