@extends('layouts/webpage') 
@section('pagejs')
<script>
  // var element = document.getElementById("#list1");
  // element.classList.add("now");


</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

</script>
@endsection
@section('rightbox')

@livewire('web.showspecies', ['spcode' =>$spcode])

@endsection