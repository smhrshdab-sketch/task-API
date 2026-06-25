<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Account $user){
        $this->user = $user; // داده‌های مربوط به رویداد
    }
}
