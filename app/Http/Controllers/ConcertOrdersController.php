<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{
    /**
     * @var PaymentGateway
     */
    private $paymentGateway;

    /**
     * ConcertOrdersController constructor.
     * @param PaymentGateway $paymentGateway
     */
    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }
    
    public function store(Request $request, Concert $concert)
    {
        $this->validate($request, [
            'email' => 'required'
        ]);

        $this->paymentGateway->charge($request['ticket_quantity'] * $concert->ticket_price, $request['payment_token']);

        $concert->orderTickets($request['email'], $request['ticket_quantity']);

        return response()->json([], 201);
    }
}
