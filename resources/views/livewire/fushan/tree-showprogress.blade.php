
<div class='flex text_outbox' style='flex-direction: column; '>
     {{-- <h1>2023 年 每木調查 進度表</h1> --}}

    <div class='text_box'>     
        <h2>輸入調查進度</h2>
        <hr>
        <div style='margin-top: 10px; line-height: 1.8em;'> 
        <form wire:submit.prevent="submitForm" method="POST">       
        <div class="iflex">
        <div>    
            <p>調查日期： <input name='date' type='date' placeholder="YYYY-MM-DD" style='width:120px; ' class='fs100' wire:model="date"></p>
            <p>調查樣方： 
                <select class="fs100 entryqx" style='width:45px; ' wire:model='qx' wire:change="qxqychange">
                @for ($i=0; $i<25;$i++)     
                <option value="{{$i}}">{{$i}}</option>
                @endfor
                </select>-<select class="fs100" style='width:45px;' wire:model='qy' wire:change="qxqychange">
                @for ($i=0; $i<25;$i++)
                <option value="{{$i}}">{{$i}} </option>
                @endfor
                </select>
            </p>
            
            <p>工作人數： <input name='ind' type='text' style='width:45px;' wire:model='ind'></p>
            <p>新增枝幹： <input name='new' type=text style='width:45px' wire:model="new"/> 筆 (同一天同一樣方輸入一筆合計資料)</p>
        </div>
        <div style='margin-left: 50px;'>    
            <p>完成小區： <br> 
                <div wire:ignore style='line-height: 3;'> 
                    @for($j=4; $j>0; $j--)
                        @for($i=1; $i<5; $i++)
                    @php
                    $class='canselect';

                    if (!empty($plots)){
                        if (in_array([$i, $j], $plots)) {
                            $class='cannotselect';
                        } 
                    }
                    @endphp            
                        <div class="plottable plot{{$i}}{{$j}} {{$class}}" show='0' i={{$i}}  j ={{$j}}>{{$i}}, {{$j}}</div>
                        @if($i==4)<br>@endif
                        @endfor
                    @endfor
                </div>
            </p>              

        </div>
        </div>
        <div class='text_box_enter'>
            <p class='savenote'>{{$note1}}</p>
            <button type='submit'>輸入</button>                
        </div>    
        </form>
        </div>     
    </div>

    
    <div class='text_box'>
        <h2>調查進度</h2>
        <hr>
        <div id='fstreeprogresstable'>
            <table id='progressTable' class='tablesorter'>
                <thead>
                    <tr>
                        <th>調查日期</th>
                        <th style='width:50px'>樣線</th>
                        <th>調查人數</th>
                        <th>完成小區數</th>
                        <th>完成小區</th>
                        <th>舊枝幹數量</th>
                        <th>新枝幹數量</th>
                    </tr>
                </thead>
                @if(!empty($table))
                <tbody>
                    @foreach($table as $pro)
                    <tr>
                        <td>{{$pro['date']}}</td>
                        <td>{{$pro['qx']}}, {{$pro['qy']}}</td>
                        <td>{{$pro['person']}}</td>
                        <td>{{$pro['plot_num']}}</td>
                        <td  style='min-width:100px'>{!!$pro['plots']!!}</td>
                        <td>{{$pro['ori_branch']}}</td>
                        <td>{{$pro['new_branch']}}</td>
                        
                    </tr>
                    @endforeach
                </tbody>
                @endif
            </table>
        </div>
    </div>

    <div class='text_box' style='background-color:#ececc4'>
        <h2>修改調查進度資料</h2>
        <hr>
        <form wire:submit.prevent="deleteForm" method='POST' style='margin-top:10px'>
            <span style='margin-right:20px'> 將 <input name='date2' type=date   style="width:120px" placeholder="YYYY-MM-DD" wire:model='date2'/> 第 
            <select name="qx2" class="fs100 entryqx" style='width:45px; ' wire:model='qx2'>
            <option value=""></option> 
            @for ($i=0; $i<25;$i++)
            <option value="{{$i}}">{{$i}} </option>
            @endfor
            </select> 
            線的調查進度資料刪除再重新輸入</span>
            <button type='submit' >刪除</button>
            <span class='savenote'>{{$note2}}</span>          
        </form>
    </div>

    <div class='text_box'>

        <h2>完成調查樣區</h2>
        <hr>
        <div style='margin:10px 0 0 0px'>
            <p style='margin:0 0 20px 0'>
                共完成 {{$countFinishSite}} 個樣區，累計 {{($countFinishSite/625)*25}} 公頃，達 {{($countFinishSite/625)*100}} %。
            </p>
            <table class='finishtable'border="1" cellpadding="1" cellspacing="0" style=''>
                <tr>
                    <td></td>
                    @for($i=0;$i<25;$i++)
                    <td style='width:25px'>{{$i}}</td>
                    @endfor
                </tr>
                @for ($i=24;$i>-1;$i--)
                <tr>
                    <td style='width:25px'>{{$i}}</td>
                    @for($j=0;$j<25;$j++)
                    @php 
                    if(isset($finishSite["'".$j.$i."'"]) && $finishSite["'".$j.$i."'"]=='16'){
                    $finishSiteClass='allfinish';
                    } else if (isset($finishSite["'".$j.$i."'"]) && $finishSite["'".$j.$i."'"]!='16'){
                    $finishSiteClass='somefinish';
                    } else {
                    $finishSiteClass='';
                    }
                    @endphp

                    <td class='{{$j}}{{$i}}  {{$finishSiteClass}}'></td>

                    @endfor
                </tr>
                @endfor
            </table>
        </div>
    </div>
</div>