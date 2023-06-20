



  <div id='header' >
{{--   @if (isset($id))
    <p style='float:left; margin:110px 0 0 40px; font-size:0.9em;'>
      @php
       echo 'Hi, '.$id
      @endphp
    </p>
  @endif --}}
  <a href='/'><div id="header_text" class='fc-w'>
    {{-- <img src="{{asset('/images/header.png')}}" alt="" border="0"/> --}}
    {{-- <img src="{{asset('/images/header_tree.png')}}" alt="" border="0" height="70" /> --}}
    台灣森林動態樣區資料管理系統

  </div></a>


  <div class='fc-w flex header_bottom' style='width: 100%;justify-content: space-between;'>
  <div style='padding: 5px 30px; font-weight: 800; font-size: 20px'>{{$sitec}} > {{$project}}</div>
  <div  style='margin-left: 50px;'>

    @yield('headerList')
  </div>

  <div class='user iflex' style='padding:10px 30px; font-size: 14px; vertical-align: bottom;'>
  <span>Hi! {{$user}}</span>
  <span style='margin-left:20px' class='back'>重新選擇工作項目</span>
  </div>  
  </div>
    @yield('headerListinner')



</div>




