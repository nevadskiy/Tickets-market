<?php

namespace Tests\Feature;

use App\Invitation;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function viewing_an_unused_invitation()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertOk();
        $this->assertTrue($response->data('invitation')->is($invitation));
    }

    /** @test */
    function viewing_a_used_invitation()
    {
        $this->withExceptionHandling();

        factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create()->id,
            'code' => 'TESTCODE1234',
        ]);

        $this->get('/invitations/TESTCODE1234')->assertNotFound();
    }

    /** @test */
    function viewing_an_invitation_that_does_not_exist()
    {
        $this->withExceptionHandling();

        $this->get('/invitations/TESTCODE1234')->assertNotFound();
    }

    /** @test */
    function registering_with_a_valid_invitation_code()
    {
        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/backstage/concerts');

        $this->assertEquals(1, User::count());

        $user = User::first();
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(Hash::check('secret', $user->password));
        $this->assertTrue($invitation->fresh()->user->is($user));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    function registering_with_a_used_invitation_code()
    {
        $this->withExceptionHandling();

        $invitation = factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create()->id,
            'code' => 'TESTCODE1234',
        ]);

        $this->assertEquals(1, User::count());
        $this->assertTrue($invitation->hasBeenUsed());

        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertNotFound();
        $this->assertEquals(1, User::count());
    }

    /** @test */
    function registering_with_an_invitation_code_that_does_not_exist()
    {
        $this->withExceptionHandling();

        $response = $this->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertNotFound();
        $this->assertEquals(0, User::count());
    }

    /** @test */
    function email_is_required()
    {
        $this->withExceptionHandling();

        factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => '',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    /** @test */
    function email_must_be_an_email()
    {
        $this->withExceptionHandling();

        factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'not-a-email',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    /** @test */
    function email_must_be_unique()
    {
        $this->withExceptionHandling();

        $existingUser = factory(User::class)->create(['email' => 'john@example.com']);
        $this->assertEquals(1, User::count());

        factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'john@example.com',
            'password' => 'secret',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, User::count());
    }

    /** @test */
    function password_is_required()
    {
        $this->withExceptionHandling();

        factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234',
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'john@example.com',
            'password' => '',
            'invitation_code' => 'TESTCODE1234',
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('password');
        $this->assertEquals(0, User::count());
    }
}
