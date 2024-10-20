<div>
    <h1>數位標本</h1>
    <div>
        選擇資料夾
            <select name="folder1"  wire:model='folder1' wire:change="searchfolder2($event.target.value)">
            <option value=""> 

            </option>
            @foreach($folder1List as $item)
            <option value="{{$item}}">{{$item}} 

            </option>

             @endforeach

             </select>
            <select name="folder2"  wire:model='folder2' wire:change="searchfolder3($event.target.value)">
            <option value=""> 

            </option>
            @foreach($folder2List as $item)
            <option value="{{$item}}">{{$item}} 

            </option>

             @endforeach


            </select>
    </div>
</div>