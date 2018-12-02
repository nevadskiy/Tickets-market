<?php

namespace App\Events;

use App\Concert;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ConcertAdded
{
    use Dispatchable, SerializesModels;

    /**
     * @var Concert
     */
    public $concert;

    /**
     * Create a new event instance.
     *
     * @param Concert $concert
     */
    public function __construct(Concert $concert)
    {
        $this->concert = $concert;
    }
}
