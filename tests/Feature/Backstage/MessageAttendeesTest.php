<?php

namespace Tests\Feature\Backstage;

use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use App\User;
use ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MessageAttendeesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_view_the_message_form_for_their_own_concert()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/create");

        $response->assertOk();
        $response->assertViewHas('concert', $concert);
    }

    /** @test */
    function a_promoter_cannot_view_the_message_form_for_another_concert()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => factory(User::class)->create()->id
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/create");

        $response->assertNotFound();
    }

    /** @test */
    function a_guest_cannot_view_the_message_form_for_any_concert()
    {
        $this->withExceptionHandling();

        $concert = ConcertFactory::createPublished();

        $this->get("/backstage/concerts/{$concert->id}/messages/create")->assertRedirect('/login');
    }

    /** @test */
    function a_promoter_can_send_a_new_message()
    {
        Queue::fake();

        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/create");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();
        $this->assertEquals($concert->id, $message->concert_id);
        $this->assertEquals('My subject', $message->subject);
        $this->assertEquals('My message', $message->message);

        Queue::assertPushed(SendAttendeeMessage::class, function ($job) use ($message) {
            return $job->message->is($message);
        });
    }

    /** @test */
    function a_promoter_cannot_send_a_new_message_for_other_concerts()
    {
        Queue::fake();

        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertNotFound();
        $this->assertEquals(0, AttendeeMessage::count());

        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    function a_guest_cannot_send_a_new_message_for_any_concerts()
    {
        Queue::fake();

        $this->withExceptionHandling();

        $concert = ConcertFactory::createPublished();

        $response = $this->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect('/login');
        $this->assertEquals(0, AttendeeMessage::count());

        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    function subject_is_required()
    {
        Queue::fake();

        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->from("/backstage/concerts/{$concert->id}/messages")
            ->actingAs($user)
            ->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => '',
            'message' => 'My message',
        ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages");
        $response->assertSessionHasErrors('subject');
        $this->assertEquals(0, AttendeeMessage::count());

        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    function message_is_required()
    {
        Queue::fake();

        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->from("/backstage/concerts/{$concert->id}/messages")
            ->actingAs($user)
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'subject' => 'My subject',
                'message' => '',
            ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages");
        $response->assertSessionHasErrors('message');
        $this->assertEquals(0, AttendeeMessage::count());

        Queue::assertNotPushed(SendAttendeeMessage::class);
    }
}
