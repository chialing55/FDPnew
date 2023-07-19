<div class='flex text_outbox' style='flex-direction: column; '>

    <div class='text_box'>     
        <h2>檢視調查資料電子檔</h2>
        <hr>
        <div style='margin-top: 10px; line-height: 1.8em;'>
                   
            檢視  
                <select class="fs100 entryqx" name='qx' id='qx'style=' ' wire:model='qx' wire:change="change">
                @for ($i=0; $i<25;$i++)     
                <option value="{{$i}}">{{$i}}</option>
                @endfor
                </select>-<select class="fs100" name='qy' id='qy' style='' wire:model='qy' wire:change="change">
                @for ($i=0; $i<25;$i++)
                <option value="{{$i}}">{{$i}} </option>
                @endfor
                </select>

                第 <select class="fs100" name='census' id='census' style='' wire:model='census' wire:change="change">
                @for ($i=1; $i<5;$i++)
                <option value="{{$i}}">{{$i}} </option>
                @endfor
                </select> 次調查之

                <select class="fs100" name='oldnew' id='oldnew' style='' wire:model='oldnew' wire:change="change">
                <option value="old">舊樹 </option>
                <option value="new">新樹 </option>
                </select>

                 @if($path!='')
                <a href='{{asset($path)}}' target="_blank"><button>送出</button></a>
                @else
                <button>送出</button>
                @endif
          
            @if($error!='')
                <p class='savenote'>{{$error}}</p>
            @endif
        </div>
    </div>

</div>
