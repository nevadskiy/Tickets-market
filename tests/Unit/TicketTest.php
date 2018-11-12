<?php

namespace Tests\Unit;

use App\Concert;
use App\Ticket;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_ticket_can_be_released()
    {
        $ticket = factory(Ticket::class)->state('reserved')->create();

        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    function it_can_be_reserved()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }
}
