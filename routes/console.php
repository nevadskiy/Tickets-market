<?php

use App\Facades\InvitationCode;
use App\Invitation;

Artisan::command('invite-promoter {email}', function ($email) {
    Invitation::create([
        'code' => InvitationCode::generate(),
        'email' => $email
    ])->send();
})->describe('Invite a new promoter to create an account');
