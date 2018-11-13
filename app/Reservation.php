<?php

namespace App;

use App\Billing\PaymentGateway;

class Reservation
{
    private $tickets;
    private $email;

    public function __construct($tickets, $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
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

    public function complete($paymentGateway, $paymentToken)
    {
        $paymentGateway->charge($this->totalCost(), $paymentToken);

        return Order::forTickets($this->tickets(), $this->email(), $this->totalCost());
    }
}
