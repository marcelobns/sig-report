<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Calendário de Salas | DERCA</title>

    <link rel="stylesheet" href="{{asset('public/css/bootstrap.min.css')}}">
    @stack('link')
</head>
<body>
    @section('before')
        <nav>

        </nav>
    @show

    @yield('content')

    @section('after')

    @show

    <script charset="utf-8" src="{{asset('public/js/jquery.min.js')}}" ></script>
    <script charset="utf-8" src="{{asset('public/js/tether.min.js')}}" ></script>
    <script charset="utf-8" src="{{asset('public/js/bootstrap.min.js')}}" ></script>
    <script charset="utf-8" src="{{asset('public/js/custom.js')}}" ></script>
    @stack('script')
</body>
</html>
