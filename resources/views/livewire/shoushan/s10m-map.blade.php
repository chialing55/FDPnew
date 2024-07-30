<div>
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
    <div id='simplenote' class='text_box'>
        <ul>
            <li>資料匯入大表，並<b>完成特殊修改</b>後，即可開始點圖。</li>
            <li>請點選欲輸入的 tag 來進行輸入。</li>
            <li>如R/F分支的資料位置不正確，可先至資料修改處修改位置。</li>
            <li>在地圖上，紫色點為舊樹，粉色點為新樹。</li>
            <li>待更新的資料會以<span style='color:blue'>藍字</span>表示；正要輸入的資料會以<span style='color:red'>紅字</span>表示；已更新的資料會以<b>粗體</b>表示。</li>
            <li>新增樹資料以<span class='bgcolorRecrutMap'>粉色底</span>表示；R/F 資料以<span class='bgcolorRMap'>黃色底</span>表示。</li>
 
        </ul>
    </div>
    <div class='text_box'>
        <div style='display: inline-flex;'>
            <span style='margin-right: 20px;'>選擇要輸入的樣區</span>
            <select class="fs100 entryplot" style='width:120px; ' wire:model='selectPlot' wire:change="searchSite($event.target.value)">
                 
                <option value=""></option>
                @for ($i=0; $i<count($plots);$i++)
                <option value="{{$i}}">{{$plots[$i]}} </option>
                 @endfor
            </select>

        </div>
        <div style='margin-top: 20px; margin-left: 10px; display: inline-flex;'>
        @if ($showdata =='1')
@php
   if ($selectPlot==0){$prevshow="prevhidden"; $nextshow='prevshow';} 
   else if ($selectPlot==count($plots)){$prevshow='prevshow'; $nextshow='prevhidden';}
   else {$prevshow='prevshow'; $nextshow='prevshow';}
@endphp
            <span class='{{$prevshow}}'><a class='a_' wire:click.once="searchSite({{($selectPlot-1)}})">上一個樣方</a></span>
            <span class='{{$nextshow}}'><a class='a_' wire:click.once="searchSite({{($selectPlot+1)}})">下一個樣方</a></span>

        @endif
        </div>
    </div>

    @if($showdata =='1')
    <div class='flex text_outbox'>
        <div class='text_box'>
            <h2 style='display: inline-block;'>({{$plots[$selectPlot]}}) </h2>

            <div>
                <div style='display: flex; flex-direction: column;'>
                <p>{{$datanote}}</p>

                <div class='treeMaptable'>
                    <table id='mapTable{{$tablePlot}} mapTable' class='tablesorter'>
                        <thead>
                            <tr style="text-align: center;">
                                <th>tag</th>
                                <th>5x</th>
                                <th>5y</th>
                                <th>x</th>
                                <th>y</th>

                            </tr>
                        </thead>
                        @if(!empty($result))
                        <tbody>
                        @foreach($result as $pre)
                        @php 
                        if ($pre['qudx']=='0' && $pre['qudy']=='0'){
                            $classtype='fontblue';
                        } else if ($pre['updated_at']===''){
                            $classtype='';
                        } else if(strpos($pre['update_date'], '2024') !== false) {
                            $classtype='fontw800';
                        } else {
                            $classtype='';
                        }


                        if ($pre['type']=='R'){
                            $classtype2='bgcolorRMap';
                        }else if ($pre['status']=='-9') {
                            $classtype2='bgcolorRecrutMap';
                        } else {
                            $classtype2='';
                        }


                        @endphp
                            <tr class='{{$classtype}} {{$classtype2}} tr{{$pre['tag']}} maptr' onclick="choiceTag(this, '{{$pre['tag']}}', {{$pre['qudx']}}, {{$pre['qudy']}}, '{{$pre['type']}}')" style='cursor: pointer;'>

                                <td>{{$pre['tag']}}

                                </td>
                                <td>{{$pre['sqx']}}</td>
                                <td>{{$pre['sqy']}}</td>
                                <td>{{$pre['qudx']}}</td>
                                <td>{{$pre['qudy']}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        </div>
        <div class='text_box'>
            <div style='display: flex;'>
                <div >
                    <form id="showForm">
                        現在要輸入的編號是 <input name='tag' id='tag' class='fs100' placeholder="tag" style="width: 80px;">，選擇的點位坐標(<input name="x" id='x' size='3'/> , <input type="text" name="y" id='y' size='3'/> )
                            <input type="hidden" name="rtype" id='rtype' size='3'/>
                            
                            <button type="button" style='margin-left: 20px;' onclick="saveData()">儲存</button>
                    </form>
                </div>
                <div class='datasavenote savenote'>{{$datasavenote}}</div>
            </div>
        @if($filePath[0]!='')
            {{-- <img src="{{asset('/images/reddot.png')}}"  id="cross" style="position:relative;visibility:hidden;z-index:2;"> 
            background-image:url('/{{$filePath[0]}}')
            --}}

            <div style = "width:710px;height:710px;">
                <img src="{{asset('/images/reddot.png')}}"  id="cross" style="position:relative;visibility:hidden;z-index:10;">
            <div id="pointer_div2" onclick="showFlywhere(event)">
                <canvas id="myChart{{$tablePlot}}" style="width: 710px;height:710px"></canvas>

            </div>
            </div>
        @else
            <div>{{$error[0]}}</div>
        @endif
            <div style="margin-top: 40px;">
                <p><a href='/{{$filePath[1]}}' target=_blank>原始圖檔</a>
                <span style='margin-left:30px'><a href='/{{$filePath[2]}}' target=_blank>舊樹紙本資料</a></span>
                <span style='margin-left:30px'><a href='/{{$filePath[3]}}' target=_blank>新樹紙本資料</a></span>
                <!--<span style='margin-left:50px'><a href='fs_map.php?add=y'><input type='button' value='新增資料'/></a></span>!-->
                </p>
            </div>

        </div>
    </div>
    @endif    
</div>

