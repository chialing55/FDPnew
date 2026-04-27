
<div>
     <div class='text_box'>    
        <h2>資料比對</h2>
        <p style="margin: 10px 0">{{ $statusNote }}</p>
    
        <button wire:click="compare">開始比對</button>
<div class="loading-container" wire:loading.class="visible">
    <div class="loading-spinner"></div>
</div>
    </div>
        @if (isset($comnote))
    <div class='text_box' style='background-color:lightyellow;'> 
        <h2>比對結果</h2>
        <p style="margin: 10px 0">

        {!!$comnote!!}</p>
       
        <a href='{{ route('admin.fushan.seedling.pdf.compare') }}' target='_blank'><button>將比對結果輸出成 pdf 檔</button></a>
        
           
    </div>
    @endif
</div>
