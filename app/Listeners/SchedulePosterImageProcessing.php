<?php

namespace App\Listeners;

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;
use Illuminate\Foundation\Bus\DispatchesJobs;

class SchedulePosterImageProcessing
{
    use DispatchesJobs;

    /**
     * Handle the event.
     *
     * @param  ConcertAdded  $event
     * @return void
     */
    public function handle(ConcertAdded $event)
    {
        if ($event->concert->hasPoster()) {
            $this->dispatch(new ProcessPosterImage($event->concert));
        }
    }
}
