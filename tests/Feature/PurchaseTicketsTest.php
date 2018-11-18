<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Facades\OrderConfirmationNumber;
use App\OrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Response;
use Mockery;
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
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey($field, $response->json()['errors']);
    }

    /** @test */
    function customer_can_purchase_tickets_to_a_published_concert()
    {
        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250])->addTickets(3);

        // THE SAME LIKE ONE-LINE COMMAND BELOW THAT WORKS WITH ANY FACADE
        // $orderConfirmationNumberGenerator = Mockery::mock(OrderConfirmationNumberGenerator::class, [
        //     'generate' => 'ORDERCONFIRMATION1234'
        // ]);
        // $this->app->instance(OrderConfirmationNumberGenerator::class, $orderConfirmationNumberGenerator);
        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertJsonFragment([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'amount' => 9750,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
    }

    /** @test */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->state('unpublished')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertNotFound();
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    function cannot_purchase_more_tickets_than_remain()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create()->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create(['ticket_price' => 3250])->addTickets(3);

        // Before first call charge
        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
            $response = $this->orderTickets($concert, [
                'email' => 'b@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]);

            $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            $this->assertFalse($concert->hasOrderFor('b@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        // First call charge (call the callback here)
        $response = $this->orderTickets($concert, [
            'email' => 'a@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('a@example.com'));
        $this->assertEquals(3, $concert->ordersFor('a@example.com')->first()->ticketQuantity());
    }

    /** @test */
    function an_order_is_not_created_if_payment_fails()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->state('published')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining('john@example.com'));
    }

    /** @test */
    function email_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertJsonValidationError('email', $response);
    }

    /** @test */
    function email_must_be_valid_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

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
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'not-valid-email',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertJsonValidationError('ticket_quantity', $response);
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->state('published')->create();

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
        $concert = factory(Concert::class)->state('published')->create();

        $response = $this->orderTickets($concert, [
            'email' => 'not-valid-email',
            'ticket_quantity' => 0,
        ]);

        $this->assertJsonValidationError('payment_token', $response);
    }
}
