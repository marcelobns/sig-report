<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') | SIG-REPORT</title>

    <link rel="stylesheet" href="{{asset('public/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/selectize.css')}}">
    <link rel="stylesheet" href="{{asset('public/css/custom.css')}}">
    @stack('style')
</head>
<body>
    <div class="container-fluid wrapper">
        <div class="row">
            @section('before')
                <nav class="col-md-2 col-sm-3">
                    <a class="navbar-brand text-muted" href="{{url('/')}}">
                        <img src='{{asset('public/img/ufrr-brasao.svg')}}'/>
                    </a>
                    <ul class="list-group">
                        <li class="list-group-item"><a href="{{url('/index')}}">Índices</a></li>
                        <li class="list-group-item"><a href="{{url('/discentes')}}">Discentes</a></li>
                        <li class="list-group-item">Docentes</li>
                        <li class="list-group-item">Cursos</li>
                        <li class="list-group-item"><a href="{{url('/censup')}}" class="btn-waiting">CENSUP</a></li>
                        <li class="list-group-item"><a href="#">Vestibulum at eros</a></li>
                    </ul>
                </nav>
            @show
            @section('center')
                <main class="col-sm-9">
                    <h2>@yield('title')</h2><hr>
                    @yield('content')
                </main>
            @show
            @section('after')
                <footer class="col-sm-12 text-muted">
                    <small><i class="fa fa-cc"></i> <?=date('Y')?></small>
                </footer>
            @show
        </div>
    </div>
    <script src="{{asset('public/js/jquery.min.js')}}" charset="utf-8"></script>
    <script src="{{asset('public/js/tether.min.js')}}" charset="utf-8"></script>
    <script src="{{asset('public/js/bootstrap.min.js')}}" charset="utf-8"></script>
    <script src="{{asset('public/js/selectize.min.js')}}" charset="utf-8"></script>
    <script src="{{asset('public/js/custom.js')}}" charset="utf-8"></script>

    @stack('script')

    @include('partial.loading')
</body>
</html>
