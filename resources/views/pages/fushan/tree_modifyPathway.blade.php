@extends('layouts/tree') 
@section('pagejs')

<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");

$(function() {
  $('.list6').addClass('now');
  $('.list6 hr').css('color', '#91A21C');
})

</script>

@php 

echo "<script>

const user = '".$user."';

</script>";

@endphp
@endsection
@section('rightbox')

<div>
<h2>每木調查資料處理流程</h2>
    <div class='text_box'>

      <ol>
        <li style="font-weight: 800;">輸入完成，檢查地圖。</li>
        <ul>
          <li>至輸入系統輸入資料兩次，同線資料兩次輸入皆完成後即可進行比對。</li>
          <li>該線資料比對完成後，檢查地圖並整理地圖資料，若發現錯誤可在輸入介面進行更正(兩次輸入皆需修改)，並重新按輸入完成鈕及重新比對，以確認輸入正確。</li>
          <li>地圖資料整理完成後，即可進行掃描備份，並將地圖部分切割出來，提供點圖程式使用。
            <ul>
              <li><a href='https://docs.google.com/document/d/1z1voCrgPnnkPb9qvoaPk6taaYdtHiriH/edit?usp=sharing&ouid=104328473015955473420&rtpof=true&sd=true' target="_blank">福山每木調查資料掃描辦法</a></li>
              <li><a href='https://drive.google.com/drive/folders/15fqlHxMPIlY5tXP1TpHVmPDvMTQZqI_f' target="_blank">第5次調查掃描資料</a></li>
            </ul>
          </li>
          <li>皆確認無誤後，由管理者匯入大表 (已當次調查命名，如 census5)。</li>
        </ul>
        <li style="font-weight: 800;">資料匯入大表，進行特殊修改。</li>
        <ul>
          <li>資料匯入大表後，即在輸入程式中禁止該線繼續輸入，如需更新資料，需透過後端資料更新介面。目前暫訂只由管理者處理。新增資料則可透過<a href={{asset('/fushan/tree/addData')}}>新增資料</a>頁面。
            <ul>
            <li>可能會發現漏新增植株/枝幹的情形：
   
                <ul>
                  <li>抽查時發現漏新增的植株/枝幹。</li>
                  <li>點圖時發現漏新增的植株/枝幹。</li>
                </ul>
            </li></ul>
          </li>          
          <li>由管理者透過後端資料修改介面，將需特殊修改的部分一一更正。
              <ul>
                <li>每一筆資料修改皆會記錄於 fixlog 資料表</li>
              </ul>
          </li>
        </ul>
        <li style="font-weight: 800;">點圖</li>
        <ul>
          <li>特殊修改完成後，即可進行點圖。
              <ul>
                <li>如有需更正的資料，請通知管理員。如需新增資料，可自行新增。</li>
              </ul>
          </li>
        </ul>
      </ol>
    </div>

</div>
@endsection
