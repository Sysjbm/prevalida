@extends('template')
@section('content')
<h2>Alertas</h2>
<div>
    <div style="overflow-y: auto; height:400px;background-color:#FFFBCF;">
        <?php print_r($log);?>
    </div><br>
    <div style="height:50px;background-color:#FAFAFA;">
        @if($log != "")
        <p style="color:red">Se encontraron errores en la validación.</p>
        @else
        <p style="color:green">La validación fue éxitosa.</p>
        @endif
    </div>
    <div class="container">
        <div class="row">
            <div class="col-sm-1">  
                <a href="{{url('validador')}}" class="btn btn-success" role="button">Volver</a>
            </div>
            <div class="col-sm-1">
                <form action="{{url('pdf')}}" method="post">
                    <input type="hidden" name="log" value="{{$log}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="submit" class="btn btn-info" value="Imprimir">
                </form>
            </div>
        </div>
    </div>
</div>
@endsection