<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
    <div class='text_box'>     
        <h2>1.05 ha 樣區調查資料電子檔</h2>
        <hr>
        <div style='margin-top: 10px; line-height: 1.8em; display: inline-flex;'>
            <div>       
                <p>樣區編號：
                <select class="fs100 entryqx" name='qx' id='qx'style=' ' wire:model='qx' wire:change="change">
                @for ($i=-4; $i<11;$i++)     
                <option value="{{$i}}">{{$i}}</option>
                @endfor
                </select>-<select class="fs100" name='qy' id='qy' style='' wire:model='qy' wire:change="change">
                @for ($i=13; $i<20;$i++)
                <option value="{{$i}}">{{$i}} </option>
                @endfor
                </select></p>
            </div>
            <div style='margin-left: 30px;' class='dataviewerdownload'>
                <table class='tablesorter'>
                    <tbody>
                        @for($i=1;$i<2;$i++)
                        <tr>
                            <td style='font-weight: 800'>{{$censusyear[$i]}}</td>
                            <td>{!!$downloadtable[$i][0]!!}</td>
                            <td>{!!$downloadtable[$i][1]!!}</td>


                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class='text_box'>     
        <h2>查詢個別植株資料</h2>
        {{-- <p style='font-size: 80%;'>*僅能查詢前 1 次調查資料</p> --}}
        <hr>
        <div style='margin-top: 10px; line-height: 1.8em; display: inline-flex;'>
            <span style='margin-right: 20px;'>枝幹編號：</span>     
            <form wire:submit.prevent='submitStemidForm()'>
                <input name='tag' class='fs100' placeholder="tag" wire:model.defer='tag' style="width: 80px;">
                {{-- .<input name='branch' class='fs100' wire:model.defer='branch' placeholder="b" style="width: 30px;"> <span style='font-size: 80%;'>*預設 b 為 0</span> --}}
                <button type="submit" style='margin-left: 20px;'>查詢</button>
            </form>            
        </div>
        {{-- 查詢結果 --}}

        <div style='margin-top: 20px;'>
            @if($resultnote!='')

            <p class='savenote'>{{$resultnote}}</p>
           @endif
           @if($result!='')
            <div class='fstreeStemidtable'>
                <table class='tablesorter'>
                    
                    <thead >
                        <tr style="text-align: center;">
                            <th>10x</th>
                            <th>10y</th>
                            <th>5x</th>
                            <th>5y</th>
                            <th>tag</th>
                            {{-- <th>b</th> --}}
                            <th>csp</th>
                            <th>最大分支號</th>
                            
                        </tr>
                    </thead>
                   
                    <tbody>
                        <tr style="text-align: center;">
                            <td>{{$baseresult['qx']}}</td>
                            <td>{{$baseresult['qy']}}</td>
                            <td>{{$baseresult['sqx']}}</td>
                            <td>{{$baseresult['sqy']}}</td>
                            <td>{{$baseresult['tag']}}</td>
                            {{-- <td>{{$result[0]['branch']}}</td> --}}
                            <td>{{$baseresult['csp']}}</td>
                            <td>{{$result['maxb']}}</td>
                        </tr>

                    </tbody>
                   
                </table>
            </div>
            <div class='fstreeStemidtable'>
                <table id='StemidTable' class='tablesorter'>
                    <thead>
                        
                        <tr style="text-align: center;">
                            <th style='background-color: lightsalmon;'>2015</th>
                            <th>branch</th>
                            <th>status</th>
                            <th>dbh</th>
                            <th>note</th>
                            
                        </tr>
                    </thead>
                    @if(!empty($result))
                    <tbody>

                    @foreach($result['data'] as $pre)
                        <tr style="text-align: center;">
                            <td></td>
                            <td>{{$pre['branch']}}</td>
                            <td>{{$pre['status']}}</td>
                            <td>{{$pre['dbh']}}</td>
                            <td>{{$pre['note']}}</td>
                        </tr>
                    @endforeach
                    <thead>
         
                        <tr style="text-align: center;">
                            <th style='background-color: lightsalmon;'>2024</th>
                            <th>branch</th>
                            <th>status</th>
                            <th>dbh</th>
                            <th>note</th>
                            
                        </tr>
                    </thead>
                    @foreach($result['data2'] as $pre)
                        <tr style="text-align: center;">
                            <td></td>
                            <td>{{$pre['branch']}}</td>
                            <td>{{$pre['status']}}</td>
                            <td>{{$pre['dbh']}}</td>
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
