<?php

namespace App;

class Reservation
{
    private $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function cancel()
    {
        $this->tickets->each(function ($ticket) {
            $ticket->release();
        });
    }
}
