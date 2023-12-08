<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>

{{--     <div class='text_box'>     
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
                <option value="map">地圖 </option>
                </select> 
                @if($oldnew=='map')
                    第
                    <select class="fs100" name='map' id='map' style='' wire:model='map' wire:change="change">
                        @for ($i=1; $i<5;$i++)
                            <option value="{{$i}}">{{$i}}</option>
                        @endfor
                    </select> 
                    區
                @endif  
                資料


               path: {{$path}} 
                <span style='margin-left:20px'>
                    @if($path!='')
                    <a href='{{asset($path)}}' target="_blank"><button>送出</button></a>
                    @else
                    <button>送出</button>
                    @endif
                </span>
          
            @if($error!='')
                <p class='savenote'>{{$error}}</p>
            @endif
        </div>
    </div> --}} 

    <div class='text_box'>     
        <h2>檢視調查資料電子檔</h2>
        <hr>
        <div style='margin-top: 10px; line-height: 1.8em; display: inline-flex;'>
            <div>       
                <p>樣區編號：
                <select class="fs100 entryqx" name='qx1' id='qx1'style=' ' wire:model='qx1' wire:change="change2">
                @for ($i=0; $i<25;$i++)     
                <option value="{{$i}}">{{$i}}</option>
                @endfor
                </select>-<select class="fs100" name='qy1' id='qy1' style='' wire:model='qy1' wire:change="change2">
                @for ($i=0; $i<25;$i++)
                <option value="{{$i}}">{{$i}} </option>
                @endfor
                </select></p>
            </div>
            <div style='margin-left: 30px;' class='dataviewerdownload'>
                <table class='tablesorter'>
                    <tbody>
                        @for($i=1;$i<5;$i++)
                        <tr>
                            <td style='font-weight: 800'>第 {{$i}} 次調查</td>
                            <td>{!!$downloadtable[$i][0]!!}</td>
                            <td>{!!$downloadtable[$i][1]!!}</td>
                            <td>{!!$downloadtable[$i][2]!!}</td>
                            <td>{!!$downloadtable[$i][3]!!}</td>
                            <td>{!!$downloadtable[$i][4]!!}</td>
                            <td>{!!$downloadtable[$i][5]!!}</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <div class='text_box'>     
        <h2>查詢個別植株資料</h2>
        <p style='font-size: 80%;'>*僅能查詢前 4 次調查資料</p>
        <hr>
        <div style='margin-top: 10px; line-height: 1.8em; display: inline-flex;'>
            <span style='margin-right: 20px;'>枝幹編號：</span>     
            <form wire:submit.prevent='submitStemidForm()'>
                <input name='tag' class='fs100' placeholder="tag" wire:model.defer='tag' style="width: 80px;">.<input name='branch' class='fs100' wire:model.defer='branch' placeholder="b" style="width: 30px;"> <span style='font-size: 80%;'>*預設 b 為 0</span>
                <button type="submit" style='margin-left: 20px;'>查詢</button>
            </form>            
        </div>
        {{-- 查詢結果 --}}

        <div style='margin-top: 20px;'>
            @if($resultnote!='')

            <p class='savenote'>{{$resultnote}}</p>
            @elseif(!empty($basedata))
            <div class='fstreeStemidtable'>
            <table class='tablesorter'>
                
                <thead >
                    <tr style="text-align: center;">
                        <th>20x</th>
                        <th>20y</th>
                        <th>5x</th>
                        <th>5y</th>
                        <th>tag</th>
                        <th>b</th>
                        <th>csp</th>
                        <th>最大分支號</th>
                        
                    </tr>
                </thead>
               
                <tbody>
                    <tr style="text-align: center;">
                        <td>{{$basedata['qx']}}</td>
                        <td>{{$basedata['qy']}}</td>
                        <td>{{$basedata['sqx']}}</td>
                        <td>{{$basedata['sqy']}}</td>
                        <td>{{$basedata['tag']}}</td>
                        <td>{{$basedata['b']}}</td>
                        <td>{{$basedata['csp']}}</td>
                        <td>{{$basedata['bs']}}</td>
                    </tr>
                </tbody>
               
            </table>
            </div>
            <div class='fstreeStemidtable'>
                <table id='StemidTable' class='tablesorter'>
                    <thead>
                        <tr style="text-align: center;">
                            <th>census</th>
                            <th>status</th>
                            <th>code</th>
                            <th>dbh/h高</th>
                            <th>pom</th>
                            <th>note</th>
                            <th>縮水</th>
                            
                        </tr>
                    </thead>
                    @if(!empty($result))
                    <tbody>
                    @foreach($result as $pre)
                        <tr style="text-align: center;">
                            <td>{{$pre['census']}}</td>
                            <td>{{$pre['status']}}</td>
                            <td>{{$pre['code']}}</td>
                            <td>{{$pre['dbh']}}</td>
                            <td>{{$pre['pom']}}</td>
                            <td>{{$pre['note']}}</td>
                            <td>@if($pre['confirm']=='1')
                            <i class="fa-solid fa-check"></i>
                            @endif</td>
                        </tr>
                    @endforeach
                    </tbody>
                    @endif
                </table>
            </div>

        @endif        
        
        </div>
    </div>

</div>
