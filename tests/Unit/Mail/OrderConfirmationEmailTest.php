<?php

namespace Tests\Unit\Mail;

use App\Mail\OrderConfirmationEmail;
use App\Order;
use Illuminate\Contracts\Mail\Mailable;
use Tests\TestCase;

class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    function it_contains_a_link_to_the_order_confirmation_page()
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        $mail = new OrderConfirmationEmail($order);

        // Also available from laravel 5.5 $rendered = $email->render() method
        $rendered = $this->render($mail);

        $this->assertContains(url('/orders/ORDERCONFIRMATION1234'), $rendered);
    }

    /** @test */
    function it_has_a_subject()
    {
        $order = factory(Order::class)->make();

        $email = new OrderConfirmationEmail($order);

        $this->assertEquals('Your Ticket Order', $email->build()->subject);
    }

    private function render($mailable)
    {
        return view($mailable->build()->view, $mailable->buildViewData())->render();
    }
}
