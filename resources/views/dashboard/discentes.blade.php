@extends('layouts.app')
@section('title', 'Discentes')
@section('content')
    <form class="" action="index.html" method="post">
        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <?=Form::select('discente[status]', $status, null, ['id'=>'DiscenteStatus', 'class'=>'select', 'placeholder'=>'<Todos os Status>', 'multiple']);?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <?=Form::select('discente[curso_id]', $cursos, null, ['id'=>'DiscenteCursoId', 'class'=>'select', 'placeholder'=>'<Todos os Cursos>']);?>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <?=Form::select('discente[columns]', $columns, null, ['id'=>'DiscenteColumns', 'class'=>'select', 'multiple', 'placeholder'=>'<Exibir Colunas>']);?>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group text-right">
                    <button type="submit" name="button" class="btn btn-sm btn-outline-primary"><i class="fa fa-filter fa-lg"></i> Filtrar</button>
                    <button type="button" name="button" class="btn btn-sm btn-outline-success"><i class="fa fa-download fa-lg"></i></button>
                </div>
            </div>
        </div>
    </form>
    <table class="table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Username</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row">1</th>
                <td>Mark</td>
                <td>Otto</td>
                <td>@mdo</td>
            </tr>
            <tr>
                <th scope="row">2</th>
                <td>Jacob</td>
                <td>Thornton</td>
                <td>@fat</td>
            </tr>
            <tr>
                <th scope="row">3</th>
                <td colspan="2">Larry the Bird</td>
                <td>@twitter</td>
            </tr>
        </tbody>
    </table>
    <nav aria-label="Page navigation example">
        <ul class="pagination justify-content-end">
            <li class="page-item disabled">
                <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
            <li class="page-item"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>
    </nav>
@endsection

@push('script')
<script type="text/javascript">
    $('.select').selectize();
</script>
@endpush
