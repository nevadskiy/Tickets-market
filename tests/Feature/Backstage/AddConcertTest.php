<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddConcertTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->withExceptionHandling();
    }

    /**
     * Set up the page where we came from
     *
     * @param string $url
     * @return $this|TestCase
     */
    public function from($url)
    {
        session()->setPreviousUrl(url($url));

        return $this;
    }

    /** @test */
    function promoters_can_view_the_add_concert_form()
    {
        $this->actingAs(factory(User::class)->create());

        $this->get('/backstage/concerts/create')->assertOk();
    }

    /** @test */
    function guests_cannot_view_the_add_concert_form()
    {
        $this->get('/backstage/concerts/create')
            ->assertRedirect('/login')
            ->assertStatus(302);
    }

    /** @test */
    function adding_a_valid_concert()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $concert = Concert::first();

        $this->assertNotNull($concert);
        $response->assertRedirect("/concerts/{$concert->id}");
        $this->assertEquals('No Warning', $concert->title);
        $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
        $this->assertEquals('You must be 19 years of age to attend this concert.', $concert->additional_information);
        $this->assertEquals(Carbon::parse('2018-11-18 8:00pm'), $concert->date);
        $this->assertEquals('The Mosh Pit', $concert->venue);
        $this->assertEquals('123 Fake St.', $concert->venue_address);
        $this->assertEquals('Laraville', $concert->city);
        $this->assertEquals('ON', $concert->state);
        $this->assertEquals('12345', $concert->zip);
        $this->assertEquals(3250, $concert->ticket_price);
        $this->assertEquals(75, $concert->ticketsRemaining());
    }

    /** @test */
    function guests_cannot_add_new_concerts()
    {
        $response = $this->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302)->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function title_is_required()
    {
        $this->withExceptionHandling();

        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => '',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('title');
    }

    /** @test */
    function subtitle_is_optional()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => '',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $concert = Concert::first();

        $this->assertNotNull($concert);
        $response->assertRedirect("/concerts/{$concert->id}");
        $this->assertEquals('No Warning', $concert->title);
        $this->assertNull($concert->subtitle);
        $this->assertEquals('You must be 19 years of age to attend this concert.', $concert->additional_information);
        $this->assertEquals(Carbon::parse('2018-11-18 8:00pm'), $concert->date);
        $this->assertEquals('The Mosh Pit', $concert->venue);
        $this->assertEquals('123 Fake St.', $concert->venue_address);
        $this->assertEquals('Laraville', $concert->city);
        $this->assertEquals('ON', $concert->state);
        $this->assertEquals('12345', $concert->zip);
        $this->assertEquals(3250, $concert->ticket_price);
        $this->assertEquals(75, $concert->ticketsRemaining());
    }

    /** @test */
    function additional_information_is_optional()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => '',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $concert = Concert::first();

        $this->assertNotNull($concert);
        $response->assertRedirect("/concerts/{$concert->id}");
        $this->assertEquals('No Warning', $concert->title);
        $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
        $this->assertNull($concert->additional_information);
        $this->assertEquals(Carbon::parse('2018-11-18 8:00pm'), $concert->date);
        $this->assertEquals('The Mosh Pit', $concert->venue);
        $this->assertEquals('123 Fake St.', $concert->venue_address);
        $this->assertEquals('Laraville', $concert->city);
        $this->assertEquals('ON', $concert->state);
        $this->assertEquals('12345', $concert->zip);
        $this->assertEquals(3250, $concert->ticket_price);
        $this->assertEquals(75, $concert->ticketsRemaining());
    }

    /** @test */
    function time_must_be_a_valid_time()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => 'invalid-time',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('time');
    }

    /** @test */
    function date_must_be_a_valid_date()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => 'invalid-date',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('date');
    }

    /** @test */
    function venue_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => '',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('venue');
    }

    /** @test */
    function city_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => '',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('city');
    }

    /** @test */
    function zip_code_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('zip');
    }

    /** @test */
    function state_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => '',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('state');
    }

    /** @test */
    function ticket_price_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    function ticket_price_must_be_numeric()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => 'not numeric price',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    function ticket_price_must_be_at_least_5_dollars()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '4.99',
            'ticket_quantity' => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    function ticket_quantity_must_be_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '35.00',
            'ticket_quantity' => '',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }

    /** @test */
    function ticket_quantity_must_be_numeric()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '35.00',
            'ticket_quantity' => 'not a numeric quantity',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2018-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '35.00',
            'ticket_quantity' => '0',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }
}
