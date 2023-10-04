@extends('layouts/seeds') 
@section('pagejs')
<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");
// $('.list41').addClass('now');
// $('.list4').addClass('listhover');
// $('.list4').unbind();
// $('.list4inner').unbind();
// $('.list4inner').css('display', 'inline-flex');
// $('.list41 hr').css('color', '#91A21C');

$(function() {
  $('.list4').addClass('now');
  $('.list4 hr').css('color', '#91A21C');
})




</script>

@endsection
@section('rightbox')

<h2>種子雨輸入注意事項</h2>
<div class='note'>
    <ul style='font-weight: 800;'>
    <li>輸入資料後需按<button class='datasavebutton' style='width:auto'>儲存</button>才能確實將資料儲存。</li>
    <li>可利用「Tab」鍵和「上下左右」鍵在各輸入欄位間移動。</li>
    <li>此次資料若未輸入完成，可在下次繼續輸入。</li>
    
    </ul>
<div class='flex text_outbox' style="flex-direction: column;">

    <div class='text_box_note_out'>    
    <div class='text_box text_box_note'>    
    <h2>種子雨調查資料輸入</h2>
        <ol>
            <li></li> 
        </ol>
    </div>


</div>    

    

</div>
</div>
@endsection