<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div class='text_box'>     
        <h2>查詢個別植株資料</h2>

        <hr>
        <div style='margin-top: 10px; line-height: 1.8em; display: inline-flex;'>
            <span style='margin-right: 20px;'>小苗編號：</span>     
            <form wire:submit.prevent='submitTagForm()'>
                <input name='tag' class='fs100' placeholder="tag" wire:model.defer='tag' style="width: 80px;">
                {{-- <label><input name='allB' type='checkbox' wire:model.defer='allB'/> 所有分支資料</label> --}}
                <button type="submit" style='margin-left: 20px;'>查詢</button>
            </form>            
        </div>
        {{-- 查詢結果 --}}

        <div style='margin-top: 20px;'>
            @if($resultnote!='')

            <p class='savenote'>{{$resultnote}}</p>
            @elseif(!empty($basedata))
        <p style='font-size: 80%;'>* [census {{$lastCensus}}] 為最新調查資料，若尚未輸入資料則保留空白。</p>            
            <div  class='fsSeedlingTagtable'>
            <table class='tablesorter'>
                
                <thead >
                    <tr style="text-align: center;">
                        <th>trap</th>
                        <th>plot</th>
                        <th>tag</th>
                        <th>種類</th>
                        <th>x</th>
                        <th>y</th>
                        <th>最大分支號</th>
                        
                    </tr>
                </thead>
               
                <tbody>
                    <tr style="text-align: center;">
                        <td>{{$basedata['trap']}}</td>
                        <td>{{$basedata['plot']}}</td>
                        <td>{{$tag}}</td>
                        <td>{{$basedata['csp']}}</td>
                        <td>{{$basedata['x']}}</td>
                        <td>{{$basedata['y']}}</td>
                        <td>{{$basedata['maxb']}}</td>

                    </tr>
                </tbody>
               
            </table>
            </div>
            <div class='fsSeedlingTagtable'>
                <table id='progressTable{{$tableTag}}' class='tablesorter'>
                    <thead>
                        <tr style="text-align: center;">
                            <th width='60px'>census</th>
                            <th width='50px'>長度</th>
                            <th width='50px'>子葉數</th>
                            <th width='50px'>葉片數</th>
                            <th width='50px'>狀態</th>
                            <th width='50px'>新舊</th>
                            <th width='50px'>萌櫱</th>
                            <th>note</th>

                            
                        </tr>
                    </thead>
                    @if(!empty($result))
                    <tbody>
                    @foreach($result as $pre)
                    @php 
                        if($pre['census']==$lastCensus){
                            $trstyle="style=text-align:center;background-color:#f9d1d7;";
                        } else {
                            $trstyle="style=text-align:center";
                        }
                    @endphp
                        <tr {{$trstyle}}>
                            <td>{{$pre['census']}}</td>
                            <td>{{$pre['ht']}}</td>
                            <td>{{$pre['cotno']}}</td>
                            <td>{{$pre['leafno']}}</td>
                            <td>{{$pre['status']}}</td>
                            <td>{{$pre['recruit']}}</td>
                            <td>{{$pre['sprout']}}</td>
                            <td>{{$pre['note']}}</td>

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
