<?php

namespace Tests\Feature\Backstage;

use App\Order;
use App\Ticket;
use App\User;
use Carbon\Carbon;
use ConcertFactory;
use OrderFactory;
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
    function a_promoter_can_view_the_10_most_recent_orders_for_their_concert()
    {
        $user = factory(User::class)->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $oldOrder = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-11 days')]);
        $recentOrder1 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-10 days')]);
        $recentOrder2 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-9 days')]);
        $recentOrder3 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-8 days')]);
        $recentOrder4 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-7 days')]);
        $recentOrder5 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-6 days')]);
        $recentOrder6 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-5 days')]);
        $recentOrder7 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-4 days')]);
        $recentOrder8 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-3 days')]);
        $recentOrder9 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-2 days')]);
        $recentOrder10 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('-1 days')]);

        $response = $this->actingAs($user)->get("/backstage/published-concerts/{$concert->id}/orders");

        $response->data('orders')->assertNotContains($oldOrder);
        $response->data('orders')->assertEquals([
            $recentOrder10,
            $recentOrder9,
            $recentOrder8,
            $recentOrder7,
            $recentOrder6,
            $recentOrder5,
            $recentOrder4,
            $recentOrder3,
            $recentOrder2,
            $recentOrder1,
        ]);
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
