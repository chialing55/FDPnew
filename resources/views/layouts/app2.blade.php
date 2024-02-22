@extends('layouts/app') 
@section('footer')
	<footer>
      <div id="header_text" class='fc-w flex' style='font-size: 14px;' >

        <p>如有任何問題，請洽 @kris1014</a></p>
        @if(session('latest_update'))
    <p style='margin-left: 100px;'>更新日期：{{ session('latest_update') }}</p>
        @endif

        

      </div>

	</footer>
@endsection