@extends('layouts/app2')

@section('title', '選擇工作項目-台灣森林動態樣區資料管理系統')


@section('header_js')

@endsection

@section('js')
    <!-- js -->
    <script>
        const user = '".$user."';

        $('.choice').on('click', function() {
            thissite = $(this).attr('site');
            thisproject = $(this).attr('project');
            if (thisproject == 'webhome') {
                location.href = '/';
            } else if (thisproject == 'webplatform') {
                location.href = '/web/splist';
            } else {
                location.href = `${thissite}/${thisproject}`;
            }
        })
    </script>
@endsection


@section('content')

    <div class="icon">

        <img src="{{ asset('/images/紅楠_葉_72_300.png') }}" alt="圖案">
    </div>

    @include('includes.header')

    <div class='content'>
        <div class='header_bottom fc-w' style='padding: 10px 30px;'>
            <h2>Hi! {{ $user }}，請選擇工作項目</h2>
        </div>
        <div class='flex' style='flex-wrap: wrap; justify-content: center; gap:20px; padding:30px;'>

            <div class='box3 choice' site='fushan' project='webhome'>
                <img src="{{ asset('/images/research/splist.png') }}" width=180 />
                <div class='boxtext'>研究成果平台</div>

            </div>
            <div class='box3 choice' site='web' project='webplatform'>

                <img src="{{ asset('/images/research/DSCN6021.JPG') }}" width=180 />
                <div class='boxtext'>網頁後端管理平台</div>
            </div>
            <div class='box1 choice' site='fushan' project='tree'>
                <img src="{{ asset('/images/research/tree.png') }}" />
                <div class='boxtext'>福山 每木</div>
            </div>
            <div class='box1 choice' site='fushan' project='seeds'>
                <img src="{{ asset('/images/research/seed.png') }}" />
                <div class='boxtext'>福山 種子雨</div>

            </div>
            <div class='box1 choice' site='fushan' project='seedling'>
                <img src="{{ asset('/images/research/seedling.png') }}" />
                <div class='boxtext'>福山 小苗</div>
            </div>
            <div class='box2 choice' site='shoushan' project='plot'>
                <img src="{{ asset('/images/research/monkey.png') }}" />
                <div class='boxtext'>壽山 植物監測</div>

            </div>

        </div>

    </div>
@endsection
