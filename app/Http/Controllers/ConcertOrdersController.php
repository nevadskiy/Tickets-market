<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Reservation;
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
            $tickets = $concert->reserveTickets($request['ticket_quantity']);
            $reservation = new Reservation($tickets);

            $this->paymentGateway->charge($reservation->totalCost(), $request['payment_token']);

            $order = Order::forTickets($tickets, $request['email'], $reservation->totalCost());

            return response()->json($order, Response::HTTP_CREATED);
        } catch (PaymentFailedException $e) {
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (NotEnoughTicketsException $e) {
            return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
