@extends('layouts/seedling') 
@section('pagejs')

<script type="text/javascript">
  $('.list4').addClass('now');
</script>

@endsection
@section('rightbox')

@livewire($site.'.seedling-compare')


@endsection