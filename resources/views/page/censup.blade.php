@extends('layouts.app')
@section('title', 'CENSUP - Alunos')
@section('content')

    {{$pessoaPagination->links('partial.files_pagination')}}

    @if(true) {{--  sizeof($inconsistencias) == 0) --}}
        <h3 class="text-center title-padding">
            <a href={{asset($filename)}} download>
                </i> Download <b>{{$censo}}-alunos-{{ @$_GET['page'] ? $_GET['page'] : 1 }}.txt</b>
            </a>
        </h3>
    @else
        <h3 class="text-center title-padding">
            <i class="fa fa-warning text-warning"></i> Inconsistências ({{sizeof($inconsistencias)}})
        </h3>
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>CPF</th>
                    <th>Matricula</th>
                    <th>Pessoa</th>
                    <th>Status</th>
                    <th>Raca</th>
                    <th>Nac.</th>
                    <th>Pais</th>
                    <th>UF</th>
                    <th>Município</th>
                    <th>Curso</th>
                    <th>INEP</th>
                    <th>EXP</th>
                </tr>
            </thead>
            <tbody>
            @foreach($inconsistencias as $item)
                <tr>                    
                    <td>{{$item['CPF']}}</td>
                    <td>{{$item['MATRICULA']}}</td>
                    <td>{{$item['PESSOA']}}</td>
                    <td>{{$item['STATUS']}}</td>
                    <td>{{$item['RACA']}}</td>
                    <td>{{$item['NACIONALIDADE']}}</td>
                    <td>{{$item['PAIS']}}</td>
                    <td>{{$item['UF']}}</td>
                    <td>{{$item['MUNICIPIO']}}</td>
                    <td>{{$item['CURSO']}}</td>
                    <td>{{$item['COD_INEP']}}</td>
                    <td>{{$item['RG_EXPEDIDOR']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
