
<div>
     <div class='text_box'>    
        <h2>資料比對</h2>
        <p style="margin: 10px 0">{{$compare}}</p>
    
        <button wire:click="compare">開始比對</button>
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
        @php
        if($cov1!=[]) {
            print_r($cov1[1][0]);
            // {echo $cov1[1][0];}
            foreach ($cov1[1][0] as $key => $value){
                echo $key;
            }
            }
        @endphp
    </div>
        @if (isset($comnote))
    <div class='text_box' style='background-color:lightyellow;'> 
        <h2>比對結果</h2>
        <p style="margin: 10px 0">

        {!!$comnote!!}</p>
       
        <a href='/fsseedling/compare-pdf' target='_blank'><button>將比對結果輸出成 pdf 檔</button></a>
        
           
    </div>
    @endif
</div>
