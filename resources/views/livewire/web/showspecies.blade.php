<div >
    <div class='spheader'>
        <div>
            <h2 ><i>{!! str_replace([' var. ', ' ssp. '], [' </i>var.<i> ', ' </i>ssp.<i> '], $speciesinfo['now_simname']) !!}</i><sapn style='margin-left: 30px'>{{$speciesinfo['csp']}}</sapn></h2>
            <p>{{$speciesinfo['spcode']}}
                @if($speciesinfo['now_simname']!=$speciesinfo['spcode_simname'])
                <span style="margin-left: 20px ;">(<i>{!! str_replace([' var. ', ' ssp. '], [' <i>var.</i> ', ' <i>ssp.</i> '], $speciesinfo['spcode_simname']) !!}</i>)</span>
                @endif
            </p>
            <p style='margin-bottom: 20px'>{{$speciesinfo['apgfamily']}}<sapn style='margin-left: 30px'>{{$speciesinfo['chapgfamily']}}</sapn></p>
            <p>{{strtoupper($speciesinfo['life_form'])}}</p>
        </div>
   

        <div style='display: inline-flex;'>
            <p>@if($speciesinfo['tree'] !=0)<span style='margin-right: 20px'><i class="fa-solid fa-tree"></i></span>@endif 
               @if($speciesinfo['seed'] !=0)<span style='margin-right: 20px'><i class="fa-solid fa-apple-whole"></i></span>@endif 
               @if($speciesinfo['seedling'] !=0)<span style='margin-right: 20px'><i class="fa-solid fa-seedling"></i></span>@endif
           </p>
           @if($leafphoto=='yes')
            <p class='text_box' style='padding: 0px;'><a href='{{ asset("/splist/leafphoto/{$speciesinfo['csp']}.jpg") }}'>
                <img src="{{ asset("/splist/leafphoto/{$speciesinfo['csp']}.jpg") }}" width="230"></a>
            </p>
            @endif
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
                        <a href='{{ asset("/splist/photo/{$photoinfo[$i]['spcode']}/{$photoinfo[$i]['filename']}") }}' data-fancybox="gallery" data-caption="類型: {{$photoinfo[$i]['type']}} / {{$photoinfo[$i]['fresh']}} / {{$photoinfo[$i]['status']}}<br>photo by: {{$photoinfo[$i]['photoby']}}@if($photoinfo[$i]['des']!='')<br>{{$photoinfo[$i]['des']}}
                        @endif" >
                        <img src="{{ asset("/splist/photo/{$photoinfo[$i]['spcode']}/s_{$photoinfo[$i]['filename']}") }}" width="250">
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

<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>

<div style='display: flex; align-items: flex-start;'>
@if($countInd>0)


<div class='text_box' style='margin-right: 20px;'>
    <p>
    共標定 {{$countInd}} 棵樹 以及 {{$countB}} 個分支。
    最大樹的胸徑為 {{$maxDBH}} cm。<br>
    </p>

    {{-- {{print_r($censusA)}} --}}
    <div class='figouter' >
    <div style='margin: 10px;'>
        <h6 wire:click="fig1data()" class='fig1creat' style="" ><i class="fa-solid fa-caret-right"></i> 各次調查植株數量</h6>
        <h6 class="fig1show figshow" style="" onclick="$('.fig1').toggle();$('.fig1ArrayDown').toggle();$('.fig1Arrayright').toggle();"> <i class="fa-solid fa-caret-down fig1ArrayDown" ></i><i class="fa-solid fa-caret-right fig1Arrayright" style='display: none;'></i> 各次調查植株數量圖</h6>

        <div class="fig fig1" style="width: 500px;">
            <canvas id="myChartFig1"></canvas>
        </div>
    </div>
    <div style='margin: 10px;'>
        <h6 wire:click="fig2data()" class='fig2creat' style="" ><i class="fa-solid fa-caret-right"></i> 第四次調查徑級結構</h6>
        <h6 class="fig2show figshow" style="" onclick="$('.fig2').toggle();$('.fig2ArrayDown').toggle();$('.fig2Arrayright').toggle();"> <i class="fa-solid fa-caret-down fig2ArrayDown" ></i><i class="fa-solid fa-caret-right fig2Arrayright" style='display: none;'></i> 第四次調查徑級結構</h6>
        <div class="fig fig2" style="width: 500px;">
            <canvas id="myChartFig2"></canvas>

        </div>        
    </div>

    <div style='margin: 10px;'>
        <h6 wire:click="fig3data()" class='fig3creat' style="" ><i class="fa-solid fa-caret-right"></i> 第四次調查植株位置分布</h6>
        <h6 class="fig3show figshow" style="" onclick="$('.fig3').toggle();$('.fig3ArrayDown').toggle();$('.fig3Arrayright').toggle();"> <i class="fa-solid fa-caret-down fig3ArrayDown" ></i><i class="fa-solid fa-caret-right fig3Arrayright" style='display: none;'></i> 第四次調查植株位置分布</h6>
        <div class="fig fig3" style="width: 500px;">
            <canvas id="myChartFig3" ></canvas>

        </div>
    </div>

    </div>
