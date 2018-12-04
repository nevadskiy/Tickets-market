<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway
{
    public const TEST_CARD_NUMBER = '4242424242424242';

    private $charges;
    private $tokens;

    /** @var \Closure */
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        $token = 'fake-tok_' . str_random(24);

        $this->tokens[$token] = $cardNumber;

        return $token;
    }

    public function charge($amount, $token, $accountId)
    {
        $this->invokeBeforeCallback();

        if (!$this->tokens->has($token)) {
            throw new PaymentFailedException;
        }

        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
            'destination' => $accountId,
        ]);
    }

    public function duringCharges($callback)
    {
        $currentCharges = $this->charges->count();

        $callback($this);

        return $this->charges->slice($currentCharges)->reverse()->values();
    }

    public function totalCharges()
    {
        return $this->charges->map->amount()->sum();
    }

    public function totalChargesFor($accountId)
    {
        return $this->charges->filter(function ($charge) use ($accountId) {
            return $charge->destination() === $accountId;
        })->map->amount()->sum();
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }

    protected function invokeBeforeCallback(): void
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback->__invoke($this);
        }
    }
}
