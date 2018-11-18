@extends('layouts.master')

@section('body')
    <div class="container">
        <div class="row justify-content-center align-items-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        {{--<h1>{{ $concert->title }}</h1>--}}
                    </div>
                    <div class="card-body">
                        <p>Confirmation number: {{ $order->confirmation_number }}</p>
                        <p>Order total: ${{ number_format($order->amount / 100, 2) }}</p>
                        <p>Card number: **** **** **** {{ $order->card_last_four }}</p>
                        <div class="card">
                            <div class="card-header">Tickets</div>
                            <div class="card-body">
                                @foreach ($order->tickets as $ticket)
                                    <p>Ticket code: {{ $ticket->code }}</p>
                                    <time datetime="{{ $ticket->concert->date->format('Y-m-d H:i') }}">
                                        <p>Date: {{ $ticket->concert->date->format('l, F j, Y') }}</p>
                                    </time>
                                    <p>Doors at {{ $ticket->concert->formatted_start_time }}</p>
                                    <hr>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
