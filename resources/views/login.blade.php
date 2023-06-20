@extends('layouts/app') 

@section('title', '登入-台灣森林動態樣區資料管理系統')


@section('header_js')



@endsection

@section('js')
<!-- js -->
<script type="text/javascript">
  $('.icon').hide();
  $("footer").hide();
</script>

@endsection


@section('content') 
<div class='index'>
  <div class='indexbox'>
    <p>台灣森林動態樣區資料管理系統</p>
    <div id ="inner">

      @if (isset($check))
              <span class='style3'>帳號或密碼輸入錯誤，請重新輸入</span>
      @else

      @endif

        <form action="/login2" method="post">
          @csrf  
          {{-- {{ csrf_field() }} --}}
          {{-- //提供表單驗證功能 (name=_token) --}}
          {{-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> --}}
          帳號 <input name="id" type="text" size=20 ><br>
          密碼 <input name="pass" type="password" size=20>
          <br><br>
          <button type="submit" value="確定">確定</button>
        </form>
    </div>
  </div>
</div>
@endsection