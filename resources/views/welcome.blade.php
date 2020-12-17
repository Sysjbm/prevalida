@extends('template')
@section('content')
<div class="jumbotron">
    <h1>Bienvenido al prevalidador 4505</h1>
    <p>El alcance de esta herramienta informática es la de realizar la prevalidación inicial de los archivos <code>.txt</code> de la resolución 4505. Con la herramienta podrá:</p>
    <small>
        ✓ Validación tipo de archivo.<br>
        ✓ Validación cantidad de campos.<br>
        ✓ Validación de campos requeridos.<br>
        ✓ Validación código de habilitación.<br>
        ✓ Validación PNA con BDUA.<br>
        ✓ Validación nombres y apellidos.<br>
        ✓ Validación de sexos.<br>
        ✓ Validación de caracteres especiales.<br>
        ✓ Visualización de errores en pantalla.<br>
        ✓ Descarga de reporte de errores en PDF.<br>
    </small><br>
    <a href="{{url('validador')}}" class="btn btn-default" role="button">Link prevalidador</a>
</div>
@endsection