<!DOCTYPE html>
<html lang="tw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="language" content="zh-TW" />  
  <link rel="alternate" href="" hreflang="en" /> 

<title>{{$title}}</title>
<style>
  header, footer, .chinese {
    font-family: msjh;
  }

  html, body {
    font-family: sans-serif;
    margin: 25px 10px 0px 10px;
    position: relative;

  }

  header, footer {
    position: fixed;
  }
  header {
/*    top:10px;*/
    margin-top: -35px;
    width: 100%;
  }
  footer {
    bottom: 0px;
    font-size: 11px;
/*    line-height:14px;*/
  }
  .record_pdf {
    margin: 0px 0px 30px 0px;
    
    
    box-sizing:border-box;

/*    page-break-after: always;*/
  

  }


  .pagenum:before {
    content: counter(page);
  }


 


  table td{
    border: thin solid #000000;
    vertical-align: middle;
/*    margin-top:2px;*/
    text-align:left;
/*    line-height:5px;*/
    padding: 0px 0px 0px 3px;
    font-size:12px;
    text-overflow:ellipsis;
  }

  .table-header {
    border-bottom-width: initial;
    border-bottom-style: double;
    border-bottom-color: #000000;
    border-top-width: 1px;
    border-top-style: solid;
    border-top-color: #000000;

  }

  .table-right{
    border-right-width: 1px;
    border-right-style: solid;
    border-right-color: #000000;
  }
  .table-left{
    border-right-width: 1px;
    border-right-style: solid;
    border-right-color: #000000;
  }
  .table-yellow{
    background-color: #FFFF7D;
    border-top-width: 1px;
    border-top-style: solid;
    border-top-color: #000000;    
  }

  .table-align-right{
    text-align: right;
    padding-right: 5px;
  }



</style>

@php
$list=0;
@endphp

</head>
<body>
<header><div class='iflex'>{{$title}}<span style='margin-left:250px; font-size: 12px;'>調查者__________________________________________紀錄者____________________</span></div> 
  
  <div style='text-align: right; margin-top: -25px; margin-right:20px; font-size: 12px;'><span class="pagenum"></span><span class='page'>
    @if(isset($numPagesTotal))
   / {{$numPagesTotal}}
    @endif</span></div>
</header>

<footer style="margin-bottom: 10px;">
  <p>[樣區上方光度] U：多層樹冠；I：一層樹冠；G：孔隙    <span style='padding-left: 30px;'>[長度/葉片數] -1：因故沒有測量；-2：DBH>=1；-4：死亡；-6：消失；-7：枝幹死亡但個體存活</span><span style='padding-left: 30px;'>[狀態] A：存活；G：見環不見苗；D：死亡；N：消失</span></p>
</footer>
<div class='record_pdf' >
{{-- <?php echo count($record);?> --}}
<table width="100%"  cellpadding="6" cellspacing="0" class=' chinese'>
    <thead class='table-header'>
     
      <td class="table-left table-header" style='width:30px'>Date</td>
        <td class="table-header" style='width:35px'>T-P</td>
        <td class="table-header" style='width:50px'>Tag</td>
        <td class="table-header" style='width:75px'>種類</td>
        <td class="table-header" style='width:35px'>長度</td>
        <td class="table-header" style='width:35px'>葉片數</td>
        <td class="table-header table-left " style='width:35px'>長度</td>
        <td class="table-header" style='width:35px'>葉片數</td>
        <td class="table-header" style='width:35px'>狀態</td>
      <td class="table-header" style='width:20px'>x</td>
      <td class="table-header" style='width:20px'>y</td>
      <td class="table-header" style='width:150px'>Note</td>
        <td class=" table-header table-left" style='width:30px'>T-P</td>
        <td class="table-header" style='width:40px'>Tag</td>
        <td class="table-header" style='width:100px'>種類</td>
        <td class="table-header" style='width:35px'>長度</td>
        <td class="table-header" style='width:35px'>葉片數</td>
      <td class="table-header" style='width:20px'>x</td>
      <td class="table-header" style='width:20px'>y</td>
        <td class="table-header table-right" style='width:100px'>Note</td>
        
    </thead>
@for($k=0;$k<count($plot);$k++)
 




  @if($plot[$k]!='33-3')

  <tr>
    
    <td class="table-yellow talble-left"></td>
      <td class="table-yellow">{{$plot[$k]}}</td>
      <td class="table-yellow" colspan="3">覆蓋度</td>
      <td class="table-yellow talble-right" colspan="7">樣區上方光度
      &nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;I&nbsp;&nbsp;&nbsp;&nbsp;G   </td>

      <td class=""></td>
      <td class=""></td>
      <td class=""></td>
      <td class=""></td>
      <td class=""></td>
    <td class=""></td>
    <td class=""></td>
      <td class="talble-right"></td>
  </tr>
  @endif
  @if(isset($record[$plot[$k]]))
    @for($i=0;$i<count($record[$plot[$k]]);$i++)

    <tr>
     
      <td class="talble-left"></td>
        <td class="">{{$record[$plot[$k]][$i]['TP']}}</td>
        <td align="center" class="">{{$record[$plot[$k]][$i]['tag']}}</td>
        <td class="">
          <div>
            <span>{{$record[$plot[$k]][$i]['csp']}}</span>
            @if(isset($maxb[$record[$plot[$k]][$i]['mtag']]) && $record[$plot[$k]][$i]['sprout'] == 'FALSE')
            <span style='font-size: 70%; color: #333333;'>  ({{$maxb[$record[$plot[$k]][$i]['tag']]}})</span>
            @endif
          </div>
        </td>
        <td class="table-align-right">{{$record[$plot[$k]][$i]['ht']}}</td>
        <td class="talble-right table-align-right">
          @if($record[$plot[$k]][$i]['cotno']>0)
            {{$record[$plot[$k]][$i]['cotno']."+"}}
          @endif
           {{$record[$plot[$k]][$i]['leafno']}}</td>
        <td class="table-align-right">
          @if($record[$plot[$k]][$i]['ht']<(-1))
          {{$record[$plot[$k]][$i]['ht']}}
          @endif
        </td>
        <td class="table-align-right">@if($record[$plot[$k]][$i]['ht']<(-1))
          {{$record[$plot[$k]][$i]['ht']}}
          @endif
        </td>
        <td class="table-align-right">@if($record[$plot[$k]][$i]['status']!='A')
          {{$record[$plot[$k]][$i]['status']}}
          @endif
        </td>
      <td class="">
        @if (($record[$plot[$k]][$i]['x']!='-1' ))
        {{$record[$plot[$k]][$i]['x']}}
        @endif
      </td>
      <td class="">@if (($record[$plot[$k]][$i]['y']!='-1' ))
        {{$record[$plot[$k]][$i]['y']}}
        @endif</td>
      @if(strlen($record[$plot[$k]][$i]['note'])>35)
      <td class="talble-right" colspan="9" style='font-size: 10px'>{{$record[$plot[$k]][$i]['note']}}</td>
      @else 
        <td class="talble-right" style='font-size: 10px' >{{$record[$plot[$k]][$i]['note']}}</td>
        <td class=""></td>
        <td class=""></td>
        <td class=""></td>
        <td class=""></td>
        <td class=""></td>
      <td class=""></td>
      <td class=""></td>
        <td class="talble-right"></td>
      @endif
    </tr>
    @endfor
  @endif
@endfor


</table>

</div>

</body>
</html>