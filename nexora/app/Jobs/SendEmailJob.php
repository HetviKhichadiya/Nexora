<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Queueable;
    protected $email;
    protected $mail_data;

    /**
     * Create a new job instance.
     */
    public function __construct($email, $mail_data)
    {
        $this->email = $email;
        $this->mail_data = $mail_data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new \App\Mail\SendMail($this->mail_data));
    }
}
