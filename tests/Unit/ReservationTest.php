<?php

namespace Tests\Unit;

use App\Reservation;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    /** @test */
    function it_calculates_the_total_cost()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }
}
