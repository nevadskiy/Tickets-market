<?php

namespace Tests\Feature;

use App\Facades\InvitationCode;
use App\Invitation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitePromoterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function inviting_a_promoter_via_the_cli()
    {
        // Given
        InvitationCode::shouldReceive('generate')->andReturn('TESTCODE1234');

        // Act phase
        $this->artisan('invite-promoter', ['email' => 'john@example.com']);

        // Asserts
        $this->assertEquals(1, Invitation::count());

        $invitation = Invitation::first();
        $this->assertEquals('john@example.com', $invitation->email);
        $this->assertEquals('TESTCODE1234', $invitation->code);
    }
}
