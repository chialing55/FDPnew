

<div>
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
    <style>
        .splist-research-column {
            box-sizing: border-box;
            width: 72px !important;
            min-width: 72px !important;
            max-width: 72px !important;
            text-align: center;
        }

        #spTable th.splist-research-column,
        #spTable td.splist-research-column {
            padding-left: 6px;
            padding-right: 6px;
            text-align: center;
            vertical-align: middle;
        }

        #spTable input[data-column="3"],
        #spTable input[data-column="4"],
        #spTable input[data-column="5"],
        #spTable input[data-column="6"] {
            box-sizing: border-box;
            width: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
        }

        .splist-tree-icon {
            display: block;
            width: 18px;
            height: 18px;
            margin: 0 auto;
            object-fit: contain;
        }

        .splist-fallen-tree-icon {
            display: block;
            width: 24px;
            height: 24px;
            margin: 0 auto;
            object-fit: contain;
        }
    </style>
    <h2>福山樣區植物名錄</h2>
    <div id='sptable'>
        <table id='spTable' class='tablesorter'>
            <thead>
                <tr>
                    <th>科名</th>
                    <th>學名</th>
                    <th>中文名</th>
                    <th class="splist-research-column">每木</th>
                    <th class="splist-research-column">種子</th>
                    <th class="splist-research-column">小苗</th>
                    <th class="splist-research-column">死亡率</th>
                </tr>

            </thead>
            <tbody>
                @foreach($splist as $sp)

                    <tr @if(!empty($sp['spcode'])) onclick="window.location='/web/species/{{$sp['spcode']}}'" style="cursor: pointer" @endif>
                    <td>{{$sp['apgfamily']}}  {{$sp['chapgfamily']}}</td>
                    <td>{{$sp['now_simname']}}</td>
                    <td>{{$sp['csp']}}</td>
                    <td class="splist-research-column" data-value="{{$sp['researches']['tree'] ?? 0}}">@if(($sp['researches']['tree'] ?? 0) !=0) <img class="splist-tree-icon" src="{{ asset('images/icon/tree.png') }}" alt="tree"> @endif</td>
                    <td class="splist-research-column" data-value="{{$sp['researches']['seed'] ?? 0}}">@if(($sp['researches']['seed'] ?? 0) !=0) <i class="fa-solid fa-apple-whole"></i> @endif</td>
                    <td class="splist-research-column" data-value="{{$sp['researches']['seedling'] ?? 0}}">@if(($sp['researches']['seedling'] ?? 0) !=0) <i class="fa-solid fa-seedling"></i> @endif</td>
                    <td class="splist-research-column" data-value="{{$sp['researches']['mortality'] ?? 0}}">@if(($sp['researches']['mortality'] ?? 0) !=0) <img class="splist-fallen-tree-icon" src="{{ asset('images/icon/fallen-tree.png') }}" alt="fallen tree"> @endif</td>
                </tr>
                
                @endforeach
            </tbody>
        </table>
        
    </div>
</div>
