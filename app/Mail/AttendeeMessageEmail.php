<?php

namespace App\Mail;

use App\AttendeeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttendeeMessageEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var AttendeeMessage
     */
    public $attendeeMessage;

    /**
     * Create a new attendeeMessage instance.
     *
     * @param AttendeeMessage $attendeeMessage
     */
    public function __construct(AttendeeMessage $attendeeMessage)
    {
        $this->attendeeMessage = $attendeeMessage;
    }

    /**
     * Build the attendeeMessage.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->attendeeMessage->subject)
            ->view('emails.attendee-message');
    }
}
