<?php

namespace Tests\Unit;

use App\Reservation;
use App\Ticket;
use Mockery;
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

    /** @test */
    function reserved_tickets_are_released_when_a_reservation_is_cancelled()
    {
        $ticket1 = Mockery::mock(Ticket::class);
        $ticket1->shouldReceive('release')->once();

        $ticket2 = Mockery::mock(Ticket::class);
        $ticket2->shouldReceive('release')->once();

        $ticket3 = Mockery::mock(Ticket::class);
        $ticket3->shouldReceive('release')->once();

        $tickets = collect([$ticket1, $ticket2, $ticket3]);

        $reservation = new Reservation($tickets);

        $reservation->cancel();
    }

    /** @test */
    function reserved_tickets_are_released_when_a_reservation_is_cancelled_2()
    {
        $tickets = collect([
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
        ]);

        $reservation = new Reservation($tickets);

        $reservation->cancel();
    }

    /** @test */
    function reserved_tickets_are_released_when_a_reservation_is_cancelled_3()
    {
        // Arrange
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        // Act
        $reservation = new Reservation($tickets);
        $reservation->cancel();

        // Assert
        foreach ($tickets as $ticket) {
            // Have received in the past
            $ticket->shouldHaveReceived('release')->once();
        }
    }
}
