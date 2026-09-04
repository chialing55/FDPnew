<?php

namespace App\Http\Livewire\Fushan;

use App\Services\Fushan\GeoTreeComparisonService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;

class GeoTreeSurveyCompare extends Component
{
    public string $statusNote = '按「開始比對」後，會一次比對全部可輸入的 GEO-TREES 資料。';
    public bool $hasCompared = false;
    public int $eligible = 0;
    public int $locked = 0;
    public int $differenceCount = 0;
    public int $page = 1;
    public int $perPage = 100;
    public array $rows = [];
    public string $resultKey = '';

    public function compare(GeoTreeComparisonService $service): void
    {
        if (!$this->tablesExist()) {
            $this->statusNote = 'record1 或 record2 尚未建立，請先產生 GEO-TREES 輸入表。';
            return;
        }

        $result = $service->compare();
        $this->resultKey = 'geo_tree_compare.' . Str::uuid();
        session()->put($this->resultKey, $result['differences']);
        $this->eligible = $result['eligible'];
        $this->locked = $result['locked'];
        $this->differenceCount = count($result['differences']);
        $this->hasCompared = true;
        $this->page = 1;
        $this->loadPage();
    }

    public function previousPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadPage();
        }
    }

    public function nextPage(): void
    {
        if ($this->page < $this->lastPage()) {
            $this->page++;
            $this->loadPage();
        }
    }

    private function loadPage(): void
    {
        $all = session()->get($this->resultKey, []);
        $this->rows = array_slice($all, ($this->page - 1) * $this->perPage, $this->perPage);
    }

    public function lastPage(): int
    {
        return max(1, (int) ceil($this->differenceCount / $this->perPage));
    }

    private function tablesExist(): bool
    {
        return Schema::connection('fs_geo_tree_survey')->hasTable('record1')
            && Schema::connection('fs_geo_tree_survey')->hasTable('record2');
    }

    public function render()
    {
        return view('livewire.fushan.geo-tree-survey-compare', ['lastPage' => $this->lastPage()]);
    }
}
