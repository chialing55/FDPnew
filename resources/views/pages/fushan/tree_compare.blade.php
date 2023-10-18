@extends('layouts/tree') 
@section('pagejs')

<script type="text/javascript">
  $('.list4').addClass('now');
</script>

@endsection
@section('rightbox')

@livewire($site.'.tree-compare')


@endsection