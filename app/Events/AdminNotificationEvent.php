<?php

namespace App\Events;

class AdminNotificationEvent extends Event
{

    public $notification;
    public $notify_type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($notification , $notify_type = 'normal')
    {
        //
        $this->notification = $notification;
        $this->notify_type = $notify_type;
    }
}
