<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{
    public function index()
    {
        return view('backstage.concerts.index', [
            'concerts' => Auth::user()->concerts
        ]);
    }

    public function create()
    {
        // TODO: create form
    }

    public function edit($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), Response::HTTP_FORBIDDEN);

        return view('backstage.concerts.edit', compact('concert'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => ['required'],
            'subtitle' => ['nullable'],
            'date' => ['required', 'date'],
            'time' => ['required', 'date_format:g:ia'],
            'venue' => ['required'],
            'venue_address' => ['required'],
            'city' => ['required'],
            'state' => ['required'],
            'zip' => ['required'],
            'ticket_price' => ['required', 'numeric', 'min:5'],
            'ticket_quantity' => ['required', 'numeric', 'min:1'],
            'additional_information' => ['nullable'],
        ]);

        $concert = Auth::user()->concerts()->create([
            'title' => $request->get('title'),
            'subtitle' => $request->get('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [
                $request->get('date'), $request->get('time')
            ])),
            'ticket_price' => $request->get('ticket_price') * 100,
            'venue' => $request->get('venue'),
            'venue_address' => $request->get('venue_address'),
            'city' => $request->get('city'),
            'state' => $request->get('state'),
            'zip' => $request->get('zip'),
            'additional_information' => $request->get('additional_information'),
        ])->addTickets($request->get('ticket_quantity'));

        $concert->publish();

        return redirect()->route('concerts.show', $concert);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => ['required'],
//            'subtitle' => ['nullable'],
//            'date' => ['required', 'date'],
//            'time' => ['required', 'date_format:g:ia'],
//            'venue' => ['required'],
//            'venue_address' => ['required'],
//            'city' => ['required'],
//            'state' => ['required'],
//            'zip' => ['required'],
//            'ticket_price' => ['required', 'numeric', 'min:5'],
//            'ticket_quantity' => ['required', 'numeric', 'min:1'],
//            'additional_information' => ['nullable'],
        ]);

        $concert = Auth::user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), Response::HTTP_FORBIDDEN);

        $concert->update([
            'title' => $request->get('title'),
            'subtitle' => $request->get('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [
                $request->get('date'), $request->get('time')
            ])),
            'ticket_price' => $request->get('ticket_price') * 100,
            'ticket_quantity' => $request->get('ticket_quantity'),
            'venue' => $request->get('venue'),
            'venue_address' => $request->get('venue_address'),
            'city' => $request->get('city'),
            'state' => $request->get('state'),
            'zip' => $request->get('zip'),
            'additional_information' => $request->get('additional_information'),
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
