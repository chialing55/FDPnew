<?php

namespace App\Http\Livewire\Fushan;

use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Livewire\WithPagination;

use App\Models\FsSeedsDateinfo;
use App\Models\FsSeedsFulldata;
use App\Models\FsWebUnk;
use App\Models\FsWebUnkPhoto;

//unk種子資訊
class SeedsUnknown extends Component
{
    public $unk='%';
    public $unkdes;
    public $unkphoto;
    public $unklist=[];

    public function mount(Request $request){

        $unkParam = $request->session()->get('unk', function () {
            return 'no';
        });

        if ($unkParam!='no'){
            $this->unk=$unkParam;
            $request->session()->forget('unk');
        }
        // $unklist=[];
        $unkphoto=[];
        $unklist=FsWebUnk::query('unkname')->orderby('unkname')->get()->toArray();

        $this->unkdes=FsWebUnk::query()->where('unkname', 'like', $this->unk)->orderby('unkname')->get()->toArray();
        $unkphotos=FsWebUnkPhoto::query()->get()->toArray();
        foreach ($unkphotos as $photo) {
            $unkphoto[$photo['unkname']][]=$photo;
        }

        foreach($unklist as $list){
            $this->unklist[]=$list['unkname'];
        }

        $this->unkphoto=$unkphoto;
        // dd($this->unklist);

    }

    public function openData(Request $request, $url, $unk){
        $request->session()->put('unk', $unk);

        return redirect()->to($url);
    }

    public function search(Request $request){
        $this->mount($request);
    }

    public function render()
    {
        return view('livewire.fushan.seeds-unknown');
    }
}
