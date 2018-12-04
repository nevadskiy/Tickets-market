<?php

namespace App\Billing;

interface PaymentGateway
{
    public function charge($amount, $token, $accountId);

    public function getValidTestToken();

    public function duringCharges($callback);
}
