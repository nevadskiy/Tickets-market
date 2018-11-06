<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
        if (!$concert->isPublished()) {
            return response()->json([], Response::HTTP_NOT_FOUND);
        }

        $this->validate($request, [
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'payment_token' => 'required'
        ]);

        try {
            $amount = $request['ticket_quantity'] * $concert->ticket_price;

            $order = $concert->orderTickets($request['email'], $request['ticket_quantity']);
            $this->paymentGateway->charge($amount, $request['payment_token']);

            return response()->json([], Response::HTTP_CREATED);
        } catch (PaymentFailedException $e) {
            $order->cancel();
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
