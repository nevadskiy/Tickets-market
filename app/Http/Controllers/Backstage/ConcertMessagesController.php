<?php

namespace App\Http\Controllers\Backstage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        return view('backstage.concert-messages.create', compact('concert'));
    }

    public function store(Request $request, $id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        $this->validate($request, [
            'subject' => ['required'],
            'message' => ['required'],
        ]);

        $concert->attendeeMessages()->create($request->only(['subject', 'message']));

        return redirect()->route('backstage.concert-messages.create', $concert)
            ->with('flash', 'You message has been sent');
    }
}
