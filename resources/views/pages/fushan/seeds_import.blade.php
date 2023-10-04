@extends('layouts/seeds') 
@section('pagejs')

<script type="text/javascript">
  $('.list5').addClass('now');
</script>

@endsection
@section('rightbox')
<h2>匯入大表</h2>

@livewire($site.'.seeds-import', ['user' => $user, 'site' => $site])


@endsection