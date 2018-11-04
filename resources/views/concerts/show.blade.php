@extends('layouts.master')

@section('body')
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h1>{{ $concert->title }}</h1>
                        <h2>{{ $concert->subtitle }}</h2>
                    </div>
                    <div class="card-body">
                        <p>{{ $concert->formatted_date }}</p>
                        <p>Doors at {{ $concert->formatted_start_time }}</p>
                        <p>{{ $concert->ticket_price_in_dollars }}</p>
                        <p>{{ $concert->venue }}</p>
                        <p>{{ $concert->venue_address }}</p>
                        <p>{{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}</p>
                    </div>
                    <div class="card-footer">
                        <p>{{ $concert->additional_information }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
