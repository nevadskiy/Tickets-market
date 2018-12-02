<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\Events\ConcertAdded;
use App\User;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
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

    private function validAttributes($overrides = [])
    {
        return array_merge([
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
        ], $overrides);
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
    function promoters_can_add_a_valid_concert()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($user = factory(User::class)->create());

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
        $this->assertTrue($concert->user->is($user));
        $this->assertFalse($concert->isPublished());

        $response->assertRedirect(route('backstage.concerts.index'));
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
        $this->assertEquals(75, $concert->ticket_quantity);
        $this->assertEquals(0, $concert->ticketsRemaining());
    }

    /** @test */
    function guests_cannot_add_new_concerts()
    {
        $response = $this->post('/backstage/concerts', $this->validAttributes());

        $response->assertStatus(302)->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function title_is_required()
    {
        $this->withExceptionHandling();

        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'title' => ''
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('title');
    }

    /** @test */
    function subtitle_is_optional()
    {
        $this->actingAs($user = factory(User::class)->create());

        $response = $this->post('/backstage/concerts', $this->validAttributes([
            'subtitle' => ''
        ]));


        $this->assertNotNull($concert = Concert::first());
        $this->assertTrue($concert->user->is($user));
        $response->assertRedirect(route('backstage.concerts.index'));
        $this->assertNull($concert->subtitle);
    }

    /** @test */
    function additional_information_is_optional()
    {
        $this->actingAs($user = factory(User::class)->create());

        $response = $this->post('/backstage/concerts', $this->validAttributes([
            'additional_information' => '',
        ]));

        $concert = Concert::first();

        $this->assertTrue($concert->user->is($user));
        $this->assertNotNull($concert);
        $response->assertRedirect(route('backstage.concerts.index'));
        $this->assertEquals('No Warning', $concert->title);
        $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
        $this->assertNull($concert->additional_information);
    }

    /** @test */
    function time_must_be_a_valid_time()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'time' => 'invalid-time',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('time');
    }

    /** @test */
    function date_must_be_a_valid_date()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'date' => 'invalid-date',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('date');
    }

    /** @test */
    function venue_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'venue' => '',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('venue');
    }

    /** @test */
    function city_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'city' => '',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('city');
    }

    /** @test */
    function zip_code_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'zip' => '',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('zip');
    }

    /** @test */
    function state_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'state' => '',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('state');
    }

    /** @test */
    function ticket_price_is_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'ticket_price' => '',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    function ticket_price_must_be_numeric()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'ticket_price' => 'not numeric price',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    function ticket_price_must_be_at_least_5_dollars()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'ticket_price' => '4.99',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_price');
    }

    /** @test */
    function ticket_quantity_must_be_required()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'ticket_quantity' => '',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }

    /** @test */
    function ticket_quantity_must_be_numeric()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'ticket_quantity' => 'not a numeric quantity',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }

    /** @test */
    function ticket_quantity_must_be_at_least_1()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'ticket_quantity' => '0',
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $this->assertEquals(0, Concert::count());
        $response->assertSessionHasErrors('ticket_quantity');
    }

    /** @test */
    function poster_image_is_uploaded_if_included()
    {
        Storage::fake('s3');
        $user = factory(User::class)->create();
        $file = File::image('concert-poster.png', 850, 1100);

        $this->actingAs($user)->post('/backstage/concerts', $this->validAttributes([
            'poster_image' => $file,
        ]));

        tap(Concert::first(), function ($concert) use ($file) {
            // 1. Assert path is stored (even can be hashed)
            $this->assertNotNull($concert->poster_image_path);

            // 2. Assert filesystem has a real file with stored path
            Storage::disk('s3')->assertExists($concert->poster_image_path);

            // 3. Assert that stored file is the same file that posted
            $this->assertFileEquals($file->getPathname(), Storage::disk('s3')->path($concert->poster_image_path));
        });
    }

    /** @test */
    function poster_image_must_be_an_image()
    {
        Storage::fake('s3');
        $user = factory(User::class)->create();
        $file = File::create('not-a-poster.pdf');

        $response = $this->actingAs($user)->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'poster_image' => $file,
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function poster_image_must_be_at_least_400px_wide()
    {
        Storage::fake('s3');
        $user = factory(User::class)->create();
        $file = File::image('poster.png', 399, 516);

        $response = $this->actingAs($user)->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'poster_image' => $file,
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function poster_image_must_have_letter_aspect_ratio()
    {
        Storage::fake('s3');
        $user = factory(User::class)->create();
        $file = File::image('poster.png', 851, 1100);

        $response = $this->actingAs($user)->from('/backstage/concerts/create')->post('/backstage/concerts', $this->validAttributes([
            'poster_image' => $file,
        ]));

        $response->assertRedirect('/backstage/concerts/create');
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    function poster_image_is_optional()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validAttributes([
            'poster_image' => null,
        ]));

        tap(Concert::first(), function ($concert) use ($user, $response) {
            $response->assertRedirect('/backstage/concerts');
            $this->assertNull($concert->poster_image_path);
        });
    }

    /** @test */
    function an_event_is_fired_when_a_concert_is_added()
    {
        Event::fake([ConcertAdded::class]);

        $user = factory(User::class)->create();

        $this->actingAs($user)->post('/backstage/concerts', $this->validAttributes());

        Event::assertDispatched(ConcertAdded::class, function ($event) {
            return $event->concert->is(Concert::firstOrFail());
        });
    }
}
