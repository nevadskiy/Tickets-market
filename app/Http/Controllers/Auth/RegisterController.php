<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Invitation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $invitation = Invitation::findByCode($request->get('invitation_code'));

        abort_if($invitation->hasBeenUsed(), Response::HTTP_NOT_FOUND);

        $this->validate($request, [
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required'],
        ]);

        $user = User::create([
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password'))
        ]);

        $invitation->update([
            'user_id' => $user->id
        ]);

        Auth::login($user);

        return redirect()->route('backstage.concerts.index');
    }
}
