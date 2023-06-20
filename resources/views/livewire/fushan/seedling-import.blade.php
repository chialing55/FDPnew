<div>
    <h2>資料匯入</h2>
    <p style='margin: 10px 0'>seedling 資料表中的最新資料為 第 {{$slmaxcensus}} 次調查資料</p>
    <p>接下來要匯入 {{$nowcensus}} 次調查資料</p>
    <p>*** 請確認已確實完成資料比對</p>   
    <input type='button' value='匯入資料'/ wire:click="import">
    @if(isset($importnote))
        <p>{{$importnote}}</p>
    @endif
</div>
