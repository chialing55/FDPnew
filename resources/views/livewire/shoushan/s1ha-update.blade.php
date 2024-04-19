<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div style='display: flex;'>
@php 
if($type==1){$addclass1='thistype'; $addclass2='';}
if($type==2){$addclass2='thistype'; $addclass1='';}
@endphp
    <div class='text_box {{$addclass1}}'>
        <h2>特殊修改</h2>
        <hr>
        <div style='display: inline-flex; '>
            <div>選擇欲特殊修改之樣線: </div>
        {{-- @include('includes.census5Progress') --}}
            <div style='margin-left:20px'>
                <form wire:submit.prevent='alternote()'>

                    <select name="qx" class="fs100 entryqx" wire:model.defer='qx' style='height:25px;'>
                        <option value=""></option>
                    @for ($i=-4; $i<11;$i++)
                    @php 
                      
                            echo "<option value=".$i.">".$i."</option>";
               
                    @endphp
                    {{-- <option value="{{$i}}">{{$i}}</option> --}}
                     @endfor
                    </select>
                    <button type="submit" style='margin-left: 20px;'>GO</button>
                </form>

            </div>

        </div>

        
    </div>
    <div class='text_box {{$addclass2}}'>
        <h2>個別枝幹修改 </h2>
        <hr>
        <div style='display: inline-flex;'>
            <div>輸入枝幹編號：</div>
        {{-- @include('includes.census5Progress') --}}
            <div style="margin-left:20px;">
                <form wire:submit.prevent="indStemid()">
                    <input name="tag" class="fs100" placeholder="tag" wire:model.defer="tag" style="width: 80px;">.<input name="branch" class="fs100" wire:model.defer="branch" placeholder="b" style="width: 30px;"> <span style="font-size: 80%;">*預設 b 為 0</span>
                    <button type="submit" style="margin-left: 20px;">GO</button>
                </form>
            </div>
        </div>
    </div>
    </div>
    @if($go!='')
    @if($go=='no')
    <div class='text_box'>
        <div class='tablenote'>
            <span style='margin-right: 20px'> 查無此樹。可能尚未匯入大表。</span>
        </div>
    </div>
    @else
    <div id='simplenote' class='text_box' style='max-width: 900px;'>
    <ul>
        <li>主幹才能修改位置資訊及物種名稱。</li>
        <li>若為更改號碼，請至<a href='{{asset('/fushan/tree/dataviewer')}}'>資料檢視</a>檢查是否重號或是否有主幹。(有時是分支要更正為獨立個體，但特殊修改沒有寫 b=0)</li>
        <li>若為換號，會因另一號碼尚未更新而出現重號的錯誤。可先給予另一位使用之編號，然後更新互相換號的另一個號碼後，再重新更新為正確的。如：200600.2與200600.3更換分支號，先將200600.2更新為200600.99，然後將200600.3更新為200600.2後，再將原本的200600.2更新為200600.3。</li>

    </ul>
</div>
    <div class='text_box' style='position: relative;'>

        
        @if($stemidlist==[])
        <div class='tablenote'>
            <span style='margin-right: 20px'> 沒有特殊修改資料</span>
        </div>
        @else

         @if($type=='1')
         <div style='display: flex; justify-content: space-between;'>

            <div >
            <span style='margin-right: 20px'>{{$finishnote}}</button></span>

            </div>
            
            
        </div>
        @endif
      
        <h2 style='display: inline-block;'>{{strval($stemid)}} </h2>

            @php
            $key = array_search($stemid, $stemidlist);
            @endphp
        <div class='tablenote'>
        @if($type=='1')

            <span style='margin-left:20px'>{{$key+1}} / {{count($stemidlist)}}</span>

        @if($key!='0')
        @php 
            $prev=$key-1;
        @endphp
            <span style='margin-left:40px'><a class="a_" wire:click.once="searchStemid({{$prev}})">上一筆</a></span>
        @endif
        @if($key!=(count($stemidlist)-1))
        @php 
            $prev=$key+1;
        @endphp
            <span style='margin-left: 30px;'><a class="a_" wire:click.once="searchStemid({{$prev}})">下一筆</a></span>
        @endif

        <span style='margin-left: 30px;'> 直接前往：
            <input name="goto" class="fs100" wire:model.defer="goto" wire:change="searchStemid($event.target.value-1)" style="width: 30px;">
        </span>
        @endif

        <span class='datasavenote savenote' style='margin: 0 30px'>{{$dataNote}}</span>
        </div>
        
        <div id='basetable{{$stemid2}}' style='margin-top: 20px;' class='fs100' > </div>
        <div id='datatable{{$stemid2}}' style='margin-top: 20px;' class='fs100' ></div>


        <p style='margin-top:5px; text-align: center;'><button name='datasave{{$stemid2}}' class='datasavebutton'>儲存</button></p>

        <div id='note{{$stemid2}}' class='simplenote' style='max-width: 800px; padding: 20px; margin-top: 20px;'>
            <p><b>如需刪除此筆資料，請注意</b>:
                <ol>
                    <li>如因鑑定錯誤(確認為藤本)需刪除資料，請在特殊修改欄位說明確認之種類，及註記要刪除，如「{..., "csp":"XXX","other":"不需調查故刪除"}」，並至<b>主幹</b>資料位置按刪除鈕。系統會保留植株所有資料，並皆註記刪除。<span class='line'>若為分支資料，則會直接刪除</span>。</li>
                    <li>如為判斷錯誤需刪除分支，資料會直接刪除，以讓下次調查可以使用該分支號。</li>
                </ol>
            </p>
            <button name='deleteCensusDataB' stemid={{$stemid}} from={{$from}} class='deleteCensusDataB' onclick="deleteCensusDataButtonClick(this)">刪除此筆資料</button>
        </div>

        @endif
    </div>
    @endif
    @endif
</div>

