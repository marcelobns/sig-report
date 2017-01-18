@extends('layouts.app')
@section('title', 'Discentes')
@section('content')
    <?=Form::model($discente, ['action' => 'DashboardController@discentes', 'method'=>'get'])?>
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <?=Form::select('status[]', $status, null, ['id'=>'DiscenteStatus', 'class'=>'select', 'placeholder'=>'<Todos os Status>', 'multiple']);?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <?=Form::select('curso_id', $cursos, null, ['id'=>'DiscenteCursoId', 'class'=>'select', 'placeholder'=>'<Todos os Cursos>']);?>
                </div>
            </div>
            <div class="col-sm-2">

            </div>
            <div class="col-sm-4">

            </div>
            <div class="col-sm-6">
                <div class="form-group text-right">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa fa-filter fa-lg"></i> Filtrar</button>
                    <button type="submit" name="csv" class="btn btn-sm btn-outline-success" value="1"><i class="fa fa-download fa-lg"></i></button>
                </div>
            </div>
        </div>
    <?=Form::close()?>
    @if (!sizeof($resultado))
        @include('shared.zero_resultado')
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
                @foreach ($resultado as $i=>$row)
                    <tr>
                        <td>{{$row->matricula}}</td>
                        <td>{{$row->ano_ingresso.'.'.$row->periodo_ingresso}}</td>
                        <td>{{$row->pessoa->nome}}</td>
                        <td>{{$row->pessoa->cpf_cnpj}}</td>
                        <td>{{$row->pessoa->data_nascimento}}</td>
                        <td>{{$row->statusDiscente->descricao}}</td>
                        <td>{{substr(@$row->curso->nome, 0, 35)}}</td>
                        <td><a href="#" class="btn-sm" title="Visualizar Cadastro"><i class="fa fa-drivers-license-o"></i></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $resultado->appends($_GET)->links('shared.pagination') }}
    @endif
@endsection

@push('script')
<script type="text/javascript">
    $('.select').selectize();
</script>
@endpush
{{-- TODO diminuir botões de paginação, ordernar por pessoa.nome  --}}
