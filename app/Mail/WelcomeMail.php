<?php

namespace App\Mail;

use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $user;
    
    public function __construct(Account $user)
    {
        $this->user = $user;
    }
    
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('hello@mysite.com', 'My Website'),
            subject: 'خوش آمدید!',
        );
    }
    
    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            //text: 'emails.welcome_plain'
        );
    }
}