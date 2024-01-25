

<div>
    <h2>樣區植物名錄</h2>
    <div id='sptable'>
        <table id='spTable' class='tablesorter'>
            <thead>
                <tr>
                    <th>科名</th>
                    <th>學名</th>
                    <th>中文名</th>
                    <th>每木</th>
                    <th>種子</th>
                    <th>小苗</th>
                </tr>

            </thead>
            <tbody>
                @foreach($splist as $sp)

                {{-- <tr @if($sp['has_photo'] == '1') onclick="window.location='/web/species/{{$sp['spcode']}}'" style="cursor: pointer" @endif> --}}
                    <tr onclick="window.location='/web/species/{{$sp['spcode']}}'" style="cursor: pointer">
                    <td>{{$sp['apgfamily']}}  {{$sp['chapgfamily']}}</td>
                    <td>{{$sp['now_simname']}}</td>
                    <td>{{$sp['csp']}}</td>
                    <td data-value="{{$sp['tree']}}">@if($sp['tree'] !=0) <i class="fa-solid fa-tree"></i> @endif</td>
                    <td data-value="{{$sp['seed']}}">@if($sp['seed'] !=0) <i class="fa-solid fa-apple-whole"></i> @endif</td>
                    <td data-value="{{$sp['seedling']}}">@if($sp['seedling'] !=0) <i class="fa-solid fa-seedling"></i> @endif</td>
                </tr>
                
                @endforeach
            </tbody>
        </table>
        
    </div>
</div>
