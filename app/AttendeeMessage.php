<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendeeMessage extends Model
{
    protected $guarded = [];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function orders()
    {
        return $this->concert->orders();
    }

    public function withRecipients(callable $callback, int $chunkSize = 20)
    {
        $this->orders()->chunk($chunkSize, function ($orders) use ($callback) {
            $callback($orders->pluck('email'));
        });
    }
}
