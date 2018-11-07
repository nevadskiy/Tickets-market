<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];

    protected $casts = [
        'date' => 'datetime'
    ];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function scopePublished(Builder $query)
    {
        return $query->whereNotNull('published_at');
    }

    public function isPublished()
    {
        return $this->published_at !== null && $this->published_at < now();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function hasOrderFor($customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->exists();
    }

    public function ordersFor($customerEmail)
    {
        return $this->orders()->where('email', $customerEmail)->get();
    }

    public function orderTickets($email, $ticketQuantity)
    {
        $tickets = $this->tickets()->available()->take($ticketQuantity)->get();

        if ($tickets->count() < $ticketQuantity) {
            throw new NotEnoughTicketsException();
        }

        $order = $this->orders()->create(['email' => $email]);

        foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }

        return $order;
    }

    public function addTickets($quantity)
    {
        foreach (range(1, $quantity) as $i) {
            $this->tickets()->create([]);
        }

        return $this;
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
