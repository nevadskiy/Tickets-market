<?php

namespace Tests\Unit\Mail;

use App\Invitation;
use App\Mail\InvitationEmail;
use Tests\TestCase;

class InvitationEmailTest extends TestCase
{
    /** @test */
    function it_contains_a_link_to_accept_the_invitation()
    {
        $invitation = factory(Invitation::class)->make([
            'email' => 'john@example.com',
            'code' => 'TESTCODE1234'
        ]);

        $email = new InvitationEmail($invitation);

        $this->assertContains(url('/invitations/TESTCODE1234'), $email->render());
    }

    /** @test */
    function it_has_the_correct_subject()
    {
        $invitation = factory(Invitation::class)->make();

        $email = new InvitationEmail($invitation);

        $this->assertEquals('You are invited!', $email->build()->subject);
    }
}
