<?php

namespace App\Http\Controllers;

use App\Concert;
use Illuminate\Http\Request;

class ConcertController extends Controller
{
    public function show(Concert $concert)
    {
        return view('concerts.show', compact('concert'));
    }
}
