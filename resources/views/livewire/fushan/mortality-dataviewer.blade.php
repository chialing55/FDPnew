<div class='flex text_outbox' style='flex-direction: column; align-items: center;'>
    <div class='text_box'>
        <div class="loading-container" wire:loading.class="visible">
            <div class="loading-spinner"></div>
        </div>

        <h2>查詢個別植株資料</h2>

        <hr>
        <div style='margin-top: 10px; line-height: 1.8em; display: inline-flex;'>
            <span style='margin-right: 20px;'>stemid：</span>
            <form wire:submit.prevent='submitTagForm()'>
                <input name='tag' class='fs100' placeholder="stemid" wire:model.defer='tag' style="width: 120px;">
                <button type="submit" style='margin-left: 20px;'>查詢</button>
            </form>
        </div>

        <div style='margin-top: 20px;'>
            @if($resultnote!='')
                <p class='savenote'>{{$resultnote}}</p>
            @elseif(!empty($basedata))
                <p style='font-size: 80%;'>* [census {{$lastCensus}}] 為最新調查資料，若尚未匯入大表則顯示暫存資料。</p>
                <div class='fsSeedlingTagtable'>
                    <table class='tablesorter'>
                        <thead>
                            <tr style="text-align: center;">
                                <th style="text-align: center;">stemid</th>
                                <th style="text-align: center;">種類</th>
                                <th style="text-align: center;">map</th>
                                <th style="text-align: center;">qx</th>
                                <th style="text-align: center;">qy</th>
                                <th style="text-align: center;">subqx</th>
                                <th style="text-align: center;">subqy</th>
                                <th style="text-align: center;">qudx</th>
                                <th style="text-align: center;">qudy</th>
                                <th style="text-align: center;">active</th>
                                <th style="text-align: center;">note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="text-align: center;">
                                <td>{{$basedata['stemid']}}</td>
                                <td>{{$basedata['spcode']}}</td>
                                <td>{{$basedata['map']}}</td>
                                <td>{{$basedata['qx']}}</td>
                                <td>{{$basedata['qy']}}</td>
                                <td>{{$basedata['subqx']}}</td>
                                <td>{{$basedata['subqy']}}</td>
                                <td>{{$basedata['qudx']}}</td>
                                <td>{{$basedata['qudy']}}</td>
                                <td>{{$basedata['is_active']}}</td>
                                <td>{{$basedata['note']}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class='fsSeedlingTagtable'>
                    <table id='progressTable{{$tableTag}}' class='tablesorter'>
                        <thead>
                            <tr style="text-align: center;">
                                <th width='60px' style="text-align: center;">census</th>
                                <th width='90px' style="text-align: center;">date</th>
                                <th width='60px' style="text-align: center;">DBH</th>
                                <th width='60px' style="text-align: center;">Status</th>
                                <th width='60px' style="text-align: center;">Mode</th>
                                <th width='80px' style="text-align: center;">Living length</th>
                                <th width='60px' style="text-align: center;">Branches</th>
                                <th width='80px' style="text-align: center;">Illumination</th>
                                <th width='60px' style="text-align: center;">Leaning</th>
                                <th width='60px' style="text-align: center;">Liana</th>
                                <th width='60px' style="text-align: center;">Fungi</th>
                                <th width='80px' style="text-align: center;">Wounded stem</th>
                                <th width='70px' style="text-align: center;">Deformity</th>
                                <th width='60px' style="text-align: center;">Rotten</th>
                                <th width='60px' style="text-align: center;">Leaves</th>
                                <th width='80px' style="text-align: center;">Leaf damage</th>
                                <th style="text-align: center;">comments</th>
                                <th style="text-align: center;">corrections</th>
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
                                        <td>{{$pre['date']}}</td>
                                        <td>{{$pre['dbh']}}</td>
                                        <td>{{$pre['status']}}</td>
                                        <td>{{$pre['mode']}}</td>
                                        <td>{{$pre['living_length']}}</td>
                                        <td>{{$pre['branches']}}</td>
                                        <td>{{$pre['illumination']}}</td>
                                        <td>{{$pre['leaning']}}</td>
                                        <td>{{$pre['liana']}}</td>
                                        <td>{{$pre['fungi']}}</td>
                                        <td>{{$pre['wounded_stem']}}</td>
                                        <td>{{$pre['deformity']}}</td>
                                        <td>{{$pre['rotten']}}</td>
                                        <td>{{$pre['leaves']}}</td>
                                        <td>{{$pre['leaf_damage']}}</td>
                                        <td>{{$pre['comments']}}</td>
                                        <td>{{$pre['corrections']}}</td>
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
