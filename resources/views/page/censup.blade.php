@extends('layouts.app')
@section('title', 'CENSUP - Alunos')
@section('content')

    {{$pessoaPagination->links('partial.files_pagination')}}
    
    <h3 class="text-center title-padding">
        <a href={{asset($filename)}} download>
            </i> Download <b>{{$censo}}-alunos-{{ @$_GET['page'] ? $_GET['page'] : 1 }}.txt</b>
        </a>
    </h3>
@endsection
