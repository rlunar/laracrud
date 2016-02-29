@extends('layouts.master')

@section('content')

<div class="row">
	<div class="page-header">
		<h1>Create new {{ $crudData['crudName'] }}</h1>
	</div>
	<div class="col-md-12">
		<form class="form-horizontal" method="POST" action="{{ url($crudData['crudRoute']) }}">
			{!! csrf_field() !!}
			@foreach ($inputs as $input)
				{!! $input !!}
			@endforeach
			<hr />
			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<a href="{{ url($crudData['crudRoute']) }}"><button type="button" class="btn btn-danger"><i class="glyphicon glyphicon-remove"></i> Cancel</button></a>
					<button type="submit" class="btn btn-primary"><i class="glyphicon glyphicon-ok"></i> Create</button>
				</div>
			</div>
		</form>
		@if (count($errors) > 0)
		    <div class="alert alert-danger">
		        <ul>
		            @foreach ($errors->all() as $error)
		                <li>{{ $error }}</li>
		            @endforeach
		        </ul>
		    </div>
		@endif
	</div>
</div>

@stop

@section('bottom-scripts')

<script type="text/javascript">
	
	$(document).ready(function(){

		if (window.top.location != window.location) {
 			
			$('.navbar').hide();
			$('body').css('padding','0px');

		}


		$(".outerRoute").fancybox({
			maxWidth	: 1024,
			fitToView	: true,
			width		: '70%',
			height		: '70%',
			autoSize	: true,
			closeClick	: false,
			openEffect	: 'fade',
			closeEffect	: 'fade',
			afterClose: function() {
				$.ajax({
					url: '{{ url($crudData['crudRoute']) }}/dropdowns',
					dataType: 'json',
					success: function(data) {
						$.each(data, function(dropdown, options) {
							var firstOption = $('#'+dropdown+' option:first').text();
							$('#'+dropdown).find('option').remove().end().append('<option value="0">'+firstOption+'</option>');
							$.each(options, function(key, value) {
								$('#'+dropdown).append('<option value="'+value.id+'">'+value.name+'</option>');
							});
						});
					},
					error: function(xhr, ajaxOptions, thrownError) {
						swal({
							title: 'Oops!',
							text: 'Something went wrong, please reload the page...',
							type: 'error',
							confirmButtonText: 'Ok'
						});
					}
				});
			}
		});

	});

</script>

@stop