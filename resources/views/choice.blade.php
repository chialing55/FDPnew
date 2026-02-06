@extends('layouts/app2')

@section('title', '選擇工作項目-台灣森林動態樣區資料管理系統')

@section('js')
    <script>
        const authUserName = @json($authUserName);

        document.addEventListener('DOMContentLoaded', () => {
            $('.choice').on('click', function() {
                const url = $(this).data('url');
                if (url) location.href = url;
            });
        });
    </script>
@endsection

@section('content')

    <div class="icon">
        <img src="{{ asset('/images/紅楠_葉_72_300.png') }}" alt="圖案">
    </div>

    @include('includes.header')

    <div class='content'>
        <div class='header_bottom fc-w' style='padding: 10px 30px;'>
            <h2>Hi! {{ $authUserName }}，請選擇工作項目</h2>
        </div>

        <div class='flex' style='flex-wrap: wrap; justify-content: center; gap:20px; padding:30px;'>
            @php
                $fixedItems = [
                    [
                        'key' => 'webhome',
                        'title' => '研究成果平台',
                        'url' => url('/'),
                        'img' => asset('/images/research/splist.png'),
                        'class' => 'box3',
                    ],
                ];

                if (auth()->user()?->is_admin) {
                    $fixedItems[] = [
                        'key' => 'webplatform',
                        'title' => '網頁後端管理平台',
                        'url' => url('/cms/login'),
                        'img' => asset('/images/research/DSCN6021.JPG'),
                        'class' => 'box3',
                    ];
                }
            @endphp
            {{-- 固定入口 --}}
            @foreach ($fixedItems as $it)
                <div class='{{ $it['class'] }} choice' data-url="{{ $it['url'] }}">
                    <img src="{{ $it['img'] }}" width="180" />
                    <div class='boxtext'>{{ $it['title'] }}</div>
                </div>
            @endforeach

            {{-- 研究工作（依權限顯示） --}}
            @php
                // 這裡先用 key 對應圖片與樣式（先過渡，之後也可搬進 config/work.php）
                $workUi = [
                    'fushan.tree' => [
                        'img' => asset('/images/research/tree.png'),
                        'class' => 'box1',
                        'label' => '福山 每木',
                    ],
                    'fushan.seeds' => [
                        'img' => asset('/images/research/seed.png'),
                        'class' => 'box1',
                        'label' => '福山 種子雨',
                    ],
                    'fushan.seedling' => [
                        'img' => asset('/images/research/seedling.png'),
                        'class' => 'box1',
                        'label' => '福山 小苗',
                    ],
                    'shoushan.tree' => [
                        'img' => asset('/images/research/monkey.png'),
                        'class' => 'box2',
                        'label' => '壽山 每木',
                    ],
                ];
            @endphp

            @foreach ($workItems as $w)
                @php
                    $ui = $workUi[$w['key']] ?? ['img' => '', 'class' => 'box1', 'label' => $w['title']];
                @endphp

                <div class='{{ $ui['class'] }} choice' data-url="{{ $w['url'] }}">
                    @if ($ui['img'])
                        <img src="{{ $ui['img'] }}" />
                    @endif
                    <div class='boxtext'>{{ $ui['label'] }}</div>
                </div>
            @endforeach

            {{-- 若沒有任何研究工作權限，給提示 --}}
            @if (count($workItems) === 0)
                <div style="padding:20px; opacity:.8;">
                    目前尚未分派任何工作權限，請洽管理者。
                </div>
            @endif

        </div>
    </div>
@endsection
