@extends('layouts/app2')

@section('title', '選擇工作項目-台灣森林動態樣區資料管理系統')

@section('css')
    <style>
        .choice-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 30px;
        }

        .choice-card {
            display: flex;
            flex-direction: row;
            padding: 20px;
            gap: 10px;
            width: 350px;
            justify-content: space-between;
            flex-wrap: wrap;
            border-radius: 5px;
            border: 1px solid #eee;
            background-color: white;
            margin: 0;
        }

        .choice-card--box2 {
            background-color: #e6e6e6;
        }

        .choice-card--box3 {
            background-color: #cdd7cb;
        }

        .choice-card--box4 {
            background-color: #e5efd2dc;
        }

        .choice-card .boxtext {
            padding: 5px;
            color: black;
            font-weight: 600;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1 1 100px;
            min-width: 0;
            overflow-wrap: anywhere;
            word-break: break-word;
            text-align: center;
        }

        .choice-card img {
            display: block;
        }

        .choice-card-image {
            width: 180px;
            height: 180px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .choice-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .choice-card-image--blank {
            background: #f7f7f7;
        }
    </style>
@endsection

@section('js')
    <script>
        const authUserName = @json($authUserName);

        document.addEventListener('DOMContentLoaded', () => {
            $('.choice').on('click', function() {
                const url = $(this).data('url');
                const newTab = Boolean($(this).data('new-tab'));
                if (!url) return;

                if (newTab) {
                    window.open(url, '_blank', 'noopener');
                    return;
                }

                location.href = url;
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

        <div class='choice-grid'>
            @php
                $fixedItems = [
                    [
                        'key' => 'webhome',
                        'label' => '研究成果平台',
                        'url' => url('/'),
                        'img' => asset('/images/research/splist.png'),
                        'style' => 'box3',
                        'new_tab' => true,
                        'new_tab' => true,
                    ],
                    [
                        'key' => 'plant.catalog',
                        'label' => '植物資料管理',
                        'url' => route('admin.plant-catalog.index'),
                        'img' => asset('/images/research/毬蘭_03.JPG'),
                        'style' => 'box3',
                        'new_tab' => false,
                    ],

                ];

                if (auth()->user()?->canAccessFilament()) {
                    $fixedItems[] = [
                        'key' => 'webplatform',
                        'label' => '網頁後端管理平台',
                        'url' => url('/cms'),
                        'img' => asset('/images/research/DSCN6021.JPG'),
                        'style' => 'box3',
                        'new_tab' => true,
                    ];
                }

                // 之後若要新增研究工作卡片，請優先改這裡：
                // 1. 在 $workUi 新增該 key 的圖片、卡片底色、顯示名稱
                // 2. 在 $workDisplayOrder 補上同一個 key，畫面就會照這裡的順序顯示
                // 固定入口和研究工作最後會合併成同一個 $cards 清單，只跑一個 @foreach。
                $workUi = [
                    'fushan.tree' => [
                        'img' => asset('/images/research/tree.png'),
                        'style' => 'box1',
                        'label' => '福山 每木',
                    ],
                    'fushan.seeds' => [
                        'img' => asset('/images/research/seed.png'),
                        'style' => 'box1',
                        'label' => '福山 種子雨',
                    ],
                    'fushan.seedling' => [
                        'img' => asset('/images/research/seedling.png'),
                        'style' => 'box1',
                        'label' => '福山 小苗',
                    ],
                    'fushan.mortality' => [
                        'img' => asset('/images/research/fs_mortality.jpg'),
                        'style' => 'box1',
                        'label' => '福山 死亡率調查',
                    ],
                    'fushan.geo-tree-survey' => [
                        'img' => asset('/images/research/DSC04347.JPG'),
                        'image_position' => 'center 33%',
                        'style' => 'box1',
                        'label' => '福山 Geo.Tree.Survey',
                    ],
                    'shoushan.tree' => [
                        'img' => asset('/images/research/monkey.png'),
                        'style' => 'box2',
                        'label' => '壽山 每木',
                    ],
                    'nanjenshan.seedling' => [
                        'img' => asset('/images/research/seedling.png'),
                        'style' => 'box4',
                        'label' => '南仁山 小苗',
                    ],
                ];

                $workDisplayOrder = [
                    'fushan.tree',
                    'fushan.seeds',
                    'fushan.seedling',
                    'fushan.mortality',
                    'fushan.geo-tree-survey',
                    'shoushan.tree',
                    'nanjenshan.seedling',
                ];

                $sortedWorkItems = collect($workItems)
                    ->sortBy(function ($w) use ($workDisplayOrder) {
                        $index = array_search($w['key'], $workDisplayOrder, true);
                        return $index === false ? 999 : $index;
                    })
                    ->values()
                    ->map(function ($w) use ($workUi) {
                        $ui = $workUi[$w['key']] ?? [
                            'img' => '',
                            'style' => 'box1',
                            'label' => $w['title'],
                        ];

                        return [
                            'key' => $w['key'],
                            'label' => $ui['label'],
                            'url' => $w['url'],
                            'img' => $ui['img'],
                            'blank_image' => $ui['blank_image'] ?? false,
                            'image_position' => $ui['image_position'] ?? 'center',
                            'style' => $ui['style'],
                            'new_tab' => false,
                        ];
                    });

                $cards = collect($fixedItems)
                    ->concat($sortedWorkItems)
                    ->values();
            @endphp

            @foreach ($cards as $card)
                <div class='choice choice-card choice-card--{{ $card['style'] }}' data-url="{{ $card['url'] }}" data-new-tab="{{ !empty($card['new_tab']) ? '1' : '0' }}">
                    @if ($card['img'])
                        <div class="choice-card-image">
                            <img src="{{ $card['img'] }}" alt="{{ $card['label'] }}"
                                style="object-position: {{ $card['image_position'] ?? 'center' }};" />
                        </div>
                    @elseif (!empty($card['blank_image']))
                        <div class="choice-card-image choice-card-image--blank" aria-hidden="true"></div>
                    @endif
                    <div class='boxtext'>{{ $card['label'] }}</div>
                </div>
            @endforeach

            {{-- 若沒有任何研究工作權限，給提示 --}}
            @if ($sortedWorkItems->count() === 0)
                <div style="padding:20px; opacity:.8;">
                    目前尚未分派任何工作權限，請洽管理者。
                </div>
            @endif

        </div>
    </div>
@endsection
