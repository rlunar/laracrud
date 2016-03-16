<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>
        <style>
            html, body {
                height: 100%;
            }

            body {
            	padding-top: 2em;
            }
        </style>
        <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container">
        	<ul class="list-group">
				@include('lara_crud::activity.list')
			</ul>
        </div>
    </body>
</html>