<div>
    <h2>更新census5資料表 (only for chialing)</h2>
    <p><b>如已完成資料比對，並完成資料掃描，即可匯入大表。</b></p>

    <div class='text_box'>
        <h2>已完成資料比對、上傳檔案、匯入大表的樣線</h2>
        <br>
        @include('includes.census5Progress')
    </div>
    <div class='text_box'>
        

        <h2>選擇要匯入大表的樣線</h2>
        <br>

            <form wire:submit.prevent='import'>

                <select name="qx" class="fs100 entryqx" wire:model='qx' style='height:25px;'>
                    <option value=""></option>
                @for ($i=0; $i<25;$i++)
                @php 
                    if (in_array($i, $directories) && !in_array($i, $updatelist)){
                        echo "<option value=".$i.">".$i."</option>";
                    } 
                @endphp
                {{-- <option value="{{$i}}">{{$i}}</option> --}}
                 @endfor
                </select>
                <button type="submit" style='margin-left: 20px;'>匯入大表</button>
            </form>
           
 
    @if($importnote!='')
        <p >{{$importnote}}</p>
        <p >{!$importnote2!}</p>
    @endif
    </div>    
</div>
