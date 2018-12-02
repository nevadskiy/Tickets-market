<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessPosterImage;
use ConcertFactory;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function it_resizes_the_poster_image_to_600px_width_and_776px_height()
    {
        Storage::fake('s3');

        Storage::disk('s3')->put(
            'posters/example-poster.jpg',
            file_get_contents(base_path('tests/fixtures/full-size-poster.jpg'))
        );

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.jpg'
        ]);

        ProcessPosterImage::dispatch($concert);

        $resizedImage = Storage::disk('s3')->get('posters/example-poster.jpg');
        list($width, $height) = getimagesizefromstring($resizedImage);

        $this->assertEquals(600, $width);
        $this->assertEquals(776, $height);

        $resizedImageContents = Storage::disk('s3')->get('posters/example-poster.jpg');
        $controlImageContents = file_get_contents(base_path('tests/fixtures/resized-poster.jpg'));

        $this->assertEquals($controlImageContents, $resizedImageContents);
    }

    /** @test */
    function it_optimizes_the_poster_image()
    {
        Storage::fake('s3');

        Storage::disk('s3')->put(
            'posters/example-poster.jpg',
            file_get_contents(base_path('tests/fixtures/small-poster.jpg'))
        );

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.jpg'
        ]);

        ProcessPosterImage::dispatch($concert);

        $optimizedImageSize = Storage::disk('s3')->size('posters/example-poster.jpg');
        $originalSize = filesize(base_path('tests/fixtures/small-poster.jpg'));

        $this->assertLessThan($originalSize, $optimizedImageSize);

        $optimizedImageContents = Storage::disk('s3')->get('posters/example-poster.jpg');
        $controlImageContents = file_get_contents(base_path('tests/fixtures/optimized-poster.jpg'));

        $this->assertEquals($controlImageContents, $optimizedImageContents);
    }
}
