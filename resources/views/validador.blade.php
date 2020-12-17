@extends('template')
<?php 
$meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
	'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
	?>
	@section('content')
	@if ($message = Session::get('error'))
	<div class="alert alert-danger alert-block">
		<button type="button" class="close" data-dismiss="alert">×</button>  
		<strong>{{ $message }}</strong>
	</div>
	@endif
	@if ($message = Session::get('success'))
	<div class="alert alert-success alert-block">
		<button type="button" class="close" data-dismiss="alert">×</button>  
		<strong>{{ $message }}</strong>
	</div>
	@endif
	<div class="alert alert-warning">
		<strong>Atención!</strong> Recuerde que debe subir un archivo con extención .xlsx para poder realizar el proceso de prevalidación.
	</div>
	<div class="col-sm-12">
		<div class="jumbotron">
			<h2 align="center">Prevalidador resolución 4505</h2>
			<code>*Por favor ingrese los siguientes campos</code>
			<br><br>
			<form action="uploadfile" method="post" enctype="multipart/form-data">
				{{ csrf_field() }}
				<div class="form-group">
					<label for="email">Subir archivo</label>
					<input type="file" class="form-control" id="myFile" name="filename" required>
				</div>
				<div class="form-group">
					<label for="email">Seleccione un mes</label>
					<select class="form-control" name="mes" required>
						@foreach($meses as $key => $mes)
						<option value="{{$key+1}}">{{$mes}}</option>
						@endforeach
					</select>
				</div>
				<div class="form-group">
					<label for="email">Seleccione un año</label>
					<select class="form-control" name="year" required>
						@for($year = date('Y')-5; $year <= date('Y'); $year++)
						<option value="{{$year}}">{{$year}}</option>
						@endfor
					</select>
				</div>
				<input class="btn btn-info" type="submit" value="Prevalidar">
			</form>
		</div>
	</div>
	@endsection