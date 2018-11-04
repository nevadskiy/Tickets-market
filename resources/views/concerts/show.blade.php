<h1>{{ $concert->title }}</h1>
<h2>{{ $concert->subtitle }}</h2>
<p>{{ $concert->formatted_date }}</p>
<p>Doors at {{ $concert->formatted_start_time }}</p>
<p>{{ $concert->ticket_price_in_dollars }}</p>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->venue_address }}</p>
<p>{{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}</p>
<p>{{ $concert->additional_information }}</p>

{{--<!doctype html>--}}
{{--<html lang="{{ app()->getLocale() }}">--}}
{{--<head>--}}
    {{--<meta charset="utf-8">--}}
    {{--<meta http-equiv="X-UA-Compatible" content="IE=edge">--}}
    {{--<meta name="viewport" content="width=device-width, initial-scale=1">--}}

    {{--<title>Laravel</title>--}}

    {{--<!-- Fonts -->--}}
    {{--<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">--}}

    {{--<link rel="stylesheet" href="{{ asset('css/app.css') }}">--}}
{{--</head>--}}
{{--<body>--}}
    {{--<div class="container">--}}
        {{--<div class="card">--}}
            {{--<div class="card-header">--}}
                {{--<h1>{{ $concert->title }}</h1>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--</div>--}}
{{--</body>--}}
{{--</html>--}}
