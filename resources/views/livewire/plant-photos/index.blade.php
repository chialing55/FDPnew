<div>
    <div class="loading-container" wire:loading.class="visible">
        <div class="loading-spinner"></div>
    </div>

    <style>
        .plant-photo-count-column {
            box-sizing: border-box;
            width: 110px !important;
            min-width: 110px !important;
            max-width: 110px !important;
            text-align: center;
        }

        #plantPhotoTable th.plant-photo-count-column,
        #plantPhotoTable td.plant-photo-count-column {
            padding-left: 6px;
            padding-right: 6px;
            text-align: center;
            vertical-align: middle;
        }
    </style>

    <div id="sptable">
        <p class=''>*點選名單列即可進入編修模式</p>
        <table id="plantPhotoTable" class="tablesorter">
            <thead>
                <tr>
                    <th>科名</th>
                    <th>學名</th>
                    <th>中文名</th>
                    <th class="plant-photo-count-column">照片張數</th>
                </tr>
            </thead>
            <tbody>
                @foreach($splist as $sp)
                    <tr @if(!empty($sp['spcode'])) onclick="window.location='{{ route('admin.plant-photos.edit', ['spcode' => $sp['spcode']]) }}'" style="cursor: pointer" @endif>
                        <td>{{ $sp['apgfamily'] }} {{ $sp['chapgfamily'] }}</td>
                        <td>{{ $sp['now_simname'] }}</td>
                        <td>{{ $sp['csp'] }}</td>
                        <td class="plant-photo-count-column" data-value="{{ $sp['photo_count'] }}">{{ $sp['photo_count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
