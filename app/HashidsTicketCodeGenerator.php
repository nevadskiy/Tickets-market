<?php

namespace App;

use Hashids\Hashids;

class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
    private $hashids;

    public function __construct($salt = '', $length = 6)
    {
        $this->hashids = new Hashids($salt ?: config('app.key'), $length, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function generateFor(Ticket $ticket)
    {
        return $this->hashids->encode($ticket->id);
    }
}
