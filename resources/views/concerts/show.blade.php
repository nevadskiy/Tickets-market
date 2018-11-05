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
                        <p><span class="far mr-2 fa-fw fa-calendar"></span>{{ $concert->formatted_date }}</p>
                        <p><span class="far mr-2 fa-clock"></span> Doors at {{ $concert->formatted_start_time }}</p>
                        <p><span class="fas mr-2 fa-money-bill-wave"></span> {{ $concert->ticket_price_in_dollars }}</p>
                        <p><span class="fas mr-2 fa-map-marker-alt"></span> {{ $concert->venue }}</p>
                        <p class="text-muted">{{ $concert->venue_address }}</p>
                        <p class="text-muted">{{ $concert->city }}, {{ $concert->state }} {{ $concert->zip }}</p>
                    </div>
                    <div class="card-footer">
                        <p><span class="fas mr-2 fa-info-circle"></span>{{ $concert->additional_information }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
