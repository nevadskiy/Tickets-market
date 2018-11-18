<?php

namespace App\Billing;

class Charge
{
    /**
     * @var
     */
    private $data;

    /**
     * Charge constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function cardLastFour()
    {
        return $this->data['card_last_four'];
    }

    /**
     * @return mixed
     */
    public function amount()
    {
        return $this->data['amount'];
    }
}
