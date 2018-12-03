<?php

namespace App\Http\Controllers;

use App\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvitationsController extends Controller
{
    public function show($code)
    {
        $invitation = Invitation::findByCode($code);

        abort_if($invitation->hasBeenUsed(), Response::HTTP_NOT_FOUND);

        return view('invitations.show', compact('invitation'));
    }
}
