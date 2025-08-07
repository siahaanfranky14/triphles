<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $kode;
    public $email;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($kode, $email)
    {
        $this->kode = $kode;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Forgot Password')
            ->view('emails.forgotpassword', [
                'kode' => $this->kode,
                'email' => $this->email
            ]);
    }
}
