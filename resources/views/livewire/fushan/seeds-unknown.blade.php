<div style='display: flex; align-items: center;flex-direction: column;'>
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>
        <h2>UNKNOWN</h2>

        <div style='margin-top:20px'>
            <select name="unk" class="fs100"  wire:model='unk' wire:change="search">
                <option value="%"> - </option>
                
                @for ($i=0; $i<count($unklist);$i++)
                <option value="{{$unklist[$i]}}">{{$unklist[$i]}} </option>
                @endfor
            </select>
        </div>
    <div  style=''>


        <div  style='display: flex; flex-wrap: wrap; justify-content: center;'>
            @foreach($unkdes as $unk)
            <div class='photocombo text_box' >
                <h6>{{$unk['unkname']}} 
                    <span wire:click="openData('/fushan/seeds/showdata', '{{$unk['unkname']}}')" style="cursor: pointer; margin-left: 20px; font-size: 80%;">檢視資料</span>
                </h6>
                <hr>
                <p>{{$unk['des']}}</p>
                @foreach($unkphoto[$unk['unkname']] as $photo)
                <div style='display: inline-flex;'>
                <div class='photocombo' style=''>
                    <div class='photo'>
                        <img src="{{ asset("/webphoto/unknown/{$photo['unkname']}/s_{$photo['filename']}") }}" width="230">

                    </div>
@php 
 $codelist=[ '1' => '果', '2' => '種子', '3' => '附屬物', '4' => '碎片', '5' => '未熟果', '6' => '花', ];

@endphp                    
                    <div class='photodes'>
                        類型: {{$codelist[$photo['code']]}} <br>
                        photo by: {{$photo['photoby']}}
                        @if($photo['des']!='')
                            <br>{{$photo['des']}}
                        @endif
                    </div>
                </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>


    </div>
    
</div>
