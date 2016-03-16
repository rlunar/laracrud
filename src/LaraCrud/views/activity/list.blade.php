@foreach ($activity as $event)
	<li class="list-group-item">
		@include("lara_crud::activity.types.{$event->name}")
	</li>
@endforeach