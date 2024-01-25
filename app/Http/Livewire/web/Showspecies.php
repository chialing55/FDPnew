<?php

namespace App\Http\Livewire\Web;

use Livewire\Component;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Response;
use Livewire\WithPagination;

use App\Models\FsBaseSpinfo;
use App\Models\FsWebPhoto;
use App\Models\FsWebDisNote;

class Showspecies extends Component
{
    public $spcode;
    public $photoinfo;
    public $desinfo;
    public $speciesinfo;

    public function mount($spcode){

        $this->photoinfo=FsWebPhoto::where('spcode', 'like', $spcode)->orderBy('type2')->get()->toArray();
        // dd($photoinfo);
        $desinfo=FsWebDisNote::where('spcode', 'like', $spcode)->orderBy('type2')->get()->toArray();

        $des = [];

        foreach ($desinfo as $data) {
            if (!isset($des[$data['type']])) {
                $des[$data['type']] = [];
            }

            $des[$data['type']][] = $data['note'];
        }

        // 將 $des 轉換為索引式陣列
        // $des = array_values($des);

        // dd($this->photoinfo);

        $this->desinfo=$des;

        $this->speciesinfo=FsBaseSpinfo::where('spcode', 'like', $spcode)->first()->toArray();
        $this->spcode=$spcode;

    }


    public function render()
    {
        return view('livewire.web.showspecies');
    }
}
