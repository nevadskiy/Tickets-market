<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ConcertsController extends Controller
{
    public function create()
    {
        // TODO: create form
    }

    public function store(Request $request)
    {
        $concert = Concert::create([
            'title' => $request['title'],
            'subtitle' => $request['subtitle'],
            'date' => Carbon::parse(vsprintf('%s %s', [
                $request['date'], $request['time']
            ])),
            'ticket_price' => $request['ticket_price'] * 100,
            'venue' => $request['venue'],
            'venue_address' => $request['venue_address'],
            'city' => $request['city'],
            'state' => $request['state'],
            'zip' => $request['zip'],
            'additional_information' => $request['additional_information'],
        ])->addTickets($request['ticket_quantity']);

        return redirect()->route('concerts.show', $concert);
    }
}
