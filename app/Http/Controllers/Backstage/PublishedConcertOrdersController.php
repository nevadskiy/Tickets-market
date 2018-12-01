<?php

namespace App\Http\Controllers\Backstage;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PublishedConcertOrdersController extends Controller
{
    public function index(int $concertId)
    {
        $concert = Auth::user()->concerts()->published()->findOrFail($concertId);

        return view('backstage.published-concert-orders.index', compact('concert'));
    }
}
