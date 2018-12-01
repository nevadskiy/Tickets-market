<?php

namespace Tests\Feature\Backstage;

use App\User;
use ConcertFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertOk();
        $response->assertViewHas('concert', $concert);
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_unpublished_concert()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertNotFound();
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_another_published_concert()
    {
        $this->withExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->assertNotFound();
    }

    /** @test */
    function a_guest_cannot_view_the_orders_of_any_published_concert()
    {
        $this->withExceptionHandling();

        $concert = ConcertFactory::createPublished();

        $this->get("/backstage/published-concerts/{$concert->id}/orders")->assertRedirect('/login');
    }
}
