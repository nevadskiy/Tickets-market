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
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $recentCharges);
        $this->assertEquals(2500, $recentCharges->sum());
    }

    /** @test */
    function can_fetch_charges_created_during_a_callback()
    {
        $paymentGateway = $this->getPaymentGateway();

        $paymentGateway->charge(2000, $paymentGateway->getValidTestToken());
        $paymentGateway->charge(3000, $paymentGateway->getValidTestToken());

        $recentCharges = $paymentGateway->duringCharges(function ($paymentGateway) {
            $paymentGateway->charge(5000, $paymentGateway->getValidTestToken());
            $paymentGateway->charge(4000, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(2, $recentCharges);
        $this->assertEquals([4000, 5000], $recentCharges->all());
    }

    /** @test */
    function charges_with_an_invalid_payment_token_fail()
    {
        $paymentGateway = $this->getPaymentGateway();

        $recentCharges = $paymentGateway->duringCharges(function ($paymentGateway) {
            try {
                $paymentGateway->charge(1500, 'invalid-payment-token');
            } catch (PaymentFailedException $e) {
                return;
            }

            $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException.');
        });

        $this->assertCount(0, $recentCharges);
    }
}
