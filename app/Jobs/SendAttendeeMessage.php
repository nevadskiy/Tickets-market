<?php

namespace App\Jobs;

use App\AttendeeMessage;
use App\Mail\AttendeeMessageEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendAttendeeMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var AttendeeMessage
     */
    public $message;

    /**
     * Create a new job instance.
     *
     * @param AttendeeMessage $message
     */
    public function __construct(AttendeeMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->message->withRecipients(function ($recipients) {
            $recipients->each(function ($recipient) {
                Mail::to($recipient)->queue(new AttendeeMessageEmail($this->message));
            });
        });
    }
}
