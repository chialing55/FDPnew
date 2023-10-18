@extends('layouts/app2') 

@section('title', '選擇工作項目-台灣森林動態樣區資料管理系統')


@section('header_js')

@endsection

@section('js')
<!-- js -->
<script src="{{asset('/js/choice.js')}}"></script>
@endsection


@php
echo "<script>

const user='".$user."';

</script>";

@endphp

@section('content') 

  <div class="icon">

    <img src="{{asset('/images/紅楠_葉_72_300.png')}}" alt="圖案">
  </div>

  @include('includes.header')

<div class='content'>
  <div class='header_bottom fc-w' style='padding: 10px 30px;' >
    <h2>Hi! {{$user}}，請選擇工作項目</h2>
  </div>
  <div class='flex'>
{{--       <div class='bg-g2 padding-10 iflex' style=' min-width: fit-content;  width:60%; justify-content: flex-end;' >
        <div class='box1' style="margin-right: 30px;">
          <img src="{{asset('/images/site/fushan.png')}}" />
          <div class='text'>福山 Fushan</div>
        </div>
      </div>
      <div class='bg-g2 triangle iflex' style=''>
        <div></div>
      </div>
      <div class='bg-g3 iflex padding-10 censustype iflex' style=' padding-left: 50px; width: 100%'>
        <div class='box1 choice' site='fushan' project='tree'>
          <img src="{{asset('/images/research/tree.png')}}"/>
          <div class='boxtext'>每木 Tree Census</div>
        </div>
        <div class='box1'>
          <img src="{{asset('/images/research/seed.png')}}"/>
          <div class='boxtext'>種子雨 Seed Rain</div>
        </div>
        <div class='box1 choice' site='fushan' project='seedling'>
          <img src="{{asset('/images/research/seedling.png')}}"/>
          <div class='boxtext'>小苗 Seedling Census</div>
        </div>
      </div> --}}

        <div class='box1 choice' site='fushan' project='tree'>
          <img src="{{asset('/images/research/tree.png')}}"/>
          <div class='boxtext'>福山 每木</div>
        </div>
        <div class='box1 choice' site='fushan' project='seeds'>
          <div class='boxtext'>福山 種子雨</div>
          <img src="{{asset('/images/research/seed.png')}}"/>
        </div>
        <div class='box1 choice' site='fushan' project='seedling'>
          <img src="{{asset('/images/research/seedling.png')}}"/>
          <div class='boxtext'>福山 小苗</div>
        </div>
        <div class='box2 choice' site='shoushan' project='plot'>
          <div class='boxtext'>壽山 植物監測</div>
          <img src="{{asset('/images/research/monkey.png')}}"/>
        </div>

  </div>

</div>
@endsection