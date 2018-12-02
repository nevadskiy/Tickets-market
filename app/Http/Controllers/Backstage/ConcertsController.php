<?php

namespace App\Http\Controllers\Backstage;

use App\Events\ConcertAdded;
use App\Http\Controllers\Controller;
use App\NullFile;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ConcertsController extends Controller
{
    public function index()
    {
        return view('backstage.concerts.index', [
            'publishedConcerts' => Auth::user()->concerts->filter->isPublished(),
            'unpublishedConcerts' => Auth::user()->concerts->reject->isPublished(),
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
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'additional_information' => ['nullable'],
            'poster_image' => ['nullable', 'image', Rule::dimensions()->minWidth(400)->ratio(8.5 / 11)],
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
            'ticket_quantity' => $request->get('ticket_quantity'),
            'poster_image_path' => $request->file('poster_image', new NullFile())->store('posters', 's3')
        ]);

        event(new ConcertAdded($concert));

        return redirect()->route('backstage.concerts.index');
    }

    public function update(Request $request, $id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), Response::HTTP_FORBIDDEN);

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
            'ticket_quantity' => ['required', 'integer', 'min:1'],
            'additional_information' => ['nullable'],
        ]);

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
