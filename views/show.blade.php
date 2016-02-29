@extends('layouts.master')

@section('content')

<div class="row">
	<div class="page-header">
		<h1>{{ $crudData['crudName'] }}</h1>
	</div>
	<div class="col-md-12">
		<form class="form-horizontal">
			@foreach ($inputs as $input)
				{!! $input !!}
			@endforeach
			<hr />
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<a href="{{ url($crudData['crudRoute']) }}"><button type="button" class="btn btn-default"><i class="glyphicon glyphicon-repeat"></i> Return</button></a>
					<a href="{{ url($crudData['crudRoute'].'/'.$personal->id.'/edit') }}"><button type="button" class="btn btn-primary"><i class="glyphicon glyphicon-pencil"></i> Edit</button></a>
				</div>
			</div>
		</form>
	</div>
</div>

@stop