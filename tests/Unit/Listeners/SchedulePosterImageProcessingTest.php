<?php

namespace Tests\Unit\Listeners;

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;
use ConcertFactory;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchedulePosterImageProcessingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_queues_a_job_to_process_a_poster_image_if_a_poster_image_is_present()
    {
        Queue::fake();

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example.png',
        ]);

        event(new ConcertAdded($concert));

        Queue::assertPushed(ProcessPosterImage::class, function ($job) use ($concert) {
            return $job->concert->is($concert);
        });
    }

    /** @test */
    function a_job_is_not_queued_if_a_poster_is_not_present()
    {
        Queue::fake();

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => null,
        ]);

        event(new ConcertAdded($concert));

        Queue::assertNotPushed(ProcessPosterImage::class);
    }
}
