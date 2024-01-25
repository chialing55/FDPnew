<div>
    <div class='text_box' style='margin: 0 auto;'>
        <h2>歷年種子資料檢視<span style="margin-left: 20px ; font-weight: 500; font-size: 70%;"> - : 所有資料</span></h2>
        <hr>
        <table id='sptable' class='tablesorter'>
            <thead>
                <tr>
                    <th>year</th>
                    <th>month</th>
                    <th>trap</th>
                    <th>csp</th>
                    <th>收到的類型</th>
                    
                </tr>
                <tr>
                    <td>
                        <select name="year" class="fs100"  wire:model='year' wire:change="search($event.target.value, '{{$month}}', '{{$trap}}', '{{$species}}', '{{$code}}')">
                            <option value="all"> - </option>
                            <option value="each"> 分年 </option>
                            @for ($i=2002; $i<=date('Y');$i++)
                            <option value="{{$i}}">{{$i}} </option>
                            @endfor
                        </select>
                    </td>
                    <td>
                        <select name="year" class="fs100"  wire:model='month' wire:change="search('{{$year}}',$event.target.value,  '{{$trap}}', '{{$species}}', '{{$code}}')">
                            <option value="all"> - </option>
                            <option value="each"> 分月 </option>
                            @for ($i=1; $i<=12;$i++)
                            <option value="{{$i}}">{{$i}} </option>
                            @endfor
                        </select>
                    </td>
                    <td>
                        <select name="year" class="fs100"  wire:model='trap' wire:change="search('{{$year}}',  '{{$month}}',$event.target.value, '{{$species}}', '{{$code}}')">
                            <option value="all"> - </option>
                            <option value="each" @if($trap=='each') selected=select  @endif> 分網 </option>
                            @for ($i=1; $i<count($traps);$i++)
                            <option value="{{$i}}">{{$i}} </option>
                            @endfor
                        </select>
                    </td>
                    <td>
                        <input type="text" class="fs100" style='width: 100px;' wire:model='species' wire:change="search('{{$year}}', '{{$month}}', '{{$trap}}', $event.target.value, '{{$code}}')">
                    </td>
                    <td>
                        <select name="year" class="fs100"  wire:model='code' wire:change="search('{{$year}}', '{{$month}}','{{$trap}}', '{{$species}}',$event.target.value)">
                            <option value="all"> - </option>
@php 
 $codelist=[ '1' => '果', '2' => '種子', '3' => '附屬物', '4' => '碎片', '5' => '未熟果', '6' => '花', ];

@endphp
                            @for ($i=1; $i<7;$i++)
                            <option value="{{$i}}">{{$codelist[$i]}} </option>
                            @endfor
                        </select>
                    </td>       


                </tr>
            </thead>

            <tbody>
                @foreach($data as $list)
                @if($list['identified']=='Y')
                    <tr onclick="window.open('/web/species/{{$list['sp']}}', '_blank')" style="cursor: pointer">
                @else
                    <tr wire:click="openUnknown('/fushan/seeds/unknown', '{{$list['sp']}}')" style="cursor: pointer">
                @endif
                    <td>{{$list['year']}}</td>
                    <td>{{$list['month']}}</td>
                    <td>{{$list['trap']}}</td>
                    <td>{{$list['csp']}}</td>
@php
    sort($list['codecomb']);
    $list['codecombtotal']='';
    foreach ($list['codecomb'] as $value){
        if (isset($codelist[$value])){
            $value=$codelist[$value];
        } else {$value='';}
        $list['codecombtotal']=$list['codecombtotal']." ".$value;
    }
@endphp

                    <td>{{$list['codecombtotal']}}</td>
                </tr>
                
                @endforeach
            </tbody>


        </table>
    </div>
    
</div>
