<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    private $paymentGateway;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params)
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertJsonValidationError($field, TestResponse $response)
    {
        $response->assertStatus(422);
        $this->assertArrayHasKey($field, $response->json()['errors']);
    }

    /** @test */
    function customer_can_purchase_concert_tickets()
    {
        $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();

        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertJsonValidationError('email', $response);
    }

    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email' => 'not-valid-email',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertJsonValidationError('email', $response);
    }

    /** @test */
    function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email' => 'not-valid-email',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertJsonValidationError('ticket_quantity', $response);
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email' => 'not-valid-email',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertJsonValidationError('ticket_quantity', $response);
    }

    /** @test */
    function payment_token_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->orderTickets($concert, [
            'email' => 'not-valid-email',
            'ticket_quantity' => 0,
        ]);

        $this->assertJsonValidationError('payment_token', $response);
    }
}
