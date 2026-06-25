<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
{
    Log::info('Sending welcome email to: ' . $event->user->email);
    
    try {
        Mail::to($event->user->email)->send(new WelcomeMail($event->user));
        Log::info('✅ Email sent successfully to: ' . $event->user->email);
    } catch (\Exception $e) {
        Log::error('❌ Failed to send email: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
    }
}
}
