<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class PublishedConcertsController extends Controller
{
    public function store(Request $request)
    {
        $concert = Concert::find($request->get('concert_id'));

        if ($concert->isPublished()) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $concert->publish();

        return redirect()->route('backstage.concerts.index');
    }
}
