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
    font-size:12px;

  }

  header, footer {
    position: fixed;
  }
  header {
/*    top:10px;*/
    margin-top: -35px;
    width: 1000px;
/*    justify-content: space-between;*/
    display:flex;
    line-height:12px;
  }
  footer {
    margin-top:10px;
    bottom: 0px;
    font-size: 12px;
  }
  .record_pdf {
    margin: 0px 0px 30px;
    padding:10px 0px;
/*    font-size:10px;*/
    box-sizing:border-box;

/*    page-break-after: always;*/
  

  }


  .pagenum:before {
    content: counter(page);
  }

thead td{
  font-size: 10px;
  padding: 2px 0px 2px 5px;
}

  table td{
/*    border: thin solid #000000;*/
    vertical-align: middle;
/*    margin-top:2px;*/
    text-align:left;
/*    line-height:5px;*/
    
/*    font-size: 13px;*/
    text-overflow:ellipsis;
  }

  .tr td{
    height: 24px;
    font-size: 13px;
    padding: 0px 5px 0px 5px;
  }
  .td-underline{
    border-bottom: 1px solid #000000;

  }
  .tr_title td{
    height: 36px;
    font-size: 18px;
    padding: 0px 5px 0px 5px;
  }

.td1 {
font-size:0.8em;
border-bottom:1px solid #dedede;
border-left:1px solid #dedede;
text-align:right ;
}
.td2{
border-bottom:1px solid #dedede;
}
.td3{
border-bottom:1px solid #dedede;
color:#b3b3b3;
}
.td4{
font-size:0.8em;
border-bottom:1px solid #dedede;
}

.td6{
  border-bottom:1px solid black;
}

.td5{
  font-size:0.8em;
  border-bottom:1px solid black;
  border-left:1px solid #dedede;
  text-align:right ;

}

.td7 {
  border-bottom:1px solid black;
  width:80px;
  display: inline-block;
  padding-left:10px;
  margin-left:5px;
  color:#b3b3b3;
}

.td8 {
  display: inline-block;
}

</style>

@php
$list=0;
@endphp

</head>
<body>
<header>
  <div style='margin-right: 60px; display: inline-flex;' >{{$title}}</div> 
  <div style='display: inline-flex; width:480px'>資料輸入1_____________________輸入日期1_____________________<br>
  資料輸入2_____________________輸入日期2_____________________</div>
  <div style='text-align: right; margin-right:20px; font-size: 12px; display: inline-flex;'><span class="pagenum"></span><span class='page'>
    @if(isset($totalpage))
   / {{$totalpage}}
    @endif</span></div>
</header>


<footer style="display: flex; ">
  <div style="display: inline-flex; width: 650px;">調查者__________________________________________記錄者_____________________檢查者_____________________</div>
  <div style="display: inline-flex; font-size: 2em; text-align:right">({{$plot}})</div>
</footer>
<div class='record_pdf'>
{{-- <?php echo count($record);?> --}}
<table width="100%"  border='0' cellpadding="4" cellspacing="0" class='chinese' valign='top'>
  <tr>
    <td style='vertical-align:text-top'><div style="font-size: 2em; margin: -10px 0 10px 0">({{$plot}})</div><div style="font-size: 1.2em"><div class='td8' >GPS</div><div class='td7' style='width:130px; color:black;'>{{$plotinfo['gps_x']}}, {{$plotinfo['gps_y']}}</div></div><div><div class='td8'>海拔</div> <div class="td7" style='color:black;'>{{$plotinfo['altitude']}} m</div></div></td>
    <td style='vertical-align:text-top'>
      
      <div><div class='td8'>坡向</div> <div class="td7">{{$plotinfo['aspect']}}</div></div>
      <div><div class='td8'>坡度</div> <div class="td7">{{$plotinfo['slope']}}</div></div>
      <div><div class='td8'>地形</div> <div class="td7">{{$plotinfo['terrain']}}</div></div>
    </td>
    <td style='vertical-align:text-top'>
      <div><div class='td8'>岩石地比例  </div> <div class="td7">{{$plotinfo['rocky']}}</div></div>
      <div><div class='td8'>地表裸露度  </div> <div class="td7">{{$plotinfo['surface']}}</div></div>
      <div><div class='td8'>凋落物覆蓋度</div> <div class="td7">{{$plotinfo['cover']}}</div></div>
      <div><div class='td8'>倒木覆蓋度  </div> <div class="td7"></div></div>
      

    </td> 
    <td style='vertical-align:text-top'>
      <div><div class='td8'>T1</div> <div class="td7">{{$plotinfo['T1']}}</div></div>
      <div><div class='td8'>T2</div> <div class="td7">{{$plotinfo['T2']}}</div></div>
      <div><div class='td8'>S </div> <div class="td7">{{$plotinfo['S']}}</div></div>
      <div><div class='td8'>H </div> <div class="td7">{{$plotinfo['H']}}</div></div>
    </td>   
  </tr>
