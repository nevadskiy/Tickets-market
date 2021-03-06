<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;

trait PaymentGatewayContractTests
{
    abstract protected function getPaymentGateway();

    /** @test */
    function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();

        $recentCharges = $paymentGateway->duringCharges(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken(), 'test_account_1234');
        });

        $this->assertCount(1, $recentCharges);
        $this->assertEquals(2500, $recentCharges->map->amount()->sum());
    }

    /** @test */
    function can_get_details_about_a_successful_charge()
    {
        $paymentGateway = $this->getPaymentGateway();

        $charge = $paymentGateway->charge(
            2500, $paymentGateway->getValidTestToken($paymentGateway::TEST_CARD_NUMBER), 'test_account_1234'
        );

        $this->assertEquals(substr($paymentGateway::TEST_CARD_NUMBER, -4), $charge->cardLastFour());
        $this->assertEquals(2500, $charge->amount());
        $this->assertEquals('test_account_1234', $charge->destination());
    }

    /** @test */
    function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken(), 'test_account_1234');
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken(), 'test_account_1234');

        $recentCharges = $paymentGateway->duringCharges(function ($paymentGateway) {
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), 'test_account_1234');
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken(), 'test_account_1234');
        });

        $this->assertCount(2, $recentCharges);
        $this->assertEquals([4000, 5000], $recentCharges->map->amount()->all());
    }

    /** @test */
    function charges_with_an_invalid_payment_token_fail()
    {
        $paymentGateway = $this->getPaymentGateway();

        $recentCharges = $paymentGateway->duringCharges(function ($paymentGateway) {
            try {
                $paymentGateway->charge(1500, 'invalid-payment-token', 'test_account_1234');
            } catch (PaymentFailedException $e) {
                return;
            }

            $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException.');
        });

        $this->assertCount(0, $recentCharges);
    }
}
