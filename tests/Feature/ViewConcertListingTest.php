<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function user_can_view_a_concert_listing()
    {
        /** Arrange */
        // Create a concert
        $concert = Concert::create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('December 13, 2018 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'additional_information' => 'For ticket, call (555) 555-5555.'
        ]);

        /** Art */
        // View the concert listing
        $response = $this->get('/concerts/' . $concert->id);

        /** Assert */
        // See the concert
        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('December 13, 2018');
        $response->assertSee('8:00pm');
        $response->assertSee('32.50');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville, ON 17916');
        $response->assertSee('For ticket, call (555) 555-5555.');
    }

    /** @test */
    function user_cannot_view_unpublished_concert_listing()
    {
        $concert = factory(Concert::class)->create([
            'published_at' => null
        ]);

        $this->get('/concerts/' . $concert->id)
            ->assertStatus(404);
    }
}
