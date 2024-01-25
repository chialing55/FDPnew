<div >
    <div class='spheader'>
        <h2 ><i>{!! str_replace([' var. ', ' ssp. '], [' </i>var.<i> ', ' </i>ssp.<i> '], $speciesinfo['now_simname']) !!}</i><sapn style='margin-left: 30px'>{{$speciesinfo['csp']}}</sapn></h2>
        <p>{{$speciesinfo['spcode']}}
            @if($speciesinfo['now_simname']!=$speciesinfo['spcode_simname'])
            <span style="margin-left: 20px ;">(<i>{!! str_replace([' var. ', ' ssp. '], [' <i>var.</i> ', ' <i>ssp.</i> '], $speciesinfo['spcode_simname']) !!}</i>)</span>
            @endif
        </p>
        <p style='margin-bottom: 20px'>{{$speciesinfo['apgfamily']}}<sapn style='margin-left: 30px'>{{$speciesinfo['chapgfamily']}}</sapn></p>
        <p>{{strtoupper($speciesinfo['life_form'])}}</p>

   
        <div class='spcode'>  
            
            <p>@if($speciesinfo['tree'] !=0)<span style='margin-right: 20px'><i class="fa-solid fa-tree"></i></span>@endif 
               @if($speciesinfo['seed'] !=0)<span style='margin-right: 20px'><i class="fa-solid fa-apple-whole"></i></span>@endif 
               @if($speciesinfo['seedling'] !=0)<span style='margin-right: 20px'><i class="fa-solid fa-seedling"></i></span>@endif
           </p>
        </div>
    </div> 
@if(count($desinfo)>0)
    <div class='text_box'>
        <h6>辨識要點<span style='margin-left:30px; font-size: 80%;'>種子雨收集與小苗調查用</span></h6>
        <hr>
        <div>
            <ul>
                @foreach($desinfo as $type => $typeNotes)
                    <li>
                        {{ $type }}:
                        @if(count($typeNotes) > 1)
                            <ol>
                                @foreach($typeNotes as $index => $note)
                                    <li>{{ $note }}</li>
                                @endforeach
                            </ol>
                        @else 
                            @foreach($typeNotes as $index => $note)
                                {{ $note }}
                            @endforeach
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
        
    </div>
@endif

@if(count($photoinfo)>0)
    <div class='text_box'>
        <h6>參考照片</h6>
        <hr>
        <div style='display: flex; flex-wrap: wrap; /*justify-content: space-evenly;*/'>
            @for($i=0;$i<count($photoinfo);$i++)
                <div class='photocombo photoimgbox' data-key='{{$i}}'>
                    <div class='photo'>
                        <a href='{{ asset("/webphoto/{$photoinfo[$i]['spcode']}/{$photoinfo[$i]['filename']}") }}' data-fancybox="gallery" data-caption="類型: {{$photoinfo[$i]['type']}} / {{$photoinfo[$i]['fresh']}} / {{$photoinfo[$i]['status']}}<br>photo by: {{$photoinfo[$i]['photoby']}}@if($photoinfo[$i]['des']!='')<br>{{$photoinfo[$i]['des']}}
                        @endif" >
                        <img src="{{ asset("/webphoto/{$photoinfo[$i]['spcode']}/s_{$photoinfo[$i]['filename']}") }}" width="230">
                        </a>

                    </div>
                    <div class='photodes'>
                        類型: {{$photoinfo[$i]['type']}} / {{$photoinfo[$i]['fresh']}} / {{$photoinfo[$i]['status']}}<br>
                        photo by: {{$photoinfo[$i]['photoby']}}
                        @if($photoinfo[$i]['des']!='')
                            <br>{{$photoinfo[$i]['des']}}
                        @endif
                    </div>
                </div>
            @endfor
        </div>
        
    </div>




@endif
</div>
