<div>
         <h2>資料比對</h2>
        <p style="margin: 10px 0">*** 請確認已確實完成兩次資料輸入</p>
        <input type='button' value='開始比對'/ wire:click="compare">

        @if (isset($comnote))

        <h6>比對結果</h6>
        <p>

        {{$comnote}}
        <br>
        <a href='fs_download_comparison' target='_blank'><input type='button' value='將比對結果輸出成 word 檔'/></a>
        </p>
        @endif   {{-- Nothing in the world is as soft and yielding as water. --}}
</div>
