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

@php 
 $codelist=[ '1' => '果', '2' => '種子', '3' => '附屬物', '4' => '碎片', '5' => '未熟果', '6' => '花', ];

@endphp  
        <div  style='display: flex; flex-wrap: wrap; justify-content: center;'>
            @foreach($unkdes as $unk)
            <div class='photocombo text_box' >
                <h6>{{$unk['unkname']}} 
                    <span wire:click="openData('/fushan/seeds/showdata', '{{$unk['unkname']}}')" style="cursor: pointer; margin-left: 20px; font-size: 80%;">檢視資料</span>
                </h6>
                <hr>
                <div class='unkDes{{$unk['unkname']}}'>{{$unk['des']}}
                    <button name='editunkDesShow'  onclick="$('.unkDes{{$unk['unkname']}}').hide();$('.editUnkDes{{$unk['unkname']}}').show();"><i class='fa-solid fa-pen-to-square'></i></button>
                </div>
                <div class='editUnkDes{{$unk['unkname']}}' style='display: none;'>
                    <form wire:submit.prevent="submitUnkEditForm" method="POST">
                        物種描述: <input type='text' id=='editUnkDes' value='{{$unk['des']}}'>

                        <button type='submit' style='margin-left:20px'>輸入</button>

                    </form>
                </div>
                @foreach($unkphoto[$unk['unkname']] as $photo)
                <div style='display: inline-flex;'>
                <div class='photocombo' style=''>
                    <div class='photo'>
                        <a href='{{ asset("/splist/photo/unknown/{$photo['unkname']}/{$photo['filename']}") }}' data-fancybox="gallery" data-caption="{{$unk['unkname']}}<br> 類型: {{$codelist[$photo['code']]}}<br>photo by: {{$photo['photoby']}}@if($photo['des']!='')<br>{{$photo['des']}}
                        @endif" >
                        <img src="{{ asset("/splist/photo/unknown/{$photo['unkname']}/s_{$photo['filename']}") }}" width="230">
                    </a>

                    </div>
                  
                    <div class='photodes{{$photo['id']}}'>
                        類型: {{$codelist[$photo['code']]}} <br>
                        photo by: {{$photo['photoby']}}
                        @if($photo['des']!='')
                            <br>{{$photo['des']}}
                        @endif
                        <button name='editShow' onclick="$('.photodes{{$photo['id']}}').hide();$('.editDes{{$photo['id']}}').show();"><i class='fa-solid fa-pen-to-square'></i></button>
                    </div>
                    <div class='editDes{{$photo['id']}}' style='display: none;'>
                        <form wire:submit.prevent="submitEditForm" method="POST">

                            類型: 
                            <select id="editType">
                                @foreach($codelist as $key => $value)
                                    <option value="{{ $key }}" {{ $photo['code'] == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select><br>
                            photo by: <input type='text' id=='editPhotoBy' value='{{$photo['photoby']}}'><br>
                            相片描述: <input type='text' id=='editPhotoDes' value='{{$photo['des']}}'>
                            <input type='hidden' id=='editPhotoId'><br>

                            <button type='submit'>輸入</button>

                        </form>
                        
                    </div>
                </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>


    </div>
    
</div>