</table>


<br>

<table width="100%"  border='0' cellpadding="4" cellspacing="0" class='chinese' >
<thead><tr  bgcolor="#c9c9c9">
  <td width="10%">tag</td>
   <td width="4%">b</td>
   <td width="15%">csp</td>
   <td width="4%"></td>
   <td width="9%">dbh15</td>

   <td width="7%">status</td>
   <td width="6%">code</td>
   <td width="7%">dbh</td>
   <td width="7%">ill</td>
   <td width="7%">leave</td>
   
   <td>note</td>
   <td width="5%">狀況</td>
   
   
  </tr></thead>

@php
  $plotlist=array("11","12","22","21");
  // $plotlist=array("11","12","22","21");
@endphp
@for($i=0;$i<count($plotlist);$i++)
@php $x=$plotlist[$i][0];  $y=$plotlist[$i][1]@endphp
 <tr bgcolor="#dedede" valign="Bottom" class='tr_title'>
 
  <td width="10%">({{$x}} , {{$y}})</td>
   <td width="4%" ></td>
   <td width="15%" class="td6">20</td>
   <td width="4%" >年</td>
   <td class="td6"></td>
  
   <td  width="6%" >月</td>
   <td width="7%" class="td6"></td>
   <td > 日</td>
   <td></td>
   <td></td>
   
   
   <td align="right" style="font-size:12px; text-align: right;" width="30%">
    @if(isset($datasqx[$x][$y]))
      共 {{count($datasqx[$x][$y])}} 筆 / {{count($datatagsqx[$x][$y])}} 棵樹
    @else
      共 0 筆 / 0 棵樹
    @endif
  </td>

   
   <td width="5%"></td>

  </tr>
  @if(isset($datasqx[$x][$y]))
  @for($q=0;$q<count($datasqx[$x][$y]);$q++)
  @php $data=$datasqx[$x][$y][$q]@endphp
<tr class='tr'>
  <td width="10%" @if($data['branch']=='0') class="td2" @else class='td3' @endif>{{$data['tag']}}</td>{{--如果status!=''，class=td3 --}}
   <td width="4%" class="td2">{{$data['branch']}}</td>
   <td width="15%" @if($data['branch']=='0') class="td2" @else class='td3' @endif>{{$data['csp']}}</td>
    <td width="4%" class="td4" style='font-size:10px'>
      @if(isset($data['maxb']))
      ({{$data['maxb']}})
      @endif
    </td>{{--(最大分支數) --}}

   <td @if($data['status']=='-1') class="td3" @else class='td2' @endif>{{$data['dbh']}}</td>

   <td width="6%" class='td5' style='font-size:10px'>{{$data['status']}}</td>
   <td width="7%" class='td5' style='font-size:10px'></td>
   <td class='td5'></td>
   <td class='td5'></td>
   <td class='td5'></td>
   
   <td width="30%" class="td1" style='font-size:10px; text-align: right;'>{{$data['note']}}</td>
   <td width="5%" class="td1"></td>
   
   
  </tr>
  @endfor
  @endif
@endfor

</table>

</div>

</body>
</html>