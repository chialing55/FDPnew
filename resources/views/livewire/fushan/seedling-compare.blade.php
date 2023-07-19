<div>
         <h2>資料比對</h2>
        <p style="margin: 10px 0">{{$compare}}</p>
    
        <button wire:click="compare">開始比對</button>

        @php
        if($cov1!=[]) {
            print_r($cov1[1][0]);
            // {echo $cov1[1][0];}
            foreach ($cov1[1][0] as $key => $value){
                echo $key;
            }
            }
        @endphp

        @if (isset($comnote))

        <h6>比對結果</h6>
        <p>

        {{$comnote}}
        <br>
        <a href='fs_download_comparison' target='_blank'><input type='button' value='將比對結果輸出成 word 檔'/></a>
        </p>
        @endif   {{-- Nothing in the world is as soft and yielding as water. --}}
</div>
