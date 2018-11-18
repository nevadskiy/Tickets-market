<?php

namespace Tests\Feature;

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function user_can_view_their_order_confirmation()
    {
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('March 12, 2018 8:00pm'),
        ]);
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION12345',
            'amount' => 8500,
            'card_last_four' => 1881,
        ]);
        $ticketA = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE123'
        ]);
        $ticketB = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE456'
        ]);

        $response = $this->get("/orders/ORDERCONFIRMATION12345");

        $response->assertOk();
        $response->assertViewHas('order', function ($viewOrder) use ($order) {
            return $viewOrder->id === $order->id;
        });

        $response->assertSee('ORDERCONFIRMATION12345');
        $response->assertSee('$85.00');
        $response->assertSee('**** **** **** 1881');
        $response->assertSee('TICKETCODE123');
        $response->assertSee('TICKETCODE456');

        // Assert the right fragment inside <time> datetime attribute
        $response->assertSee('2018-03-12 20:00');
     }
}