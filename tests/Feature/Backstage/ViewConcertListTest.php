<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewConcertListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function guests_cannot_view_a_promoters_concert_list()
    {
        $this->withExceptionHandling()
            ->get('/backstage/concerts')
            ->assertRedirect('/login');
    }

    /** @test */
    function promoters_can_only_view_a_list_of_their_own_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $concertA = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertB = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertC = factory(Concert::class)->create(['user_id' => $otherUser->id]);
        $concertD = factory(Concert::class)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertOk();
        $response->data('concerts')->assertContains($concertA);
        $response->data('concerts')->assertContains($concertB);
        $response->data('concerts')->assertContains($concertD);
        $response->data('concerts')->assertNotContains($concertC);
    }
}
