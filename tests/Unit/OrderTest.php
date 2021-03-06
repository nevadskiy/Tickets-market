<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_creates_an_order_from_tickets_email_and_charge()
    {
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);

        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        $order = Order::forTickets($tickets, 'john@example.com', $charge);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);

        $tickets->each->shouldHaveReceived('claimFor', [$order]);
    }

    /** @test */
    function retrieving_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION12345'
        ]);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION12345');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception()
    {
        $this->expectException(ModelNotFoundException::class);

        Order::findByConfirmationNumber('WRONG_CONFIRMATION_NUMBER');
    }

    /** @test */
    function it_converts_to_an_array()
    {
        $order = factory(Order::class)->create([
            'email' => 'jane@example.com',
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'amount' => 6000
        ]);

        $order->tickets()->saveMany([
            factory(Ticket::class)->make(['code' => 'TICKETCODE1']),
            factory(Ticket::class)->make(['code' => 'TICKETCODE2']),
            factory(Ticket::class)->make(['code' => 'TICKETCODE3']),
        ]);

        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'jane@example.com',
            'amount' => 6000,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ], $result);
    }
}
