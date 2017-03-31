@extends('layouts.app')
@section('title', 'GDA')
@section('content')

<table>    
    @foreach($pessoas as $pessoa)
    <tr>
        {{-- <td>'{{str_pad($pessoa->cpf_cnpj, 11, '0', STR_PAD_LEFT)}}',</td> --}}
        <td>{{$pessoa->cpf_cnpj}},</td>
    </tr>        
    @endforeach
</table>