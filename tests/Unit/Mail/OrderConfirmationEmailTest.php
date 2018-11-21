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

        $rendered = $this->render($mail);

        $this->assertContains(url('/orders/ORDERCONFIRMATION1234'), $rendered);
    }

    private function render($mailable)
    {
        return view($mailable->build()->view, $mailable->buildViewData())->render();
    }
}