</div>
@endif
@if($countSeeds>0 || $countFlower>0 || $countSeedlings>0)
<div class='text_box'>
    <p>
    共收集到 
    @if($countFlower>0)
         {{$countFlower}} 筆落花。
    @endif
    @if($countSeeds>0)
        {{$countSeeds}} 顆種子。
    @endif
    @if($countSeedlings>0)
        記錄到 {{$countSeedlings}} 筆小苗。
    @endif
    </p>

    <div class='figouter' >
@if($countFlower>0)
    <div style='margin: 10px;'>
        <h6 wire:click="fig4data()" class='fig4creat' style="" ><i class="fa-solid fa-caret-right"></i> 開花量時間變化</h6>
        <h6 class="fig4show figshow" style="" onclick="$('.fig4').toggle();$('.fig4ArrayDown').toggle();$('.fig4Arrayright').toggle();"> <i class="fa-solid fa-caret-down fig4ArrayDown" ></i><i class="fa-solid fa-caret-right fig4Arrayright" style='display: none;'></i> 開花量時間變化</h6>

        <div class="fig fig4" style="width: 500px;">
            <canvas id="myChartFig4"></canvas>
        </div>
    </div>
@endif
@if($countSeeds>0)
    <div style='margin: 10px;'>
        <h6 wire:click="fig5data()" class='fig5creat' style="" ><i class="fa-solid fa-caret-right"></i> 結果量時間變化</h6>
        <h6 class="fig5show figshow" style="" onclick="$('.fig5').toggle();$('.fig5ArrayDown').toggle();$('.fig5Arrayright').toggle();"> <i class="fa-solid fa-caret-down fig5ArrayDown" ></i><i class="fa-solid fa-caret-right fig5Arrayright" style='display: none;'></i> 結果量時間變化</h6>
        <div class="fig fig5" style="width: 500px;">
            <canvas id="myChartFig5"></canvas>

        </div>        
    </div>
@endif
@if($countSeedlings>0)
    <div style='margin: 10px;'>
        <h6 wire:click="fig6data()" class='fig6creat' style="" ><i class="fa-solid fa-caret-right"></i> 小苗數量時間變化</h6>
        <h6 class="fig6show figshow" style="" onclick="$('.fig6').toggle();$('.fig6ArrayDown').toggle();$('.fig6Arrayright').toggle();"> <i class="fa-solid fa-caret-down fig6ArrayDown" ></i><i class="fa-solid fa-caret-right fig6Arrayright" style='display: none;'></i> 小苗數量時間變化</h6>
        <div class="fig fig6" style="width: 500px;">
            <canvas id="myChartFig6" ></canvas>

        </div>
    </div>
@endif
    </div>

</div>
@endif
</div>
</div>
