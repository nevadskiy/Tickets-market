<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $guarded = [];

    public static function findByCode($code)
    {
        return static::whereCode($code)->firstOrFail();
    }

    public function hasBeenUsed(): bool
    {
        return $this->user_id !== null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
