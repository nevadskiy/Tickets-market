<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class PublishConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_publish_their_own_concert()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->state('unpublished')->create([
            'user_id' => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/backstage/concerts');

        tap($concert->fresh(), function ($concert) {
            $this->assertTrue($concert->isPublished());
            $this->assertEquals(3, $concert->ticketsRemaining());
        });
    }

    /** @test */
    function a_concert_can_only_be_published_once()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertEquals(3, $concert->fresh()->ticketsRemaining());
    }

    /** @test */
    function a_promoter_cannot_publish_other_concerts()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->state('unpublished')->create([
            'user_id' => $otherUser->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertNotFound();

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertFalse($concert->isPublished());
            $this->assertEquals(0, $concert->ticketsRemaining());
        });
    }

    /** @test */
    function a_guest_cannot_publish_concerts()
    {
        $this->withExceptionHandling();

        $concert = factory(Concert::class)->state('unpublished')->create([
            'ticket_quantity' => 3
        ]);

        $response = $this->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/login');

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertFalse($concert->isPublished());
            $this->assertEquals(0, $concert->ticketsRemaining());
        });
    }

    /** @test */
    function concerts_that_do_not_exists_cannot_be_published()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => 666,
        ]);

        $response->assertNotFound();
    }
}
