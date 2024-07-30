<div>
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>
{{--     <div class='text_box'>
        <h6>已完成資料比對、上傳檔案、匯入大表的樣線</h6>
        @include('includes.fstree-census5-progress')
    </div> --}}
    <div id='simplenote' class='text_box'>
        <ul>
            <li>資料匯入大表，並<b>完成特殊修改</b>後，即可開始點圖。</li>
            <li>請點選欲輸入的 tag 來進行輸入。</li>
            <li>在地圖上，紫色點為舊樹，粉色點為新樹。</li>
            <li>待更新的資料會以<span style='color:blue'>藍字</span>表示；正要輸入的資料會以<span style='color:red'>紅字</span>表示；已更新的資料會以<b>粗體</b>表示。</li>
            <li>新增樹資料以<span class='bgcolorRecrutMap'>粉色底</span>表示；R 資料以<span class='bgcolorRMap'>黃色底</span>表示。</li>
 
        </ul>
    </div>
    <div style='display: flex; align-items: flex-start;'>
    <div class='text_box'>
        <div style='display: inline-flex;'>
            <span style='margin-right: 20px;'>選擇要輸入的樣方</span>
            <form wire:submit.prevent='submitForm'>
                <select name="qx" class="fs100 entryqx" wire:model.defer='qx' style='height:25px;'>
                    <option value=""></option>
                @for ($i=0; $i<25;$i++)
                        @php 
                            if (in_array($i, $alternotelist)){
                                echo "<option value=".$i.">".$i."</option>";
                            } 
                        @endphp
                 @endfor
                </select>-<select name="qy" class="fs100" wire:model.defer='qy' style='height:25px;'>
                    <option value=""></option>
                @for ($i=0; $i<25;$i++)
                <option value="{{$i}}">{{$i}} 
                </option>
                 @endfor
                </select>
                <button type="submit" style='margin-left: 20px;'>送出</button>

            </form>

        </div>
        <div style='margin-left: 10px; display: inline-flex;'>
        @if ($showdata =='1')
        @php
           if ($qy==0){$prevshow="prevhidden"; $nextshow='prevshow';} 
           else if ($qy==24){$prevshow='prevshow'; $nextshow='prevhidden';}
           else {$prevshow='prevshow'; $nextshow='prevshow';}
        @endphp
                    <span class='{{$prevshow}}'><a class='a_' wire:click.once="searchSite({{$qx}}, {{$qy-1}}, 1, 1)">上一個樣方</a></span>
                    <span class='{{$nextshow}}'><a class='a_' wire:click.once="searchSite({{$qx}}, {{$qy+1}}, 1, 1)">下一個樣方</a></span>

        @endif
    </div>
    </div>
    <div class='text_box'>
        <h6 style="cursor: pointer;" onclick="$('.mapProgress').toggle();$('.mapProgressArrayDown').toggle();$('.mapProgressArrayright').toggle();"> <i class="fa-solid fa-caret-down mapProgressArrayDown"  style='display: none;'></i><i class="fa-solid fa-caret-right mapProgressArrayright"></i> 植株位置輸入進度</h6>
        
        <div class='mapProgress' style='display: none;'>
            <div style='margin:0 0 20px 0; display:flex;'>
                <div style='display:inline-flex; margin-right:30px;'>已匯入地圖資料 <div class='entry1fin entryfinshow' ></div></div>
                <div style='display:inline-flex; margin-right:30px;'>點圖完成 <div class='entry2fin entryfinshow'></div></div>
            </div>
            <table class='finishtable'border="1" cellpadding="1" cellspacing="0" style=''>

                @for ($i=24;$i>-1;$i--)
                <tr>
                    <td style='width:25px'>{{$i}}</td>
                    @for($j=0;$j<25;$j++)
                    @php 
                    if($finishMap["'".$j."-".$i."'"]=='1'){
                        $finishMapClass='entry1fin';
                    } else if ($finishMap["'".$j."-".$i."'"]=='2'){
                        $finishMapClass='entry2fin';
                    } else {
                        $finishMapClass='';
                    }
                    @endphp

                    <td class='{{$j}}-{{$i}}  {{$finishMapClass}}'></td>

                    @endfor
                </tr>
                @endfor
                                <tr>
                    <td></td>
                    @for($i=0;$i<25;$i++)
                    <td style='width:25px'>{{$i}}</td>
                    @endfor
                </tr>
            </table>

        </div>
    </div>
    </div>
    @if($showdata =='1')

    <div class='flex text_outbox'>
        <div class='text_box'>
            <div style="display: flex;">
            <h2 style='display: inline-block;'>({{$qx}}, {{$qy}}) </h2>

            <div style='line-height: 1.5; margin: -10px 20px 0 0 ; transform: scale(0.8);'> 
                @for($j=4; $j>0; $j--)
                    @for($i=1; $i<5; $i++)
                @php

                switch ($i) {  //sqx, subqx
                    case '1':
                    case '2':
                        $m = '1';
                        break;
                    case '3':
                    case '4':
                        $m = '2';
                        break;
                    default:
                        $m; // 保留原始值
                        break;
                }

                switch ($j) {   //sqy, subqy
                    case '1':
                    case '2':
                        $n = '1';
                        break;
                    case '3':
                    case '4':
                        $n = '2';
                        break;
                    default:
                        $n; // 保留原始值
                        break;
                }

                if ($m==$subqx && $n==$subqy)
                {$class='selected';} else {$class='';}
                @endphp            
                    <div class="plottable2 plot{{$qx}}{{$qy}}{{$m}}{{$n}} {{$class}}" wire:click.once="submitsqxForm({{$m}}, {{$n}})">{{$i}}, {{$j}}</div>
                    @if($i==4)<br>@endif
                    @endfor
                @endfor
            </div>
            </div>
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
                        現在要輸入的編號是<input name='tag' id='tag' class='fs100' placeholder="tag" style="width: 80px;">，選擇的點位坐標(<input name="x" id='x' size='3'/> , <input type="text" name="y" id='y' size='3'/> )
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
