@extends('layouts.app')
@section('title', 'Discentes')
@section('content')
    {{Form::model($form, ['action' => 'DiscenteController@index', 'method'=>'get'])}}
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    {{Form::select('status[]', $status, null, ['id'=>'DiscenteStatus', 'class'=>'select', 'placeholder'=>'<Todos os Status>', 'multiple', 'required'])}}
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    {{Form::select('curso_id', $cursos, null, ['id'=>'DiscenteCursoId', 'class'=>'select', 'placeholder'=>'<Todos os Cursos>'])}}
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    {{Form::select('periodo', $periodos, null, ['id'=>'DiscentePeriodo', 'class'=>'select', 'placeholder'=>'<Todos os Periodos>'])}}
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    {{Form::text('search_text', null, ['id'=>'DiscenteSearchText', 'class'=>'form-control form-control-sm', 'placeholder'=>'Buscar por Matricula, Nome ou CPF'])}}
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group text-right">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa fa-filter fa-lg"></i> Filtrar</button>
                    <button type="submit" class="btn btn-sm btn-outline-success" name="csv" value="1"><i class="fa fa-file-excel-o fa-lg"></i> Arquivo</button>
                </div>
                @if (@$filepath)
                    <div class="form-group text-right filepath">
                        <a href="{{$filepath}}" target="_blank"> baixar arquivo.csv </a>
                    </div>
                @endif
            </div>
        </div>
    {{Form::close()}}
    @if (!sizeof($table))
        @include('partial.zero_result')
    @else
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>Matricula</th>
                    <th>Ingresso</th>
                    <th>Nome</th>
                    <th>CPF</th>
                    <th>Nascimento</th>
                    <th>Situação</th>
                    <th>Curso</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($table as $i=>$row)
                    <tr>
                        <td>{{$row->matricula}}</td>
                        <td>{{$row->ano_ingresso.'.'.$row->periodo_ingresso}}</td>
                        <td>{{$row->pessoa->nome}}</td>
                        <td>{{$row->pessoa->cpf_cnpj}}</td>
                        <td>{{$row->pessoa->data_nascimento}}</td>
                        <td>{{$row->statusDiscente->descricao}}</td>
                        <td>{{substr(@$row->curso->nome, 0, 35)}}</td>
                        <td><a href="{{url("/discente/{$row->pessoa->id_pessoa}")}}" class="btn-sm" title="Visualizar Cadastro" data-toggle="modal" data-target="#modal_frame"><i class="fa fa-drivers-license-o"></i></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{$table->appends($_GET)->links('partial.pagination')}}
    @endif
@endsection

@include('partial.loading')
@include('partial.model_frame')

@push('script')
<script type="text/javascript">
    $('[name=csv]').click(function(){
        $('.loading').show();
    });
    $('.form-control, .select').change(function(){
        $('.filepath').hide();
    });
</script>
@endpush
