<?php

namespace App\Billing;

use Stripe\Charge as StripeCharge;
use Stripe\Token;
use Stripe\Error\InvalidRequest;

class StripePaymentGateway implements PaymentGateway
{
    public const TEST_CARD_NUMBER = '4242424242424242';

    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }
    
    public function charge($amount, $token)
    {
        try {
            $data = StripeCharge::create([
                'amount' => $amount,
                'source' => $token,
                'currency' => 'usd',
            ], ['api_key' => $this->apiKey]);

            return new Charge([
                'amount' => $data['amount'],
                'card_last_four' => $data['source']['last4'],
            ]);
        } catch (InvalidRequest $e) {
            throw new PaymentFailedException;
        }
    }

    /**
     * Return ID of card (card token)
     *
     * @param string $cardNumber
     * @return mixed|null
     */
    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        return Token::create([
            'card' => [
                'number' => $cardNumber,
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

        return $this->recentChargesSince($latestCharge)->map(function ($data) {
            return new Charge([
                'amount' => $data['amount'],
                'card_last_four' => $data['source']['last4'],
            ]);
        });
    }

    private function lastCharge()
    {
        return StripeCharge::all([
            'limit' => 1,
        ], [
            'api_key' => $this->apiKey
        ])['data'][0];
    }

    private function recentChargesSince($charge = null)
    {
        $recentCharges = StripeCharge::all([
            'ending_before' => $charge ? $charge->id : null,
        ], [
            'api_key' => $this->apiKey
        ])['data'];

        return collect($recentCharges);
    }
}
