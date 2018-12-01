<?php

use App\Concert;

class ConcertFactory
{
    public static function createPublished($attributes = [])
    {
        return factory(Concert::class)->create($attributes)->publish();
    }
}
