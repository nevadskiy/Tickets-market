<?php

namespace App\Billing;

use Stripe\Charge;
use Stripe\Token;
use Stripe\Error\InvalidRequest;

class StripePaymentGateway implements PaymentGateway
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }
    
    public function charge($amount, $token)
    {
        try {
            Charge::create([
                'amount' => $amount,
                'source' => $token,
                'currency' => 'usd',
            ], ['api_key' => $this->apiKey]);
        } catch (InvalidRequest $e) {
            throw new PaymentFailedException;
        }
    }

    /**
     * Return ID of card (card token)
     *
     * @return mixed|null
     */
    public function getValidTestToken()
    {
        return Token::create([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 1,
                'exp_year' => date('Y') + 1,
                'cvc' => '123'
            ]
        ], ['api_key' => $this->apiKey])->id;
    }

    public function duringCharges($callback)
    {
        $latestCharge = $this->lastCharge();

        $callback($this);

        return $this->recentChargesSince($latestCharge)->pluck('amount');
    }

    private function lastCharge()
    {
        return Charge::all([
            'limit' => 1,
        ], [
            'api_key' => $this->apiKey
        ])['data'][0];
    }

    private function recentChargesSince($charge = null)
    {
        $recentCharges = Charge::all([
            'ending_before' => $charge ? $charge->id : null,
        ], [
            'api_key' => $this->apiKey
        ])['data'];

        return collect($recentCharges);
    }
}
