<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Laravel CRUD</title>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="/css/app.css">
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/bs-3.3.5/jqc-1.11.3,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.8,af-2.0.0,b-1.0.1,b-colvis-1.0.1,b-flash-1.0.1,b-html5-1.0.1,b-print-1.0.1,cr-1.2.0,fc-3.1.0,fh-3.0.0,kt-2.0.0,r-1.0.7,rr-1.0.0,sc-1.3.0,se-1.0.0/datatables.min.css"/>
		<!-- Add fancyBox -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css" type="text/css" media="screen" />
		@yield('top-scripts')
		<style type="text/css">
			body {
				padding-top: 70px;
			}
		</style>
	</head>
	<body>

	    <nav class="navbar navbar-default navbar-fixed-top">
		    <div class="container">
		        <!-- Brand and toggle get grouped for better mobile display -->
		        <div class="navbar-header">
		            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
		                <span class="sr-only">Toggle navigation</span>
		                <span class="icon-bar"></span>
		                <span class="icon-bar"></span>
		                <span class="icon-bar"></span>
		            </button>
		            <a class="navbar-brand" href="#">Laravel CRUD</a>
		        </div>

		        <!-- Collect the nav links, forms, and other content for toggling -->
				<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
					<ul class="nav navbar-nav">
						<li><a href="{{ url('/company') }}">Company <span class="sr-only">(current)</span></a></li>
						<li><a href="{{ url('/personal') }}">Personal</a></li>
					</ul>
				</div><!-- /.navbar-collapse -->

		    </div><!-- /.container-fluid -->
		</nav>
		
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

							<form method="POST" action="{{ url($crudData['crudRoute'].'/'.$result['id']) }}" style="display: inline;">
								{!! csrf_field() !!}
								<input name="_method" type="hidden" value="DELETE" />
								<button type="submit" class="btn btn-danger">Delete <i class="glyphicon glyphicon-trash"></i></button>								
							</form>
						</td>
					</tr>
				@endforeach
	        </tbody>
	    </table>

		<!-- Latest compiled and minified JS -->
		<script src="https://code.jquery.com/jquery.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="https://cdn.datatables.net/r/bs-3.3.5/jqc-1.11.3,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.8,af-2.0.0,b-1.0.1,b-colvis-1.0.1,b-flash-1.0.1,b-html5-1.0.1,b-print-1.0.1,cr-1.2.0,fc-3.1.0,fh-3.0.0,kt-2.0.0,r-1.0.7,rr-1.0.0,sc-1.3.0,se-1.0.0/datatables.min.js"></script>
		<!-- Add fancyBox -->
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js"></script>

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

		@include('lara_crud::laracrudflash')

	</body>
	
</html>