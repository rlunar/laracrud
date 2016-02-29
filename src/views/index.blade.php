@extends('layouts.master')

@section('content')
	<div class="btn-group" role="group" aria-label="">
		<a href="{{ url($crudData['crudRoute'].'/create') }}">
			<button type="button" class="btn btn-primary"><i class="glyphicon glyphicon-plus"></i> Create new {{ $crudData['crudName'] }}</button>
		</a>
	</div>
	<hr>
	<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
            	@foreach($table['headers'] as $header)
            		@if($header->column_name !== 'id')
                		<th>{{ $header->title }}</th>
                	@endif
                @endforeach
                <th>Actions</th>
            </tr>
        </thead>
 
        <tfoot>
            <tr>
                @foreach($table['headers'] as $header)
                	@if($header->column_name !== 'id')
                		<th>{{ $header->title }}</th>
                	@endif
                @endforeach
                <th>Actions</th>
            </tr>
        </tfoot>
 
        <tbody>
			@foreach($table['results'] as $result)
				<tr>
					@foreach($result as $key => $value)
						@if ($key !== 'id')
							<td>{{ $value }}</td>
						@endif
					@endforeach
					<td>
						<a href="{!! url($crudData['crudRoute'].'/'.$result['id']) !!}" title="">
							<button type="button" class="btn btn-primary">View <i class="glyphicon glyphicon-eye-open"></i></button>
						</a>
						<a href="{!! url($crudData['crudRoute'].'/'.$result['id'].'/edit') !!}" title="">
							<button type="button" class="btn btn-info">Update <i class="glyphicon glyphicon-pencil"></i></button>
						</a>
						{!! Form::open(['method' => 'delete', 'action' => [$crudData['crudController'].'@destroy', $result['id']], 'style' => 'display: inline;']) !!}
							<button type="submit" class="btn btn-danger">Delete <i class="glyphicon glyphicon-trash"></i></button>
						{!! Form::close() !!}
					</td>
				</tr>
			@endforeach
        </tbody>
    </table>

@stop

@section('bottom-scripts')
	<script type="text/javascript">
		$('.btn-danger').click(function(e){
			e.preventDefault();
			var that = $(this);
			swal({   
				title: "Are you sure you want to delete row?",
				text: "You will not be able to recover this!",
				type: "warning",
				showCancelButton: true,
				closeOnConfirm: true,
				closeOnCancel: true,
				showLoaderOnConfirm: true,
			}, function(isConfirm){   
				if (isConfirm) {
					that.parent('form').submit();
				}
			});
		});

		$(document).ready(function() {

			if (window.top.location != window.location) {
 			
				parent.jQuery.fancybox.close();

			}

		    $('#example').DataTable({
		    	
		    });
		});
	</script>
@stop