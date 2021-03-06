<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditConcertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    private function validAttributes($overrides = [])
    {
        return array_merge([
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12 5:00pm',
            'time' => '5:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
            'ticket_quantity' => '10',
        ], $overrides);
    }

    private function oldAttributes($overrides = [])
    {
        return array_merge([
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2018-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
            'ticket_quantity' => 5,
        ], $overrides);
    }

    /** @test */
    function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertOk();
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function promoters_cannot_view_the_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->state('published')->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertForbidden();
    }

    /** @test */
    function promoters_cannot_view_the_edit_form_for_other_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit")->assertNotFound();
    }

    /** @test */
    function promoters_see_a_404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)->get("/backstage/concerts/666/edit")->assertNotFound();
    }

    /** @test */
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert()
    {
        $otherUser = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $otherUser->id]);

        $this->get("/backstage/concerts/{$concert->id}/edit")->assertRedirect('/login');
    }

    /** @test */
    function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exists()
    {
        $this->get("/backstage/concerts/999/edit")->assertRedirect('/login');
    }

    /** @test */
    function promoters_can_edit_their_own_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2018-01-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->put("/backstage/concerts/{$concert->id}", [
            'title' => 'New title',
            'subtitle' => 'New subtitle',
            'additional_information' => 'New additional information',
            'date' => '2018-12-12',
            'time' => '5:00pm',
            'venue' => 'New venue',
            'venue_address' => 'New address',
            'city' => 'New city',
            'state' => 'New state',
            'zip' => '99999',
            'ticket_price' => '72.50',
            'ticket_quantity' => '10',
        ]);

        $response->assertRedirect('/backstage/concerts');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('New title', $concert->title);
            $this->assertEquals('New subtitle', $concert->subtitle);
            $this->assertEquals('New additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2018-12-12 5:00pm'), $concert->date);
            $this->assertEquals('New venue', $concert->venue);
            $this->assertEquals('New address', $concert->venue_address);
            $this->assertEquals('New city', $concert->city);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals(10, $concert->ticket_quantity);
        });
    }

    /** @test */
    function promoters_cannot_edit_other_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $concert = factory(Concert::class)->create($this->oldAttributes([
            'user_id' => $otherUser->id,
        ]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->put("/backstage/concerts/{$concert->id}", $this->validAttributes());

        $response->assertNotFound();

        $this->assertArraySubset($this->oldAttributes([
            'user_id' => $otherUser->id
        ]), $concert->fresh()->getAttributes());
    }

    /** @test */
    function promoters_cannot_edit_published_concerts()
    {
        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->state('published')->create($this->oldAttributes([
            'user_id' => $user->id,
        ]));

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->put("/backstage/concerts/{$concert->id}", $this->validAttributes());

        $response->assertForbidden();

        $this->assertArraySubset($this->oldAttributes(), $concert->fresh()->getAttributes());
    }

    /** @test */
    function guests_cannot_edit_concerts()
    {
        $concert = factory(Concert::class)->create();

        $this->assertFalse($concert->isPublished());

        $this->put("/backstage/concerts/{$concert->id}")->assertRedirect('/login');
    }

    /** @test */
    function title_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'title' => 'Old title',
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->put("/backstage/concerts/{$concert->id}", $this->validAttributes([
            'title' => ''
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('Old title', $concert->title);
        });
    }

    /** @test */
    function ticket_quantity_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->put("/backstage/concerts/{$concert->id}", $this->validAttributes([
            'ticket_quantity' => '',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }

    /** @test */
    function ticket_quantity_must_be_an_integer()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->put("/backstage/concerts/{$concert->id}", $this->validAttributes([
            'ticket_quantity' => '7.8',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 5,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")->put("/backstage/concerts/{$concert->id}", $this->validAttributes([
            'ticket_quantity' => '0',
        ]));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals(5, $concert->ticket_quantity);
        });
    }
}
