<?php

namespace App\Jobs;

use App\Concert;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessPosterImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Concert
     */
    public $concert;

    /**
     * Create a new job instance.
     *
     * @param Concert $concert
     */
    public function __construct(Concert $concert)
    {
        $this->concert = $concert;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $imageContents = Storage::disk('s3')->get($this->concert->poster_image_path);
        $image = Image::make($imageContents);

        $processedImageContents = $image->resize(600, 776)
            ->limitColors(255)
            ->encode();

        Storage::disk('s3')->put($this->concert->poster_image_path, $processedImageContents);
    }
}
