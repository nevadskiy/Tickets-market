<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ForceStripeAccount;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForceStripeAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function users_without_a_stripe_account_are_forced_to_connect_with_stripe()
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => null
        ]));

        $middleware = new ForceStripeAccount;

        $response = $middleware->handle(new Request(), function ($request) {
            $this->fail('Next middleware was called when it should not have been.');
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(route('backstage.stripe-connect.connect'), $response->getTargetUrl());
    }

    /** @test */
    function users_with_a_stripe_account_can_continue()
    {
        $this->be(factory(User::class)->create([
            'stripe_account_id' => 'test_stripe_account_1234'
        ]));

        $middleware = new ForceStripeAccount;

        $request = new Request();

        Mockery::getConfiguration()->allowMockingNonExistentMethods(true);
        $statusMock = Mockery::mock('stdClass')->shouldReceive('called')->once()->getMock();

        $response = $middleware->handle($request, function ($request) use ($statusMock) {
            $statusMock->called();
            return $request;
        });

        $this->assertSame($response, $request);
    }

    /** @test */
    function middleware_is_applied_to_all_backstage_routes()
    {
        $routes = [
            'backstage.concerts.index',
            'backstage.concerts.create',
            'backstage.concerts.edit',
            'backstage.concerts.store',
            'backstage.concerts.update',
            'backstage.published-concerts.store',
            'backstage.published-concert-orders.index',
            'backstage.concert-messages.create',
            'backstage.concert-messages.store',
        ];

        foreach ($routes as $route) {
            $this->assertContains(
                ForceStripeAccount::class,
                Route::getRoutes()->getByName($route)->gatherMiddleware()
            );
        }
    }
}
