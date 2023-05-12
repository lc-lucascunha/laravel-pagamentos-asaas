<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Title -->
    <title>{{ config('app.name') }}</title>

    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <style>
        .row{
            padding: 2rem;
            margin: 0;
        }
        .row-header{
            border-bottom: 1px solid rgba(0, 0, 0, 0.175);
        }

        table tbody tr td{
            font-size:  0.7875rem;
        }
        table tbody tr:last-child {
            border-bottom: 0 solid white;
        }
        table{
            margin-bottom: unset !important;
        }

        button, .btn{
            color: white !important;
            text-transform: uppercase;
        }
        .btn-180{
            min-width: 180px;
        }
        .count, .count-null{
            background: #c8e6c9;
            color: #263238;
            text-align: center;
            padding-top: 0.3rem;
            font-size:  0.7875rem;
            width: 1.8rem;
            height: 1.8rem;
        }
        .count{
            cursor: pointer;
        }
        .count:hover{
            background: #2e7d32;
            color: #c8e6c9;
        }
        .count-null {
            background: #e0e0e0;
        }

        .p-1{padding: 1rem !important;}
        .pt-0{padding-top: 0 !important;}
        .pb-0{padding-bottom: 0 !important;}
        .pb-1{padding-bottom: 1rem !important;}
        .pl-0{padding-left: 0 !important;}
        .pr-0{padding-right: 0 !important;}

        .cursor-pointer{
            cursor: pointer;
        }
    </style>
</head>
<body>

    <main id="app">
        @yield('content')
    </main>

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
