@extends('layouts.app')
@section('title', 'CENSUP - Alunos')
@section('content')

    {{$pessoaPaginator->links('partial.files_pagination')}}
    <h3 class="text-center title-padding">
        <a href={{asset($filepath)}} download>
            </i> Download <b>{{$filename}}</b>
        </a>
    </h3>
@endsection
